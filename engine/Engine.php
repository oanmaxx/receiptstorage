<?php

require __DIR__ . '/vendor/autoload.php';
require_once 'DummyResponse.php';
require_once 'OcrSpaceFormatter.php';
require_once 'OcrSpaceReceiptDecoder.php';
require_once 'CorrectionController.php';

class Engine
{
    public static function uploadToApi($target_file)
    {
		$result = self::parseWithDummy($target_file);
		if ($result == null) {
            $result = self::parseWithOCRSpace($target_file);
		}

        return $result;
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

            $result = self::parseResponse($r->getBody());

        } catch(Exception $err) {            
            $result = $err->getMessage();
        }

        return $result;
    }

    private static function parseWithDummy($target_file)
    {
        $target_file = strtolower($_FILES["attachment"]["name"]);
        $dummyData = DummyResponse::getDummyData($target_file);
		
		if ($dummyData != null) {
			return self::parseResponse($dummyData);
		}

        return null;
    }

    private static function parseResponse($jsonData)
    {
        $response = json_decode($jsonData, true);
        if (isset($response['ErrorMessage']) && !empty($response['ErrorMessage'])) {
            return $response['ErrorMessage'];
        }
        
        $lines = isset($response['ParsedResults'][0]['TextOverlay']['Lines'])
            ? $response['ParsedResults'][0]['TextOverlay']['Lines']
            : ($response['ParsedResults'][0]['Overlay']['Lines'] ?? null);
                    
        return $lines ? self::formatToHtml($lines) : 'Invalid Response';  
    }

    private static function formatToHtml($decodedLines)
    {
        $decoder = new OcrSpaceReceiptDecoder($decodedLines);
        $storeName = '';
        $articles = $decoder->getArticles();
        $totalSum = 0;
        
        $formatter = new OcrSpaceFormatter();
        return $formatter->formatResultTable($storeName, $articles, $totalSum);
    }
}