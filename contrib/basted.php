#!/var/www/bin/php 
<?
include('/var/www/htdocs/basted/admin/includes/config.php');
include('/var/www/htdocs/basted/admin/includes/functions.php');

$link=dblink($dbuser,$dbpass,$dbhost,$dbname);
/*
 Most of these have been taken from 
 http://www.theukwebdesigncompany.com/articles/php-incoming-mail.php
*/
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);
$fd=fopen("/tmp/mail","w");
fwrite($fd,$email);
fclose($fd);
$splittingheaders = true;
$to=$subject=$from=$delivered="";
$lines = explode("\n", $email);
for ($i=0; $i<count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = tosql($matches[1]);
        }
        if (preg_match("/^Delivered-To: (.*)/", $lines[$i], $matches)) {
            $delivered = tosql($matches[1]);
        }
        if (preg_match("/^X-Original-To: (.*)/", $lines[$i], $matches)) {
            $to = tosql($matches[1]);
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = tosql($matches[1]);
        }
    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}
$headers=tosql($headers);
$message=tosql($message);
mysql_query("INSERT INTO mailbox (headers,date,receipient,sender,subject,content,delivered) VALUES('$headers',NOW(),'$to','$from','$subject','$message','$delivered')") or die(mysql_error());
mysql_query("UPDATE generated SET alias_status='Spammed' WHERE genmail='$to'") or die(mysql_error());
?>
