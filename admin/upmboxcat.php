<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$mailcat=tohtml(@$_GET['mailcat']);
$id=@$_GET['id'];
settype($id,"integer");
$LOCATION="Location: index.php";
if($id>=1 && $mailcat=="Followup") {
	$QUERY="INSERT INTO communications (subject,date,status,body,mailto,mailfrom) SELECT subject,date,'Followup',content,receipient,sender FROM mailbox WHERE id='$id'";
	mquery($QUERY);
	$QUERY="DELETE FROM mailbox WHERE id='$id'";
	mquery($QUERY);
	$LOCATION="Location: communications.php";
}
header($LOCATION);
die();

ob_end_flush();
?>
