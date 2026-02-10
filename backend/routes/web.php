<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AllergensController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\CheckAdminMiddleware;
use App\Http\Middleware\CheckTelegram;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "api"], function () {
    Route::group(["prefix" => "auth", "middleware" => CheckTelegram::class], function () {
        Route::post("profile", [AuthController::class, "profile"]);
        Route::post("update", [AuthController::class, "update"]);

        Route::post("register", [AuthController::class, "register"]);
        Route::post("check", [AuthController::class, "check"]);
    });

    Route::group(["prefix" => "order", "middleware" => CheckTelegram::class], function () {
        Route::post("/history", [OrderController::class, "history"]);
        Route::post("/", [OrderController::class, "create"]);
        Route::post("/{id}", [OrderController::class, "get"]);
        Route::post("/{id}/cancel", [OrderController::class, "delete"]);
    });

    Route::group(["prefix" => "achievement", "middleware" => CheckTelegram::class], function () {
        Route::post("{achievement}/pin", [AchievementController::class, "pin"]);
        Route::post("{achievement}/unpin", [AchievementController::class, "unpin"]);
    });

    Route::group(["prefix" => "webhook"], function () {
        Route::post("/tg", [WebhookController::class, 'tg']);
    });

    // ADMIN
    Route::group(["prefix" => "stats", "middleware" => CheckAdminMiddleware::class], function () {
        Route::get("/", [StatsController::class, "index"]);
    });

    Route::post("/admin/login", [AdminController::class, "login"]);
    Route::group(["prefix" => "admin", "middleware" => CheckAdminMiddleware::class], function () {
        Route::get("profile", [AdminController::class, "profile"]);
        Route::post("logout", [AdminController::class, "logout"]);

        Route::prefix('ads')->group(function () {
            Route::get('/', [AdminController::class, 'ads']);
            Route::post('/', [AdminController::class, 'createAd']);
            Route::post('{ad}', [AdminController::class, 'updateAd']);
            Route::delete('{ad}', [AdminController::class, 'deleteAd']);
        });

        Route::prefix('mailing')->group(function () {
            Route::get("/", [PostController::class, 'index']);
            Route::post("/", [PostController::class, "store"]);
            Route::delete("/{post}", [PostController::class, "destroy"]);
            Route::post("/{post}", [PostController::class, "update"]);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'users']);
            Route::get('{user}', [AdminController::class, 'showUser']);
            Route::post("{user}/bonus", [AdminController::class, 'changeUserBonus']);
        });

        Route::prefix('achievements')->group(function () {
            Route::get('/', [AdminController::class, 'achievements']);
            Route::post('/', [AdminController::class, 'createAchievement']);
            Route::post('{achievement}', [AdminController::class, 'updateAchievement']);
            Route::delete('{achievement}', [AdminController::class, 'deleteAchievement']);
        });

        Route::post("banner", [BannerController::class, "update"]);
        Route::post("bonus", [AdminController::class, "changeBonusPercent"]);
    });
});
