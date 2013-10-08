<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$ID=@$_GET['id'];
settype($ID,"integer");
$QUERY="SELECT * FROM communications WHERE id='$ID'";
$result=mquery($QUERY);
if(mysql_num_rows($result)==0) {
	header("Location: communications.php");
	die();
}
$rs=mysql_fetch_object($result);
$QUERY="SELECT * FROM communications WHERE status='Initial Notification' AND id!='$ID'";
$paresult=mquery($QUERY);

?>
<br>
<table width="50%">
<thead><tr><td>Communications Details</td></tr></thead>
<tr>
	<th bgcolor="lightblue" align="right">From</th>
	<td bgcolor="#cccccc"><?=tohtml($rs->mailfrom)?></td>
</tr>
<tr>
	<th bgcolor="lightblue" align="right">To:</th>
	<td bgcolor="#cccccc"><?=tohtml($rs->mailto)?></td>
</tr>
<tr>
	<th bgcolor="lightblue" align="right">Date:</th>
	<td bgcolor="#cccccc"><?=tohtml($rs->date)?></td>
</tr>
<tr>
	<th bgcolor="lightblue" align="right">Subject:</th>
	<td bgcolor="#cccccc"><?=tohtml($rs->subject)?></td>
</tr>
<tr>
	<th bgcolor="lightblue" align="right">Status:</th>
	<td bgcolor="#cccccc">
		<form action="comstat.php">
		<input type=hidden name=id value=<?=$rs->id?>>
		<select name="status">
		<?
		foreach(array("Initial Notification","Followup","Solved") as $key) {
			echo "<option ";
			if($key==$rs->status) echo "SELECTED";
			echo " >$key</option>";
		}?>
		</select>
		<input type=submit name="update" value="update">
		</form>
	</td>
</tr>
<? if(mysql_num_rows($paresult)!=0) {?>
<tr> 
	<th bgcolor="lightblue" align="right">Parent:</th>
	<td bgcolor="#cccccc">
	   <form action="mparent.php">
	   <input type=hidden name=comid value="<?=$rs->id?>">
	   <select name="parentid">
	<?
		while($prs=mysql_fetch_object($paresult)) {
			echo "<option ";
			if($rs->parentid==$prs->id) echo "SELECTED";
			echo " value=\"$prs->id\">[ID-$prs->id] ";
			echo tohtml(substr($prs->subject,0,20));
			echo "</option>";
		}
	?>
	   </select>
	   <input type=submit name="update" value="update">
	   </form>
<? } ?>
	</td>
</tr>
<tr>
	<th bgcolor="lightblue" align=right>Actions</th>
	<td bgcolor="#cccccc">(<a title="Delete ONLY this record and zero out childs" href="mdelete.php?id=<?=$rs->id?>">Delete</a>) / (<a href="mdelete.php?id=<?=$rs->id?>&amp;action=ALL" title="Delete this record an all childs">Delete All</a>)</td>
</tr>
<tr>
	<th colspan=2 bgcolor="lightblue" align="center">body</th>
</tr>
<tr>
	<td colspan=2 bgcolor="#dddddd"><pre><?=tohtml($rs->body)?></pre></td>
</tr>
</table>
<?
if($rs->status=='Initial Notification') {
  $QUERY="SELECT * FROM communications";
  $QUERY.=" WHERE parentid='$rs->id' ORDER BY date DESC";
$result=mquery($QUERY);
while($rs=mysql_fetch_object($result)) {
?>
<table width="50%">
<thead><tr><td>Followup</td></tr></thead>
<tr></tr>
<tr>
        <th bgcolor="lightblue" align="right">From</th>
        <td bgcolor="#cccccc"><?=tohtml($rs->mailfrom)?></td>
</tr>
<tr>
        <th bgcolor="lightblue" align="right">To:</th>
        <td bgcolor="#cccccc"><?=tohtml($rs->mailto)?></td>
</tr>
<tr>
        <th bgcolor="lightblue" align="right">Date:</th>
        <td bgcolor="#cccccc"><?=tohtml($rs->date)?></td>
</tr>
<tr>
        <th bgcolor="lightblue" align="right">Subject:</th>
        <td bgcolor="#cccccc"><a href="mdetails.php?id=<?=$rs->id?>"><?=tohtml($rs->subject)?></a></td>
</tr>
<? } ?>
</table>
<?}?>
</body></html>
<?
ob_end_flush();
?>
