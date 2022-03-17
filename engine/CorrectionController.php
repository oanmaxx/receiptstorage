<?php

class CorrectionController
{
    private $existingWordCorrections = [
        'BIC' => 'BUC',
        '1.C00' => '1.000',
        'C00' => '1.000',
        'SPRESSU' => 'ESPRESSO',
        'BuICX' => 'BUC X',
    ];

    private $existingLineCorrections = [
        'C00 BUCx 21.99' => '1.000 BUC X 21.99',
        'Bluza 100 Gri Dana' => 'Bluza 100 Gri Dama',
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

    public function getCorrectedLine($line)
    {
        if (isset($this->existingLineCorrections[$line])) {
            return $this->existingLineCorrections[$line];
        }
        return null;
    }
}