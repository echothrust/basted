<?
ob_start();
include('includes/config.php');
include('includes/functions.php');
include('includes/header.php');
$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
$id=@$_GET['id'];
settype($id,"integer");
$bgcolor="";
if($id<1) {
	header("Location: spam.php");
	die();
}
else {
	$QUERY="SELECT * FROM mailbox WHERE id='$id'";
	$result=mquery($QUERY);
	if(mysql_num_rows($result)==0) {
		header("Location: index.php");
		die();
	}
	$rs=mysql_fetch_object($result);
	$QUERY="SELECT * FROM generated WHERE genmail='$rs->receipient'";
	$res=mquery($QUERY);
	$NUMROWS=mysql_num_rows($res);
	
	$rg=mysql_fetch_object($res);
?>
<br>
<table width="100%" height="80%">
<tr valign=top><td width="50%">
<table width="100%" border="0">
<tr><thead>Viewing: [Spam]</thead></tr>
<tr bgcolor=lightblue>
    <th colspan=2>Spam Details</th>
</tr>
<tr bgcolor="#DDDDDD">
    <th align=right>Category</th>
    <td>
	<form action=upmboxcat.php>
	<input type=hidden name=id value="<?=$rs->id?>">
	<select name=mailcat>
	<option>Default</option>
	<option>Followup</option>
	</select>
	<input type=submit name="update" value="update">
	</form>
    </td>
</tr>
<tr bgcolor="#DDDDDD">
    <th align=right>From:</th>
    <td><?=tohtml($rs->sender)?></td>
</tr>
<tr bgcolor="#DDDDDD">
    <th align=right>To:</th>
    <td><?=tohtml($rs->receipient)?></td>
</tr>
<tr  bgcolor="#DDDDDD">
    <th align=right>Delivered:</th>
    <td><?=tohtml($rs->delivered)?></td>
</tr>
<tr bgcolor="#DDDDDD">
	<th align=right>Subject:</th>
	<td><?=tohtml($rs->subject)?></td>
</tr>
<tr bgcolor="#DDDDDD">
    <th align=right>Date:</th>
    <td><?=tohtml($rs->date)?></td>
</tr>
<tr bgcolor=lightblue>
	<th colspan=2>Headers</th>
</tr>
<tr bgcolor="#DDDDDD">
	<td colspan=2>
	  <pre><?=makenice(tohtml($rs->headers),$id)?></pre>
	</td>
</tr>

<tr bgcolor=lightblue>
	<th colspan=2>Body</th>
</tr>
<tr bgcolor="#DDDDDD">
	<td colspan=2><pre><?=tohtml($rs->content)?></pre></td>
</tr>
</table>
</td>
<td>
<?
if ($NUMROWS!=0) {?>
<table width="100%" border=0>
<thead>&nbsp</thead>
<tr><th colspan=2 bgcolor=lightblue>Bot Details</th></tr>
<tr bgcolor="#CCCCCC">
	<th>IP:Port</th>
	<td><?=long2ip(@$rg->ip)?>:<?=@$rg->port?></td>
</tr>
<tr bgcolor="#CCCCCC">
	<th>User Agent:</th>
	<td><?=tohtml(@$rg->agent)?></td>
</tr>
<tr bgcolor="#CCCCCC">
	<th>Referrer:</th>
	<td><?=tohtml(@$rg->referer)?></td>
</tr>
<tr bgcolor="#CCCCCC">
	<th>Generation Time:</th>
	<td><?=tohtml(@$rg->gentime)?></td>
</tr>
<tr bgcolor="#CCCCCC">
	<th>Alias:</th>
	<td><?=tohtml(@$rg->alias)?></td>
</tr>
<tr bgcolor="#CCCCCC">
	<th>Alias Status:</th>
	<td><?=tohtml(@$rg->alias_status)?></td>
</tr>
</table>
<?}?>
</td></tr>
<? 
$QUERY="SELECT * FROM communications WHERE mboxid='$id' AND status='Initial Notification'";
$report_res=mquery($QUERY);
if(mysql_num_rows($report_res)!=0) {
	$reps=mysql_fetch_object($report_res);
?>
<tr><td colspan=2>
<table width="100%" border=0>
<tr><th colspan=2 bgcolor=lightblue>Report Details</th></tr>
<tr>
	<th valign=top bgcolor="#cccccc">Report Status</th>
	<td bgcolor="#dddddd"><?=tohtml($reps->status)?></td>
</tr>
<tr>
	<th valign=top bgcolor="#cccccc">Report date</th>
	<td bgcolor="#dddddd"><?=tohtml($reps->date)?></td>
</tr>
<tr>
	<th valign=top bgcolor="#cccccc">Report Sent</th>
	<td bgcolor="#dddddd"><pre><?=tohtml($reps->body)?></pre></td>
</tr>
<tr>
	<th valign=top bgcolor="#cccccc">Contacts Whois</th>
	<td bgcolor="#dddddd"><pre><?=tohtml($reps->whois)?></pre></td>
</tr>
</table></tr></td>
<?}?>
</table>
<?
}
?>
</body></html>
<?
ob_end_flush();
?>
