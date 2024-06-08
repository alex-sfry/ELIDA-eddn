<?php

namespace Core\Model;

use Core\Debug\Debug;

/**
 * Class StationData
 */
class SystemData extends Model
{
    // private $government = [
    //     '$government_Anarchy;' => 'Anarchy',
    //     '$government_Communism;' => 'Communism',
    //     '$government_Confederacy;' => 'Confederacy',
    //     '$government_Cooperative;' => 'Cooperative',
    //     '$government_Corporate;' => 'Corporate',
    //     '$government_Democracy;' => 'Democracy',
    //     '$government_Dictatorship;' => 'Dictatorship',
    //     '$government_Feudal;' => 'Feudal',
    //     '$government_Imperial;' => 'Imperial',
    //     '$government_None;' => 'None',
    //     '$government_Patronage;' => 'Patronage',
    //     '$government_PrisonColony;' => 'Prison Colony',
    //     '$government_Theocracy;' => 'Theocracy',
    //     '$government_Engineer;' => 'Engineer',
    //     '$government_Carrier;' => 'Private Ownership',
    // ];

    private $security = [];
    private $allegiance = [];
    private $economies = [];

    public function __construct()
    {
        parent::__construct();
        $sql = 'SELECT id, economy_id FROM `economies`';
        foreach (self::getConnection()->query($sql) as $row) {
            $this->economies[$row['economy_id']] = $row['id'];
        }

        $sql = 'SELECT id, security_id FROM `security`';
        foreach (self::getConnection()->query($sql) as $row) {
            $this->security[$row['security_id']] = $row['id'];
        }

        $sql = 'SELECT id, faction_name FROM `allegiance`';
        foreach (self::getConnection()->query($sql) as $row) {
            $this->allegiance[$row['faction_name']] = $row['id'];
        }
    }

    public function addSystemData(string $json): void
    {
        if (!$json) {
            echo "json is NULL\n";
            return;
        }

        $json_data = json_decode($json, true);
        $data = $this->prepData($json_data['message']);
        // Debug::d($data);

        if (in_array(null, array_values($data))) {
            return;
        }

        $paramArray = [];
        $sqlArray = '(' . implode(',', array_fill(0, count($data), '?')) . ')';

        foreach ($data as $element) {
            $paramArray[] = $element;
        }

        // sql query 1st part - table, columns
        $sql = 'INSERT IGNORE INTO `systems`
        (name, x, y, z, population, security_id,
        allegiance_id, economy_id)
        VALUES ';

        $sql .= $sqlArray;

        // sql query 3rd part - columns to update
        $sql .= "ON DUPLICATE KEY UPDATE
                name=VALUES(name), x=VALUES(x), y=VALUES(y), z=VALUES(z), 
                population=VALUES(population), allegiance_id=VALUES(allegiance_id), 
                economy_id=VALUES(economy_id), security_id=VALUES(security_id)
                ";

        $query = self::getConnection()->prepare($sql);
        $query->execute($paramArray);

        echo 'added / updated ' . $query->rowCount() . "rows\n";
    }

    private function prepData(array $data): array
    {
        $result['name'] = isset($data['StarSystem']) && $data['StarSystem'] ?
            $data['StarSystem'] : null;

        if (isset($data['StarPos'])) {
            $result['x'] = (float)$data['StarPos'][0];
            $result['y'] = (float)$data['StarPos'][1];
            $result['z'] = (float)$data['StarPos'][2];
        }

        $result['population'] = isset($data['Population']) && $data['Population'] ?
            (int)$data['Population'] : null;

        $result['security'] = isset($data['SystemSecurity']) && $data['SystemSecurity'] ?
            $data['SystemSecurity'] : 6;

        $result['allegiance'] = isset($data['SystemAllegiance']) && $data['SystemAllegiance'] ?
            (int)$this->allegiance[$data['SystemAllegiance']] : 7;

        $result['economy'] = isset($data['SystemEconomy']) && $data['SystemEconomy'] ?
            (int)$this->economies[$data['SystemEconomy']] : 18;

        return $result;
    }
}
