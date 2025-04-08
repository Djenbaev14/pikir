<?php

namespace App\Console\Commands;

use App\Models\Feedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFeebackToTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-feeback-to-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yangi feedback larni Telegram kanalga yuborish';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('yaratildi');
        $feedbacks = Feedback::where('telegram_sent', false)->get();

        foreach ($feedbacks as $feedback) {
            $message = "📢 *{$feedback->reviewQuestion->question}*\n\n{$feedback->rating}";

            $response = Http::post("https://api.telegram.org/bot" . $feedback->business->token . "/sendMessage", [
                'chat_id' =>  $feedback->business->chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                $feedback->telegram_sent = true;
                $feedback->save();
                $this->info("Feedback ID {$feedback->id} yuborildi.");
            } else {
                $this->error("Feedback ID {$feedback->id} yuborilmadi.");
            }
        }

        return Command::SUCCESS;
    }
}
