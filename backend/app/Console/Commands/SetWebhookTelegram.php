<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetWebhookTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:set-webhook {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setting webhook telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Telegram::setWebhook(["url" => $this->argument('url')]);
    }
}
