<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthUpdateRequest;
use App\Http\utils;
use App\Models\Achievement;
use App\Models\Ad;
use App\Models\Order;
use App\Models\PhoneRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function profile (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $request = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/users/get");

        $data = $request->json()["data"];
        $response = collect($data)->firstWhere('id', $user->id);

        $cache = Cache::get("data");
        if (!$cache) {
            $request = Http::withHeaders([
                'Authentication' => env("INTERNAL_TOKEN"),
            ])->post("https://kfsamara.ru/api/products/get");

            $cache["products"] = array_values(array_filter($request->json()["data"], function($el) {
                return $el["status"] === 1;
            }));

            $request = Http::withHeaders([
                'Authentication' => env("INTERNAL_TOKEN"),
            ])->post("https://kfsamara.ru/api/sections/get");

            $cache["sections"] = array_values(array_filter($request->json()["data"], function($el) {
                return $el["status"] === 1;
            }));
            Cache::put('data', $cache);
        } else $cache["from_cache"] = 1;
        $cache["ads"] = Ad::all();
        $cache["allergensList"] = ["пшеница","глютен","яичный белок","яичный желток","яйцо","молоко","лактоза","казеин","сыр","сливки","масло сливочное","маргарин","творог","рыба","лосось","тунец","краб","креветка","омары","гребешок","моллюски","устрица","анчоусы","морской гребешок","осьминог","угорь","кальмар","икра","арахис","фисташки","грецкий орех","кешью","миндаль","орехи","фундук","пекан","семечки подсолнечника","кунжут","семена кунжута","горчица","горчичное масло","соевый соус","соевые бобы","соевый лецитин","соевый белок","глутамат натрия","мясо курицы","курица","индейка","утка","свинина","говядина","баранина","желатин","колбасы","ветчина","бекон","колбасные изделия","лук","чеснок","сельдерей","морковь","перец","томат","помидор","кукуруза","картофель","дрожжи","уксус","винный уксус","рис","рисовый уксус","гречка","ячмень","ржаная мука","крахмал","мёд","черный перец","базилик","орегано","тимьян","розмарин","чили","хрен","майонез","кетчуп","горчица (соус)","кетчуп (соус)","петрушка","укроп","трюфель","трюфельное масло","корень имбиря","васаби","лимонная кислота","яблочный уксус","красители пищевые","консерванты","улучшители вкуса","перец халапеньо"];
        $cache["settings"] = Setting::all();
        $cache["achievements"] = Achievement::all();

        $cache["orders"] = Order::where("user_id", $user->id)->where("finish", 0)->get()->toArray();

        $response = (object) array_merge((array) $response, (array) $cache, $user->toArray());

        return response()->json($response);
        // return Products, addresses, history, user, bonuses, allergens
        // addresses, history in local DB
    }

    public function update (AuthUpdateRequest $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) abort(423, "Unregistered");

        $data = $request->validated();
        $user->update($data);

        if ($request->has("addresses")) utils::addData($user, "count_address", 1);

        return response()->json("ok");
    }

    public function register (Request $request) {
        if (!$request->has("phone")) abort(400, "Не введен номер телефона");

        $req = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/users/get");

        $data = $req->json()["data"];
        $response = collect($data)->firstWhere('phone', $request->phone);
        if (!$response) {
            $req = Http::withHeaders([
                'Authentication' => env("INTERNAL_TOKEN"),
            ])->post("https://kfsamara.ru/api/users/add", [
                "data" => [
                    "name" =>
                        trim(
                            (isset($request["initData"]["user"]["first_name"]) ? $request["initData"]["user"]["first_name"] : "") .
                            " " .
                            (isset($request["initData"]["user"]["last_name"]) ? $request["initData"]["user"]["last_name"] : "")
                        ),
                    "phone" => $request->phone, // todo: check correct number
                    "type" => 2,
                    "status" => 0,
                    "password" => 1,
                ]
            ]);
            if ($req->status() !== 200) {
                Log::critical($req->json());
                abort(400, "Ошибка на стороне сервера");
            }
        }

        $req = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/users/loginSms", [
            "conditions" => [
                [
                    "k" => "phone",
                    "v" => "+" . $request->phone,
                ]
            ]
        ]);

//        $req = Http::withHeaders([
//            'Authentication' => env("INTERNAL_TOKEN"),
//        ])->post("https://kfsamara.ru/api/clients/login", [
//            "phone" => $request->phone,
//            "route" => "voice",
//        ]);

        if ($req->status() !== 200) {
            Log::critical($req->json());
            abort(400, "Ошибка на стороне сервера");
        }

        $old = PhoneRequest::where("user_id", $request["initData"]["user"]["id"])->first();
        if ($old) $old->delete();

        PhoneRequest::create([
            "user_id" => $request["initData"]["user"]["id"],
            "phone" =>  $request->phone,
        ]);

        return response()->json("ok");
    }

    public function check (Request $request) {
        if (!$request->has("code")) abort(400, "Не введен код");

        $old = PhoneRequest::where("user_id", $request["initData"]["user"]["id"])->first();
        if (!$old) abort(400, "Код не был отправлен");

        $req = Http::withHeaders([
            'Authentication' => env("INTERNAL_TOKEN"),
        ])->post("https://kfsamara.ru/api/users/smsCheck", [
            "conditions" => [
                [
                    "k" => "phone",
                    "v" => $old->phone,
                ],
                [
                    "k" => "code",
                    "v" => $request->code,
                ]
            ]
        ]);
        if ($req->status() !== 200) {
            Log::critical($req->json());
            abort(400, "Ошибка на стороне сервера");
        }

        if (!$req->json()["data"]) abort(403, "Неправильный код!");
        $userId = $req->json()["data"]["id"];

        // $old->delete();

        User::where("telegram_id", $request["initData"]["user"]["id"])->update([
            "telegram_id" => 0
        ]);

        $oldUser = User::where("id", $userId)->first();
        if ($oldUser) $oldUser->update(["telegram_id" => $request["initData"]["user"]["id"]]);
        else User::create([
            "id" => $userId,
            "telegram_id" => $request["initData"]["user"]["id"],
            "pinned_achievements" => json_encode([]),
        ]);
        return $this->profile($request);
    }
}
