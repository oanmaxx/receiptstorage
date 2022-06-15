<?php

require_once __DIR__ . '/../engine/dummy_data/DummyYummyYang.php';
require_once __DIR__ . '/../engine/dummy_data/DummyDecathlon.php';
require_once __DIR__ . '/../engine/dummy_data/DummyLunchBox.php';
require_once __DIR__ . '/../engine/dummy_data/DummyMega.php';
require_once __DIR__ . '/../engine/dummy_data/DummyNarcoffee.php';
require_once __DIR__ . '/../engine/dummy_data/DummyCaro.php';
require_once __DIR__ . '/../engine/dummy_data/DummyCarrefour.php';

require_once __DIR__ . '/../engine/OcrSpaceReceiptDecoder.php';


function getLines($data) {
    $response = json_decode($data, true);
    $lines = isset($response['ParsedResults'][0]['TextOverlay']['Lines'])
        ? $response['ParsedResults'][0]['TextOverlay']['Lines']
        : ($response['ParsedResults'][0]['Overlay']['Lines'] ?? null);
    return $lines;
}

function getDifferences($actual, $expected) {
    $differences = [];
    $expectedCount = count($expected);
    $actualCount = count($actual);
    $max = $expectedCount > $actualCount ? $expectedCount : $actualCount;
    for ($idx = 0; $idx < $max; $idx++) {
        $expectedItem = $idx < $expectedCount ? $expected[$idx] : null;
        $actualItem = $idx < $actualCount ? $actual[$idx] : null;
        if ($expectedItem) {        
            foreach($expectedItem as $key => $value) {   
                if (!isset($actualItem[$key]) || strcmp($actualItem[$key], $value) !== 0) {
                    $actualValue = $actualItem[$key] ?? null;
                    $differences[] = [
                        'expected' => $key . ' => ' . $value,
                        'actual' => $actualValue,
                    ];
                }
            }
        } else if ($actualItem) {
            foreach($actualItem as $key => $value) {
                $differences[] = [
                    'expected' => '',
                    'actual' => $value
                ];
            }
        }
    }
    return $differences;
}

function showExpectedAndActual($expected, $actual) {
    $expectedCount = count($expected);
    $actualCount = count($actual);
    $max = $expectedCount > $actualCount ? $expectedCount : $actualCount;
    echo '<table>';
    echo '<th>Expected Rows</th><th>Actual Rows</th>';  
    for ($i = 0; $i<$max; $i++){
        $expectedItem = $expected[$i] ?? null;
        $actualItem = $actual[$i] ?? null;
        echo "<tr>";
        echo "<td style='background-color:lightgrey'>". $expectedItem[OcrSpaceCommon::ORIGINAL_LINE] ?? '' . "</td>";
        echo "<td>".  $actualItem[OcrSpaceCommon::ORIGINAL_LINE] ?? '' . "</td>";
        echo "</tr>";
    }
    echo '</table>';
}

function showDifferences($testId, $actual, $expected) {
    $differences = getDifferences($actual, $expected);
    $hasDifferences = !empty($differences);
    if ($hasDifferences) {
        echo '<label style="background-color:red">Tests for ' . $testId . ' have failed:</label>';
        showExpectedAndActual($expected, $actual);

        echo '<table>';
        echo '<th>Expected Values</th><th>Actual Values</th>';
        foreach($differences as $expectedArticle) {            
            echo '<tr>';
            echo "<td style='background-color:lightgrey'>". $expectedArticle['expected'] . "</td>";
            echo "<td>". $expectedArticle['actual'] . "</td>";                                
        }
        echo '</table>';
    } else {
        echo '<label style="background-color:lightgreen">Tests for ' . $testId . ' have passed:</label>';
        showExpectedAndActual($expected, $actual);   
    }
}


$response = DummyYummyYang::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyYummyYang::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyYummyYang', $actual, $expectation);

$response = DummyMega::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyMega::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyMega', $actual, $expectation);

$response = DummyNarcoffee::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyNarcoffee::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyNarcoffee', $actual, $expectation);


$response = DummyDecathlon::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyDecathlon::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyDecathlon1', $actual, $expectation);


$response = DummyDecathlon::getDummyResponse2();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyDecathlon::getDummyExpectation2();
$actual = $decoder->getArticles();
showDifferences('DummyDecathlon2', $actual, $expectation);


$response = DummyDecathlon::getDummyResponse3();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyDecathlon::getDummyExpectation3();
$actual = $decoder->getArticles();
showDifferences('DummyDecathlon3', $actual, $expectation);


$response = DummyCaro::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyCaro::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyCaro', $actual, $expectation);


$response = DummyLunchBox::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyLunchBox::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyLunchBox', $actual, $expectation);


$response = DummyCarrefour::getDummyResponse1();
$decoder = new OcrSpaceReceiptDecoder(getLines($response));
$expectation = DummyCarrefour::getDummyExpectation1();
$actual = $decoder->getArticles();
showDifferences('DummyCarrefour', $actual, $expectation);