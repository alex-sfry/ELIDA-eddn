<?php

require_once('../vendor/autoload.php');
require_once('../components/DBConnect.php');
require_once('import_json_functions.php');

$pdo = \Eddn\DBConnect::getConnection();
$stations = JsonMachine\Items::fromFile('../json/stations.json');

create_table_stations($pdo);

$sql_allegiance = 'SELECT id, faction_name from allegiance';
$query_allegiance = $pdo->query($sql_allegiance)->fetchAll();

$sql_economy = 'SELECT id, economy_name from economies';
$query_economy = $pdo->query($sql_economy)->fetchAll();

// array_keys($query_sec[0])[1] - *name column in a table
$faction_name = array_keys($query_allegiance[0])[1];
$economy_name = array_keys($query_economy[0])[1];

$station_arr = [];
$count = 0;

foreach ($stations as $station) {
    $allegiance_id = get_definition_id(
        $query_allegiance,
        $station->allegiance,
        $faction_name
    );

    $economy_id_1 = get_definition_id(
        $query_economy,
        $station->economy,
        $economy_name
    );

    $economy_id_2 = get_definition_id(
        $query_economy,
        $station->secondEconomy,
        $economy_name
    );
    
    $sys_arr[] = [
        (int)$station->id,
        (int)$station->marketId,
        (int)$station->systemId,
        (string)$station->name,
        (string)$station->type,
        (int)$station->distanceToArrival,
        (string)$station->government,
        (int)$allegiance_id,
        (int)$economy_id_1,
        (int)$economy_id_2
    ];

    $count++;

    if ($count === 5000) {
        fill_table_stations($pdo, $sys_arr);
        $count = 0;
        unset($sys_arr);
        $sys_arr = [];
        // break;
    }
}

fill_table_stations($pdo, $sys_arr);

echo "finished\n";