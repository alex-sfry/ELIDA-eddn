<?php

namespace Core\Helper;

use JsonSchema\Validator;

/**
 * Class SchemaValidator
 *  Validates jsons against different schemas
 */
class SchemaValidator
{
    /**
     * @param Validator $validator
     * @param string $json
     * @return bool
     */
    public function validateCommodities(/* Validator $validator, */ string $json): bool
    {
        $validator = new Validator();
        $data = json_decode($json);
        $validator->validate($data, (object)['$ref' => 'file://' . realpath('commodities_shema.json')]);

        if ($validator->isValid()) {
            echo "The supplied JSON validates against the COMMODITIES schema.\n";
            unset($validator);
            return true;
            // file_put_contents('price2.json', $message);
            // die;
        } else {
            unset($validator);
            // echo "JSON does not validate. Violations:\n";
            return false;
        }
    }

    /**
     * @param \JsonSchema\Validator $validator
     * @param string $json
     *
     * @return bool
     */
    public function validateJournal(/* Validator $validator,  */string $json): bool
    {
        $validator = new Validator();
        $data = json_decode($json);
        $validator->validate($data, (object)['$ref' => 'file://' . realpath('journal_schema.json')]);

        if ($validator->isValid()) {
            if ($data->message->event === 'Docked') {
                echo "The supplied JSON validates against the JOURNALS schema (DOCKED event).\n";
                // file_put_contents('journal.json', $json, FILE_APPEND);
                unset($validator);
                return true;
            } else {
                unset($validator);
                return false;
            }
        } else {
            unset($validator);
            // echo "JSON does not validate. Violations:\n";
            return false;
        }
    }

    /**
     * @param \JsonSchema\Validator $validator
     * @param string $json
     *
     * @return bool
     */
    public function validateOutfitting(/* Validator $validator,  */string $json): bool
    {
        $validator = new Validator();
        $data = json_decode($json);
        $validator->validate($data, (object)['$ref' => 'file://' . realpath('outfitting_schema.json')]);

        if ($validator->isValid()) {
            echo "The supplied JSON validates against the OUTFITTING schema.\n";
            // file_put_contents('outfitting.json', $json, FILE_APPEND);
            unset($validator);
            return true;
        } else {
            // echo "JSON does not validate. Violations:\n";
            unset($validator);
            return false;
        }
    }

    /**
     * @param \JsonSchema\Validator $validator
     * @param string $json
     *
     * @return bool
     */
    public function validateShipyard(/* Validator $validator,  */string $json): bool
    {
        $validator = new Validator();
        $data = json_decode($json);
        $validator->validate($data, (object)['$ref' => 'file://' . realpath('shipyard_schema.json')]);

        if ($validator->isValid()) {
            echo "The supplied JSON validates against the SHIPYARD schema.\n";
            // file_put_contents('shipyard.json', $json, FILE_APPEND);
            unset($validator);
            return true;
        } else {
            // echo "JSON does not validate. Violations:\n";
            unset($validator);
            return false;
        }
    }
}
