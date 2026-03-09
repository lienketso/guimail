<?php

namespace App\Services;
use App\Models\ProductImport;
use Illuminate\Support\Str;
class MaterialCodeService
{
    public function generate(string $productName, ?string $taxCode = null): string
    {
        $taxCode = $taxCode ?? '';

        // 1️⃣ Bỏ dấu tiếng Việt + viết hoa
        $cleanName = Str::upper(Str::ascii($productName));

        // 2️⃣ Bỏ ký tự đặc biệt, chỉ giữ chữ & số
        $cleanName = preg_replace('/[^A-Z0-9]/', '', $cleanName);

        // 3️⃣ Lấy 6 ký tự đầu
        $prefix = substr($cleanName, 0, 6);

        // Nếu tên quá ngắn thì pad thêm X
        $prefix = str_pad($prefix, 6, 'X');

        // 4️⃣ Lấy 2 số cuối của mã số thuế
        $taxDigits = preg_replace('/\D/', '', $taxCode);
        $suffix = substr($taxDigits, -2);

        if (!$suffix) {
            $suffix = '00';
        }

        $code = $prefix . $suffix;

        // Đảm bảo không trùng lặp trong DB
        $baseCode = $code;
        $counter = 1;
        while (ProductImport::where('material_code', $code)->exists()) {
            $code = $baseCode . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;

            if ($counter > 99) {
                // fallback: thêm 2 ký tự ngẫu nhiên nếu quá nhiều lần thử
                $code = $baseCode . Str::upper(Str::random(2));
                break;
            }
        }

        return $code;
    }
}
