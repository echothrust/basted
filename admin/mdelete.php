<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$mailcat=tohtml(@$_GET['mailcat']);
$id=@$_GET['id'];
$action=@$_GET['action'];
settype($id,"integer");
$QUP="";
$LOCATION="Location: communications.php";
$QUERY="DELETE FROM communications WHERE id='$id'";
if($action=='all') 
	$QUERY.=" OR parentid='$id'";
else $QUP="UPDATE communications SET parentid='0' WHERE parentid='$id'";
mquery($QUERY);
if($QUP!="") mquery($QUP);
header("Location: communications.php");
die();
ob_end_flush();
?>
