<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'task';
    protected $fillable = ['title', 'content', 'priority', 'user_id', 'task_type', 'status', 'end_date'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
