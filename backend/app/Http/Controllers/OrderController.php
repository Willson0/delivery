<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderCreateRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function create (OrderCreateRequest $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $backendRequest = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/users/get");

        $data = $backendRequest->json()["data"];
        $response = collect($data)->firstWhere('id', $user->id);

        $data = $request->validated();
        $reqData = $data["address"];
        $reqData["paymentType"] = $data["paymentType"];

        $cache = Cache::get("data");
        if (!$cache) {
            $request = Http::withHeaders([
                'Authentication' => env("INTERNAL_TOKEN"),
            ])->post("https://kfsamara.ru/api/products/get");

            $products = array_values(array_filter($request->json()["data"], function($el) {
                return $el["status"] === 1;
            }));
        } else $products = $cache["products"];
        $user = array_merge((array) $response, (array) $cache, $user->toArray());

        $cart = json_decode($user["cart"], 1);
        $bonusSum = 0;
        $rublesSum = 0;

        $productsString = "";
        $number = 1;

        $resCart = [];

        foreach ($cart as $item) {
            $filter = array_filter($products, function ($pr) use ($item) {
                return $pr['id'] === $item["id"];
            });
            $product = reset($filter);

            if ($product["priceDiscount"] != 0) $price = $product["priceDiscount"];
            else $price = $product["price"];

            if ($item["isBonus"]) $bonusSum += $price*$item["count"];
            else $rublesSum += $price*$item["count"];

            $productsString .= $number . ". " . $product["name"] . ": " . $price
                . ($item["isBonus"] ? ' бонусов' : '₽') . ' (' . $item["count"] . "шт)\r\n";

            if (!$item["isBonus"])
                $resCart[] = [
                    "id" => $product["id"],
                    "name" => $product["name"],
                    "price" => $price,
                    "sectionId" => $product["sectionId"],
                    "sectionName" => $product["sectionName"],
                    "quantity" => $item["count"],
                ];
        }
        if ($bonusSum > $user["bonus"]) abort(400, "Недостаточное количество бонусов для оплаты");

        if ($user["allergens"] != null) {
            $allergens = 'Аллергены: ';
            foreach (json_decode($user["allergens"], 1) as $allergen) {
                $allergens .= $allergen . ", ";
            }
            $allergens = substr($allergens, 0, -2);
        }

        $req = [
            "data" => array_merge((array) $reqData, [
                "phone" => $user["phone"],
                "price" => $rublesSum + 200,
                "priceFull" => $rublesSum + 200,
                "products" => $productsString,
                "comment" => $allergens ?? '',
            ]),
            "cart" => $resCart,
        ];

        $backendRequest = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/orders/create", $req);
        $data = $backendRequest->json()["data"];

        $plusBonus = $rublesSum * (Setting::where("key", "bonusPercent")->first()->value / 100);
        $order = Order::create([
            "id" =>  $data["id"],
            "user_id" => $user["id"],
            "bonus" => $bonusSum,
            "plus_bonus" => $plusBonus,
            "products" => json_encode(array_column($cart, "id")),
        ]);

        $user->update([
            "cart" => null,
            "bonus" => $user->bonus - $bonusSum + $plusBonus,
        ]);

        // todo: create field plusBonus on order
        // todo: admin users

        return response()->json($order);
    }

    public function get ($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $order = Order::find($id);
        if (!$order) abort(404);

        if ($order->user_id !== $user->id) abort(403);

        $backendRequest = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/orders/byidget", (string) $id);
        $data = $backendRequest->json()["data"];

        if ($data["status"] === 18) $data->update([
                                        "finish" => true,
                                    ]);
        else if ($data["status"] === 16) {
            $this->onDelete($user, $order);
        }

        return response()->json(array_merge($data, $order->toArray()));
    }

    public function delete ($id, Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $order = Order::find($id);
        if (!$order) abort(404);

        if ($order->user_id !== $user->id) abort(403);

        $backendRequest = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/orders/byidget", (string) $id);
        $data = $backendRequest->json()["data"];

        if ($data["status"] !== 0) abort(400, "Нельзя отменить оплаченный заказ");
        $this->onDelete($user, $order);

        return response()->json(array_merge($data, $order->toArray()));
    }

    private function onDelete(User $user, Order $order) {
        $user->update([
            "bonus" => $user->bonus + $order->bonus - $order->plus_bonus,
        ]);
        $order->delete();
    }

    public function history (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $backendRequest = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/orders/get", [
            "conditions" => [
                [
                    "k" => "clientId",
                    "v" => $user->id,
                    "op" => 0,
                    "con" => 0
                ]
            ],
            "orders" => [
                [
                    "k" => "id",
                    "isd" => 0
                ]
            ],
            "limits" => [0, 1000000]
        ]);
        $data = $backendRequest->json()["data"];
        usort($data, function($a, $b) {
            return $b["dateCreate"] <=> $a["dateCreate"];
        });
        return response()->json($data);
    }
}
