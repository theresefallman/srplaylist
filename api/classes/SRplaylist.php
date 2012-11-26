<?php

require_once("webservice.php");
require_once(__DIR__ . "/../config/database.php");

class SRplaylist {
	
	const PLAYLIST = "playlist";
	const APCTTL = 30;
	
	private $_webservice = null;
	private $_database = null;
	private $_apcTtl = null;
	private $_table = "sr_channels";
	private $_lastUpdate = "sr_update";
	
	public function __construct($host, $user, $pwd, $dbname) {
		$this->_database = new Database($host, $user, $pwd, $dbname, $this->_table, $this->_lastUpdate);
		$this->_webservice = new Webservice();
	}
	
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
			$result = array("message" => "The requested resource doesn't exist.");
		}
		
		return json_encode($result);
	}
	
	public function getAllChannels() {
		$sql = "SELECT channel_id, name, audio_url, channel_url FROM $this->_table";
		
		$channels = $this->_database->runAndFetch($sql);
	
		if (empty($channels) || $this->_lastUpdate() == true) {
			$channels = $this->_refreshDatabase();
		}
		
		return json_encode($channels);

	}
	
	// TODO : Check if name exist in webservice
	private function _getChannel($name) {
		
		$sql = "SELECT channel_id, name, audio_url, channel_url FROM $this->_table WHERE name='$name';";
		$channelInfo = $this->_database->runAndFetch($sql);
		
		if (empty($channelInfo) || $this->_lastUpdate() == true) {
			$channelInfo = $this->_refreshDatabase($name);
			
			if ($channelInfo == false) {
				return false;
			}
		}
		
		return $channelInfo;		
	}
	
	private function _refreshDatabase($name = null) {
		
		$this->_database->runAndPrepare("DELETE FROM $this->_table");
		$channels = $this->_webservice->findChannels();
		
		for ($i = 0; $i < sizeof($channels); $i++) {
			
			$params = array(
				":channelid" => $channels[$i]["id"],
				":name" => $channels[$i]["name"],
				":audiourl" => $channels[$i]->liveaudio->url,
				":channelurl" => $channels[$i]->siteurl,
			);
			
			$sql = "INSERT INTO $this->_table (channel_id, name, audio_url, channel_url) VALUES(:channelid, :name, :audiourl, :channelurl)";
			$this->_database->runAndPrepare($sql, $params);			
		}
		
		if ($name == null) {
			return $this->_database->runAndFetch("SELECT channel_id, name, audio_url, channel_url FROM $this->_table");
		} else {
			return $this->_database->runAndFetch("SELECT channel_id, name, audio_url, channel_url FROM $this->_table WHERE name='$name';");
		}
	}
	
	private function _getCache($key) {
		$result = false;
		$data = apc_fetch($key, $result);
		return ($result) ? $data : null;	
	}
	
	private function _setCache($key, $data) {
		return apc_store($key, $data, self::APCTTL);
	}
	
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