<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
?>
<br>
<table border=0 width="100%">
<tr>
<th bgcolor="lightblue" colspan=2>Welcome to B.A.S.T.E.D. administration interface</th>
</tr>
<tr></tr>
<tr>
	<th valign=top align="left" bgcolor="#dddddd"><a href="traps.php">TRAPS</a></th>
	<td bgcolor="#cccccc" align=justify><?=file_get_contents("help/traps.txt")?></td>
</tr>
<tr></tr>
<tr>
	<th valign=top align="left" bgcolor="#dddddd"><a href="spam.php">SPAM</a></th>
	<td bgcolor="#cccccc" align=justify><?=file_get_contents("help/spam.txt")?></td>
</tr>
<tr></tr>
<tr>
	<th align="left" valign=top bgcolor="#dddddd"><a href="communications.php">Communications</a>&nbsp;</th>
	<td bgcolor="#cccccc" align=justify><?=file_get_contents("help/communications.txt")?></td>
</tr>
<tr></tr>

</table>
<br>

</body></html>
<?
ob_end_flush();
?>
