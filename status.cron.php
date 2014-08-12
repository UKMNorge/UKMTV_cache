<?php
require_once('UKMconfig_cache.inc.php');
require_once('UKM/sql.class.php');
require_once('UKM/curl.class.php');

function writeCacheId( $id = 0 ) {
    $handle = fopen('/etc/cache_id', 'w');
    fwrite( $handle, 0);
    fclose( $handle );    
}

/// START
if( !file_exists('/etc/cache_id') ) {
    writeCacheId( 0 );
    define('CACHE_ID', 0);
} else {
    $handle = fopen('/etc/cache_id');
    $contents = stream_get_contents($handle);
    fclose($handle);
    define('CACHE_ID', $contents);
}

// SEND
$curl = new UKMCURL();
$curl->timeout(6);
$curl->post( array('cache_id' => CACHE_ID ) );
$curlRes = $curl->request( 'http://tv.'. UKM_HOSTNAME .'/caches/heartbeat.php' );

if( isset( $curl->data->cache_id ) && is_numeric( $curl->data->cache_id ) ) {
    writeCacheId( $curl->data->cache_id );
}