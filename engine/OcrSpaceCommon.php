<?php

class OcrSpaceCommon
{
    public const ORIGINAL_LINE = 'line';    
    public const ARTICLE_NAME = 'articleName';
    public const QUANTITY_TEXT = 'quantityText';
    public const QUANTITY = 'quantity';
    public const TOTAL_COST = 'totalCost';

    public static function sortWordsLeftToRight($lineA, $lineB) {
        return $lineA['MaxRight'] > $lineB['MaxRight'];
    }
    
    public static function toNumber($str) {
        $len = strlen($str);
        if ($len > 1) {
            if ($str[$len-1] == '=') {
                $str = str_replace('=', '', $str);
            } else if ($str[$len-1] == '-') {
                $str = str_replace('-', '', $str);
            }
        }
        return str_replace(',', '.', $str);
    }
    
    public static function stringIsNumber($str) {
        $number = self::toNumber($str);
        return strlen($number) < 16 && is_numeric($number);
    }
}