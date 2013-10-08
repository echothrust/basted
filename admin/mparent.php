<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$mailcat=tohtml(@$_GET['mailcat']);
$comid=@$_GET['comid'];
$parentid=@$_GET['parentid'];
settype($comid,"integer");
settype($parentid,"integer");
$LOCATION="Location: communications.php";
$result=mquery("SELECT * FROM communications WHERE id='$comid'");
ifnot($result);
$result=mquery("SELECT * FROM communications WHERE id='$parentid'");
ifnot($result);
$QUERY="UPDATE communications SET parentid='$parentid' WHERE id='$comid'";
mquery($QUERY);
header($LOCATION);
die();

ob_end_flush();
function ifnot($result) {
if(mysql_num_rows($result)==0) {
	header("Location: communications.php");
	die();
}
else return mysql_num_rows($result);
}

?>
