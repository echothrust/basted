<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$bgcolor="";
$QUERY="SELECT * FROM generated";
$show=@$_GET['show'];
switch($show) {
	case 'active':
		$QUERY.=" WHERE alias_status='Active'";
		break;
	case 'spammed':
		$QUERY.=" WHERE alias_status='Spammed'";
	case 'all':
	default:
		break;
}
$result=mquery($QUERY);
?>
<br>
<table border=0>
<form action="tdelete.php" method=POST>
<tr><thead>Viewing: [Traps] <a href="?show=all">All</a> / <a href="?show=active">Active</a> / <a href="?show=spammed">Spammed</a></thead></tr>
<tr bgcolor=lightblue>
   <th></th>
   <th>Mbox Addr</th>
   <th>Mbox Creation</th>
   <th>SpamBot IP</th>
   <th>Trap status</th>
</tr>
<?
while ($rs=mysql_fetch_object($result)) {
        $bgcolor!="#CCCCCC"? $bgcolor="#CCCCCC": $bgcolor="#DDDDDD";
?>
<tr bgcolor=<?=$bgcolor?>>
    <td><input type=checkbox name="did[]" value="<?=$rs->id?>"></td>
    <td>&nbsp;<?=tohtml($rs->genmail)?>&nbsp;</td>
    <td>&nbsp;<?=$rs->gentime?>&nbsp;</td>
    <td>&nbsp;<?=long2ip($rs->ip)?>:<?=$rs->port?>&nbsp;</td>
    <td>&nbsp;<?=$rs->alias_status?>&nbsp;</td>
</tr>
<?
    }
?>
<tr>
<td></td>
<td colspan=2 ><input type=submit name="delete" value="delete selected"></td>
<td colspan=2><input type=submit name="delete" value="delete all"></td>
</tr>
</form>
</table>
</body></html>
<?
ob_end_flush();
?>
