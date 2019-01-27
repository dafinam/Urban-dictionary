<?php
require_once('DatabaseHandler.php');
require_once(__DIR__."/../../lib/Utilities.php");

/* Get connection config data from the json file */
$file = file_get_contents('connection.json', true);

$jsonDBData = json_decode($file);
$decrypted_pass = my_simple_crypt($jsonDBData->password, 'd');

define('DB_SERVER', $jsonDBData->server);
define('DB_USERNAME', $jsonDBData->user);
define('DB_PASSWORD', $decrypted_pass);
define('DB_NAME', 'urban_dictionary');
define ('HOMEPAGE', '/web-assignment2');
 
/* Attempt to connect to MySQL database */
global $link;
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>