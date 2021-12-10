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

    private function formatDetectionForHtml($id, $value)
    {
        var_dump($value);
        $idDetection = "detectie_" . $id;
        $idAlias = "alias_" . $id;
        $idIgnore = "ignora_" . $id;
        $htmlResponse = '<td><label for="' . $idDetection . '">Detectie:</label><br><input class="detectionInput" type="text" id="' . $idDetection . '" value="'. $value['nume'] .'" readonly/></td>';
        $htmlResponse .= '<td><label for="' . $idAlias . '">Salveaza ca:</label><br><input class="detectionInput" type="text" id="' . $idAlias . '" value="'. $value['corectieNume'] .'" /></td>';
    
        return $htmlResponse;
    }
}