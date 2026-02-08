<?php

namespace App\Http\Controllers;

use App\Http\utils;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public function tg (Request $request) {
        $update = Telegram::getWebhookUpdate();

        if ($update->has('message')) {
            $message = $update->getMessage();

            $requestUser = $message["from"]; // $requestUser["id"]

            $text = $message->getText();
            if (strpos($text, '/start') === 0) {
                $text = "*Добро пожаловать в наш бот! 👋*
Самара";
                $escape_chars = ['[', ']', '(', ')', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
                foreach ($escape_chars as $char) {
                    $text = str_replace($char, '\\' . $char, $text);
                }

                Telegram::sendPhoto([
                    'chat_id' => $requestUser["id"],
                    'caption' => $text,
                    'parse_mode' => 'MarkdownV2',
                    "photo" => InputFile::create(Storage::disk("public")->path("start_message.png")),
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [
                                [
                                    "text" => "Открыть веб-приложение",
                                    "web_app" => [
                                        "url" => "https://" . env("DOMAIN")
                                    ]
                                ],
                            ],
                        ]
                    ])
                ]);
            }
        }
        return response("ok", 200);
    }
}
