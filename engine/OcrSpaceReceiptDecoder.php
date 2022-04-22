<?php

require_once 'OcrSpaceCommon.php';
require_once 'CorrectionController.php';

class OcrSpaceReceiptDecoder
{
    private $correctionController;
    private $ocrLines;
    private $otherPossibleArticles;

    public function __construct($decodedLines)
    {
        $this->correctionController = new CorrectionController();

        $this->ocrLines = $this->aggregateLinesWithSimilarHeight($decodedLines);
        $this->otherPossibleArticles = [];
    }

    public function getArticles()
    {
        $result = [];
        $lineCount = count($this->ocrLines);
        for ($idx = 0; $idx < $lineCount; $idx++) {
            $line = $this->ocrLines[$idx];
            $lineWords = explode(' ', $line);
            $correctedWords = $this->getCorrectedWords($lineWords);
            $cost = $this->extractArticleCost($correctedWords);
            if (!empty($cost)) { // avem o linie care contine pretul unui articol
                $articleResult = null;

                $presumedArticleWords = [];
                foreach($correctedWords as $articleWord) {
                    $presumedArticleWords[] = $articleWord;
                }
                
                $quantity = $this->extractQuantity($presumedArticleWords);
                if (!empty($quantity)) {
                    $articleWordsCount = count($presumedArticleWords);
                    $articleWords = array_slice($correctedWords, 0, $articleWordsCount);                    
                    $articleStr = implode(' ', $articleWords);
                    $finalLine = sprintf("%s %s = %s (%s)", $articleStr, $quantity['lineText'], $cost['price'], $cost['type']);
                    $articleResult = [
                        OcrSpaceCommon::LINE => $finalLine,
                        OcrSpaceCommon::ARTICLE_NAME => $articleStr,
                        OcrSpaceCommon::QUANTITY_TEXT => $quantity['lineText'],
                        OcrSpaceCommon::QUANTITY => floatval($quantity['quantity']),
                        OcrSpaceCommon::TOTAL_COST => floatval($cost['price'])
                    ];                    
                } else if (!empty($presumedArticleWords)) {
                    $prevLine = $this->ocrLines[$idx-1];
                    $prevLineWords = explode(' ', $prevLine);
                    $correctedPrevLineWords = $this->getCorrectedWords($prevLineWords);
                    $quantity = $this->extractQuantity($correctedPrevLineWords);
                    if (!empty($quantity)) {
                        $articleStr = implode(' ', $presumedArticleWords);
                        $finalLine = sprintf("%s %s = %s (%s)", $articleStr, $quantity['lineText'], $cost['price'], $cost['type']);
                        $articleResult = [
                            OcrSpaceCommon::LINE => $finalLine,
                            OcrSpaceCommon::ARTICLE_NAME => $articleStr,
                            OcrSpaceCommon::QUANTITY_TEXT => $quantity['lineText'],
                            OcrSpaceCommon::QUANTITY => floatval($quantity['quantity']),
                            OcrSpaceCommon::TOTAL_COST => floatval($cost['price'])
                        ];
                    }
                }

                if (!is_null($articleResult)) {
                    $result[] = $articleResult;
                } else {
                    $this->otherPossibleArticles[] = $line;
                }
            }
        }

        // var_dump($this->otherPossibleArticles);

        return $result;
    }

    private function extractArticleCost(&$lineWords)
    {
        $cost = [];
        $wordCount = count($lineWords);
        if ($wordCount > 2) {
            $lastWord = array_pop($lineWords);
            if (strlen($lastWord) == 1) {   // tipul articolului
                $secondLastWord = array_pop($lineWords);
                if (OcrSpaceCommon::stringIsNumber($secondLastWord)) {  // acesta poate fi pretul eg: 23.02 A 
                    $cost = [
                        'type' => $lastWord,
                        'price' => OcrSpaceCommon::toNumber($secondLastWord)
                    ];
                }
            } else { // tipul articolului poate fi alipit de pret
                $presumedCost = substr($lastWord, 0, strlen($lastWord) - 2);
                if (OcrSpaceCommon::stringIsNumber($presumedCost)) {
                    $cost = [
                        'type' => $lastWord,
                        'price' => OcrSpaceCommon::toNumber($presumedCost)
                    ];
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

        $quantity = [];
        $presumedUnitPrice = $lineWords[$wordsCount - 1];
        $presumedOrder = $lineWords[$wordsCount - 2];
        $presumedUnitType = $lineWords[$wordsCount - 3];
        $presumedQuantity = $lineWords[$wordsCount - 4];
        
        if (strtolower($presumedOrder) == 'x' && OcrSpaceCommon::stringIsNumber($presumedUnitPrice) && OcrSpaceCommon::stringIsNumber($presumedQuantity)) {
            $quantity = OcrSpaceCommon::toNumber($presumedQuantity);
            $unitPrice = OcrSpaceCommon::toNumber($presumedUnitPrice);
            $quantity = [
                'lineText' => sprintf("%s %s X %s", $quantity, $presumedUnitType, $unitPrice),
                'unitprice' => $presumedUnitPrice,
                'unittype' => $unitPrice,
                'quantity' => $quantity
            ] ;
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