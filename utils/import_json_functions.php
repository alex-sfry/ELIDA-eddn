<?php

use Core\Debug\Debug;

function get_definition_id($query, $def_name, $key)
{
    $not_found = null;

    foreach ($query as $item) {
        if ($item[$key] == $def_name) {
            return $item['id'];
        } elseif ($item[$key] == 'unknown') {
            $not_found =  $item['id'];
        }
    }

    return $not_found;
}

function create_table_systems($pdo): void
{
    $sql = "CREATE TABLE IF NOT EXISTS `systems`
            (`id` INT NOT NULL, `name` VARCHAR(255),
            `x` DOUBLE, `y` DOUBLE, `z` DOUBLE,
            `population` BIGINT, `security_id` INT,
            `allegiance_id` INT, `economy_id` INT)";

    if ($pdo->query($sql)) {
        echo "table created / exists\n";
    } else echo "something went wrong\n";
}

function create_table_stations($pdo): void
{
    $sql = "CREATE TABLE IF NOT EXISTS `stations`
            (`id` INT NOT NULL, `market_id` BIGINT,
            `system_id` INT, `name` VARCHAR(255),
            `type` VARCHAR(100), `distance_to_arrival` INT,
            `government` VARCHAR(100), `allegiance_id` INT,
            `1_economy_id` INT, `2_economy_id` INT)";

    if ($pdo->query($sql)) {
        echo "table created / exists\n";
    } else echo "something went wrong\n";
}

function fill_table_sys($pdo, $sys_arr): void
{
    $paramArray = [];
    $sqlArray = [];

    foreach ($sys_arr as $sys) {
        $sqlArray[] = '(' . implode(',', array_fill(0, count($sys), '?')) . ')';
    }

    // flatten source array
    foreach ($sys_arr as $sys) {
        foreach ($sys as $item) {
            $paramArray[] =  $item;
        }
    }

    $sql = "INSERT INTO `systems2`
            (id, `name`, x, y, z, `population`,
            security_id, allegiance_id, economy_id)
            VALUES";

    $sql .= implode(',', $sqlArray);

    $sql .= " ON DUPLICATE KEY UPDATE 
                x=VALUES(x),
                y=VALUES(y),
                z=VALUES(z),
                population=VALUES(population),
                security_id=VALUES(security_id),
                allegiance_id=VALUES(allegiance_id),
                economy_id=VALUES(economy_id)
                ";

    $query = $pdo->prepare($sql);

    if ($query->execute($paramArray)) {
        echo "data inserted\n";
    } else echo var_dump($query->errorInfo()) . "\n";

    unset($paramArray, $sqlArray);
}

function fill_table_stations($pdo, $sys_arr): void
{
    $paramArray = [];
    $sqlArray = [];

    foreach ($sys_arr as $sys) {
        $sqlArray[] = '(' . implode(',', array_fill(0, count($sys), '?')) . ')';
    }

    // flatten source array
    foreach ($sys_arr as $sys) {
        foreach ($sys as $item) {
            $paramArray[] =  $item;
        }
    }

    $sql = "INSERT INTO `stations2`
            (id, market_id, system_id, name, type, distance_to_arrival,
            government, allegiance_id, 1_economy_id, 2_economy_id)
            VALUES";

    $sql .= implode(',', $sqlArray);

    $sql .= " ON DUPLICATE KEY UPDATE 
                market_id=VALUES(market_id),
                system_id=VALUES(system_id),
                name=VALUES(name),
                type=VALUES(type),
                distance_to_arrival=VALUES(distance_to_arrival),
                government=VALUES(government),
                allegiance_id=VALUES(allegiance_id),
                1_economy_id=VALUES(1_economy_id),
                2_economy_id=VALUES(2_economy_id)
                ";

    $query = $pdo->prepare($sql);

    if ($query->execute($paramArray)) {
        echo "data inserted\n";
    } else echo var_dump($query->errorInfo()) . "\n";

    unset($paramArray, $sqlArray);
}
