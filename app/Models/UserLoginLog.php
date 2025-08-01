<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    protected $table = 'user_login_logs';
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'login_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
