<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Categories;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'slug', 'description', 'content', 'file_attach', 'status', 'type', 'category_id', 'user_id', 'view_count', 'meta_title', 'meta_description'];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_slug($value,'-','');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class,'category_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
