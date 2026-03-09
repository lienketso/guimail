<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelFile extends Model
{
    protected $table = 'excel_files';
    protected $fillable = [
        'company_id',
        'file_name',
        'file_path',
        'total_rows'
    ];

    public function products()
    {
        return $this->hasMany(ProductImport::class);
    }
}
