<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$id=@$_GET['id'];
$status=trim(@$_GET['status']);
settype($id,"integer");
$LOCATION="Location: index.php";
if(($status=="Initial Notification" || $status=="Followup" || $status=="Solved")
&& $id>=1) {
	$QUERY="UPDATE communications SET status='$status' WHERE id='$id'";
	mquery($QUERY);
	$LOCATION="Location: mdetails.php?id=$id";
}
header($LOCATION);
die();

ob_end_flush();
?>
