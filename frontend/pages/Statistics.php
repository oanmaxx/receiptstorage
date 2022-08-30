<?php

require_once 'Page.php';
require_once 'Header.php';
require_once 'Footer.php';
require_once __DIR__ . '/../../engine/Engine.php';
require_once __DIR__ . '/../../database_setup/DbConnection.php';

class Statistics
{
    public static function getPage()
    {
        $content = Page::renderFragment('Statistics.html');

        $result = self::process();

        $content = str_replace("STATISTICS_PLACEHOLDER", $result, $content);

        return Header::getPage() . $content . Footer::getPage();
    }

    private static function process()
    {
        $search = $_GET['search'] ?? null;
        $html = '';
        if ($search) {
            $regexSearch = $search === '*' ? '%' : "%$search%";
            $distinctPrices = self::pretDistinctLunarProdusPerMagazin($regexSearch);
            $html .= '<table>';
            $html .= '<thead><tr><th>An</th><th>Luna</th><th>Produs</th><th>Pret</th><th>Magazin</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($distinctPrices as $price) {
                $an = $price['AN'];
                $luna = $price['LUNA'];
                $prod = $price['Produs'];
                $pret = $price['Pret_Unitar'];
                $magazin = $price['Magazin'];
                $html .= '<tr>';
                $html .= "<td>$an</td><td>$luna</td><td>$prod</td><td>$pret</td><td>$magazin</td>";
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        return $html;
    }

    private static function pretDistinctLunarProdusPerMagazin($product)
    {
        $sql = "
            SELECT DISTINCT
              p.category Produs,
              p.unit_price Pret_Unitar,
              YEAR(r.date) AN,
              MONTH(r.date) LUNA,
              s.name Magazin
            FROM `products` p
            LEFT JOIN `receipts` r ON p.id_receipt = r.id_receipt
            LEFT JOIN `stores` s ON s.id_store = r.id_store
            WHERE p.category like ?
            GROUP BY p.category, YEAR(r.date), MONTH(r.date), s.name
            ORDER BY r.date desc";

        $result = [];
        $db = new DbConnection();
        if ($db->connect()) {
            try {
                $res = $db->executePrepared($sql, [$product], 's');
                while ($row = $res->fetch_assoc()) {
                    $result[] = [
                        'Produs' => $row['Produs'],
                        'Pret_Unitar' => $row['Pret_Unitar'],
                        'AN' => $row['AN'],
                        'LUNA' => $row['LUNA'],
                        'Magazin' => $row['Magazin']
                    ];
                }
            } finally {
                $db->disconnect();
            }
        }
        return $result;
    }
}