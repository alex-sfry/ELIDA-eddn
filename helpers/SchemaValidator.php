<?php

namespace App\Helpers;

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
    public function validateCommodities(Validator $validator, string $json): bool
    {
        $data = json_decode($json);
        $validator->validate($data, (object)['$ref' => 'file://' . realpath('commodities_shema.json')]);

        if ($validator->isValid()) {
            echo "The supplied JSON validates against the COMMODITIES schema.\n";
            return true;
            // file_put_contents('price2.json', $message);
            // die;
            // \Eddn\DBConnect::d($message);
        } else {
            return false;
            // echo "JSON does not validate. Violations:\n";
            // foreach ($validator->getErrors() as $error) {
            //     printf("[%s] %s\n", $error['property'], $error['message']);
            // }
        }
    }
}