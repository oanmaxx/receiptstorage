<?php

function sortWordsLeftToRight($lineA, $lineB) {
    return $lineA['MaxRight'] > $lineB['MaxRight'];
}

class OcrSpaceReceiptDecoder
{
    private $correctionController;
    private $ocrLines;

    public function __construct($decodedLines)
    {
        $this->correctionController = new CorrectionController();

        $this->ocrLines = $this->aggregateLinesWithSimilarHeight($decodedLines);
        var_dump($this->ocrLines);
    }

    public function getArticles()
    {
        $result = [];
        foreach($this->ocrLines as $line) {
            $words = $line['Words'];
            $wordCount = count($words);
            if ($wordCount > 1) {
                $lastWord = $words[$wordCount-1]['WordText'] ?? '';
                $secondLastWord = $words[$wordCount-2]['WordText'] ?? '';
                if (strlen($lastWord) == 1) {   // tipul articolului
                    $number = str_replace(',', '.', $secondLastWord);
                    if (is_numeric($number)) {  // acesta poate fi pretul eg: 23.02 A 
                        $cost = floatval($number);
                        $article = $this->detectArticle($this->ocrLines, $line, $secondLastWord);
                        if ($cost > 0 && $article) { // nu consideram promotii cu pret 0          
                            $result[] = array_merge($article, [ 'totalCost' => $cost ]);
                        }
                    }
                }
            }
        }

        return $result;
    }
    
    private function sortLinesLeftToRight(&$lineWords)
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

        usort($lineWordsToSort, 'sortWordsLeftToRight');
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

            $this->sortLinesLeftToRight($aggregate);
            
            $fullLine = [];
            foreach($aggregate as $wordLine) {
                $fullLine[] = $wordLine['LineText'];
            }            
            $ocrLines[] = implode(' ', $fullLine);            
        }

        return $ocrLines;
    }

    private function detectArticle($decodedLines, $articleCostLine, $costStr)
    {
        $verticalPos = intval($articleCostLine['MinTop'] ?? 0);
        $textHeight = intval($articleCostLine['MaxHeight'] ?? 0);

        if (!$verticalPos || !$textHeight) {
            return [];
        }        

        $errorMargin = $verticalPos * 0.01;
        $minSearchVertical = $verticalPos - $errorMargin;
        $maxSearchVertical = $verticalPos + $textHeight + $errorMargin;

        $costLineText = $articleCostLine['LineText'] ?? 'Unknown';        
        $articleName = '';
        $articleNameCorrected = '';
        $quantityText = '';
        $quantityTextCorrected = '';
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
                        if (strpos($correctedLineText, $costStr) !== false) {
                            $quantityText = $lineText;
                            $quantityTextCorrected = $correctedLineText;
                            $quantity = $this->detectQuantity($correctedLineText, $lineWords[0]);
                        } else {
                            $articleName .= $lineText . ' ';
                            $articleNameCorrected .= $correctedLineText . ' ';
                        }
                    }
                }
            }
            if (!empty($articleName) && $quantity > 0) {
                break;
            }
        }

        return [ 
            'articleName' => trim($articleName), 
            'articleNameCorrected' => trim($articleNameCorrected), 
            'quantityText' =>  $quantityText,
            'quantityTextCorrected' => $quantityTextCorrected,
            'quantity' => $quantity
        ];
    }

    private function getCorrectedLine($lineText, $lineWords)
    {
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

    private function detectQuantity($correctedLineText, $quantityWord)
    {
        $quantity = 1;
        if (!isset($quantityWord['WordText'])) {
            return $quantity;
        }

        $firstWordCorrected = $this->correctionController->getCorrectedWord($quantityWord['WordText'])['result'];
        if (is_numeric($firstWordCorrected)) {
            $quantity = intval($firstWordCorrected);
        } else {
            $firstWord = strtok($correctedLineText, " ");
            if (is_numeric($firstWord)) {
                $quantity = intval($firstWord);
                $this->correctionController->addCorrectedWord($quantityWord['WordText'], $firstWord);
            }
        }

        return $quantity > 0 ? $quantity : 1;
    }
}