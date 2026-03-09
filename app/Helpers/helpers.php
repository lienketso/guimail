<?php
use Illuminate\Support\Str;

if (! function_exists('str_slug')) {
    function convert_vi_to_en($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        return $str;
    }
}
if (! function_exists('str_slug')) {

    function str_slug($title, $separator = '-', $language = 'en')
    {
        return convert_vi_to_en(Str::slug($title, $separator, $language));
    }
}

function normalize_excel_header($value): string
{
    $value = (string) $value;
    $value = trim($value);
    $value = mb_strtolower($value, 'UTF-8');

    if (function_exists('convert_vi_to_en')) {
        $value = convert_vi_to_en($value);
    }

    // Chuẩn hoá dấu câu/ký tự phân cách thành khoảng trắng để match ổn định
    $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value);

    return trim($value);
}

function findColumnIndex(array $headerMap, array $keywords): ?int
{
    foreach ($headerMap as $header => $index) {
        $normalizedHeader = normalize_excel_header($header);

        foreach ($keywords as $keyword) {
            $normalizedKeyword = normalize_excel_header($keyword);

            if ($normalizedKeyword !== '' && str_contains($normalizedHeader, $normalizedKeyword)) {
                return $index;
            }
        }
    }

    return null;
}

function parsePrice($price)
{
    if (!$price) return null;

    return floatval(str_replace(',', '', $price));
}
