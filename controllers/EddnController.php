<?php

use Core\Model\MarketData;
use JsonSchema\Validator;
use Core\Helper\SchemaValidator;
use Core\Model\ShipModulesData;
use Core\Model\ShipyardData;
use Core\Model\StationData;
use Core\Model\SystemData;

$relayEDDN              = 'tcp://eddn.edcd.io:9500';
$timeoutEDDN            = 600000;

/**
 * START
 */

$context    = new ZMQContext();
$subscriber = $context->getSocket(ZMQ::SOCKET_SUB);

$subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");
$subscriber->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO, $timeoutEDDN);

$schema_validator = new SchemaValidator();
$market_data = new MarketData();
$ship_modules = new ShipModulesData();
$shipyard = new ShipyardData();
$station = new StationData();
$system = new SystemData();

while (true) {
    try {
        $subscriber->connect($relayEDDN);

        while (true) {
            $message = $subscriber->recv();

            if ($message === false) {
                $subscriber->disconnect($relayEDDN);
                break;
            }

            $message    = zlib_decode($message);
            $json       = $message;

            /**
             * Validate
             */
            // $validator_commodities = new Validator();
            // $validator_journal = new Validator();
            // $validator_journal_location = new Validator();
            // $validator_outfitting = new Validator();
            // $validator_shipyard = new Validator();

            if ($schema_validator->validateCommodities($json)) {
                $market_data->addMarketData($json);
            }
            if ($schema_validator->validateOutfitting($json)) {
                $ship_modules->addShipModulesData($json);
            }
            if ($schema_validator->validateJournal($json)) {
                $station->addStationData($json);
            }
            if ($schema_validator->validateJournalLocation($json)) {
                $system->addSystemData($json);
            }
            if ($schema_validator->validateShipyard($json)) {
                $shipyard->addShipyardData($json);
            }

            //fwrite(STDOUT, $json . PHP_EOL);
        }
    } catch (ZMQSocketException $e) {
        fwrite(STDOUT, 'ZMQSocketException: ' . $e . PHP_EOL);
        sleep(10);
    }
}

// Exit correctly
//exit(0);
