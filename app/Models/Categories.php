<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Post;

class Categories extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'parent_id', 'sort_order', 'status'];

    public function setSlugAttribute($value)
    { 
        $this->attributes['slug'] = str_slug($value,'-','');
    }
    public function posts()
    {
        return $this->hasMany(Post::class,'category_id','id');
    }
}
