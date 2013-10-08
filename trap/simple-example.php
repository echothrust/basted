<?
include('../admin/includes/functions.php');
include('../admin/includes/config.php');

$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$mailto=geninsert($link, $_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_PORT'],
	  $_SERVER['HTTP_USER_AGENT'],@$_SERVER['HTTP_REFERER'],$domain);
printf("Hi my name is %s. You can mail me at %s\n<br>",$mailto[0],"$mailto[1]@$domain");
?>
