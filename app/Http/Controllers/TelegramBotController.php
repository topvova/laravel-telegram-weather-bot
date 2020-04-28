<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function getIncomingUpdates()
    {
        $activity = Telegram::getUpdates();

        dd($activity);
    }
}
