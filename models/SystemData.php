<?php

namespace Core\Model;

use Core\Debug\Debug;

/**
 * Class SystemData
 */
class SystemData extends Model
{
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

    public function addSystemData(string $json): bool
    {
        if (!$json) {
            echo "json is NULL\n";
            return false;
        }

        $json_data = json_decode($json, true);
        $data = $this->prepData($json_data['message']);
        // Debug::d($data);

        if (in_array(null, array_values($data))) {
            return false;
        }

        $sql_rec_exist = "SELECT EXISTS(SELECT 1 FROM `systems` WHERE name=:name)";
        $query = self::getConnection()->prepare($sql_rec_exist);
        $query->bindParam(":name", $data['name'], \PDO::PARAM_STR);
        $query->execute();
        $exists = $query->fetchColumn();
        echo 'exists - ' . $exists . "\n";

        if ($exists) {
            $sql = "UPDATE `systems`
                    SET x=:x, y=:y, z=:z, population=:population, allegiance_id=:allegiance,
                    security_id=:security, economy_id=:economy
                    WHERE name=:name";

            $pdo = self::getConnection();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $query = $pdo->prepare($sql);

            $query->bindParam(':name', $data['name'], \PDO::PARAM_STR);
            $query->bindParam(':x', $data['x'], \PDO::PARAM_INT);
            $query->bindParam(':y', $data['y'], \PDO::PARAM_STR);
            $query->bindParam(':z', $data['z'], \PDO::PARAM_STR);
            $query->bindParam(':population', $data['population'], \PDO::PARAM_STR);
            $query->bindParam(':allegiance', $data['allegiance'], \PDO::PARAM_INT);
            $query->bindParam(':security', $data['security'], \PDO::PARAM_INT);
            $query->bindParam(':economy', $data['economy'], \PDO::PARAM_INT);
            $query->execute();

            echo $data['name'] . "\n";
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
            $sql = 'INSERT IGNORE INTO `systems`
            (name, x, y, z, population, security_id,
            allegiance_id, economy_id)
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
            (int)$this->security[$data['SystemSecurity']] : 6;

        $result['allegiance'] = isset($data['SystemAllegiance']) && $data['SystemAllegiance'] ?
            (int)$this->allegiance[$data['SystemAllegiance']] : 7;

        $result['economy'] = isset($data['SystemEconomy']) && $data['SystemEconomy'] ?
            (int)$this->economies[$data['SystemEconomy']] : 18;

        return $result;
    }
}
