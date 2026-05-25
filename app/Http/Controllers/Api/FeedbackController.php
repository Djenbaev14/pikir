<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\QuestionResource;
use App\Models\Business;
use App\Models\Feedback;
use App\Models\FeedbackDetail;
use App\Models\ReviewQuestion;
use App\Models\TelegramChat;
use App\Support\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function questions($slug)
    {
        $questions = ReviewQuestion::whereHas('business', function ($q) use ($slug) {
            $q->where('slug', '=', $slug);
        })
            ->when(
                Business::where('slug', $slug)->value('type') === 'single_choice',
                fn($query) => $query->with('questionOptions')
            )->get();
        return $this->responsePagination($questions, QuestionResource::collection($questions));
    }
    public function business(Request $request)
    {
        $query = Business::where('status', true);
        if ($request->has('slug')) {
            $slug = $request->slug;
            $query = $query->where('slug', '=', $slug);
        }
        $business = $query->get();

        return $this->responsePagination($business, BusinessResource::collection($business));
    }
    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());
        $business = Business::findOrFail($request->business_id);
        $rules = [
            'business_id' => 'required|exists:businesses,id',
            'feedback' => 'required|array',
            'feedback.*.question_id' => 'required|exists:review_questions,id',
        ];

        if ($business->type === 'single_choice') {
            $rules['feedback.*.question_option_id'] = 'required|exists:question_options,id';
        }

        if ($business->type === 'rating') {
            $rules['feedback.*.rating'] = 'required|integer|min:1|max:5';
        }


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $feedback = Feedback::create([
            'owner_id' => $business->owner_id,
            'business_id' => $request->business_id,
            'comment' => $request->comment
        ]);
        foreach ($request->feedback as $key => $feed) {
            FeedbackDetail::create([
                'feedback_id' => $feedback->id,
                'business_id' => $request->business_id,
                'review_question_id' => $feed['question_id'],
                'question_option_id' => $business->type == 'rating' ? null : $feed['question_option_id'],
                'rating' => $business->type == 'rating' ? $feed['rating'] : null
            ]);
        }


        // Custom message format
        $message = "*Новый отзыв⭐️*\n";
        foreach ($feedback->feedbackDetails as $key => $detail) {
            if ($business->type == 'rating') {
                $stars = str_repeat("⭐", $detail->rating);  // Ratingga qarab yulduzlarni ko‘paytirish
                $question = $detail->reviewQuestion->question;
                // $message .= "*Ответ: *" . $detail->rating." ".$stars."\n";
                $message .= "*$question: *" . $detail->rating . "-" . $stars . "\n";
            } else {
                $question = $detail->reviewQuestion->question;
                $text = $detail->QuestionOption->text;
                // $message .= "*Ответ: *" . $detail->rating." ".$stars."\n";
                $message .= "*$question: *" . $text . "\n";
            }
        }
        $message .= "*Пожелания: *" . $feedback->comment;

        $base = Telegram::apiBase();

        // 1) ESKI yo'l (backward-compat): biznesning o'z chat_id'siga — hozirgidek.
        //    Deploy paytida hech narsa uzilmasligi uchun saqlanadi.
        if (!empty($business->token) && !empty($business->chat_id)) {
            $response = Http::post("{$base}/bot{$business->token}/sendMessage", [
                'chat_id' => $business->chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info('Otziv eski chat_id ga yuborildi', [
                    'feedback_id' => $feedback->id,
                    'chat_id' => $business->chat_id,
                ]);
            } else {
                Log::error('Otziv eski chat_id ga yuborilmadi', [
                    'feedback_id' => $feedback->id,
                    'status' => $response->status(),
                ]);
            }
        }

        // 2) YANGI yo'l: bot ulangan barcha faol guruhlarga yuboramiz.
        //    Bu bot webhook bilan guruhlarni ushlagan bot bilan bir xil bo'lishi shart.
        $token = Telegram::token();
        $chats = TelegramChat::where('is_active', true)->get();

        if (!empty($token)) {
            foreach ($chats as $chat) {
                $response = Http::post("{$base}/bot{$token}/sendMessage", [
                    'chat_id' => $chat->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);

                if ($response->successful()) {
                    Log::info('Otziv guruhga yuborildi', [
                        'feedback_id' => $feedback->id,
                        'chat_id' => $chat->chat_id,
                    ]);
                } else {
                    Log::error('Otziv guruhga yuborilmadi', [
                        'feedback_id' => $feedback->id,
                        'chat_id' => $chat->chat_id,
                        'status' => $response->status(),
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Feedback success'], 200);
    }
}
