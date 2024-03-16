<?php

namespace Core\Model;

/**
 * Class MarketData
 */
class MarketData extends Model
{
    protected const RESTRICTED_VALUES = [
        'statusFlags',
        'Producer',
        'Rare',
        'id'
    ];

    /**
     * @param string|null $json
     *
     * @return void
     */
    public function addMarketData(string $json = null): void
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $output = $json_data['message']['marketId'] . ' ' . count($json_data['message']['commodities']);
        echo $output . "\n";

        if (count($json_data['message']['commodities']) < 1) {
            return;
        }

        foreach ($json_data['message']['commodities'] as $i => &$commodity) {
            $commodity = array_filter($commodity, function ($key) {
                return !in_array($key, self::RESTRICTED_VALUES);
            }, ARRAY_FILTER_USE_KEY);

            switch (true) {
                case !is_numeric($commodity['buyPrice']):
                    $commodity['buyPrice'] = 0;
                case !is_numeric($commodity['demand']):
                    $commodity['demand'] = 0;
                case !is_numeric($commodity['meanPrice']):
                    $commodity['meanPrice'] = 0;
                case !is_numeric($commodity['stockBracket']):
                    $commodity['stockBracket'] = 0;
                case !is_numeric($commodity['demandBracket']):
                    $commodity['demandBracket'] = 0;
                case !is_numeric($commodity['sellPrice']):
                    $commodity['sellPrice'] = 0;
                case !is_numeric($commodity['stock']):
                    $commodity['stock'] = 0;
            }

            $commodity['marketId'] = (int)$json_data['message']['marketId'];
            $commodity['timestamp'] = htmlspecialchars($json_data['message']['timestamp']);
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

        // sql query 1st part - table, columns
        $sql = 'INSERT IGNORE INTO markets 
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

        $query = self::getConnection()->prepare($sql);
        $query->execute($paramArray);

        echo 'added / updated ' . $query->rowCount() . "rows\n";

        if ($query->rowCount() === 0) {
            $err = $query->errorInfo()[2];
            echo $err . "\n";
        }
    }
}
