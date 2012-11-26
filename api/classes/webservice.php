<?php

class Webservice {

	// Constants for URI to SR.se
	const BASEURL = "http://sverigesradio.se/api/v2";
	const CHANNELURL = "/channels?pagination=false";
	const TRACKSURL = "/playlists/rightnow?channelid="; 
	
	/***************************
	Function that requests all channels from sverigesradio.se/api/v2/
	@return array[] with channel info extracted from request
	***************************/
	public function findChannels() {
		
		$output = $this->_processRequest(self::BASEURL . self::CHANNELURL);
		$result = array();
		
		if ($output !== false) {
			$xml = new SimpleXMLElement($output);
			
			// Use xpath to extract from relevant node
			$channels = $xml->xpath("//channel");
			
			foreach($channels as $key => $value) {
				$result[] = $value;
			}
		} else {
			$result = array("message" => "Error requesting data from sverigesradio.se/api");
		}

		return $result;
	}

	/***************************
	Function that requests tracks from sverigesradio.se/api/v2/ based on channel id 
	@param int valid channel id
	@return array[] with playlist for channel
	***************************/
	public function findPlaylist($channel_id = 0) {
		
		$requestUriTracks = self::BASEURL . self::TRACKSURL . $channel_id;
		
		$output = $this->_processRequest($requestUriTracks); 
		
		if ($output !== false) {	
			$xml = new SimpleXMLElement($output);	
			$tracks = $xml->xpath("//playlist");
	
			// Create an array with playlist info from xml	
			$result[] = array(
				"currentSong" => array(
					"title" => (string)$tracks[0]->song->title,
					"artist" => (string)$tracks[0]->song->artist
				),
				"nextSong" => array(
					"title" => (string)$tracks[0]->nextsong->title,
					"artist" => (string)$tracks[0]->nextsong->artist
				)
			);
			
		} else {
			$result = array("message" => "Error requesting data from sverigesradio.se/api");
		}
	
		return $result;		
	}

	/***************************
	Private function for processing request and sets up cURL
	@param string url for request path
	@return XML-output
	***************************/
	private function _processRequest($uri) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept' => 'text/xml; charset=utf-8'));
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
		$result = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		if ($status == "200") {
			return $result;
		} else {
			return false;
		}
	}	
}

?>