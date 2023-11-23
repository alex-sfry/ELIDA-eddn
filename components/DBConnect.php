<?php

namespace DBConnect;

/**
 * Class DBConnect
 * класс подключения к БД
 * исп статические свойства и методы - обращение без создания объекта класса
 */
class DBConnect
{
    // получаем строку DSN
    /**
     * @return string
     */
    private function getDSN(): string
    {
        return "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST;
    }

    // получаем объект соединения с БД
    /**
     * @return object
     */
    public function getConnection(): object
    {
        return new \PDO(
            $this->getDSN(),
            DB_LOGIN,
            DB_PASSWORD,
            [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]
        );
    }

    public static function d($arr)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
