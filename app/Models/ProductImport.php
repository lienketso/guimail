<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    protected $table = 'product_imports';
    protected $fillable = [
        'excel_file_id',
        'company_id',
        'tax_code',
        'material_code',
        'product_name',
        'unit',
        'price',
    ];

    public function excelFile()
    {
        return $this->belongsTo(ExcelFile::class);
    }
}
