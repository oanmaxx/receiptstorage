<?php

require_once 'OcrSpaceCommon.php';

class OcrSpaceFormatter
{
    public function formatResultTable($storeName, $articles, $totalSum)
    {    
        $tableResponse = '<form action="index.php" method="POST">';  
        $tableResponse .= '<input type="hidden" name="action" value="confirmConversion" /> ';
        $tableResponse .= '<input type="hidden" name="engine" value="OcrSpace" /> ';
        $tableResponse .= '<table>';
        
        $i = 1;
        foreach($articles as $article) {
            $tableResponse .= $this->formatDetectionForHtml($i, $article);
            if ($i++ > 10000) {
                break;
            }            
        }

        $tableResponse .= '</table>';
        $tableResponse .= '<button type="submit">Confirma</button>';        
        $tableResponse .= '</form>';

        return $tableResponse;
    }

    private function formatDetectionForHtml($id, $article)
    {
        $htmlResponse = '';
        if (isset($article[OcrSpaceCommon::ORIGINAL_LINE])) {
            $htmlResponse .= '<tr><td colspan="'. count($article) . '">';
            $htmlResponse .= '<label>Detectie: '. $article[OcrSpaceCommon::ORIGINAL_LINE] .'</label>';
            $htmlResponse .= '</td></tr>';
        }

        $htmlResponse .= '<tr>';
        foreach($article as $key => $field) {
            $baseId = $key . '_' . $id;
            if ($key == OcrSpaceCommon::ORIGINAL_LINE) {
                continue;
            }

            $htmlResponse .= '<td>';
            $htmlResponse .= '<label for="' . $baseId . '">' . $this->getTranslation($key) . ':</label>';
            $htmlResponse .= '<br>';
            $htmlResponse .= '<input class="detectionInput" type="text" id="' . $baseId . '" value="'. $field .'" readonly/>';
            $htmlResponse .= '</td>';
        }        
        $htmlResponse .= '</tr>';
    
        return $htmlResponse;
    }

    private function getTranslation($key)
     {
        $text = '';
        switch($key) {
            case OcrSpaceCommon::TOTAL_COST:
                $text = "Pret total";
                break;
            case OcrSpaceCommon::ARTICLE_NAME:
                $text = 'Nume articol';
                break;
            case OcrSpaceCommon::QUANTITY_TEXT:
                $text = 'Cantitate';
                break;
            case OcrSpaceCommon::QUANTITY:
                $text = 'Cantitate detectata';
                break;
            default:
                $text = 'Detectie';
                break;
        }

        return $text;
    }
}