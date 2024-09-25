<?php

namespace Core\Model;

/**
 * Class ShipModulesData
 */
class ShipModulesData extends Model
{
    public function addShipModulesData(string $json): void
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $output = $json_data['message']['marketId'] . ' ' . count($json_data['message']['modules']);
        echo $output . "\n";

        if (count($json_data['message']['modules']) < 1) {
            return;
        }

        $modules = [];
        $market_id = (int)$json_data['message']['marketId'];

        foreach ($json_data['message']['modules'] as $i => $module) {
            $modules[$i]['name'] = $module;
            $modules[$i]['marketId'] = $market_id;
            $modules[$i]['timestamp'] = htmlspecialchars($json_data['message']['timestamp']);
        }

        unset($json_data);

        $paramArray = [];
        $sqlArray = [];

        foreach ($modules as $row) {
            $sqlArray[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';

            // flatten source array
            foreach ($row as $element) {
                $paramArray[] = $element;
            }
        }

        // sql query 1st part - table, columns
        $sql = 'INSERT IGNORE INTO ship_modules 
        (name, market_id, timestamp) 
        VALUES';

        // sql query 2nd part - placeholders
        $sql .= implode(',', $sqlArray);

        // sql query 3rd part - columns to update
        // $sql .= "ON DUPLICATE KEY UPDATE
        //         timestamp=VALUES(timestamp)";

        // delete previous records for station
        $sql_del = 'DELETE FROM ship_modules
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
