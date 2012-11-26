<?php 

require_once("srplaylist/classes/SRplaylist.php");

$api = new SRplaylist("localhost", "root", "root", "Tracks");

// Default is "P3"
$playlist = $api->getPlaylist();

echo $playlist;

?>