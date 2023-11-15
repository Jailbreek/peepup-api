<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Article extends Model
{
    use HasFactory;


    protected $table = 'articles';
    public $incrementing = false;
    protected $keyType = "string";
    public $timestamps = true;
    protected $casts = [
        'tags' => 'array',
    ];
    protected $fillable = [
        "title",
        "slug",
        "description",
        "content",
        "categories",
        "image",
        "status",
        "like_count",
        "click_count",
        "repost_count",
        "author_id",
        "category",
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($post) {
            $post->{$post->getKeyName()} = (string) Uuid::uuid4();
        });
    }

    public function getIncrementing() {
        return false;
    }


    public function getKeyType() {
        return 'string';
    }
}
