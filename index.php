<?php

ini_set("log_errors", 1);
ini_set('display_errors', 0);

require_once('UKMconfig.inc.php');
require_once('UKM/sql.class.php');

?>

<!DOCTYPE html>

<title>Cache status</title>
<link rel="stylesheet" href="http://ukm.dev/wp-content/themes/UKMresponsive/vendor/bootstrap/3.0.1/css/bootstrap.css">
<meta http-equiv="refresh" content="10">
<body class="container">

<h1>Status</h1>

<strong>Cache ID: </strong><?= 'foobar' /*getCacheID() This should be added to a common util class for the caches, along with humaizeSize */ ?>

<h2>Files</h2>
<table class="table">
<tr><th>Cron ID</th><th>Size</th><th>Last access</th></tr>
<?php

$files_query = new SQL("SELECT * FROM files");
$res = $files_query->run( $files_query );
while( $row = mysql_fetch_assoc( $res ) ) {
    $atime = $row['atime'];
    $size = $row['size'];
    $cron_id = $row['cron_id'];
    $path_mobile = $row['path_mobile'];
    $path_hd = $row['path_hd'];
    $path_smil = $row['path_smil'];
    $path_image = $row['path_image'];
    echo "<tr><td>$cron_id</td><td>$size</td><td>$atime</td></tr>";
} 
?>

</table>

<h2>Files to fetch</h2>
<table class="table">
<tr><th>Cron ID</th><th>URL</th><th>Local path</th></tr>

<?php
$ftf_query = new SQL("SELECT * FROM files_to_fetch");
$res = $ftf_query->run( $ftf_query );
while ( $row = mysql_fetch_assoc( $res ) ) {
    $cron_id = $row['cron_id'];
    $url = $row['url'];
    $local_path = $row['local_path'];
    echo "<tr><td>$cron_id</td><td>$url</td><td>$local_path</td></tr>";
}

?>

</table>
</body>
