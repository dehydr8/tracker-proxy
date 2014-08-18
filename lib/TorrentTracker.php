<?php

	abstract class TorrentTracker {
		
		protected $connectionTimeout;
		protected $peerId;
		protected $port;

		public function __construct($connectionTimeout = 2, $peerId = null, $port = 41414) {
			$this->connectionTimeout = $connectionTimeout;
			$this->peerId = $peerId;
			$this->port = $port;

			if ($this->peerId == null) {
				$this->peerId = sha1(microtime(true).mt_rand(10000,90000));
			}
		}

		abstract public function scrape($trackerURL, $infoHash);
	}

?>