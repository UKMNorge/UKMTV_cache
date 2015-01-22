<?php

ini_set("log_errors", 1);
ini_set('display_errors', 0);

require_once('UKMconfig.inc.php');
require_once('../tvconfig.php');
require_once('UKM/sql.class.php');

$caches_query = new SQL("SELECT `id`, `ip`, `status`, `last_heartbeat` FROM `ukm_tv_caches_caches`");
$res = $caches_query->run( $caches_query );

?>

<!DOCTYPE html>
<title>Caches :: UKM-TV</title>
<link rel="stylesheet" href="http://ukm.dev/wp-content/themes/UKMresponsive/vendor/bootstrap/3.0.1/css/bootstrap.css">
<meta http-equiv="refresh" content="30">
<body class="container">
<h1>Caches</h1>
<table class="table">
    <tr><th>ID</th><th>IP</th><th>Status</th><th>Last heartbeat</th></tr>

<?php

while ( $row = mysql_fetch_assoc( $res ) ) {
    $cache_id = $row['id'];
    $ip = $row['ip'];
    $status = $row['status'];
    $last_heartbeat = $row['last_heartbeat'];
    echo "<tr><td>$cache_id</td><td>$ip</td><td>$status</td><td>$last_heartbeat</td></tr>";
}

?>
</table>
</body>
