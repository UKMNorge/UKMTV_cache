<?php
require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');

$SQL = new SQL("SELECT * FROM `files`");
$res = $SQL->run();

while( $f = mysql_fetch_assoc( $res ) ) {
    $atime = fileatime( $f['path'] );
    
    $SQLupd = new SQLins('files', array('id' => $f['id'] ) );
    $SQLupd->add('atime', date('Y-m-d H:i:s', $atime) );
    $SQLupd->run();
}