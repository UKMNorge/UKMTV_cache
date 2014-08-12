<?php
require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');

// If fetching, stop cron
$SQL = new SQL("SELECT *
                FROM `files_to_fetch`
                WHERE `status` = 'fetching' 
                LIMIT 1");
$res = $SQL->run();
if( mysql_num_rows( $res ) > 0 ) {
    die('Already fetching one');
}

// Select next fetch-job
$SQL = new SQL("SELECT *
                FROM `files_to_fetch`
                WHERE `status` = 'fetch' 
                ORDER BY `id` ASC
                LIMIT 1");
$res = $SQL->run();

while( $f = mysql_fetch_assoc( $res ) ) {
    // Set fetching
    ftf_update( $f, 'fetching' );

    // Calc paths and name
    $filename = basename( $f['url'] );
    $local = UKM_CACHE_PATH . $f['path'] . $filename;
    $remote = $f['url'];
    $call = "wget -O $local $remote";

    // fetch (WGET)
    exec($call, $call_response, $call_return_code);
    
    // If not ok, set crashed
    if( $call_return_code != 0 ) {
        ftf_update( $f, 'crashed' );
    // Ok, set complete, and decide whether to notify UKM-TV
    } else {
        ftf_update( $f, 'complete');
        // All files complete for this cron_id?
        $SQLcheck = new SQL("SELECT *
                             FROM `files_to_fetch`
                             WHERE `cron_id` = '#cronid'
                             AND `status` != 'complete'",
                             array('cronid' => $f['cron_id']));
        $REScheck = $SQLcheck->run();
        // All files complete, notify UKM-TV
        if( mysql_num_rows( $REScheck ) == 0) {
            $curl = new UKMCURL();
            $curl->timeout(6);
            $curl->post( array('cache_id' => CACHE_ID, 'cron_id' => $f['cron_id'] ) );
            $curlRes = $curl->request( 'http://tv.'. UKM_HOSTNAME .'/caches/fileAdded.php' );
        }
    }
}

// Function to update status
function ftf_update( $f, $status ) {
    $SQLins = new SQLins('files_to_fetch', array('id' => $f['id']));
    $SQLins->add('status', $status);
    $SQLins->run();
}