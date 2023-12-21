<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Uuid;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';
    public $incrementing = false;
    protected $keyType = "string";
    public $timestamps = false;
    protected $fillable = [
        "title",
        "slug",
        "description",
        "content",
        "image_cover",
        "status",
        "visit_count",
        "author_id",
        "reading_time"
    ];


    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            function ($article) {
                $article->{ $article->getKeyName() } = (string) Uuid::uuid4();
            }
        );
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, "article_category");
    }

    public function stars()
    {
        return $this->hasMany(Star::class);
    }

    public function reposts()
    {
        return $this->hasMany(Repost::class);
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function uniqueIds(): array
    {
        return ['id'];
    }
}
