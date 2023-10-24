<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class articles extends Model {
    protected $table = 'articles';
    public $incrementing = false;
    protected $primaryKey = "id";
    protected $keyType = "string";
    protected $timestamps = true;
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

 protected static function boot()
    {
        parent::boot();

        /* Generate a UUID */
        self::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

}
