<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Feedback extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function feedbackDetails()
    {
        return $this->hasMany(FeedbackDetail::class);
    }
    public function averageRating()
    {
        return $this->feedbackDetails()->avg('rating');
    }
    
}
