<?php

namespace Core\Database;

use PDO;

/**
 * Class DBConnect
 * connect to DB
 */
class DBConnect
{
    public function __construct()
    {
        require_once ROOT . '/../bootstrap3.php';
    }

    /**
     * get DSN string
     *
     * @return string
     */
    private function getDSN(): string
    {
        return "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST;
    }

    /**
     * get DB connection object
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return new PDO(
            $this->getDSN(),
            DB_LOGIN,
            DB_PASSWORD,
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]
        );
    }
}
