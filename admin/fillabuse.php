<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/mailtemplate.php');
include('includes/header.php');
require "Net/Whois.php";
$whois = new Net_Whois; 
$ip=ip2long(@$_GET['ip']);
$ID=@$_GET['id'];
settype($ID,"integer");
settype($ip,"integer");
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);

if(($ID<1 && $GID<1) || $ip==-1 || $ip==0) {
	header("Location: spam.php");
	die();
}
$result=mquery("SELECT * FROM mailbox WHERE id='$ID'");
ifnot($result);
$mb=mysql_fetch_object($result);
$IP=long2ip($ip);
$data = $whois->query($IP); 
$lines=explode("\n",$data);
$RefLine=array_values(preg_grep("/ReferralServer/",$lines));
$server=explode("//",@$RefLine[0]);
if(trim(@$server[1])!="") 
	$data = $whois->query($IP,$server[1]);
preg_match_all("/[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}/",$data,$mails);
$emails=array_unique($mails[0]);
$mails=array_values(preg_grep("/(abuse)|(spam)/",$emails));
?> 
<br>
<table width="100%" border=0>
<tr><td>
<form action="send.php" method=POST>
<table >
<tr><th colspan=2 bgcolor="lightblue">Abuse Report</th></tr>
<input type=hidden name=id value="<?=$ID?>">
<input type=hidden name=whois value="<?=tohtml($data)?>">
<tr>
	<td>Mail From</td>
	<td><input readonly type=text name=mailfrom value="<?=$MAILFROM?>" size=60></td>
</tr>
<tr>
	<td>Mail To:</td>
	<td><input type=text size=60 name="mailto[]"></td>
</tr>
<?
for ($i=0;$i<count($mails) ;$i++) {?>
<tr>
	<td>Mail To:</td>
	<td>
	   <input type=text size=60 name="mailto[]" value="<?=$mails[$i]?>">
	</td>
</tr>
<?}?>

<tr>
	<td>Subject:</td>
	<td><input type=text size=60 name=subject value="<?=$SUBJECT?>"></td>
</tr>

<tr>
	<td bgcolor="lightblue" align=center colspan=2>Mail body</td>
</tr>

<tr>
	<td align=center colspan=2>
		<textarea wrap=hard cols=80 rows=30 name=body><?=$BODY?></textarea>
	</td>
</tr>
<tr><td align=center colspan=2><textarea wrap=hard cols=80 rows=20 name=details>
**Mail Headers Follow**
***********************
<?=tohtml(ereg_replace("Delivered-To: [^<>[:space:]]+[[:alnum:]]","Delivered-To: $mb->receipient",$mb->headers))?>
</textarea></td></tr>
<tr><td colspan=2 align=center><input type=submit name=sent value=send></td></tr>

</table>
</form>
</td>
<td valign="top">
<table width="100%">
<tr><th bgcolor="lightblue">Whois Data [<?=$IP?>]</th></tr>
<tr><td bgcolor="#cccccc">
<pre>
<?=tohtml($data)?>
</pre>
</td></tr></table>
</td></tr>
</table>
</body>
</html>
<?
function ifnot($result) {
if(mysql_num_rows($result)==0) {
	header("Location: spam.php");
	die();
}
}
?>
