<?php

namespace Eddn;

class MarketData
{
    const RESTRICTED_VALUES = [
        'statusFlags',
        'Producer',
        'Rare',
        'id'
    ];

    public static function addMarketData($pdo, $json = null)
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $output = $json_data['message']['marketId'] . ' ' . count($json_data['message']['commodities']);
        echo $output . "\n";

        if (count($json_data['message']['commodities']) < 1) return;

        for ($i = 0; $i < count($json_data['message']['commodities']); $i++) {
            $json_data['message']['commodities'][$i] =
                array_filter($json_data['message']['commodities'][$i], function ($key) {
                    return ($key !== self::RESTRICTED_VALUES[0] &&
                            $key !== self::RESTRICTED_VALUES[1] &&
                            $key !== self::RESTRICTED_VALUES[2] &&
                            $key !== self::RESTRICTED_VALUES[3]
                        );
                }, ARRAY_FILTER_USE_KEY);

            if (!is_numeric($json_data['message']['commodities'][$i]['buyPrice'])) {
                $json_data['message']['commodities'][$i]['buyPrice'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['demand'])) {
                $json_data['message']['commodities'][$i]['demand'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['meanPrice'])) {
                $json_data['message']['commodities'][$i]['meanPrice'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['stockBracket'])) {
                $json_data['message']['commodities'][$i]['stockBracket'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['demandBracket'])) {
                $json_data['message']['commodities'][$i]['demandBracket'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['sellPrice'])) {
                $json_data['message']['commodities'][$i]['sellPrice'] = 0;
            }

            if (!is_numeric($json_data['message']['commodities'][$i]['stock'])) {
                $json_data['message']['commodities'][$i]['stock'] = 0;
            }

            $json_data['message']['commodities'][$i]['marketId'] =
                (int)$json_data['message']['marketId'];

            $json_data['message']['commodities'][$i]['timestamp'] =
                htmlspecialchars($json_data['message']['timestamp']);
        }

        $paramArray = [];
        $sqlArray = [];

        // create placeholders and flatten source array
        foreach ($json_data['message']['commodities'] as $row) {
            $sqlArray[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';

            // flatten source array
            foreach ($row as $element) {
                $paramArray[] = $element;
            }
        }

        // if (count($sqlArray) < 1) return;

        // sql query 1st part - table, columns
        $sql = 'INSERT INTO markets 
        (buy_price, demand, demand_bracket, mean_price,`name`,
        sell_price, stock, stock_bracket, market_id, `timestamp`) 
        VALUES';

        // sql query 2nd part - placeholders
        $sql .= implode(',', $sqlArray);

        // sql query 3rd part - columns to update
        $sql .= "ON DUPLICATE KEY UPDATE 
        buy_price=VALUES(buy_price), demand=VALUES(demand), 
        demand_bracket=VALUES(demand_bracket), mean_price=VALUES(mean_price), 
        sell_price=VALUES(sell_price), stock=VALUES(stock), 
        stock_bracket=VALUES(stock_bracket), timestamp=VALUES(timestamp)";

        $query = $pdo->prepare($sql);
        $query->execute($paramArray);

        echo 'added / updated ' . $query->rowCount() . "rows\n";

        if ($query->rowCount() === 0) {
            $err = $query->errorInfo()[2];
            echo $err . "\n";
        }
    }
}
