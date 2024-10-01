<?php

namespace Core\Model;

use Core\Debug\Debug;

/**
 * Class StationData
 */
class StationData extends Model
{
    private $government = [
        '$government_Anarchy;' => 'Anarchy',
        '$government_Communism;' => 'Communism',
        '$government_Confederacy;' => 'Confederacy',
        '$government_Cooperative;' => 'Cooperative',
        '$government_Corporate;' => 'Corporate',
        '$government_Democracy;' => 'Democracy',
        '$government_Dictatorship;' => 'Dictatorship',
        '$government_Feudal;' => 'Feudal',
        '$government_Imperial;' => 'Imperial',
        '$government_None;' => 'None',
        '$government_Patronage;' => 'Patronage',
        '$government_PrisonColony;' => 'Prison Colony',
        '$government_Theocracy;' => 'Theocracy',
        '$government_Engineer;' => 'Engineer',
        '$government_Carrier;' => 'Private Ownership',
    ];
    private $allegiance = [];
    private $economies = [];

    public function __construct()
    {
        parent::__construct();
        $sql = 'SELECT id, economy_id FROM `economies`';
        foreach (self::getConnection()->query($sql) as $row) {
            $this->economies[$row['economy_id']] = $row['id'];
        }

        $sql = 'SELECT id, faction_name FROM `allegiance`';
        foreach (self::getConnection()->query($sql) as $row) {
            $this->allegiance[$row['faction_name']] = $row['id'];
        }
    }

    public function addStationData(string $json): bool
    {
        if (!$json) {
            echo "json is NULL\n";
            return false;
        }

        $json_data = json_decode($json, true);
        $data = $this->prepData($json_data['message']);
        // Debug::d($data);

        $market_id = (int)$data['market_id'];

        if (in_array(null, array_values($data))) {
            return false;
        }

        $sql_rec_exist = "SELECT EXISTS(SELECT 1 FROM `stations` WHERE market_id=:market_id)";
        $query = self::getConnection()->prepare($sql_rec_exist);
        $query->bindParam(":market_id", $market_id, \PDO::PARAM_INT);
        $query->execute();
        $exists = $query->fetchColumn();
        echo 'exists - ' . $exists . "\n";

        if ($exists) {
            $sql = "UPDATE `stations`
                    SET distance_to_arrival=:dta, name=:name, type=:type, government=:gov, allegiance_id=:allegiance,
                    economy_id_1=:economy_1, economy_id_2=:economy_2, system_id=:system_id
                    WHERE market_id=:market_id";

            $pdo = self::getConnection();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $query = $pdo->prepare($sql);

            $query->bindParam(':dta', $data['dist_from_star'], \PDO::PARAM_INT);
            $query->bindParam(':name', $data['name'], \PDO::PARAM_STR);
            $query->bindParam(':type', $data['type'], \PDO::PARAM_STR);
            $query->bindParam(':gov', $data['gov'], \PDO::PARAM_STR);
            $query->bindParam(':allegiance', $data['allegiance'], \PDO::PARAM_INT);
            $query->bindParam(':economy_1', $data['economy_1'], \PDO::PARAM_INT);
            $query->bindParam(':economy_2', $data['economy_2'], \PDO::PARAM_INT);
            $query->bindParam(':system_id', $data['system_id'], \PDO::PARAM_INT);
            $query->bindParam(':market_id', $market_id, \PDO::PARAM_INT);
            $query->execute();

            echo 'updated ' . $query->rowCount() . "rows\n";
            unset($pdo);
            unset($query);

            return true;
        } else {
            $paramArray = [];
            $sqlArray = '(' . implode(',', array_fill(0, count($data), '?')) . ')';

            foreach ($data as $element) {
                $paramArray[] = $element;
            }

            // sql query 1st part - table, columns
            $sql = 'INSERT IGNORE INTO `stations`
            (distance_to_arrival, name, type, market_id, government,
            allegiance_id, economy_id_1, economy_id_2, system_id)
            VALUES ';

            $sql .= $sqlArray;

            $query = self::getConnection()->prepare($sql);
            $query->execute($paramArray);
            echo 'added ' . $query->rowCount() . "rows\n";

            return true;
        }
    }

    private function prepData(array $data): array
    {
        $result['dist_from_star'] = isset($data['DistFromStarLS']) ? (int)round((float)$data['DistFromStarLS']) : null;
        $system = isset($data['StarSystem']) ? $data['StarSystem'] : null;
        $result['name'] = isset($data['StationName']) ? $data['StationName'] : null;
        $result['type'] = isset($data['StationType']) ? $data['StationType'] : null;
        $result['market_id'] = isset($data['MarketID']) ? $data['MarketID'] : null;
        $result['gov'] = isset($data['StationGovernment']) ? $this->government[$data['StationGovernment']] : null;
        $result['allegiance'] = isset($data['StationAllegiance']) ?
            (int)$this->allegiance[$data['StationAllegiance']] : 7;

        if (isset($data['StationType'])) {
            if ($data['StationType'] === 'FleetCarrier' || $data['StationType'] === 'MegaShip') {
                $result['type'] = null;
            }
        } else {
            $result['type'] = null;
        }

        if ($result['type']) {
            switch ($result['type']) {
                case 'CraterPort':
                    $result['type'] = 'Planetary Port';
                    break;
                case 'CraterOutpost':
                    $result['type'] = 'Planetary Outpost';
                    break;
                case 'OnFootSettlement':
                    $result['type'] = 'Odyssey Settlement';
                    break;
                case 'Coriolis':
                    $result['type'] = 'Coriolis Starport';
                    break;
                case 'Orbis':
                    $result['type'] = 'Orbis Starport';
                    break;
                case 'Ocellus':
                    $result['type'] = 'Ocellus Starport';
                    break;
                case 'AsteroidBase':
                    $result['type'] = 'Asteroid base';
                    break;
            }
        }

        if (isset($data['StationEconomies'])) {
            $result['economy_1'] = (int)$this->economies[$data['StationEconomies'][0]['Name']];

            if (count($data['StationEconomies']) > 1) {
                $result['economy_2'] = (int)$this->economies[$data['StationEconomies'][1]['Name']];
            } else {
                $result['economy_2'] = 18;
            }
        } else {
            $result['economy_1'] = 18;
            $result['economy_2'] = 18;
        }

        $sql = 'SELECT id FROM `systems` 
                WHERE systems.name=?';

        $query = self::getConnection()->prepare($sql);
        $query->execute([$system]);

        $result['system_id'] = (int)$query->fetch()['id'];

        if ($result['system_id'] === 0) {
            $result['system_id'] = null;
        };

        return $result;
    }
}
