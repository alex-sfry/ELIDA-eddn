<?php

namespace Core\Model;

/**
 * Class ShipyardData
 */
class ShipyardData extends Model
{
    /**
     * @param string $json
     *
     * @return void
     */
    public function addShipyardData(string $json): void
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $output = $json_data['message']['marketId'] . ' ' . count($json_data['message']['ships']);
        echo $output . "\n";

        if (count($json_data['message']['ships']) < 1) {
            return;
        }

        $ships = [];

        foreach ($json_data['message']['ships'] as $i => $ship) {
            $ships[$i]['name'] = $ship;
            $ships[$i]['marketId'] = (int)$json_data['message']['marketId'];
            $ships[$i]['timestamp'] = htmlspecialchars($json_data['message']['timestamp']);
        }

        unset($json_data);

        $paramArray = [];
        $sqlArray = [];

        foreach ($ships as $row) {
            $sqlArray[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';

            // flatten source array
            foreach ($row as $element) {
                $paramArray[] = $element;
            }
        }

        // sql query 1st part - table, columns
        $sql = 'INSERT IGNORE INTO shipyard 
        (name, market_id, timestamp) 
        VALUES';

        // sql query 2nd part - placeholders
        $sql .= implode(',', $sqlArray);

        // sql query 3rd part - columns to update
        $sql .= "ON DUPLICATE KEY UPDATE 
                timestamp=VALUES(timestamp)";

        $query = self::getConnection()->prepare($sql);
        $query->execute($paramArray);

        echo 'added / updated ' . $query->rowCount() . "rows\n";

        if ($query->rowCount() === 0) {
            $err = $query->errorInfo()[2];
            echo $err . "\n";
        }
    }
}
