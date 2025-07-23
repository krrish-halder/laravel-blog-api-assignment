<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    public const SORT_LATEST = 'latest';
    public const SORT_MOST_LIKED = 'most_liked';

    public const SORT_OPTIONS = [
        self::SORT_LATEST,
        self::SORT_MOST_LIKED,
    ];

    protected $fillable = ['user_id', 'title', 'content', 'image_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
