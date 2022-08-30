<?php

require_once 'OcrSpaceCommon.php';

class OcrSpaceFormatter
{
    public function formatResultTable($storeName, $articles, $totalSum)
    {
        $dateNow = date('Y-m-d H:i:s');
        $tableResponse = '<form action="index.php" method="POST">';  
        $tableResponse .= '<input type="hidden" name="action" value="confirmConversion" /> ';
        $tableResponse .= '<input type="hidden" name="engine" value="OcrSpace" /> ';

        $tableResponse .= '<table>';
        $tableResponse .= '<tr><td>Data bon: </td><td><input class="receiptDateInput" name="receiptDate" value="'. $dateNow .'" /></td></tr>';
        $tableResponse .= '<tr><td style="vertical-align: top;">Magazin: </td><td><input class="storeInput" name="store" value="Nume magazin" /></td></tr>';
        $tableResponse .= '</table>';

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
            $htmlResponse .= "<input class='detectionInput' type='text' id='$baseId' name='$baseId' value='$field' readonly/>";
            $htmlResponse .= '</td>';
        }
        $htmlResponse .= self::renderCategory($id, $article[OcrSpaceCommon::ARTICLE_NAME]);
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

    private function renderCategory($id, $default)
    {
        $baseId = OcrSpaceCommon::CATEGORY . '_' . $id;

        $htmlResponse = '<td>';
        $htmlResponse .= '<label for="' . $baseId . '">Categorie:</label>';
        $htmlResponse .= '<br>';
        $htmlResponse .= "<input class='detectionInput' type='text' id='$baseId' name='$baseId' value='$default'/>";
        $htmlResponse .= '</td>';

        return $htmlResponse;
    }
}