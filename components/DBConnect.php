<?php

namespace DBConnect;

use PDO;

/**
 * Class DBConnect
 */
class DBConnect
{
    /**
     * @return string
     */
    private function getDSN(): string
    {
        return "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST;
    }

    // получаем объект соединения с БД
    /**
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
