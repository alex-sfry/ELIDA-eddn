<?php

namespace Core\Model;

use Core\Debug\Debug;

/**
 * Class RingsData
 */
class RingsData extends Model
{
    private $ring_types = [
        'eRingClass_Icy' => 'Icy',
        'eRingClass_Metalic' => 'Metallic',
        'eRingClass_MetalRich' => 'Metal Rich',
        'eRingClass_Rocky' => 'Rocky'
    ];

    public function addRingsData(string $json): void
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $data = $json_data['message'];

        if (!isset($data['Rings'])) {
            return;
        }
        if (!isset($data['ReserveLevel'])) {
            return;
        }
        if (isset($data['ReserveLevel']) && $data['ReserveLevel'] !== 'PristineResources') {
            return;
        }

        $paramArray = [];
        $sqlArray = [];
        $result = [];

        foreach ($data['Rings'] as $row) {
            if (!isset($row['RingClass'])) {
                continue;
            }
            if ($row['RingClass'] !== 'eRingClass_Metalic') {
                continue;
            }

            $result['name'] = isset($row['Name']) && $row['Name'] ?
                $row['Name'] : null;
            $result['type'] = isset($row['RingClass']) && $row['RingClass'] ?
                $this->ring_types[$row['RingClass']] : null;
            $result['system_name'] = isset($data['StarSystem']) && $data['StarSystem'] ?
                $data['StarSystem'] : null;
            $result['x'] = isset($data['StarPos']) && is_array($data['StarPos']) && count($data['StarPos']) === 3 ?
                $data['StarPos'][0] : null;
            $result['y'] = isset($data['StarPos']) && is_array($data['StarPos']) && count($data['StarPos']) === 3 ?
                $data['StarPos'][1] : null;
            $result['z'] = isset($data['StarPos']) && is_array($data['StarPos']) && count($data['StarPos']) === 3 ?
                $data['StarPos'][2] : null;
            $result['distance_to_arrival'] = isset($data['DistanceFromArrivalLS']) && $data['DistanceFromArrivalLS'] ?
                (int)$data['DistanceFromArrivalLS'] : null;
            $result['body_name'] = isset($data['BodyName']) && $data['BodyName'] ?
                $data['BodyName'] : null;
            $result['reserve'] = isset($data['ReserveLevel']) && $data['ReserveLevel'] ?
                $data['ReserveLevel'] : null;
            $result['timestamp'] = isset($data['timestamp']) && $data['timestamp'] ?
                $data['timestamp'] : null;
        }

        $sqlArray[] = '(' . implode(',', array_fill(0, count($result), '?')) . ')';

        Debug::f($sqlArray);
        Debug::f($paramArray);

        // flatten source array
        foreach ($result as $element) {
            $paramArray[] = $element;
        }

        Debug::f($paramArray);

        $sql = "INSERT IGNORE INTO `rings`
        (name, type, system_name, x, y, z, distance_to_arrival, body_name, reserve, timestamp)
        VALUES";

        $sql .= implode(',', $sqlArray);

        // sql query 3rd part - columns to update
        $sql .= "ON DUPLICATE KEY UPDATE
                type=VALUES(type), system_name=VALUES(system_name), x=VALUES(x), y=VALUES(y), z=VALUES(z),
                distance_to_arrival=VALUES(distance_to_arrival), body_name=VALUES(body_name),
                reserve=VALUES(reserve), timestamp=VALUES(timestamp)
                ";


        $query = self::getConnection()->prepare($sql);
        $query->execute($paramArray);

        echo 'added / updated ' . $query->rowCount() . "rows\n";
    }
}
