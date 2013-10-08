<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$did=@$_POST['did'];
$delete=@$_POST['delete'];
$LOCATION="Location: traps.php";

if($delete=="delete selected") {
	foreach($did as $id) {
		if(ctype_digit($id)) 
			mquery("DELETE FROM generated WHERE id='$id'");
	}
	$LOCATION="Location: traps.php";
} else {
	mquery("DELETE FROM generated");
	$LOCATION="Location: index.php";
}
header($LOCATION);
die();
ob_end_flush();
?>
