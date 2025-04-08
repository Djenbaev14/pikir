<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\QuestionResource;
use App\Models\Business;
use App\Models\Feedback;
use App\Models\ReviewQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{

    public function questions($slug){
        $questions=ReviewQuestion::whereHas('business', function($q) use($slug){
            $q->where('slug', '=', $slug);
        })->orderBy('id','desc')->get();
        
        return $this->responsePagination($questions, QuestionResource::collection($questions));
    }
    public function business(Request $request){
        $slug=$request->slug;
        $business=Business::where('slug','=',$slug)->where('status',true)->first();
        
        return $this->responsePagination($business, new BusinessResource($business));
    }
    public function store(Request $request){
        $rules = [
            'business_id'=>'required|exists:businesses,id',
            'feedback'=>'required|array',
            'feedback.*.question_id'=>'required|exists:review_questions,id',
            'feedback.*.rating'=>'required|integer',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $business=Business::find($request->business_id);
        $token = $business->token;
        $chatId = $business->chat_id;
        foreach ($request->feedback as $key => $feedback) {
            $feed=Feedback::create([
                'owner_id'=>$business->owner_id,
                'business_id'=>$request->business_id,
                'review_question_id'=>$feedback['question_id'],
                'rating'=>$feedback['rating']
            ]);
            // $message = 
            //         "*Sorov:* {$feed->reviewQuestion->question}\n" .
            //         "*Baho:* {$feed->rating}";
            
            $stars = str_repeat("⭐", $feed->rating);  // Ratingga qarab yulduzlarni ko‘paytirish
            // Custom message format
            $message = "*Новый отзыв⭐️*\n";
            $message .= "*Ответ: *" . $feed->rating." ".$stars."\n";
            $message .= "*Вопрос: *" . $feed->reviewQuestion->question;

            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        }
        return response()->json(['message' => 'Feedback success'],200);
    }
}
