<?php

namespace App\Observers;

use App\Models\Feedback;
use Illuminate\Support\Facades\Http;

class FeedbackObserver
{
    public function created(Feedback $feedback)
    {
        $token = $feedback->business->token;
        $chatId = $feedback->business->chat_id;
        $message = 
                   "*Sorov:* {$feedback->reviewQuestion->question}\n" .
                   "*Baho:* {$feedback->rating}";

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
