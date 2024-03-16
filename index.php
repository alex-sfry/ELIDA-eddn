<?php

// namespace Eddn;
 const ROOT = __DIR__;

 require_once('utils/import_stations.php');

// require_once(ROOT . "/components/Autoload.php");

//echo phpinfo();

// DBConnect::d(scandir(ROOT . "/controllers"));


/* =====curl example===== */
// $url = "https://www.edsm.net/api-status-v1/elite-server";

// $curl = curl_init($url);
// curl_setopt($curl, CURLOPT_URL, $url);
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// $resp = curl_exec($curl);
// curl_close($curl);
// var_dump($resp);

/* download file */
// $url = 'https://www.edsm.net/dump/systemsPopulated.json.gz';
// $url = 'https://www.edsm.net/dump/stations.json.gz';

    // Use basename() function to return the base name of file
    // $file_name = basename($url);

    // Use file_get_contents() function to get the file
    // from url and use file_put_contents() function to
    // save the file by using base name
    // if (file_put_contents($file_name, file_get_contents($url)))
    // {
    //     echo "File downloaded successfully";
    // }
    // else
    // {
    //     echo "File downloading failed.";
    // }
