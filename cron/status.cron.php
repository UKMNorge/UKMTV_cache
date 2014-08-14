<?php
require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');
require_once('UKM/curl.class.php');

function writeCacheId( $id = false ) {
    $handle = fopen('/etc/tvcache/cache_id', 'w');
    fwrite( $handle, $id);
    fclose( $handle );
}

/// START
if( !file_exists('/etc/tvcache/cache_id') ) {
    writeCacheId( 0 );
    define('CACHE_ID', 0);
} else {
    $handle = fopen('/etc/tvcache/cache_id', 'r');
    $contents = stream_get_contents($handle);
    fclose($handle);
    define('CACHE_ID', (int) $contents);
}

function doHealthCheck () {
    // TODO: Check if services are running and responding properly
    return 'OK';
}

// SEND
$curl = new UKMCURL();
$curl->timeout(6);
$curl->post( array(
    'cache_id' => CACHE_ID,
    'status' => doHealthCheck(),
    'space_total' => 123,
    'space_used' => 23,
));
$curlRes = $curl->request( 'http://tv.'. UKM_HOSTNAME .'/caches/heartbeat.php' );

if( isset( $curl->data->cache_id ) && is_numeric( $curl->data->cache_id ) ) {
    writeCacheId( $curl->data->cache_id );
}
