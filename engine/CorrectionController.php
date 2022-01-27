<?php

class CorrectionController
{
    private $existingWordCorrections = [
        'BIC' => 'BUC',
        '1.C00' => '1.000',
        'C00' => '1.000',
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
        $correction = [];
        if (isset($this->existingWordCorrections[$word])) {
            $correction['corrected'] = true;
            $correction['result'] = $this->existingWordCorrections[$word];
        } else {
            $correction['corrected'] = false;
            $correction['result'] = $word;
        }

        return $correction;
    }

    public function getCorrectedLine($line)
    {
        $correction = [];
        if (isset($this->existingLineCorrections[$line])) {
            $correction['corrected'] = true;
            $correction['result'] = $this->existingLineCorrections[$line];
        } else {
            $correction['corrected'] = false;
            $correction['result'] = $line;
        }

        return $correction;
    }
}