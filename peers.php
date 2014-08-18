<?php
	require_once(dirname(__FILE__) . '/lib/UDPTracker.php');
	require_once(dirname(__FILE__) . '/lib/HTTPTracker.php');

	$trackerURL = trim($_GET["tracker"]);
    $hash       = trim($_GET["hash"]);
    
    $data = array();
    $tracker = (strpos($trackerURL, "http") === 0) ? new HTTPTracker() : new UDPTracker();

    try {
        $result = $tracker->scrape($trackerURL, $hash);
        $data = array(
            "error" => false,
            "data" => $result
        );
    } catch (Exception $e) {
        $data = array(
            "error" => true,
            "message" => $e->getMessage()
        );
    }
    echo json_encode($data);
?>