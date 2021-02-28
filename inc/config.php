<?php

$debug = true;

$basedir = '/inventar';                 // base directory
$imgdir = 'img/archive';      // images
$tmbdir = 'img/archive_tmb';  // thumbnails
$qrurl = 'http://flipdot.org/qr'; // url for the qr code generator

$upload_size = 5; // upload form size limit in megabytes
/*
$serverbasedir = '/html/net.felix-moeller/www/inventar'; // server side
$serverimgdir = $serverbasedir.'/img/archive';      // images
$servertmbdir = $serverbasedir.'/img/archive/tmb';  // thumbnails
*/

$dbhst = 'localhost';
$dbusr = 'flipdot';
$dbpwd = 'LuedwiHitArEntin';
$dbnam = 'flipdot_inventar';
$dbpre = 'fda_';

$tbl_items = $dbpre.'items';
$tbl_users = $dbpre.'users';
$tbl_tags = $dbpre.'tags';
$tbl_tags_link = $dbpre.'tags_link';
$tbl_urls = $dbpre.'urls';

$pageentries = 30;

?>
