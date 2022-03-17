<?php

class OcrSpaceCommon
{
    public const LINE = 'line';
    public const TOTAL_COST = 'totalCost';
    public const ARTICLE_NAME = 'articleName';
    public const ARTICLE_NAME_CORRECTED = 'articleNameCorrected';    
    public const QUANTITY_TEXT = 'quantityText';
    public const QUANTITY_TEXT_CORRECTED = 'quantityTextCorrected';
    public const QUANTITY = 'quantity';

    public static function sortWordsLeftToRight($lineA, $lineB) {
        return $lineA['MaxRight'] > $lineB['MaxRight'];
    }
    
    public static function toNumber($str) {
        $len = strlen($str);
        if ($len > 1 && $str[$len-1] == '=') {
            $str = str_replace('=', ' ', $str);
        }
        return str_replace(',', '.', $str);
    }
    
    public static function stringIsNumber($str) {
        return is_numeric(self::toNumber($str));
    }
}