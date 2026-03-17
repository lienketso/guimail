<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Excel temporary files
    |--------------------------------------------------------------------------
    |
    | Để tránh lỗi open_basedir trên hosting, ta cấu hình lại thư mục tạm
    | mà Laravel Excel (PhpSpreadsheet) sử dụng. Thư mục này phải nằm trong
    | các path được phép của open_basedir.
    |
    */

    'temporary_files' => [
        // Luôn nằm trong project: /home/doccument/domains/kiemtoanvietnam.vn/public_html/storage/framework/laravel-excel
        'local_path' => env('EXCEL_TEMP_PATH', storage_path('framework/laravel-excel')),

        // Không dùng remote disk cho temp trong trường hợp này
        'remote_disk'   => null,
        'remote_prefix' => null,
    ],

];

