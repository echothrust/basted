<?
/* 

This is an example only. 
This is a simple trap, this requires a place that will only serve 
as spambot trap. If you need another simpler example take a look at 
simple-example.php

*/

// Change this and put the full path to the following files
include('../admin/includes/functions.php');
include('../admin/includes/config.php');


$link=dblink($dbuser,$dbpass,$dbhost,$dbname);

/*
   Geneates a random mail and checks if exists in the database.
   If not returns the generated, if exists the function generates
   another until a uniq found.
*/
$generated = getvalid($link);
$word = $generated[0];
$hyphenated_word = $generated[1];
$ip = ip2long($_SERVER['REMOTE_ADDR']);
$port = tosql($_SERVER['REMOTE_PORT']);
$agent = tosql($_SERVER['HTTP_USER_AGENT']);
$referer = tosql(@$_SERVER['HTTP_REFERER']);
insert($ip,$port,$agent,$referer,"$hyphenated_word@$domain");
?>
<html><head>
<title><?=$word?>'s Infopage</title></head>
<body bgproperties="fixed" background="files/backer" bgcolor="#500030" link="#44aaff" vlink="#009999" alink="yellow">
<center><font size="7" color="#ffa000" face="times new roman">
        Welcome to <?=$domain?> home of <a href="mailto:<?=$hyphenated_word?>@<?=$domain?>"><?=$word?></a>
</font></center>

<hr size="4" width="75%" align="center" noshade="noshade">
<font color="#ffffff"><br><br>
    <table>
      <tbody><tr>
<td>
  <br><br>
  <font color="#ffffff">
    <h3>
    Welcome to my personal infopage.
    <br>
    I graduated with a degree in 
      <font face="verdana" color="#ffa000">
	<i>
	  Science - C.S.
	</i>
      </font> 
      with great distinction from the Albino in Nigeria. 
    </h3>
    <table>
      <tbody><tr>
	<td width="15%">
	</td>
	<td>
	  <font color="#ffffff">
	    My classes this semester are:
	    <ul><font face="monospace">
				<li> Working on my stinkin Master's thesis. I guess I'm lookin at way to obtain the
[partial] derivatives needed to make the implemented techniques
described here work right. I'm completely out of my depth, believe me! </li></font></ul>
	    <br><br>
	    What I do:
	    <ol>
	      <li>
		  Gripe 
	      </li><li>
		  University 
	      </li><li>
Music : compose, sing (most forms of it: choirs, chamber music,
soloist, barbershopper, musicals, operas) play trombone </li><li>
		  Have fun at Church
		</li>
	    </ol>
	  </font>
	</td>
      </tr>
    </tbody></table></font>
  </td>
  <td>
    <br><br>
      <img alt="The Faces  of Alex" src="files/Alex%2520face" border="0" width="211" height="250">
  </td>
</tr>
<tr><td>
<table>
  <tbody><tr> 
   <td width="15%">
   </td>

<td><font color=whitesmoke>Some of my shallow selection of interests and hobbies include:<br><pre>
* Dog breeding
* Doll making
* Sculpture
* Sewing
* Wood carving
* Antiques
* Books
* Classic videogames
* Comic books
* Dumpster diving
* Records
* Stamps
* Trading cards such as baseball cards
* Computer programming
* Linux
* Open source and the free software movement
* Cooking
* Electronics
* Hardware hacking
* Amateur radio and CB radio
* Robots
* DIY
* HoMe Repairs
* Film-making
* Games
* Board games
* Chess
* Pente
* Card games
* Bridge
* Poker
* Backgammon
* Gin Rummy
* Dominoes
* Wargaming, sometimes with miniatures
* Role-playing games
* Historical reenactment, as in the Society for Creative Anachronism
* Homebrewing
* Literature
* Reading
* Writing
* Learning foreign languages
* Motor vehicles
* Antique cars
* Kit-cars
* Motorcycles
* Trucks
* Music
* Musical composition and MIDI compostion
* Observation
* Amateur astronomy
* Train, plane, and bus spotting
* Outdoor nature activities
* Birdfeeding, birding, and birdwatching
* Butterfly watching
* Canoeing and kayaking
* Mountain climbing
* Rafting
* Stone skipping
* Walking
* Photography
* Kite photography
* Crossword puzzles
* Research-related
* Genealogy
* Hagiography
* Restoration of highly entropic artifacts
* Antique machinery
* SailboatZ
* Houses
* Sports
* Gliding
* Hunting
* Sailing
* Shooting rifles, pistols, and shotguns
* Fantasy sports
* Toy
* Lego, including brikwars
</pre></font></Td></tr>
</tbody></table></td><td>&nbsp;</td></tr></tbody></table>
    <br><br>
    </font><hr size="4" width="75%" align="center" noshade="noshade">
<font color="#ffffff">    <font size="2">
      This page was last updated: 
      <i>
        <font color="yellow">
          <script language="JavaScript">
            var dateMod = "";
            dateMod = document.lastModified;
            document.write(dateMod);
            document.write(); 
          // --></script>
        </font>
      </i>
      <br>
      by <?=$word?>:
      <a href="mailto:<?=$hyphenated_word?>@<?=$domain?>">
        <img align="middle" alt="Please E-mail me!" border="0" id="IMG1" src="files/mailbox.gif" width="50" height="50">
      </a>
      <br>
    </font>
  </font>
</body></html>
