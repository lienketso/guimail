<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'parent_id',
        'company_id',
        'sort_order',
        'ngay_nop',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Lấy đường dẫn đầy đủ của folder (từ root đến folder hiện tại)
     */
    public function getParentPath()
    {
        $path = [];
        $current = $this;
        
        while ($current->parent) {
            array_unshift($path, $current->parent->name);
            $current = $current->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Lấy tất cả parent folders
     */
    public function getAllParents()
    {
        $parents = [];
        $current = $this->parent;
        
        while ($current) {
            $parents[] = $current;
            $current = $current->parent;
        }
        
        return array_reverse($parents); // Trả về từ root đến gần nhất
    }
} 