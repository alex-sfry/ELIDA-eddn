<?php

function validate_commodities($pdo, $validator, $json)
{
    $data = json_decode($json);
    $validator->validate($data, (object)['$ref' => 'file://' . realpath('commodities_shema.json')]);

    if ($validator->isValid()) {
        echo "The supplied JSON validates against the COMMODITIES schema.\n";
        \Eddn\MarketData::addMarketData($pdo,  $json);
        // file_put_contents('price2.json', $message);
        // die;
        // \Eddn\DBConnect::d($message);
    } else {
        return;
        // echo "JSON does not validate. Violations:\n";
        // foreach ($validator->getErrors() as $error) {
        //     printf("[%s] %s\n", $error['property'], $error['message']);
        // }
    }
}