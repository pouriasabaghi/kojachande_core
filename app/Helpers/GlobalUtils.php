<?php
namespace App\Helpers;

class GlobalUtils
{
    public static function fixNumber(string|int|null $number = 0){
        $number ??= 0;

        $english = range(0, 9);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        // remove separators
        $number = str_replace([',', '٫', 'از', 'تومان'], '', $number);
     
        $convertedPersianNumbers = str_replace($persian, $english, $number);
        $convertedPersianNumbers = str_replace($arabic, $english, $convertedPersianNumbers);

        return trim($convertedPersianNumbers);
    }
}
