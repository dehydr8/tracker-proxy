<?php

	require_once(dirname(__FILE__) . '/TorrentTracker.php');

	class UDPTracker extends TorrentTracker {

		private function getPackedLong($value) {
			$highMap = 0xffffffff00000000; 
			$lowMap = 0x00000000ffffffff; 
			$higher = ($value & $highMap) >>32; 
			$lower = $value & $lowMap; 
			return pack('NN', $higher, $lower); 
		}

		public function scrape($trackerURL, $infoHash) {
			
			if (!preg_match('#^[a-f0-9]{40}$#i', $infoHash))
				throw new Exception("Invalid InfoHash");

			if (!preg_match('%udp://([^:/]*)(?::([0-9]*))?(?:/)?%si', $trackerURL, $m)) {
				throw new Exception("Invalid Tracker URL");
			}

			$tracker = 'udp://' . $m[1];
			$port = isset($m[2]) ? $m[2] : 80;
			$transaction_id = mt_rand(0,65535);

			$fp = fsockopen($tracker, $port, $errno, $errstr);
			if(!$fp){ throw new Exception('Could not open UDP connection: ' . $errno . ' - ' . $errstr,0,true); }
			stream_set_timeout($fp, $this->connectionTimeout);

			$current_connid = "\x00\x00\x04\x17\x27\x10\x19\x80";
			
			// Connection request
			$packet = $current_connid . pack("N", 0) . pack("N", $transaction_id);
			fwrite($fp,$packet);

			// Connection response
			$ret = fread($fp, 16);

			if(strlen($ret) < 1){ throw new Exception('No connection response.'); }
			if(strlen($ret) < 16){ throw new Exception('Too short connection response.'); }
			$retd = unpack("Naction/Ntransid",$ret);
			if($retd['action'] != 0 || $retd['transid'] != $transaction_id){
				throw new Exception('Invalid connection response.');
			}

			$current_connid = substr($ret, 8, 8);

			// Announce Request
			$packet = 	$current_connid . 
						pack("N", 1) . 
						pack("N", $transaction_id) . 
						pack("H*", $infoHash) . 
						pack("H*", $this->peerId) . 
						$this->getPackedLong(0) . 
						$this->getPackedLong(100) . 
						$this->getPackedLong(0) .
						pack("N", 0) . 
						$this->getPackedLong(0) . 	// ip
						pack("N", 0) . 				// key
						pack("N", 10) .				// num_want
						pack("n", $this->port);

			fwrite($fp,$packet);

			$ret = fread($fp, 4096);

			if(strlen($ret) < 1){ throw new Exception('No announce response.'); }

			$retd = unpack("Naction/Ntransid/Ninterval/Nseeders/Nleechers",$ret);
			
			if($retd['action'] != 1 || $retd['transid'] != $transaction_id) {
				throw new Exception('Invalid announce response.');
			}

			$peers = array();
			$index = 4 + 4 + 4 + 4 + 4;
			$total = $retd['seeders'] + $retd['leechers'];
			for ($i=$index; $i<$index+(6*$total); $i+=6) {
				$peerd = unpack("Nip/nport", substr($ret, $i));
				$peers[] = array("ip" => long2ip($peerd["ip"]), "port" => $peerd["port"]);
			}

			return array(
					"count" => $total,
					"peers" => $peers
				);
		}

	}

?>