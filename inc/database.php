<?php

// database connection

function openDB() {
	global $dbhst, $dbusr, $dbpwd, $dbnam;
	$dblnk = mysql_connect($dbhst, $dbusr, $dbpwd) or die(errorDB(0));
	$dbsel = mysql_select_db($dbnam, $dblnk) or die(errorDB(1));
}

function closeDB() {
	mysql_close();
}

function errorDB($errorID, $text="") {
	switch ($errorID) {
		case 0: // connection error
			echo "<p>Fehler beim Verbinden mit der Datenbank.</p>";
			break;
		case 1: // no such db
			echo "<p>Keine entsprehende Datenbank gefunden.</p>";
			break;
		case 2: // sql error
			echo "<p>Fehler beim Ausf&uuml;hren des SQL-Befehls. ($text)</p>";
			break;
		default:
			echo "<p>Unbekannter Fehler beim Verbinden mit der Datenbank.</p>";
	}
}

// sql statements

function sqlSelectInventory($page) {
	global $pageentries;
	global $tbl_items;
	$page = htmlspecialchars(mysql_real_escape_string($page));
	$pageskip = ($page-1)*$pageentries;
	return "SELECT `uid`, `name`, `condition`, `lent`, `description`, `lending`, `owner` FROM `$tbl_items` ORDER BY `name` ASC LIMIT $pageskip, ".$pageentries.";";
}

function sqlSelectInventoryByID($id) {
	global $tbl_items;
	$id = htmlspecialchars(mysql_real_escape_string($id));
	return "SELECT `uid`, `name`, `condition`, `lent`, `description`, `lending`, `owner` FROM `$tbl_items` WHERE `uid`='".alphabetToNumber($id)."';";
}

function sqlSelectInventoryLast() {
	global $tbl_items;
	return "SELECT `uid`, `name`, `condition`, `lent`, `description`, `lending`, `owner` FROM `$tbl_items` ORDER BY `uid` DESC LIMIT 1;";
}

