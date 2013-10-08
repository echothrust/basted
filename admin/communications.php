<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$bgcolor="";
?>
<br>
<table border="0">
<tr><thead>Viewing: [Communications]</thead></tr>
<tr bgcolor=lightblue>
    <th>ID</th>
    <th>Mail From</th>
    <th>Recipient</th>
    <th>Subject</th>
    <th>Mail Date</th>
    <th>Status</th>
</tr>
<?
$QUERY="SELECT * FROM communications WHERE parentid=0";
$result=mquery($QUERY);
while($rs=mysql_fetch_object($result)) {
@$bgcolor=="#CCCCCC" ? $bgcolor="#DDDDDD" : $bgcolor="#CCCCCC";
  $out="<tr bgcolor=\"$bgcolor\">
	<td bgcolor=\"lightblue\">ID-".tohtml($rs->id)."</td>
	<td>".tohtml($rs->mailfrom)."</td>
	<td>".tohtml($rs->mailto)."</td>
	<td>".tohtml(substr($rs->subject,0,25)."...")."</td>
	<td>".tohtml($rs->date)."</td>
	<td><a href=\"mdetails.php?id=$rs->id\">".tohtml($rs->status)."</a></td>
</tr>";
	echo $out;
}
?>
</table>
</body></html>
<?
ob_end_flush();
?>
