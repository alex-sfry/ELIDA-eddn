<?php

namespace Core\Model;

/**
 * Class ShipyardData
 */
class ShipyardData extends Model
{
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

        $market_id = (int)$json_data['message']['marketId'];

        foreach ($json_data['message']['ships'] as $i => $ship) {
            $ships[$i]['name'] = $ship;
            $ships[$i]['marketId'] = $market_id;
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

        // delete previous records for station
        $sql_del = 'DELETE FROM shipyard
        WHERE market_id=:market_id';
        $query = self::getConnection()->prepare($sql_del);
        $query->bindParam(':market_id', $market_id, \PDO::PARAM_INT);
        $query->execute();
        echo 'deleted ' . $query->rowCount() . "rows\n";

        $query = self::getConnection()->prepare($sql);
        $query->execute($paramArray);

        echo 'added ' . $query->rowCount() . "rows\n";
    }
}
