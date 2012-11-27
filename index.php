<?php 
/*
*	Example file for using SRplaylist-api
*/

require_once("api/classes/SRplaylist.php");

$api = new SRplaylist("localhost", "root", "root", "sr_playlist");

$result = $api->getPlaylist("P3");

$output = json_decode($result);
$channel = $output->channelInfo[0];
$playlist = $output->playlist[0];


echo "På " . $channel->name . " spelas just nu "  . $playlist->currentSong->title . " av ". $playlist->currentSong->artist . ".";

?>