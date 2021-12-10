<?php

class OcrSpaceReceiptDecoder
{
    private $correctionController;

    public function __construct()
    {
        $this->correctionController = new CorrectionController();
    }

    public function getArticles($decodedLines)
    {
        $result = [];
        foreach($decodedLines as $line) {
            $words = $line['Words'];
            $wordCount = count($words);
            if ($wordCount > 1) {
                $lastWord = $words[$wordCount-1]['WordText'] ?? '';
                $secondLastWord = $words[$wordCount-2]['WordText'] ?? '';
                if (strlen($lastWord) == 1 && is_numeric($secondLastWord)) { // acesta poate fi pretul eg: 23.02 A 
                    $cost = floatval($secondLastWord);
                    $article = $this->detectArticle($decodedLines, $line);
                    if ($cost > 0 && $article) { // nu consideram promotii cu pret 0          
                        $result[] = array_merge([ 'costTotal' => $cost ], $article);
                    }
                }
            }
        }

        return $result;
    }

    private function detectArticle($decodedLines, $articleCostLine)
    {        
        $verticalPos = intval($articleCostLine['MinTop'] ?? 0);
        $textHeight = intval($articleCostLine['MaxHeight'] ?? 0);

        if (!$verticalPos || !$textHeight) {
            return [];
        }        

        $errorMargin = $verticalPos * 0.08;
        $minSearchVertical = $verticalPos - $errorMargin;
        $maxSearchVertical = $verticalPos + $textHeight + $errorMargin;

        $costLineText = $articleCostLine['LineText'] ?? 'Unknown';
        $articleName = '';
        $articletNameCorrected = '';
        $quantityText = '';
        $quantity = -1;
        foreach ($decodedLines as $line) {
            $lineTop = intval($line['MinTop'] ?? 0);
            $lineBottom = $lineTop + intval($line['MaxHeight'] ?? 0);
            if (!$verticalPos || !$textHeight) {
                continue;
            }
            if ($lineTop > $minSearchVertical && $lineBottom < $maxSearchVertical) {
                $lineText = $line['LineText'] ?? null;
                $lineWords = $line['Words'] ?? null;
                if ($lineText && $lineWords) {
                    $correctedLineText = $this->getCorrectedLine($lineText, $lineWords);
                    if ($correctedLineText != $costLineText) {
                        $articleName .= $lineText . ' ';
                        $articletNameCorrected .= $correctedLineText . ' ';
                    }
                }            
            }
        }

        return [ 'nume' => trim($articleName), 'corectieNume' => trim($articletNameCorrected) ];
    }

    private function getCorrectedLine($lineText, $lineWords) {
        $correctedLine = $this->correctionController->getCorrectedLine($lineText);
        // if we do not have a correction in line, try a correction of words
        $correctedLineText = $correctedLine['corrected']
            ? $correctedLine['result']
            : implode(' ', $this->getCorrectedWords($lineWords));

        return $correctedLineText;
    }

    private function getCorrectedWords($words)
    {
        $correctedWords = [];
        foreach($words as $word) {
            $correctedWords[] = $this->correctionController->getCorrectedWord($word['WordText'])['result'];
        }

        return $correctedWords;
    }
}