<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Business extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
    public function reviewQuestions()
    {
        return $this->hasMany(ReviewQuestion::class);
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($business) {
            $baseSlug = Str::slug($business->name);
            $slug = $baseSlug;
            $count = 1;

            // Agar slug takrorlangan bo‘lsa, yangisini yaratamiz
            while (static::where('slug', $slug)->where('id', '!=', $business->id)->exists()) {
                $slug = $baseSlug . '-' . $count;
                $count++;
            }
            
            $url = "https://qrmap.ru/company/{$slug}/review";
            $qrCode = QrCode::format('png')->size(200)->generate($url);

            $filePath = auth()->user()->name.'/' . $slug . '.png';

            Storage::disk('public')->put($filePath, $qrCode);
            
            $business->slug = $slug;
            $business->qr_code_path = $filePath;
        });
    }
}
