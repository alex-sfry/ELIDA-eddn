<?php

namespace Core\Model;

use PDO;
use PDOStatement;
use Core\Database\DBConnect;

/**
 * Base Model class with DB connection
 * and DB query methods
 */
class Model
{
    private static $connection;

    public function __construct()
    {
        if (!self::$connection) {
            self::$connection = (new DBConnect())->getConnection();
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return self::$connection;
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @return bool|PDOStatement
     */
    public function query(string $sql, array $params): PDOStatement|bool
    {
        if (!$sql) {
            echo 'Empty SQL string';
            return false;
        }

        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);

        if ($params) {
            foreach ($params as $param => $val_arr) {
                $stmt->bindParam($param, $val_arr['value'], $val_arr['type']);
            }
        }

        $stmt->execute();
        return $stmt;
    }
}
