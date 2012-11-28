<?php

require_once("webservice.php");
require_once(__DIR__ . "/../config/database.php");

class SRplaylist {
	
	// Time-to-live (apc) -- TODO: inparameter?
	const APCTTL = 30;
	const PLAYLIST = "playlist";
	
	private $_webservice = null;
	private $_database = null;
	private $_apcTtl = null;
	
	// Table names in MySQL-database
	private $_table = "sr_channels";
	private $_lastUpdate = "sr_update";
	
	public function __construct($host, $user, $pwd, $dbname) {
		$this->_database = new Database($host, $user, $pwd, $dbname, $this->_table, $this->_lastUpdate);
		$this->_webservice = new Webservice();
	}
	
	/*
	*	Core function - gets channelinfo and playlist
	*	Stores playlist in apc-cache
	*	@param string: channel name (default is 'P3')
	*	@return json: includes playlist and channel
	*/	
	public function getPlaylist($name = "P3") {
		$result = array();
		$channel = $this->_getChannel($name);
		
		if (isset($channel) && $channel != false) {
			$id = $channel[0]["channel_id"];
			
			$result = $this->_getCache(self::PLAYLIST . $id);

			if ($result == false) {
				$playlist = $this->_webservice->findPlaylist($id);
				$result["channelInfo"] = $channel;
				$result["playlist"] = $playlist;
			}

			if ($result !== null) {
				$this->_setCache(self::PLAYLIST . $id, $result);
			}
		} else {
			$result = array("error" => "The requested resource doesn't exist.");
		}
		
		return json_encode($result);
	}
	
	/*
	*	Returns all channels from database
	*	Updates database if empty or < last update
	*	@return json: all channels
	*/
	public function getAllChannels() {
		$sql = "SELECT channel_id, name, audio_url, channel_url, image FROM $this->_table";
		
		$channels = $this->_database->runAndFetch($sql);

		if (empty($channels) || $this->_lastUpdate() == true) {
			$channels = $this->_refreshDatabase();
		}
		
		return json_encode($channels);

	}
	
	/*
	*	Gets channel info based on name
	*	@param string: channel name
	*	@return array: channel info for specific channel
	*	TODO: Check if name exist in webservice 
	*/
	private function _getChannel($name) {
		
		$sql = "SELECT channel_id, name, audio_url, channel_url, image FROM $this->_table WHERE name='$name';";
		$channelInfo = $this->_database->runAndFetch($sql);
		
		if (empty($channelInfo) || $this->_lastUpdate() == true) {
			$channelInfo = $this->_refreshDatabase($name);
			
			if ($channelInfo == false) {
				return false;
			}
		}
		
		return $channelInfo;		
	}
	
	/*
	*	Updates channels in database
	*	@param string: channel name (optional)
	*	@return array[]: all channels or specific channel
	*/
	private function _refreshDatabase($name = null) {
		$this->_database->runAndPrepare("DELETE FROM $this->_table");
		$channels = $this->_webservice->findChannels();
		
		// Loops through array from data source and insert in database
		for ($i = 0; $i < sizeof($channels); $i++) {
			
			$params = array(
				":channelid" => $channels[$i]["id"],
				":name" => $channels[$i]["name"],
				":audiourl" => $channels[$i]->liveaudio->url,
				":channelurl" => $channels[$i]->siteurl,
				":image" => $channels[$i]->image
			);
			
			$sql = "INSERT INTO $this->_table (channel_id, name, audio_url, channel_url, image) 
							VALUES(:channelid, :name, :audiourl, :channelurl, :image)";
			$this->_database->runAndPrepare($sql, $params);			
		}
		
		if ($name == null) {
			return $this->_database->runAndFetch("SELECT channel_id, name, audio_url, channel_url, image FROM $this->_table");
		} else {
			return $this->_database->runAndFetch("SELECT channel_id, name, audio_url, channel_url, image 
				FROM $this->_table WHERE name='$name';");
		}
	}
	
	/*
	*	Gets playlist stored in cache
	*	@param string: identification key
	*	@return array[]/null
	*/
	private function _getCache($key) {
		$result = false;
		$data = apc_fetch($key, $result);
		return ($result) ? $data : null;	
	}
	
	/*
	*	Stores result-array in cache
	*	@param string, array: id, playlist
	*	@return boolean
	*/
	private function _setCache($key, $data) {
		return apc_store($key, $data, self::APCTTL);
	}
	
	/*
	*	Updates table for "time-to-live"
	*	@return boolean
	*	Todo: Make ttl-time an option for user (24 hours)
	*/
	private function _lastUpdate() {
		$dateNow = date("Y-m-d H:i:s");
		$ttl = date("Y-m-d H:i:s", strtotime($dateNow . "+ 24 hours"));
		$sql = "SELECT * from $this->_lastUpdate";
		
		if ($lastUpdate = $this->_database->runAndFetch($sql)) {

			$time = $lastUpdate[0]["ttl"];
			$id = $lastUpdate[0]["id"];
			
			if ($time < $dateNow) {
				$arr = array(
					":ttl" => $ttl
				);
				
				$query = "UPDATE $this->_lastUpdate SET ttl='$ttl' WHERE id=$id";
				$this->_database->runAndPrepare($query);
				return true;
			}
		}
		
		return false;
	}
}


?>