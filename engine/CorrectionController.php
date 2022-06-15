<?php

class CorrectionController
{
    private $existingWordCorrections = [
        'BIC' => 'BUC',
        'BUCX' => 'BUC X',
        'BUCx' => 'BUC X',
        'BI' => 'BUC',
        '1.C00' => '1.000',
        '1.U00' => '1.000',
        '1.UU0' => '1.000',
        'UUU' => '1.000',
        'C00' => '1.000',
        'SPRESSU' => 'ESPRESSO',
        'BuICX' => 'BUC X',
        '1.003.00' => '1.00 3.00',
        'AIYERICANO' => 'AMERICANO',
        'CPPUCCIND' => 'CAPPUCINO',
        'b.99' => '6.99',
        'BLICX' => 'PLIC X',
    ];

    public function addCorrectedWord($word, $correction)
    {
        if (!isset($existingWordCorrections[$word])) {
            $existingWordCorrections[$word] = $correction;
        }
    }

    public function getCorrectedWord($word)
    {
        if (isset($this->existingWordCorrections[$word])) {
            return $this->existingWordCorrections[$word];
        }
        return null;
    }
}