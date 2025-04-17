<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewQuestion extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function questionOptions()
    {
        return $this->hasMany(QuestionOption::class);
    }
    public function getTypeAttribute()
    {
        return $this->business->type; // `type`ni `Business` modelidan olamiz
    }
}
