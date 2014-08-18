<?php

	abstract class TorrentTracker {
		
		protected $connectionTimeout;
		protected $peerId;
		protected $port;

		protected static $INVALID_IPS = array("0.0.0.0", "127.0.0.1");

		public function __construct($connectionTimeout = 2, $peerId = null, $port = 41414) {
			$this->connectionTimeout = $connectionTimeout;
			$this->peerId = $peerId;
			$this->port = $port;

			if ($this->peerId == null) {
				$this->peerId = sha1(microtime(true).mt_rand(10000,90000));
			}
		}

		protected function isValidPeer($ip, $port) {
			return !(in_array($ip, $INVALID_IPS) || $port == 0);
		}

		abstract public function scrape($trackerURL, $infoHash);
	}

?>