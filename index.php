<?php
include("apiGameSpy.php");

$result = getInfosFromServer("ts3.armasites.com",2302,2302);
//$result = getInfosFromPublic();
//$result = getInfosFromPrive();
var_dump($result);

?>

