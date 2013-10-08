<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$mid=@$_GET['mid'];
$gid=@$_GET['gid'];
settype($mid,"integer");
settype($gid,"integer");
$bgcolor="";
?>
<br>
<table border="0">
<tr><thead>Viewing: [Spam]</thead></tr>
<tr bgcolor=lightblue>
    <th>&nbsp;Sender&nbsp;</th>
    <th>Recipient</th>
    <th>Mail Date</th>
    <th>Status</th>
    <th colspan=2>Report</th>
</tr>
<?
$QUERY="select *, if(mailbox.receipient in (SELECT genmail FROM generated ORDER BY genmail),'Spam','Direct') as alias_status, IFNULL((SELECT count(id) FROM communications WHERE mboxid=mailbox.id ORDER BY id LIMIT 1),'Not Reported') as whois_status FROM mailbox ORDER by date desc";
$result=mquery($QUERY);
while($rs=mysql_fetch_object($result)) {
@$bgcolor=="#CCCCCC" ? $bgcolor="#DDDDDD" : $bgcolor="#CCCCCC";
  $out="<tr bgcolor=\"$bgcolor\">
	<td>".tohtml($rs->sender)."</td>
	<td>".tohtml($rs->receipient)."</td>
	<td>".tohtml($rs->date)."</td>
	<td>".tohtml($rs->alias_status)."</td>
	<td>".tohtml(ctype_digit($rs->whois_status)?"Reported Times: $rs->whois_status" : $rs->whois_status)."</td>
	<td><a href=\"details.php?id=$rs->id\">Details</a></td>
</tr>";
	echo $out;
}
?>
</table>
</body></html>
<?
ob_end_flush();
?>
