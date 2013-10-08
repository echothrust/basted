<?
ob_start();
extract($_POST);
include('includes/config.php');
include('includes/functions.php');
include('includes/mailtemplate.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$whois=tosql($whois);
settype($id,"integer");
if($id<1) {
	header("Location: spam.php");
	die();
}

$emails=trim(implode(",",$mailto));
$MESBODY=$body."\n\n".$details;
$body=tosql($MESBODY);
$sub=tosql($subject);
$QUERY="INSERT INTO communications (mboxid, subject,mailto,mailfrom,body,date,status,whois)";
$QUERY.=" VALUES ('$id','$subject','$emails','$MAIL','$body',NOW(),'Initial Notification','$whois')";
mquery($QUERY);
$RID=mysql_insert_id();
mail("$emails","[ID-$RID] $subject","$MESBODY",$HEADERS);
header("Location: spam.php");
die();
ob_end_flush();
?>
