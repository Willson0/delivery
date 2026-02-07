<?php

namespace App\Http\Controllers;

use App\Http\Requests\admin\AdminCreateAchievementRequest;
use App\Http\Requests\admin\AdminCreateAdRequest;
use App\Http\Requests\admin\AdminCreateCourseRequest;
use App\Http\Requests\admin\AdminCreateLessonRequest;
use App\Http\Requests\admin\AdminCreateProbeRequest;
use App\Http\Requests\admin\AdminCreateStateRequest;
use App\Http\Requests\admin\AdminCreateVariantRequest;
use App\Http\Requests\admin\AdminUpdateAchievementRequest;
use App\Http\Requests\admin\AdminUpdateAdRequest;
use App\Http\Requests\admin\AdminUpdateCourseRequest;
use App\Http\Requests\admin\AdminUpdateLessonRequest;
use App\Http\Requests\admin\AdminUpdateProbeRequest;
use App\Http\Requests\admin\AdminUpdateStateRequest;
use App\Http\Requests\admin\AdminUpdateVariantRequest;
use App\Http\utils;
use App\Models\Achievement;
use App\Models\Ad;
use App\Models\Admin;
use App\Models\AdminCookie;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\Probe;
use App\Models\Setting;
use App\Models\State;
use App\Models\Story;
use App\Models\Subject;
use App\Models\Support;
use App\Models\User;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class AdminController extends Controller
{
    public function profile (Request $request) {
        return $request->get("user");
    }
    public function login (Request $request) {
        $admin = Admin::where("login", $request->login)->first();
        if (!$admin or !password_verify($request->password, $admin->password))
            abort (403, "Неверный логин или пароль");

        $cookie = utils::gen_cookie($admin, isadmin: true);
        $respcookie = Cookie::forever("admin", $cookie);

        return response()
            ->json(["Message" => "Успешная авторизация!", "cookie" => $cookie])
            ->withCookie($respcookie);
    }
    public function logout (Request $request) {
        $admin = $request->get("user");
        AdminCookie::where("cookie", $request->cookie("admin"))->delete();

        $respcookie = Cookie::forget("admin");

        return response()->json(["Message" => "Вы успешно вышли из системы администрации."])->withCookie($respcookie);
    }

    public function users (Request $request) {
        return utils::index(User::class, $request, true);
    }
    public function showUser (User $user, Request $request) {
        return $user;
    }

    public function changeUserBonus (User $user, Request $request) {
        if (!$request->has("bonus")) abort(400, "Не введено количество бонусов");
        if (!is_numeric($request->bonus)) abort(400, "Количество должно быть числом!");
        if ($request->bonus < 0) abort(400, "Количество бонусов не может быть меньше 0");

        $user->bonus = $request->bonus;
        $user->save();

        return response()->json("ok");
    }

    public function ads () {
        return Ad::all();
    }
    public function updateAd (Ad $ad, AdminUpdateAdRequest $request) {
        $validate = $request->validated();

        if ($request->has("picture")) {
            Storage::disk("public")->delete($ad->picture);

            $picture = $request->file("picture");
            $time = time();
            $url = "ads/image_$time" . "." . $picture->extension();
            Storage::disk("public")->putFileAs("ad", $picture, "image_$time" . "." . $picture->extension());
            $validate["picture"] = $url;
        }

        $ad->update($validate);
        return $this->ads();
    }

    public function deleteAd (Ad $ad, Request $request) {
        $ad->delete();
        return $this->ads();
    }

    public function createAd (AdminCreateAdRequest $request) {
        $validate = $request->validated();

        $picture = $request->file("picture");
        $time = time();
        $url = "ads/image_$time" . "." . $picture->extension();
        Storage::disk("public")->putFileAs("ads", $picture, "image_$time" . "." . $picture->extension());
        $validate["picture"] = $url;

        Ad::create($validate);
        return $this->ads();
    }

//    public function logs (Request $request) {
//        return Log::limit(100)->get();
//    }

    public function setTrial (Request $request) {
        if (!$request->has("days")) abort(400, "Не введено количество дней");
        if ($request->days < 0) abort(400, "Количество дней не может быть меньше 0");
        utils::setTrial($request->days);
        return response()->json(["ok" => true]);
    }

    public function changeBonusPercent (Request $request) {
        if (!$request->has("percent")) abort(400, "Не введен процент бонуса!");
        $percent = intval($request->percent);

        if ($percent < 0) abort(400, "Процент не может быть ниже нуля!");
        Setting::where("key", "bonusPercent")->first()->update([
            "value" => $percent
        ]);

        return response()->json(["ok" => true]);
    }
}
