<?php

require_once 'Page.php';
require_once 'Header.php';
require_once 'Footer.php';
require_once __DIR__ . '/../../engine/OcrSpaceCommon.php';
require_once __DIR__ . '/../../database_setup/DbConnection.php';

class ConversionPersist
{
    public static function getPage()
    {
        $content = Page::renderFragment('conversionPersist.html');

        $result = self::process();

        $content = str_replace("PERSIST_PLACEHOLDER", $result, $content);

        return Header::getPage() . $content . Footer::getPage();
    }

    private static function process()
    {
        $storeName = $_POST['store'] ?? 'Necunoscut';
        $idStore = self::findStoreID($storeName);
        if ($idStore < 1) {
            $idStore = self::getNewStoreID($storeName);
            if ($idStore < 1) {
                return "Eroare la baza de date. Magazinul nu a putut fi creat.";
            }
        }

        $idReceipt = self::getNextReceiptID();
        if ($idReceipt < 1) {
            return "Eroare la baza de date. Bonul nu a putut fi creat.";
        }
        $articles = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, OcrSpaceCommon::ARTICLE_NAME)) {
                $tokenSplit = explode('_', $key);
                $rowId = $tokenSplit[1] ?? null;
                if ($rowId) {
                    $articleKeyRowId = OcrSpaceCommon::ARTICLE_NAME . '_' . $rowId;
                    $quantityKeyRowId = OcrSpaceCommon::QUANTITY . '_' . $rowId;
                    $totalCostKeyRowId = OcrSpaceCommon::TOTAL_COST . '_' . $rowId;
                    $categoryKey = OcrSpaceCommon::CATEGORY . '_' . $rowId;

                    $quantity = isset($_POST[$quantityKeyRowId]) ? (float)($_POST[$quantityKeyRowId]) : 0;
                    $totalPrice = isset($_POST[$totalCostKeyRowId]) ? (float)($_POST[$totalCostKeyRowId]) : 0;
                    if ($quantity <= 0) {
                        $quantity = 1;
                    }
                    $unitPrice = $totalPrice / $quantity;
                    $description = $_POST[$articleKeyRowId] ?? '';
                    $category = $_POST[$categoryKey] ?? $description;
                    $articles[] = [
                        'description' => $description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'category' => $category
                    ];
                }
            }
        }

        $result = null;
        if(!empty($articles)) {
            $connection = new DbConnection();
            $connection->connect();

            try {
                $now = date('Y-m-d H:i:s');
                $receiptDate = $_POST['receiptDate'] ?? $now;
                $connection->insertPrepared(
                    'receipts',
                    ['id_receipt', 'id_store', 'date'],
                    [$idReceipt, $idStore, $receiptDate],
                    ['i', 'i', 's']
                );

                foreach ($articles as $article) {
                    $productId = self::getNextProductID();
                    if ($productId < 1) {
                        throw new Exception("Eroare la baza de date. Produsul nu a putut fi creat.");
                    }
                    $connection->insertPrepared(
                        'products',
                        ['description', 'id_product', 'id_receipt', 'quantity', 'total_price', 'unit_price', 'category'],
                        [
                            $article['description'],
                            $productId,
                            $idReceipt,
                            $article['quantity'],
                            $article['total_price'],
                            $article['unit_price'],
                            $article['category']
                        ],
                        ['s', 'i', 'i', 'd', 'd', 'd', 's']
                    );
                    if ($connection->lastError()) {
                        throw new Exception($connection->lastError());
                    }
                }
            } catch (Exception $ex) {
                $result = $ex->getMessage();
            } finally {
                $connection->disconnect();
            }
        } else {
            $result = "Eroare. Articolele nu au putut fi detectate.";
        }

        return $result ?? 'Incarcare cu succes.';
    }

    private static function findStoreID($storeName)
    {
        $storeId = -1;
        $connection = new DbConnection();
        if ($connection->connect()) {
            try {
                $res = $connection->queryPrepared(
                    'stores',
                    ['id_store'],
                    [
                        'name' => $storeName
                    ],
                    ['s']
                );
                $store = $res->fetch_assoc();
                if (isset($store['id_store'])) {
                    // existing store
                    $storeId = $store['id_store'];
                }
            } finally {
                $connection->disconnect();
            }
        }

        return $storeId;
    }

    private static function getNewStoreID($storeName)
    {
        $storeId = -1;
        $connection = new DbConnection();
        if ($connection->connect()) {
            try {
                $res = $connection->query("select IFNULL(MAX(id_store), 0) + 1 ID from stores");
                $row = $res->fetch_assoc();
                if (isset($row['ID'])) {
                    $storeId = $row['ID'];
                    $connection->insertPrepared(
                        'stores',
                        ['id_store', 'name'],
                        [$storeId, $storeName],
                        ['i', 's']
                    );
                }
            } catch (Exception) {
                $storeId = -1;
            }
            finally {
                $connection->disconnect();
            }
        }
        return $storeId;
    }

    private static function getNextReceiptID()
    {
        $connection = new DbConnection();
        if ($connection->connect()) {
            try {
                $res = $connection->query("select IFNULL(MAX(id_receipt), 0) + 1 ID from receipts");
                $row = $res->fetch_assoc();
            } finally {
                $connection->disconnect();
            }

            return $row['ID'] ?? -1;
        }

        return -1;
    }

    private static function getNextProductID()
    {
        $connection = new DbConnection();
        if ($connection->connect()) {
            try {
                $res = $connection->query("select IFNULL(MAX(id_product), 0) + 1 ID from products");
                $row = $res->fetch_assoc();
            } finally {
                $connection->disconnect();
            }

            return $row['ID'] ?? -1;
        }

        return -1;
    }
}