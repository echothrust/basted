#!/usr/bin/perl 
# script for inserting mails into mysql database.
# $Id: attach.pl,v 1.1.1.1 2004/03/26 20:52:55 dtb Exp $

use Mail::Box::Manager;
use Mail::Box::Maildir;
use MIME::Type;
use DBI qw(:sql_types);

my $HOST     = "localhost";
my $DATABASE = "database";
my $USERNAME = "user";
my $PASSWORD = "password";
my $BASEDIR  = "/full/path/to/Maildir";

my $mgr    = Mail::Box::Manager->new();
my $folder = $mgr->open(
    folder            => $BASEDIR,
    type              => 'Mail::Box::Maildir',
    access            => 'rw',
    remove_when_empty => 0,
);

my $count        = 0;
my $databaseName = "DBI:mysql:$DATABASE";
my $dbh          = DBI->connect( $databaseName, $USERNAME, $PASSWORD )
  || die "Connect failed: $DBI::errstr\n";

foreach my $message ( $folder->messages ) {
    my @attachments = $message->parts;
    my $subject = $message->get('Subject') || "Nosubject";
    $subject = $dbh->quote($subject) || "NoSubject";
    my $from      = $dbh->quote( $message->get('from') );
    my $to        = $dbh->quote( $message->get('X-Original-To') );
    my $delivered = $dbh->quote( $message->get('Delivered-To') );
    my $body      = $dbh->quote( $message->body );
    my $head      = $dbh->quote( $message->head );

    $stmt =
"INSERT INTO mailbox (`headers`,`date`,`receipient`,`sender`,`subject`,`content`,`delivered`) VALUES ($head,NOW(),$to,$from,$subject,$body,$delivered)";
    $sth = $dbh->prepare($stmt) || die "prepare: $stmt: $DBI::errstr";
    $sth->execute || die "execute: $stmt: $DBI::errstr";
    $stmt = "UPDATE generated SET alias_status='Spammed' WHERE genmail=$to";
    $sth = $dbh->prepare($stmt) || die "prepare: $stmt: $DBI::errstr";
    $sth->execute || die "execute: $stmt: $DBI::errstr";
    $message->delete;
}
$folder->close;
$sth->finish();
$dbh->disconnect();

