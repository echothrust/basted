<?
$HEADERS = "MIME-Version: 1.0\n";
$HEADERS.="From: $MAILFROM\n";
$HEADERS.="Rerurn-Path: $MAILFROM\n";
$HEADERS.="Reply-To: $MAIL\nX-Mailer: BASTED$VERSION Mailer \n";
$HEADERS.= "Content-type: text/plain; charset=iso-8859-1\n";
$BODY="Hi,

Our network has setup a B.A.S.T.E.D honeypot to attract unsolicited commercial
email, otherwise known as SPAM, in order to notify network administrators about
SPAM activity deriving from their networks.

This is a BASTED generated report: If you believe that you have received this
message in error, please accept our sincere apologies. We ask that you please 
reply to this email message keeping the subject line intact (Re:, FW: fwd: 
doesnt matter the interesting part is the [ID:#]).

The report below will help you track the spam source inside your ip range.

Thank you very much,

Postmaster

B.A.S.T.E.D. report";
?>
