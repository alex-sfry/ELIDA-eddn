<?php

use Core\Debug\Debug;
use Core\Database\DBConnect;
use JsonMachine\Items;
use JsonMachine\Exception;

require_once(ROOT . '/vendor/autoload.php');
require_once('import_json_functions.php');

$pdo =  (new DBConnect)->getConnection();

try {
    $systems = Items::fromFile(ROOT . '/json/systemsPopulated.json');
} catch (Exception\InvalidArgumentException $e) {
    Debug::d($e);
}

create_table_systems($pdo);

$sql_sec = 'SELECT id, security_level from `security`';
$query_sec = $pdo->query($sql_sec)->fetchAll();

$sql_allegiance = 'SELECT id, faction_name from allegiance';
$query_allegiance = $pdo->query($sql_allegiance)->fetchAll();

$sql_economy = 'SELECT id, economy_name from economies';
$query_economy = $pdo->query($sql_economy)->fetchAll();

// array_keys($query_sec[0])[1] - *name column in a table
$security_level = array_keys($query_sec[0])[1];
$faction_name = array_keys($query_allegiance[0])[1];
$economy_name = array_keys($query_economy[0])[1];

$sys_arr = [];
$count = 0;

/**@var Items $systems**/

foreach ($systems as $system) {
    $security_id = get_definition_id(
        $query_sec,
        $system->security,
        $security_level
    );

    $allegiance_id = get_definition_id(
        $query_allegiance,
        $system->allegiance,
        $faction_name
    );

    $economy_id = get_definition_id(
        $query_economy,
        $system->economy,
        $economy_name
    );

    $sys_arr[] = [
        (int)$system->id,
        (string)$system->name,
        (float)$system->coords->x,
        (float)$system->coords->y,
        (float)$system->coords->z,
        (int)$system->population,
        (int)$security_id,
        (int)$allegiance_id,
        (int)$economy_id
    ];

    $count++;

    if ($count === 10000) {
        fill_table_sys($pdo, $sys_arr);
        $count = 0;
        unset($sys_arr);
        $sys_arr = [];
        // break;
    }
}

fill_table_sys($pdo, $sys_arr);

echo "finished\n";
// var_dump($sys_arr[999]);
