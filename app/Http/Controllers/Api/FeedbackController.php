<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\QuestionResource;
use App\Models\Business;
use App\Models\Feedback;
use App\Models\FeedbackDetail;
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
        $query=Business::where('status',true);
        if($request->has('slug')){
            $slug=$request->slug;
            $query=$query->where('slug','=',$slug);
        }
        $business=$query->get();
        
        return $this->responsePagination($business, BusinessResource::collection($business));
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
        $feedback=Feedback::create([
            'owner_id'=>$business->owner_id,
            'business_id'=>$request->business_id,
            'comment'=>$request->comment
        ]);
        foreach ($request->feedback as $key => $feed) {
            FeedbackDetail::create([
                'feedback_id'=>$feedback->id,
                'business_id'=>$request->business_id,
                'review_question_id'=>$feed['question_id'],
                'rating'=>$feed['rating']
            ]);
        }

        
        // Custom message format
        $message = "*Новый отзыв⭐️*\n";
        foreach ($feedback->feedbackDetails as $key => $detail) {
            $stars = str_repeat("⭐", $detail->rating);  // Ratingga qarab yulduzlarni ko‘paytirish
            // $message .= "*Ответ: *" . $detail->rating." ".$stars."\n";
            $message .= "*Вопрос: *" . $detail->reviewQuestion->question." ". $detail->rating." ".$stars."\n";
        }
        $message .= "*Пожелания: *" . $feedback->comment;

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
        return response()->json(['message' => 'Feedback success'],200);
    }
}
