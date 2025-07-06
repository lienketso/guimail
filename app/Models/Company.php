<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'tax_code', 'founded_year','phone','address','ceo_name','logo','description','email'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
} 