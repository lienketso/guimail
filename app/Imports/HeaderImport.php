<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\ToArray;

class HeaderImport implements ToArray
{
    public $header = [];

    public function array(array $array)
    {
        if (isset($array[0])) {
            $this->header = $array[0]; // dòng đầu tiên là header
        }
    }
}
