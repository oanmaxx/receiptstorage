<?php

require_once __DIR__ . '/dummy_data/DummyYummyYang.php';
require_once __DIR__ . '/dummy_data/DummyDecathlon.php';
require_once __DIR__ . '/dummy_data/DummyLunchBox.php';
require_once __DIR__ . '/dummy_data/DummyMega.php';
require_once __DIR__ . '/dummy_data/DummyNarcoffee.php';
require_once __DIR__ . '/dummy_data/DummyCaro.php';
require_once __DIR__ . '/dummy_data/DummyCarrefour.php';

class DummyResponse
{
    public function getDummyData($fileName)
    {
        if ($fileName === 'yummyYang_bon_1.png') {
            return DummyYummyYang::getDummyResponse1();
        } else if ($fileName ==='decathlon_bon_1.png') {
            return DummyDecathlon::getDummyResponse1();
        } else if ($fileName ==='decathlon_bon_2.png') {
            return DummyDecathlon::getDummyResponse2();
        } else if ($fileName ==='decathlon_bon_3.png') {
            return DummyDecathlon::getDummyResponse3();
        } else if ($fileName === 'narcoffee_bon_1.png') {
            return DummyNarcoffee::getDummyResponse1();
        } else if ($fileName === 'caro_bon_1.png') {
            return DummyCaro::getDummyResponse1();
        } else if ($fileName === 'lunchBox_bon_1.png') {
            return DummyLunchBox::getDummyResponse1();
        } else if ($fileName === 'mega_bon_1.png') {
            return DummyMega::getDummyResponse1();
        } else if ($fileName === 'carrefour_bon_1.png') {
            return DummyCarrefour::getDummyResponse1();
        }
    }
}