function sqlSelectInventoryTags($page=1, $tags) {
	global $pageentries;
	global $tbl_items;
	global $tbl_tags;
	global $tbl_tags_link;
	$pageskip = ($page-1)*$pageentries;
	$sql = "SELECT i.`uid`, i.`name`, i.`condition`, i.`lent`, i.`description`, i.`lending`, i.`owner`
            FROM `$tbl_items` i, $tbl_tags t, $tbl_tags_link l
            WHERE i.`uid` = l.`iid` AND t.`uid` = l.`tid` AND ( ";
	$i = 0;
	$tags = explode(" ", utf8_decode($tags));
	foreach ($tags as $tag) {
		$tag = htmlspecialchars(mysql_real_escape_string(decodeLink($tag)));
		$sql .= "t.`name` = '$tag' ";
		if($i < count($tags)-1)
			$sql .= " OR ";
		else
			$sql .= " ) ORDER BY i.`name` LIMIT $pageskip, ".$pageentries.";";
		$i++;
	}
	return $sql;
}


function sqlCountInventoryTags($tags) {
	global $pageentries;
	global $tbl_items;
	global $tbl_tags;
	global $tbl_tags_link;
	$sql = "SELECT COUNT(i.`uid`)
            FROM `$tbl_items` i, $tbl_tags t, $tbl_tags_link l
            WHERE i.`uid` = l.`iid` AND t.`uid` = l.`tid` AND ( ";
	$i = 0;
	$tags = explode(" ", utf8_decode($tags));
	foreach ($tags as $tag) {
		$tag = htmlspecialchars(mysql_real_escape_string(decodeLink($tag)));
		$sql .= "t.`name` = '$tag' ";
		if($i < count($tags)-1)
			$sql .= " OR ";
		else
			$sql .= " );";
		$i++;
	}
	return $sql;
}

function sqlCountInventory() {
	global $tbl_items;
	return "SELECT COUNT(`uid`) FROM `$tbl_items` ;";
}

function sqlSelectTags() {
	global $tbl_tags;
	return "SELECT `uid`, `name` FROM `$tbl_tags` ORDER BY `name`;";
}

function sqlSelectTag($tid) {
	global $tbl_tags;
	$tid = htmlspecialchars(mysql_real_escape_string($tid));
	return "SELECT `name` FROM `$tbl_tags` WHERE `uid`=$tid;";
}

function sqlSelectTagID($name) {
	global $tbl_tags;
	$name = htmlspecialchars(mysql_real_escape_string($name));
	return "SELECT `uid` FROM `$tbl_tags` WHERE `name`='$name';";
}

function sqlSelectLinkedTagIDs($iid) {
	global $tbl_tags_link;
	$iid = htmlspecialchars(mysql_real_escape_string($iid));
	return "SELECT `tid` FROM `$tbl_tags_link` WHERE `iid`=$iid;";
}

function sqlCreateTag($name) {
	global $tbl_tags;
	$name = htmlspecialchars(mysql_real_escape_string(str_replace("-", " ", $name)));
	return "INSERT INTO `$tbl_tags` 
                   (`name`)
             VALUES('$name')";
}

function sqlCreateInventoryItem($name, $condition, $lent, $description, $lending, $owner) {
	global $tbl_items;
	$name = htmlspecialchars(mysql_real_escape_string($name));
	$id = htmlspecialchars(mysql_real_escape_string($id));
	$condition = htmlspecialchars(mysql_real_escape_string($condition));
	$lent = htmlspecialchars(mysql_real_escape_string($lent));
	$description = htmlspecialchars(mysql_real_escape_string($description));
	$lending = htmlspecialchars(mysql_real_escape_string($lending));
	$owner = htmlspecialchars(mysql_real_escape_string($owner));
	return "INSERT INTO `$tbl_items` 
                   (`name`, `condition`, `lent`, `description`, `lending`, `owner`)
             VALUES('$name', '$condition', '$lent', '$description', '$lending', '$owner')";
}

function sqlEditInventoryItem($uid, $name, $condition, $lent, $description, $lending, $owner, $newid) {
	global $tbl_items;
	$name = htmlspecialchars(mysql_real_escape_string($name));
	$id = htmlspecialchars(mysql_real_escape_string($id));
	$condition = htmlspecialchars(mysql_real_escape_string($condition));
	$lent = htmlspecialchars(mysql_real_escape_string($lent));
	$description = htmlspecialchars(mysql_real_escape_string($description));
	$lending = htmlspecialchars(mysql_real_escape_string($lending));
	$owner = htmlspecialchars(mysql_real_escape_string($owner));
	$sql = "UPDATE `$tbl_items` 
             SET `name`='$name',
				`condition`='$condition',
				`lent`='$lent',
				`description`='$description',
				`lending`='$lending',
				`owner`='$owner'";
	if ($newid) {
		$newuid = dbGetInventoryLast();
		$newuid = $newuid[0] -> {'uid'} + 1;
		$sql .= ",`uid`='$newuid'";
	}
	$sql .= "WHERE `uid`='$uid'";
	
	return $sql;
}

function sqlDeleteInventoryItem($id) {
	global $tbl_items;
	if ($id == "")
		return false;
	$id = htmlspecialchars(mysql_real_escape_string($id));
	$sql = "DELETE FROM `$tbl_items`
			WHERE `uid`='".alphabetToNumber($id)."' LIMIT 1;";
	return $sql;
}

function sqlCreateTagLinks($uid, $tags) {
	global $tbl_items;
	global $tbl_tags;
	global $tbl_tags_link;
	$sql = "INSERT INTO $tbl_tags_link(iid, tid)
			SELECT i.`uid`, t.`uid`
			FROM  `$tbl_items` i, `$tbl_tags` t
			WHERE i.`uid` = '$uid' AND ( ";
	$i = 0;
	foreach ($tags as $tag) {
		$tag = htmlspecialchars(mysql_real_escape_string(decodeLink($tag)));
		$sql .= "t.`name` = '$tag' ";
		if($i < count($tags)-1)
			$sql .= " OR ";
		else
			$sql .= " );";
		$i++;
	}
	return $sql;
}

function sqlDeleteTagLinks($iid) {
	global $tbl_tags_link;
	$iid = htmlspecialchars(mysql_real_escape_string($iid));
	$sql = "DELETE FROM `$tbl_tags_link`
			WHERE `iid`=$iid;";
	return $sql;
}

function sqlDeleteTagLinksTID($tid) {
	global $tbl_tags_link;
	$tid = htmlspecialchars(mysql_real_escape_string($tid));
	$sql = "DELETE FROM `$tbl_tags_link`
			WHERE `tid`=$tid;";
	return $sql;
}

function sqlDeleteTag($name) {
	global $tbl_tags;
	$name = htmlspecialchars(mysql_real_escape_string($name));
	$sql = "DELETE FROM `$tbl_tags`
			WHERE `name`='$name';";
	return $sql;
}

function sqlEditTagLinks($iid, $tids) {
	global $tbl_tags_link;
	$sql = "INSERT INTO `$tbl_tags_link` (iid, tid)
			VALUES ";
	$i = 0;
	foreach ($tids as $tid) {
		$tag = htmlspecialchars(mysql_real_escape_string(decodeLink($tid)));
		$sql .= " ('$iid', '$tid' ) ";
		if($i < count($tids)-1) {
			$sql .= " , ";
		} else {
			$sql .= " ;";
		}
		$i++;
	}
	return $sql;
}

function sqlMoveTagLinks($olduid, $newuid) {
	global $tbl_tags_link;
	$olduid = htmlspecialchars(mysql_real_escape_string($olduid));
	$newuid = htmlspecialchars(mysql_real_escape_string($newuid));
	$sql = "UPDATE `$tbl_tags_link` 
             SET `iid`=$newuid
             WHERE `iid`=$olduid";
	return $sql;
}

function sqlMoveURLs($olduid, $newuid) {
	global $tbl_urls;
	$olduid = htmlspecialchars(mysql_real_escape_string($olduid));
	$newuid = htmlspecialchars(mysql_real_escape_string($newuid));
	$sql = "UPDATE `$tbl_urls` 
             SET `iid`=$newuid
             WHERE `iid`=$olduid";
	return $sql;
}

function sqlDeleteURLs($iid) {
	global $tbl_urls;
	$iid = htmlspecialchars(mysql_real_escape_string($iid));
	$sql = "DELETE FROM `$tbl_urls`
			WHERE `iid`=$iid;";
	return $sql;
}

function sqlCreateURLs($id, $urls) {
	global $tbl_urls;
	$sql = "INSERT INTO `$tbl_urls` (title, url, iid)
			VALUES ";
	$l = count($urls);
	for ($i = 0; $i < $l; $i++) {
		$title = htmlspecialchars(mysql_real_escape_string($urls[$i][0]));
		$url = htmlspecialchars(mysql_real_escape_string($urls[$i][1]));
		$sql .= " ('$title', '$url', '$id' ) ";
		if($i < $l-1) {
			$sql .= " , ";
		} else {
			$sql .= " ;";
		}
	}
	return $sql;
}

function sqlSelectURLs($id) {
	global $tbl_urls;
	$id = htmlspecialchars(mysql_real_escape_string($id));
	return "SELECT `title`, `url` FROM `$tbl_urls` WHERE `iid`=$id;";
}

// data collectors
function dbGetTag($tid) {
	openDB();
	$sql = mysql_query(sqlSelectTag($tid)) or die(errorDB(2, mysql_error()));
	if ($row = mysql_fetch_object($sql)) {
		return $row -> {'name'};
	} else {
		return false;
	}
}

function dbGetTagID($name) {
	openDB();
	$sql = mysql_query(sqlSelectTagID($name)) or die(errorDB(2, mysql_error()));
	if ($row = mysql_fetch_object($sql)) {
		return $row -> {'uid'};
	} else {
		return false;
	}
}

function dbGetTags() {
	openDB();
	$sql = mysql_query(sqlSelectTags()) or die(errorDB(2, mysql_error()));
	$rows = array();
	while ($row = mysql_fetch_object($sql)) {
		array_push($rows, $row);
	}
	if (count($rows) < 1) {
		return false;
	} else {
		return $rows;
	}
}

function dbGetURLs($id) {
	openDB();
	$sql = mysql_query(sqlSelectURLs($id)) or die(errorDB(2, mysql_error()));
	$rows = array();
	while ($row = mysql_fetch_object($sql)) {
		array_push($rows, $row);
	}
	if (count($rows) < 1) {
		return false;
	} else {
		return $rows;
	}
}

function dbGetInventory($page=1, $tags="") {
	openDB();
	if ($tags == "")
		$sql = mysql_query(sqlSelectInventory($page)) or die(errorDB(2, mysql_error()));
	else
		$sql = mysql_query(sqlSelectInventoryTags($page, $tags)) or die(errorDB(2, mysql_error()));
	$rows = array();
	while ($row = mysql_fetch_object($sql)) {
		array_push($rows, $row);
	}
	if (count($rows) < 1) {
		return false;
	} else {
		return $rows;
	}
}

function dbGetInventoryByID($id) {
	openDB();
	$sql = mysql_query(sqlSelectInventoryByID($id)) or die(errorDB(2, mysql_error()));
	$rows = array();
	if ($row = mysql_fetch_object($sql)) {
		return array($row);
	} else {
		return false;
	}
}

function dbGetInventoryLast() {
	openDB();
	$sql = mysql_query(sqlSelectInventoryLast()) or die(errorDB(2, mysql_error()));
	$rows = array();
	if ($row = mysql_fetch_object($sql)) {
		return array($row);
	} else {
		return false;
	}
}

function dbGetInventoryItems($tags = "") {
	openDB();
	if ($tags == "")
		$sql = mysql_query(sqlCountInventory()) or die(errorDB(2, mysql_error()));
	else
		$sql = mysql_query(sqlCountInventoryTags($tags)) or die(errorDB(2, mysql_error()));
	if ($row = mysql_fetch_object($sql)) {
		if ($tags == "")
			return $row -> {'COUNT(`uid`)'};
		else
			return $row -> {'COUNT(i.`uid`)'};
	} else {
		return false;
	}
}

function dbGetInventoryPages($tags = "") {
	global $pageentries;
	openDB();
	return (int)ceil(dbGetInventoryItems($tags) / $pageentries);
	
}

function dbGetInventoryItemTags($iid) {
	if (!is_numeric($iid))
		return false;
	openDB();
	$sql = mysql_query(sqlSelectLinkedTagIDs($iid)) or die(errorDB(2, mysql_error()));
	$rows = array();
	while ($row = mysql_fetch_object($sql)) {
		array_push($rows, dbGetTag($row -> {'tid'}));
	}
	if (count($rows) > 0)
		return $rows;
	else
		return false;
}

// data transmitters
function dbCreateTag($name) {
	openDB();
	$sql = mysql_query(sqlCreateTag($name)) or die(errorDB(2, mysql_error()));
	if ($sql)
		return true;
	return false;
}

function dbCreateInventoryItem($name, $condition, $lent, $description, $lending, $owner) {
	openDB();
	$sql = mysql_query(sqlCreateInventoryItem($name, $condition, $lent, $description, $lending, $owner)) or die(errorDB(2, mysql_error()));
	if ($sql)
		return true;
	return false;
}

function dbCreateTagLinks($uid, $tags) {
	openDB();
	if (is_null($tags))
		return true;
	$sql = mysql_query(sqlCreateTagLinks($uid, $tags)) or die(errorDB(2, mysql_error()));
	if ($sql)
		return true;
	return false;
}

function dbEditInventoryItem($uid, $name, $condition, $lent, $description, $lending, $owner, $newid) {
	openDB();
	$sql = mysql_query(sqlEditInventoryItem($uid, $name, $condition, $lent, $description, $lending, $owner, $newid)) or die(errorDB(2, mysql_error()));
	if ($newid) {
		$newuid = dbGetInventoryLast();
		$newuid = $newuid[0] -> {'uid'};
		imageMove(numberToAlphabet($uid), numberToAlphabet($newuid));
		dbMoveURLs($uid, $newuid);
		dbMoveTagLinks($uid, $newuid);
	}
	if ($sql)
		return true;
	return false;
}

function dbDeleteInventoryItem($id) {
	openDB();
	$sql = mysql_query(sqlDeleteInventoryItem($id)) or die(errorDB(2, mysql_error()));
	return true;
}

function dbEditTagLinks($id, $tags) {
	openDB();
	$sql = mysql_query(sqlDeleteTagLinks($id)) or die(errorDB(2, mysql_error()));
	if (!is_null($tags))
		$sql = mysql_query(sqlEditTagLinks($id, $tags)) or die(errorDB(2, mysql_error()));
	return true; // crashes on error. so always true
}

function dbMoveTagLinks($olduid, $newuid) {
	openDB();
	$sql = mysql_query(sqlMoveTagLinks($olduid, $newuid)) or die(errorDB(2, mysql_error()));
	return true; // crashes on error. so always true
}

function dbMoveURLs($olduid, $newuid) {
	openDB();
	$sql = mysql_query(sqlMoveURLs($olduid, $newuid)) or die(errorDB(2, mysql_error()));
	return true; // crashes on error. so always true
}

function dbDeleteTag($name) {
	openDB();
	$sql = mysql_query(sqlDeleteTagLinksTID(dbGetTagID($name))) or die(errorDB(2, mysql_error()));
	$sql = mysql_query(sqlDeleteTag($name)) or die(errorDB(2, mysql_error()));
	return true;
}

function dbEditURLs($id, $urls) {
	openDB();
	$sql = mysql_query(sqlDeleteURLs($id)) or die(errorDB(2, mysql_error()));
	if (count($urls) > 0)
		$sql = mysql_query(sqlCreateURLs($id, $urls)) or die(errorDB(2, mysql_error()));
	return true; // crashes on error. so always true
}

function dbCreateURLs($id, $urls) {
	openDB();
	if (count($urls) == 0)
		return true;
	$sql = mysql_query(sqlCreateURLs($id, $urls)) or die(errorDB(2, mysql_error()));
	if ($sql)
		return true;
	return false;
}

?>
