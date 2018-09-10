<?php
ini_set("log_errors", 1);
ini_set('display_errors', 0);

require_once('UKMconfig.inc.php');
require_once('UKM/inc/crypto.inc.php');
require_once('UKM/sql.class.php');

// The port that needs to be open for the cache to function correctly
define('WOWZA_PORT', 1935);

// video.ukm.no (mother-server) utilizes two different IP's. One for HTTP and one for wowza
// The HTTP one is always accessed by DNS, and logged IP should therefore be wowza IP
function translate_mother_server_dual_ip_issue( $host ) {
	if( $host == gethostbyname( 'video.ukm.no' ) ) {
		return gethostbyname('wowza.video.ukm.no');
	}
	return $host;
}

function validate_access_key() {
	$sent_key = $_POST['key'];
	if ( UKM_CACHE_KEY != $sent_key ) { #!constant_time_compare($sent_key, UKM_CACHE_KEY) ) {
		error_log('auth failed by recieved key: '. $sent_key);
		http_response_code(400);
		die(json_encode(array(
			'message' => 'Auth failed',
		)));
	}
}

function get_cache_status() {
	// Fetch the data about the cache reporting in
	$cache_status = $_POST['status'];
	if ( !$cache_status ) {
		http_response_code(400);
		error_log("Got invalid heartbeat, data was cache_id=$cache_id, status=$cache_status");
		die(json_encode(array(
			'message' => 'One or more required fields missing.',
		)));
	}
	return $cache_status;
}

function is_port_open($host, $port) {
	$connection = @fsockopen($host, $port, $timeout=2.0);
	if ( is_resource( $connection ) ) {
		fclose($connection);
		return true;
	} else {
		error_log("Host $host does not seem to have a running service on $port, or is blocked by a firewall");
		return false;
	}
}

function validate_open_ports($host) {
	if ( !is_port_open($host, WOWZA_PORT) ) {
		http_response_code(400);
		die(json_encode(array(
			'message' => "I can't reach your service on port 1935, please verify that wowza is "
				. "running and that you're not blocked by a firewall, and try again.",
		)));
	}
}

validate_access_key();
$cache_status = get_cache_status();
$cache_id = $_POST['cache_id'];
$cache_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
if( empty( $cache_ip ) ) {
	$cache_ip = $_SERVER['REMOTE_ADDR'];
}

$cache_ip = translate_mother_server_dual_ip_issue( $cache_ip );

$post_for_log = $_POST;
$post_for_log['key'] = substr($_POST['key'], 0, 3) .'***obfuscated***'. substr($_POST['key'], strlen($_POST['key'])-3, 3);
error_log('Got heartbeat from '. $cache_ip ." along with POST-data: \r\n". var_export( $post_for_log, true ) );

validate_open_ports($cache_ip);

if ( !$cache_id ) {
	$sql = new SQLins('ukm_tv_caches_caches');
	$sql->add('ip', $cache_ip);
	$sql->add('status', $cache_status);
	$results = $sql->run();
	$cache_id = $sql->insid();
} else {
	$select_sql = new SQL("SELECT id FROM `ukm_tv_caches_caches` WHERE `id`='#id'", array('id' => $cache_id));
	$res = $select_sql->run( $select_sql );
	$res = SQL::fetch( $res );
	if ( !$res ) {
		error_log("Got heartbeat from unknown id. ID=$cache_id, ip=$cache_ip.");
		$insert_new = new SQLins('ukm_tv_caches_caches');
		$insert_new->add('id', $cache_id);
		$insert_new->add('ip', $cache_ip);
		$insert_new->add('status', $cache_status);
		$insert_new->run();
	}
	error_log("Updating status for ip $cache_ip");
	$sql = new SQLins('ukm_tv_caches_caches', array('id' => $cache_id));
	$sql->add('ip', $cache_ip);
	$sql->add('status', $cache_status);
	$sql->add('last_heartbeat',  date('Y-m-d G:i:s'));
	$sql->run();
}

die(json_encode(array(
	'cache_id' => $cache_id,
	'status' => $cache_status,
	'ip' => $cache_ip,
)));
