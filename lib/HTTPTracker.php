<?php

	require_once(dirname(__FILE__) . '/TorrentTracker.php');
	require_once(dirname(__FILE__) . '/lightbenc.php');

	class HTTPTracker extends TorrentTracker {

		protected $maxReadSize = 4096;

		public function scrape($trackerURL, $infoHash) {

			if (!preg_match('#^[a-f0-9]{40}$#i', $infoHash))
				throw new Exception("Invalid InfoHash");

			if (!preg_match('%(http://.*?/)announce([^/]*)$%i', $trackerURL)) {
				throw new Exception("Invalid Tracker URL");
			} 
			
			$sep = preg_match ('/\?.{1,}?/i', $trackerURL) ? '&' : '?';
			$requesturl = 	$trackerURL . 
							$sep . 'info_hash=' . rawurlencode(pack('H*', $infoHash)) . 
							'&port=' . $this->port . 
							'&compact=1' . 
							'&peer_id=' . rawurlencode(pack('H*', $this->peerId)) . 
							'&uploaded=0&downloaded=0&left=100';

			ini_set('default_socket_timeout',$this->connectionTimeout);
			$rh = @fopen($requesturl,'r');
			if(!$rh){ throw new Exception('Could not open HTTP connection.'); }
			stream_set_timeout($rh, $this->connectionTimeout);
			
			$return = '';
			$pos = 0;
			while (!feof($rh) && $pos < $this->maxReadSize){
				$return .= fread($rh,1024);
			}
			fclose($rh);
			
			if(!substr($return, 0, 1) == 'd'){ throw new Exception('Invalid announce response'); }
			$arr_scrape_data = lightbenc::bdecode($return);

			$ret = $arr_scrape_data["peers"];

			$peers = array();
			$index = 0;
			$total = $retd['seeders'] + $retd['leechers'];
			for ($i=0; $i<strlen($ret); $i+=6) {
				$peerd = unpack("Nip/nport", substr($ret, $i));
				$peers[] = array("ip" => long2ip($peerd["ip"]), "port" => intval($peerd["port"]));
			}
			return array(
					"count" => count($peers),
					"peers" => $peers
				);
		}
	}

?>