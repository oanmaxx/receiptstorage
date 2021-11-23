<?php

require __DIR__ . '/vendor/autoload.php';
require_once 'DummyResponse.php';

class Engine
{
    public static function uploadToApi($target_file)
    {        
        //$result = self::parseWithOCRSpace($target_file);
        $result = self::parseWithDummy($target_file);

        return $result;
    }

    private static function parseWithDummy($target_file)
    {
        $dummyData = DummyResponse::getDummyResponse3();
        $response =  json_decode($dummyData,true);
        return self::formatResultTable($response['ParsedResults']);
    }

    private static function parseWithOCRSpace($target_file)
    {
        $result = '';
        $fileData = fopen($target_file, 'r');

        $client = new \GuzzleHttp\Client(array(
            'request.options' => array (
                'timeout' => 300,
                'connect_timeout' => 300 
            ) 
        ));

        try {
            $r = $client->request('POST', 'https://api.ocr.space/parse/image',
                [
                    'headers' => [
                        'apiKey' => '618adf964888957'
                    ],
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $fileData
                        ],
                        [
                            'name' => 'scale',
                            'contents' => 'true'
                        ],
                        [
                            'name' => 'isTable',
                            'contents' => 'true'
                        ],
                        [
                            'name' => 'isCreateSearchablePdf',
                            'contents' => 'true'
                        ],
                        [
                            'name' => 'isOverlayRequired',
                            'contents' => 'true'
                        ],
                        [
                            'name' => 'OCREngine',
                            'contents' => '2'
                        ]
                    ],                    
                ],
                [ 
                    'file' => $fileData
                ]
            );
            $response =  json_decode($r->getBody(),true);            
            if (!isset($response['ErrorMessage']) || empty($response['ErrorMessage'])) {
                $result = self::formatResultTable($response['ParsedResults']);
            } else {
                $result = $response['ErrorMessage'];
            }
        } catch(Exception $err) {            
            $result = $err->getMessage();
        }

        return $result;
    }

    private static function formatResultTable($resultArray)
    {
        if (!isset($resultArray[0]['Overlay']['Lines']))
        {
            return 'Invalid Response';
        }

        $lines = $resultArray[0]['Overlay']['Lines'];
    
        $tableResponse = '<form action="index.php" method="POST">';  
        $tableResponse .= '<input type="hidden" name="action" value="confirmConversion" /> ';
        $tableResponse .= '<table>';
        
        $i = 0;
        foreach($lines as $line) {
            $tableResponse .= '<tr><td>';
            $tableResponse .= '<input type="text" name="value" value="'. $line['LineText'] .'" />'; 
            $tableResponse .= '</td></tr>';
            if ($i++ > 10000) {
                break;
            }            
        }

        $tableResponse .= '</table>';
        $tableResponse .= '<button type="submit">Confirma</button>';        
        $tableResponse .= '</form>';

        return $tableResponse;
    }

}