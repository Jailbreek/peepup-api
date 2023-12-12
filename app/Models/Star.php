<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Star extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $incrementing = true;
    public $primaryKey = "id";
    public $keyType = "int";
    public $table = "stars";
    public $fillable = [
        "article_id",
        "user_id",
        "star_value"
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function identity()
    {
        return $this->belongsTo(Identity::class, "id");
    }
}
