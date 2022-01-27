<?php

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
            $tableResponse .= '<tr>' . $this->formatDetectionForHtml($i, $article);

            $tableResponse .= '</tr>';
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
        foreach($article as $key => $field) {
            $baseId = $key . '_' . $id;
            $htmlResponse .= '<td>';
            $htmlResponse .= '<label for="' . $baseId . '">' . $this->getTranslation($key) . ':</label>';
            $htmlResponse .= '<br>';
            $readonly = (strstr($key, 'Corrected') == false ? 'readonly' : '');
            $htmlResponse .= '<input class="detectionInput" type="text" id="' . $baseId . '" value="'. $field .'" '. $readonly .'/>';
            $htmlResponse .= '</td>';
        }        
    
        return $htmlResponse;
    }

    private function getTranslation($key)
     {
        $text = '';
        switch($key) {
            case 'totalCost':
                $text = "Pret total";
                break;
            case 'articleName':
                $text = 'Nume articol';
                break;
            case 'articleNameCorrected':
                $text = 'Corectie nume articol';
                break;
            case 'quantityText':
                $text = 'Cantitate';
                break;
            case 'quantityTextCorrected':
                $text = 'Corectie cantitate';
                break;
            case 'quantity':
                $text = 'Cantitate detectata';
                break;
            default:
                $text = 'Detectie';
                break;
        }

        return $text;
    }
}