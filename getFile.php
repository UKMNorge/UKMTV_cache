<?php

header('Content-Type: application/json; charset=utf-8');

require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');

if( !isset( $_POST['url'] ) || !isset( $_POST['localpath'] ) || !isset( $_POST['size'] ) || !isset( $_POST['cron_id'] ) ) {
    header( 'HTTP/1.1 450 BAD REQUEST' );
    die( json_encode( array('success' => false) ) );
} elseif( !is_array( $_POST['url'] ) ) {
    header( 'HTTP/1.1 451 BAD REQUEST' );
    die( json_encode( array('success' => false) ) );
}

// ADD JOB
foreach( $_POST['url'] as $filetype => $url ) {
    // If existing job for current $url != (complete && fetching), delete and create new job
    // Thus resetting crashes (++) and send to end of queue
    $SQLcheck = new SQL("SELECT `status`
                         FROM `files_to_fetch`
                         WHERE `url` = '#url'",
                         array('url' => $url));
    $existing = $SQLcheck->run('field', 'status');
    if( $existing != 'complete' && $existing != 'fetching' ) {
        $SQLdel = new SQLdel('files_to_fetch', array('url' => $url ))
        $SQLdel->run();
    }
    
    // Insert fetch-job
    $SQLins = new SQLins('files_to_fetch');
    $SQLins->add('cron_id', $_POST['cron_id']);
    $SQLins->add('url', $url);
    $SQLins->add('localpath', $_POST['localpath']);
    $SQLins->add('status', 'fetch');
    $SQLins->add('time_set', date('Y-m-d H:i:s'));
    $SQLins->run();
}

// CHECK FOR SPACE
$space = new SQL("SELECT SUM( `size` ) AS `size`
                  FROM `files`");
$space = $space->run('field', 'size');

$deleted = array();

if( ( (int) $space + (int) $_POST['size'] ) > CACHE_SIZE ) {
    // DELETE SOME FILES
    
    # Fetch from db, last accessed
    # While looping rows and $space - $current_file_size < CACHE_SIZE
    # delete files, and add $deleted[] = $cron_id;
}

die( json_encode( array('success' => true, 'deleted_files' => $deleted ) ) ) ;