<?php

require_once(dirname(__FILE__) . "/simpletest/autorun.php");
require_once(__DIR__. "/../api/classes/SRPlaylist.php");

class TestNextTrack extends UnitTestCase {

	private $_playlist = null;
	private $_database = null;
	
	// ---------------------------------------------------------------- //
	// Tests for class Track                                            //
	// ---------------------------------------------------------------- //
	
	public function setup() {
		$this->_playlist = new SRPlaylist("localhost", "root", "root", "Tracks");
		$this->_database = new Database("localhost", "root", "root", "Tracks", "sr_channels", "sr_update");
	}
	
	public function tearDown() {
		unset($this->_playlist);
		unset($this->_database);
	}

	/***************************
	Test to get tracks with existing channel name
	Should return an array with values
	@return boolean
	***************************/
	public function testGetPlaylistWithExistingChannelName() {
		$channel_name = "P3";
		$playlist = $this->_playlist->getPlaylist($channel_name); 
		$json = json_decode($playlist, true);
		$this->assertTrue($json);	
		$this->assertFalse(empty($json));
		$this->assertTrue(is_array($json));
		$this->assertTrue($json["channelInfo"] && $json["playlist"]);
		$this->assertTrue($json["channelInfo"][0]["channel_id"] == "164" && $json["channelInfo"][0]["name"] == "P3");
	}

	/***************************
	Test to get tracks with non existing channel name
	Should return an empty array
	@return boolean
	***************************/
	// public function testGetPlaylistWithNonExsistingChannelName() {
	// 	$channel_name = "TestWrongName";
	// 	$playlist = $this->_playlist->getPlaylist($channel_name); 
	// 	$json = json_decode($playlist, true);
	// 	$this->assertTrue($json);
	// 	$this->assertFalse(empty($json));
	// 	$this->assertTrue(is_array($json));
	// 	$this->assertTrue($json["message"] == "The requested resource doesn't exist.");
	// 
	// }
	
	public function testGetChannels() {
		$playlist = $this->_playlist->getAllChannels(); 
		$json = json_decode($playlist, true);
		$this->assertTrue($json);	
		$this->assertFalse(empty($json));
		$this->assertTrue(is_array($json));
	}
}


?>