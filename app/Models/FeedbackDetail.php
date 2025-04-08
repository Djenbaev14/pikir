<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackDetail extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }
    public function reviewQuestion()
    {
        return $this->belongsTo(ReviewQuestion::class);
    }
}
