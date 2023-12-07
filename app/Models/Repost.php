<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repost extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $keyType = "int";
    protected $table = "reposts";
    public $incrementing = true;
    protected $fillable = [
        "article_id",
        "user_id",
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(Identity::class, "id");
    }

}
