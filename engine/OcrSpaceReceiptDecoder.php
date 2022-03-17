<?php

require_once 'OcrSpaceCommon.php';

class OcrSpaceReceiptDecoder
{
    private $correctionController;
    private $ocrLines;

    public function __construct($decodedLines)
    {
        $this->correctionController = new CorrectionController();

        $this->ocrLines = $this->aggregateLinesWithSimilarHeight($decodedLines);
    }

    public function getArticles()
    {
        $result = [];
        $lineCount = count($this->ocrLines);        
        for ($idx = 0; $idx < $lineCount; $idx++) {
            $line = $this->ocrLines[$idx];
            $lineWords = explode(' ', $line);
            $correctedWords = $this->getCorrectedWords($lineWords);
            $price = $this->extractArticlePrice($correctedWords);
            if ($price > 0) { // avem o linie care contine pretul unui articol
                $presumedArticleWords = [];
                foreach($correctedWords as $articleWord) {
                    $presumedArticleWords[] = $articleWord;
                }

                $quantity = $this->extractQuantity($presumedArticleWords);
                if ($quantity) {
                    $articleWordsCount = count($presumedArticleWords);
                    $articleWords = array_slice($lineWords, 0, $articleWordsCount);
                    $quantityWords = array_slice($lineWords, $articleWordsCount);

                    $articleStr = implode(' ', $articleWords);
                    $articleStrCorrected = implode(' ', $presumedArticleWords);
                    $quantityStr = implode(' ', $quantityWords);
                    $quantityStrCorrected = implode(' ', $quantityWords);
                    $resultArticle = [
                        OcrSpaceCommon::LINE => $line,
                        OcrSpaceCommon::ARTICLE_NAME => $articleStr,
                        OcrSpaceCommon::ARTICLE_NAME_CORRECTED => $articleStrCorrected,
                        OcrSpaceCommon::QUANTITY_TEXT => $quantity,
                        OcrSpaceCommon::QUANTITY_TEXT_CORRECTED => $quantity,
                        OcrSpaceCommon::QUANTITY => $quantity,
                        OcrSpaceCommon::TOTAL_COST => $price
                    ];                    
                } else {
                    $resultArticle = [
                        OcrSpaceCommon::LINE => $line
                    ];
                }

                $result[] = $resultArticle;
            }
        }

        return $result;
    }

    private function extractArticlePrice(&$lineWords)
    {
        $cost = 0;
        $wordCount = count($lineWords);
        if ($wordCount > 2) {
            $lastWord = array_pop($lineWords);
            if (strlen($lastWord) == 1) {   // tipul articolului
                $secondLastWord = array_pop($lineWords);
                if (OcrSpaceCommon::stringIsNumber($secondLastWord)) {  // acesta poate fi pretul eg: 23.02 A 
                    $cost = floatval(OcrSpaceCommon::toNumber($secondLastWord));
                }
            } else { // tipul articolului poate fi alipit de pret
                $presumedCost = substr($lastWord, 0, strlen($lastWord) - 2);
                if (OcrSpaceCommon::stringIsNumber($presumedCost)) {
                    $cost = floatval(OcrSpaceCommon::toNumber($presumedCost));
                    array_pop($lineWords);
                }
            }
        }

        return $cost;
    }

    private function extractQuantity(&$lineWords)
    {
        $wordsCount = count($lineWords);
        if ($wordsCount < 4) {
            return 0;
        }

        $lastWord = $lineWords[$wordsCount - 1];
        if ($lastWord == '=') {
            array_pop($lineWords);
            $wordsCount--;
            if ($wordsCount < 4) {
                return 0;
            }
            $lastWord = $lineWords[$wordsCount - 1];
        }

        $quantity = 0;
        $presumedUnitPrice = $lineWords[$wordsCount - 1];
        $presumedOrder = $lineWords[$wordsCount - 2];
        $presumedQuantity = $lineWords[$wordsCount - 4];        
        if (strtolower($presumedOrder) == 'x' && OcrSpaceCommon::stringIsNumber($presumedUnitPrice) && OcrSpaceCommon::stringIsNumber($presumedQuantity)) {
            $quantity = floatval(OcrSpaceCommon::toNumber($presumedQuantity));
            array_pop($lineWords);
            array_pop($lineWords);
            array_pop($lineWords);
            array_pop($lineWords);
        }

        return $quantity;
    }

    private function aggregateLinesWithSimilarHeight($sortedLines)
    {
        $ocrLines = [];
        $currentMaxHeight = -1;
        foreach($sortedLines as &$line) {
            $verticalPos = intval($line['MinTop'] ?? 0);
            if (isset($line['processed']) || $verticalPos < $currentMaxHeight) {
                continue;
            }
            $line['processed'] = true;            
            
            $text = $line['LineText'];            
            $textHeight = intval($line['MaxHeight'] ?? 0);
            $errorMargin = $textHeight * 0.3;
            $currentMaxHeight = $verticalPos - $errorMargin;

            $aggregate = [ $line ];
            foreach ($sortedLines as &$otherDetectedLine) {
                if (isset($otherDetectedLine['processed'])) {
                    continue;
                }

                $otherVerticalPos = intval($otherDetectedLine['MinTop'] ?? 0);
                $otherTextHeight = intval($otherDetectedLine['MaxHeight'] ?? 0);
                                
                if ($verticalPos - $errorMargin < $otherVerticalPos && 
                    $verticalPos + $textHeight + $errorMargin > $otherVerticalPos + $otherTextHeight)
                {
                    $otherDetectedLine['processed'] = true;  
                    $otherText = $otherDetectedLine['LineText'];
                    if ($text !== $otherText) {
                        $aggregate[] = $otherDetectedLine;
                    }
                }
            }

            $this->sortLineWordsLeftToRight($aggregate);
            
            $fullLine = [];
            foreach($aggregate as $wordLine) {
                $fullLine[] = $wordLine['LineText'];
            }            
            $ocrLines[] = implode(' ', $fullLine);            
        }

        return $ocrLines;
    }

    private function sortLineWordsLeftToRight(&$lineWords)
    {
        if (count($lineWords) < 2) {
            return;
        }

        $lineWordsToSort = &$lineWords;
        foreach($lineWordsToSort as &$lineWord) {
            $maxRight = -1;
            foreach($lineWord['Words'] as $word) {
                $right = $word['Left'] + $word['Width'];
                if ($right > $maxRight) {
                    $maxRight = $right;
                }
            }
            $lineWord['MaxRight'] = $maxRight;
        }

        usort($lineWordsToSort, 'OcrSpaceCommon::sortWordsLeftToRight');
    }

    private function getCorrectedWords($words)
    {
        $correctedWords = [];
        foreach($words as $word) {
            if ($correction = $this->correctionController->getCorrectedWord($word)) {
                $tok = strtok($correction, " ");
                while ($tok !== false) {
                    $correctedWords[] = $tok;
                    $tok = strtok(" ");
                }
            } else {
                $correctedWords[] = $word;
            }
        }

        return $correctedWords;
    }
}