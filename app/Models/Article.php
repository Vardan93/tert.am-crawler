<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Article
 *
 * @property int $id
 * @property string $title
 * @property string $image
 * @property string $image_url
 * @property int $is_active
 * @property int $article_id
 * @property string $article_url
 * @property string $description
 * @property string $local_img_url
 *
 * @package App\Models
 */
class Article extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'title',
        'image',
        'image_url',
        'is_active',
        'article_id',
        'article_url',
        'description',
    ];

    protected $appends = [
        'local_img_url',
    ];

    public function getLocalImgUrlAttribute()
    {
        return asset('storage/images/' . $this->image . '?' . now()->timestamp);
    }
}
