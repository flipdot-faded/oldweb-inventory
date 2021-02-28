<?php

function renderLogin() {
	renderHeader();
	echo '<div id="login">';
	echo '<form class="input" method="post" action="'.htmlentities($_SERVER["REQUEST_URI"]).'">';
	echo
	'<fieldset>
		<legend>Login</legend>
		<ol>
			<li><label for="user"><span>User</span></label><input id="user" name="user" type="text" placeholder="..."></li>
			<li><label for="pass""><span>Pass</span></label><input id="pass" name="pass" type="password" placeholder="..."></li>
			<li><input type="submit" value="Einloggen &raquo;" name="submit"></li>
		</ol>
	</fieldset></form></div>';
	renderFooter();
}


function renderLogout() {
	renderHeader();
	echo '	<div id="login">
				<fieldset>
					<legend>Logout</legend>
					<p>Du wurdest erfolgreich abgemeldet.</p>
				</fieldset>
			</div>';
	renderFooter();
}

function renderNewID() {
	$uid = dbGetInventoryLast();
	$uid = $uid[0] -> {'uid'} + 1;
	$id = numberToAlphabet((int)$uid);
	echo $id;
}

function render() {
	global $categories;
	global $categories_callbacks;
	global $currentcategory;
	global $subcategories;
	global $subcategories_callbacks;
	global $currentsubcategory;
	global $page;
	global $a;
	global $b;


	// do nothing if redirected or special page
	if (redirect() or specialPage())
		return;
	
	renderHeader();
	renderMenu();
	
	if($currentsubcategory >= 0) {
		$subpage = $subcategories_callbacks[$currentcategory][$currentsubcategory];
	}
	$page = $categories_callbacks[$currentcategory];
	
	/*if($currentsubcategory == -1 || $currentsubcategory == "") {
		$page = $categories_callbacks[$currentcategory];
	} else {
		$page = $subcategories_callbacks[$currentcategory][$currentsubcategory];
	}*/

	switch ($subpage) {
		// sub pages
		case "inventory-new":
			renderInventoryNew();
			break;
		case "inventory-edit":
			renderInventoryEdit();
			break;
		case "tags-new":
			renderTagsNew();
			break;
		case "people-new":
			renderPeopleNew();
			break;
		case "people-profile":
			renderPeopleProfile();
			break;
			
		// main page, error or special page
		default:
			switch ($page) {
				// main pages
				case "inventory":
					if (isset($b)) {
						$tmp = explode("/", $b);
						if (is_numeric($b)) // its a page number
							renderInventory($b);
						else if ($tmp[0] == "bearbeiten")
							renderInventoryEdit($tmp[1]);
						else // it must be an id
							renderInventory($b, 1, 1);
					} else {
						renderInventory(1);
					}
					break;
				case "tags":
					if (isset($b)) {
						if ($b == "lÃ¶schen") { // fugly but works. no time to lose
							renderTagsDelete();
						} else {
							// check pages/tags here
							$tmp = explode("/", $b);
							$tags = $tmp[1];
							$pagenum = $tmp[0];
							if (is_numeric($pagenum) && $pagenum > 0)
								renderInventory($pagenum, $tags, 2);
							else
								renderInfobox("Falsche Seitennummer");
						}
					} else {
						renderTags();
					}
					break;
				case "people":
					renderPeople();
					break;
				case "help":
					renderHelp();
					break;
				default:
					render404();
		}
	}
	renderFooter();
}

function renderPeople() {
	echo "renderPeople";
}

function renderInfobox($text) {
	echo '<div class="infobox">'.$text.'</div><div class="infobox_clear"></div>';
}

function renderInventoryNew() {
	// process incoming data
	if(isset($_POST['name'], $_POST['condition'], $_POST['lent'], $_POST['description'])) {
		$name = $_POST['name'];
		$condition = $_POST['condition'];
		$lent = $_POST['lent'];
		$description = $_POST['description'];
		$owner = $_POST['owner'];
		$lending = $_POST['lending'];
		if ($name != "" && $condition != "" && $lent != "") {
			if (dbCreateInventoryItem($name, $condition, $lent, $description, $lending, $owner)) {
				$uid = dbGetInventoryLast();
				$uid = $uid[0] -> {'uid'};
				$id = numberToAlphabet((int)$uid);	
				$urls = convertArrayURL($_POST["url-titles"], $_POST["url-urls"]);			
				// inventory item created successfully
				// now to the tags
				//if (dbCreateTags)
				if (dbCreateTagLinks($uid, $_POST['tags'])) {
						if (dbCreateURLs($uid, $urls)) {
						// now to the uploaded image, if any...
						if($_FILES['imagefile'] && processImageUpload($_FILES['imagefile'], $id))
							renderInfobox("Bild und Gegenstand erfolgreich erstellt.");
						else
							renderInfobox("Erfolgreich erstellt.");
						renderInventory("", "", 3);
					} else {
						renderInfobox("Fehler beim Erstellen der URL-Links.");
					}
				} else {
					renderInfobox("Fehler beim Erstellen der Tag-Links.");
				}
			} else {
				renderInfobox("Fehler beim Einfügen der Daten!");
				renderInventoryForm($name, $id, $condition, $lent, $description, $lending, $owner);
			}
		} else {
			renderInfobox("Bitte alles benötigte angeben!");
			renderInventoryForm($name, $id, $condition, $lent, $description, $lending, $owner);
		}
	} else {
		// form to fill data in
		renderInventoryForm();
	}
}

function renderInventoryEdit() {
	global $b;
	
	// delete if its the intention
	if($_POST['submit'] == "Ja") {
		if(dbDeleteInventoryItem($_POST['id']) && imageDelete($_POST['id'])) {
			renderInfobox("Gel&ouml;scht!");
			return;
		} else {
			renderInfobox("Fehler beim L&ouml;schen!");
			return;
		}
	}
	
	// process incoming data
	if(isset($_POST['name'], $_POST['oldid'], $_POST['id'], $_POST['condition'], $_POST['lent'], $_POST['description'])) {
		$name = $_POST['name'];
		$id = strtoupper($_POST['oldid']);
		$condition = $_POST['condition'];
		$lent = $_POST['lent'];
		$description = $_POST['description'];
		$lending = $_POST['lending'];
		$owner = $_POST['owner'];
		$uid = alphabetToNumber($id);
		if ($_POST['id'] != $_POST['oldid'])
			$newid = true;
		$urls = convertArrayURL($_POST["url-titles"], $_POST["url-urls"]);
		
		if ($name != "" && $id != "" && $condition != "" && $lent != "" && $lending != "" && $owner != "") {
			if (dbEditInventoryItem($uid, $name, $condition, $lent, $description, $lending, $owner, $newid)) {
				// inventory item created successfully
				// now to the tags
				if ($newid) {
					$newuid = dbGetInventoryLast();
					$newuid = $newuid[0] -> {'uid'};
				} else {
					$newuid = $uid;
				}
				if (dbEditTagLinks($newuid, $_POST['tags'])) {
					if (dbEditURLs($newuid, $urls)) {
						// now to the uploaded image, if any...
						if($_FILES['imagefile'] && processImageUpload($_FILES['imagefile'], $id)) {
							renderInfobox("Bild und Gegenstand erfolgreich bearbeitet.");
						} else {
							renderInfobox("Erfolgreich bearbeitet.");
						}
						if($newid)
							renderInventory($_POST['id'], 1, 1);
						else
							renderInventory($id, 1, 1);
					} else {
						renderInfobox("Fehler beim Bearbeiten der URL-Links.");
					}
				} else {
					renderInfobox("Fehler beim Bearbeiten der Tag-Links.");
				}
			} else {
				renderInfobox("Fehler beim Bearbeiten der Daten!");
				renderInventoryForm($uid, $name, $condition, $lent, $description, $lending, $owner, true, true);
			}
		} else {
			renderInfobox("Bitte alles benötigte angeben!");
			renderInventoryForm($uid, $name, $condition, $lent, $description, $lending, $owner, true, true);
		}
	} else {
		// form with data filled in
		$tmp = explode("/", $b);
		$id = $tmp[1];
		$uid = alphabetToNumber($id);
		$items = dbGetInventoryByID($id);
		renderInventoryForm($uid, $items[0] -> {'name'}, $items[0] -> {'condition'}, $items[0] -> {'lent'}, $items[0] -> {'description'}, $items[0] -> {'lending'}, $items[0] -> {'owner'} , true, true);
	}
}

function renderInventoryForm($uid = "", $name = "", $condition = "", $lent = "", $description = "", $lending = "", $owner = "", $use_tag_ids = false, $edit = false) {
	global $basedir;
	global $imgdir;
	global $tmbdir;
	global $upload_size;

	if($uid == "") {
		$uid = dbGetInventoryLast();
		$uid = $uid[0] -> {'uid'} + 1;
	}
	if($lending == "")
		$lending = -2; // set to "nein"
		
	$id = numberToAlphabet($uid);
	
	if (file_exists($imgdir.'/'.$id.'.jpg') && file_exists($tmbdir.'/'.$id.'.jpg'))
		$img = true;
	echo '<div class="form">';
	echo '<form class="input" enctype="multipart/form-data" method="post" action="'.htmlentities($_SERVER["REQUEST_URI"]).'">';
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.($upload_size * 1024 * 1024).'" />';
    if ($edit)
		echo '<input type="hidden" name="oldid" value="'.$id.'">';
	echo '<fieldset>';
	if ($edit)
		echo '<legend>Gegenstand bearbeiten</legend>';
	else
		echo '<legend>Neuen Gegenstand inventarisieren</legend>';
	echo '<ol>';
	echo '<li><label for="name">Name:</label><input placeholder="..." name="name" type="text" value="'.$name.'"></li>';
	if ($edit) {
		/*echo '<li><label for="id">ID:</label><input placeholder="..." name="id" style="text-transform:uppercase;" maxlength="5" type="text" value="'.$id.'" readonly >';
		echo '<input type="checkbox" id="newid" value="true" name="newid" onchange="getNewID(this);">';
		echo '<label class="formcheckbox" for="newid">ID erneuern</label>';
		echo '</li>';*/
		
		//echo '<li class="multi"><a>neu</a><input type="text" placeholder="ID"></li>';
		echo '<li><label>ID:</label></li>';
		echo '<li class="multi" style="float: right;">';
		echo '<input type="checkbox" id="newid" value="true" name="newid" onchange="getNewID(this);">';
		echo '<label for="newid" class="img_refresh"></label>';
		echo '<input class="small" placeholder="..." name="id" style="text-transform:uppercase;" maxlength="5" type="text" value="'.$id.'" readonly >';
		echo '</li><li class="hint" id="newidhint">';
		echo '<p class="hint">Wird nur f&uuml;r neue Aufkleber ben&ouml;tigt</p>';
		echo '</li>';
	} else {
		echo '<li><label for="name">ID:</label><input placeholder="..." name="id" style="text-transform:uppercase;" maxlength="5" type="text" value="'.$id.'" readonly ></li>';
	}
	
	echo '<li><label for="lent">Bild:</label>';
	if ($img)
		echo '<a href="'.$basedir.'/'.$imgdir.'/'.$id.'.jpg" target="_blank"><img class="preview" alt="'.$id.'" src="'.$basedir.'/'.$tmbdir.'/'.$id.'.jpg"></a>';
	else
		echo '<img class="preview" alt="Datei nicht gefunden" src="'.$basedir.'/img/file_not_found.jpg">';
	
	echo '<div class="fileinputs">
			<input name="imagefile" accept="image/jpg" type="file" class="file" />
			</div>';
	if ($img)
		echo '<p class="hint">Vorhandenes Bild wird bei Upload ersetzt</p>';
	echo '</li><li><label for="condition">Zustand:</label><div class="select"><select name="condition">';
	echo '<option value="4">unbekannt</option>';
	echo '<option value="1">funktioniert</option>';
	echo '<option value="2">funktioniert (hackbar)</option>';
	echo '<option value="0">defekt</option>';
	echo '<option value="3">Ersatzteillager</option>';
	echo '</select></div></li>';
	echo '<li><label for="owner">Besitzer:</label><div class="select"><select name="owner">';
	
	var_dump($owner);
	echo '<option';
	if ($owner == -1 || $owner == "") echo ' selected ';
	echo ' value="-1">flipdot</option>';
	
	echo '<option';
	if ($owner == 0 && $owner != "") echo ' selected ';
	echo ' value="0">niemand</option>';
	
	echo '<option';
	if ($owner == 1) echo ' selected ';
	echo ' value="1">Test-User</option>';
	
	echo '</select></div></li>';
	
	echo '<input type="hidden" id="lending" name="lending" value="'.$lending.'">';
	echo '<li><label for="lendingdisplay">Ausleihe:</label><div class="select"><select name="lendingdisplay" onchange="checkLending(this);">';
	
	echo '<option';
	if ($lending == -2) echo ' selected ';
	echo ' value="-2">nein</option>';
	
	echo '<option';
	if ($lending == 0) echo ' selected ';
	echo ' value="0">einfach nehmen</option>';
	
	echo '<option';
	if ($lending == -1) echo ' selected ';
	echo ' value="-1">nur Besitzer</option>';
	
	echo '<option';
	if ($lending > 0) echo ' selected ';
	echo ' value="n"># Tage</option>';
	echo '</select></div></li>';
		
	echo '<div id="numberinput" class="fileinputs" ';
	if ($lending > 0) echo ' style="display:block"; ';
	echo '><input type="text" id="lendingdays" class="lendingdays" name="lendingdays" onchange="checkNumDays(this);" value="'.$lending.'"><p id="lendingdaysdisplay" class="lendingdays">Tage</p>';
	echo '</div>';
	
	echo '<li><label for="lent">Verliehen:</label><div class="select"><select name="lent">';
	echo '<option value="0">nein</option>';
	echo '<option value="1">an Test-User</option>';
	echo '</select></div></li>';
	
	echo '<li><label for="lent">Links:</label></li>';
	echo '<div id="urlwrap">';
	
	$urls = dbGetURLs($uid);
	$l = count($urls);
	for ($i = 0; $i < $l; $i++) {
		$title = $urls[$i] -> {'title'};
		$url = $urls[$i] -> {'url'};
		echo '<li class="multi" id="url'.$i.'"><a onclick="removeURL('.$i.')" class="img_minus"></a><input type="text" placeholder="Titel #'.($i+1).'" id="url'.$i.'title" name="url-titles['.$i.']" value="'.$title.'"><input type="text" placeholder="URL #'.($i+1).'" id="url'.$i.'url" name="url-urls['.$i.']" value="'.$url.'"></li>';
	}

	echo '</div>';
	echo '<li class="multi"><a onclick="addURL()" class="plus img_plus"></a></li>';
	
	echo '<li><fieldset class="multiline"><legend>Beschreibung:</legend><textarea rows="7" cols="30" placeholder="optional" name="description">'.$description.'</textarea></li>';
	echo '<li><fieldset class="multiline"><legend>Tags:</legend>';
	// tag list
	$tags = dbGetTags();
	// iterate over tags
	$itemtags = dbGetInventoryItemTags($uid);
	
	if (!$tags) {
		echo '<p>Keine Tags in der Datenbank vorhanden.</p>';
	} else {
		echo '<div class="checklist">';
		
		for ($i = 0; $i < count($tags); $i++) {
			$tag = $tags[$i] -> {'name'};
			$id = (int)$tags[$i] -> {'uid'};
			
			echo '<input type="checkbox" ';
			if (!is_bool($itemtags) && in_array($tag, $itemtags))
				echo ' checked ';
			if ($use_tag_ids)
				echo ' id="tag'.$i.'" value="'.$id.'" name="tags[t'.$i.']">';
			else
				echo ' id="tag'.$i.'" value="'.encodeLink($tag).'" name="tags[t'.$i.']">';
			echo '<label for="tag'.$i.'">'.$tag .'</label>';
		}
		
		echo '</div>';
	}
	echo '</li><li><div id="throbber"><img id="throbberimg"></div>';
	
	if ($edit) {
		echo '<p class="delete" onclick="showDeleteButton();">L&ouml;schen &raquo;</p>';
		echo '<p style="float:right"><input type="submit" id="submitButton" value="Speichern &raquo;" name="submit" onclick="submitForm();"></p></li>';
		echo '<li><div id="deletebox">Bist du dir sicher?<input type="submit" id="deleteButton" value="Ja" name="submit"></div></li>';
	} else {
		echo '<input type="submit" id="submitButton" value="Erstellen &raquo;" name="submit" onclick="submitForm();"></li>';
	}
	echo '</ol>	</fieldset></form></div>';
}

function renderTagsNew() {
	if ($_POST['tag']) {
		if (dbCreateTag($_POST['tag'])) {
			renderInfobox('Tag wurde erfolgreich erstellt.');
			renderTags();
		} else {
			renderInfobox('Fehler beim Erstellen des Tags.');
		}
	} else {
		echo '<div class="textwrap form">';
		echo '<form class="input" method="post" action="'.htmlentities($_SERVER["REQUEST_URI"]).'">';
		echo '<fieldset>
				<legend>Neuen Tag erstellen</legend>
					<ol>';
		echo '<li><label for="tag">Tagname:</label><input placeholder="..." name="tag" type="text"></li>';
		echo '<li><input type="submit" value="Erstellen &raquo;" name="submit"></li>';
		echo '</ol>	</fieldset></form></div>';
	}
}

function renderTagsDelete() {
	// delete if its the intention
	if($_POST['submit'] == "Ja") {
		if(dbDeleteTag($_POST['tag'])) {
			renderInfobox("Gel&ouml;scht!");
			return;
		} else {
			renderInfobox("Fehler beim L&ouml;schen!");
			return;
		}
	}
	
	$tags = dbGetTags();
	
	echo '<div class="textwrap">';
	echo '<h2>Zu l&ouml;schenden Tag ausw&auml;hlen</h2>';
	
	if (!$tags) {
		echo '<p>Keine Tags in der Datenbank vorhanden.</p>';
	} else {
		echo '<div class="checklist">';
		echo '<form class="tags" method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">';
		
		for ($i = 0; $i < count($tags); $i++) {
			$tag = $tags[$i] -> {'name'};
			$id = (int)$tags[$i] -> {'uid'};
			
			echo '<input type="radio" id="tag'.$i.'" value="'.encodeLink($tag).'" name="tag">';
			echo '<label for="tag'.$i.'">'.$tag.'</label>';
		}
		
		echo '<p class="delete" style="float:right!important;" onclick="showDeleteButton();">L&ouml;schen &raquo;</p>';
		echo '<div id="deletebox" style="float:right!important;">Bist du dir sicher?<input type="submit" id="deleteButton" value="Ja" name="submit"></div>';
		echo '</div>';
	}
	echo '</div>';
}

function renderPeopleNew() {
	echo "renderPeopleNew";
}

function renderPeopleProfile() {
	echo "renderPeopleProfile";
}


function renderHeader() {
	global $basedir;
	echo <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>flipdot: archiv</title>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
		<link rel="icon" type="image/png" href="/favicon.png" />
		<link rel="stylesheet" type="text/css" href="$basedir/common.css">
		<script type="text/javascript" src="$basedir/common.js"></script>
	</head>
	<body onload="initFileUploads();">
END;
}

function renderFooter() {
	echo <<<END
		</div>
	</body>
</html>
END;
}

function renderMenu() {
	global $basedir;
	global $categories;
	global $subcategories;
	global $currentcategory;
	global $currentsubcategory;
	global $a;
	global $username;
	
	//<a href="http://flipdot.org/"><div id="menulogo"></div></a>
	
	echo '<div id="menubar">';
	echo ' <div id="logout">'.$username.'<a href="'.$basedir.'/logout"><div id="logout_img"></div></a></div>';
	echo '  <ul>';

	// main categories
	$i = 0;
	foreach ($categories as &$category) {
		if ($i == $currentcategory) {
			echo '<a href="'.$basedir.'/'.utf8_decode(encodeLink($category)).'"><li class="selected">'.utf8_decode($category).' </li></a>';
			// sub categories
			if (!is_null($subcategories[$currentcategory])) {
				echo '<ul>';
				$j = 0;
				foreach ($subcategories[$currentcategory] as &$subcategory) {
					if ($currentsubcategory == $j) {
						echo '<a href="'.$basedir.'/'.utf8_decode(encodeLink($category)).'/'.utf8_decode(encodeLink($subcategory)).'"><li class="selected">'.utf8_decode($subcategory).' </li></a>';
					} else {
						echo '<a href="'.$basedir.'/'.utf8_decode(encodeLink($category)).'/'.utf8_decode(encodeLink($subcategory)).'"><li>'.utf8_decode($subcategory).' </li></a>';
					}
					$j++;
				}
				echo '</ul>';
			}
		} else {
			echo '<a href="'.$basedir.'/'.encodeLink($category).'"><li>'.$category.' </li></a>';
		}
		$i++;
	}

	echo <<<END
			</ul>
		</div>
		<div id="content">
END;
}

function renderInventory($page = 1, $tags = "", $mode = 0) {
	global $basedir;
	global $imgdir;
	global $tmbdir;
	global $qrurl;
	global $categories;
	global $currentcategory;
	
	switch ($mode) {
		case 0:
			// its a page
			$items = dbGetInventory($page, $tags);
			break;
		case 1:
			// its an id
			$items = dbGetInventoryByID($page);
			break;
		case 2:
			// its one or more categories
			$items = dbGetInventory($page, $tags);
			break;
		case 3:
			// its the last added one
			$items = dbGetInventoryLast();
			break;
		default:
			echo "<p>Falscher Modus beim Auslesen des Inventars.</p>";
			return;
	}
	
	if (!$items) {
		echo "<p>Es konnten keine Gegenst&auml;nde zu Ihrer Suchanfrage gefunden werden.</p>";
		return;
	}
	
	for ($i = 0; $i < count($items); $i++) {
		$name = $items[$i] -> {'name'};
		$condition = (int)$items[$i] -> {'condition'};
		$lent = (int)$items[$i] -> {'lent'};
		$owner = $items[$i] -> {'owner'};
		$lending = $items[$i] -> {'lending'};
		$description = $items[$i] -> {'description'};
		$uid = $items[$i] -> {'uid'};
		$id = numberToAlphabet($uid);
		
		switch ($condition) {
			case 0:
				$conditiontext = "<span class=\"bad\">defekt</span>"; break;
			case 1:
				$conditiontext = "funktioniert"; break;
			case 2:
				$conditiontext = "funktioniert<br><i>darf gehackt werden</i>"; break;
			case 3:
				$conditiontext = "Ersatzteillager"; break;
			case 4:
				$conditiontext = "<span class=\"bad\">unbekannt</span>"; break;
			default:
				$conditiontext = "eigenartig";
		}
		
		switch ($lent) {
			case 0:
				$lenttext = "nein"; break;
			default:
				$lenttext = 'an <a href="#">User#'.$lent.'</a>';
		}
		
		switch ($owner) {
			case -1:
				$ownertext = "flipdot"; break;
			case 0:
				$ownertext = "niemand"; break;
			default:
				$ownertext = '<a href="#">User#'.$owner.'</a>';
		}
		
		switch ($lending) {
			case -2:
				$lendingtext = "nein"; break;
			case -1:
				$lendingtext = "nur Besitzer"; break;
			case 0:
				$lendingtext = "einfach nehmen"; break;
			default:
				if ($lending > 30) {
					$lendingtext = "max. ".round($lending/30)." Monate";
				} else {
					$lendingtext = "max. $lending Tage";
				}
		}
		
		echo '<div class="item">';
		echo '	<a href="'.$basedir.'/'.encodeLink($categories[0]).'/'.$id.'"><p>'.$name.'</p></a>';
		if (file_exists($tmbdir.'/'.$id.'.jpg'))
			echo '<p class="image"><a href="'.$basedir.'/'.$imgdir.'/'.$id.'.jpg" target="_blank"><img alt="'.$id.'" src="'.$basedir.'/'.$tmbdir.'/'.$id.'.jpg"></a></p>';
		else
			echo '<p class="image"><img alt="Datei nicht gefunden" src="'.$basedir.'/img/file_not_found.jpg"></p>';
			
		echo '	<table>
				<tbody>';
		//echo '<tr><td>ID</td><td>'.$id.'<a href="'.$qrurl.'/'.$id.'" class="qr" target="_blank"></a></td></tr>';
		echo '<tr><td>ID</td><td>'.$id.'</td></tr>';
		echo '<tr><td>Zustand</td><td>'.$conditiontext.'</td></tr>';
		echo '<tr><td>Besitzer</td><td>'.$ownertext.'</td></tr>';
		echo '<tr><td>Ausleihe</td><td>'.$lendingtext.'</td></tr>';
		if ($lent != 0)
			echo '<tr><td>Verliehen</td><td>'.$lenttext.'</td></tr>';
		// iterate over Links
		$urls = dbGetURLs($uid);
		if ($urls) {
			echo '<tr><td>URLs</td><td>';
			for ($u = 0; $u < count($urls); $u++) {
				$title = $urls[$u] -> {'title'};
				$url = $urls[$u] -> {'url'};
				//echo '<li class="multi" id="url'.$i.'"><a onclick="removeURL('.$i.')" >-</a><input type="text" placeholder="Titel" id="url'.$i.'title" name="url-titles['.$i.']" value="'.$title.'"><input type="text" placeholder="URL" id="url'.$i.'url" name="url-urls['.$i.']" value="'.$url.'"></li>';
				echo '<a href="'.$url.'" target="_blank" class="url">'.$title.'</a>';
			}
			echo '</td></tr>';
		}
		echo '	</tbody>
			</table>';
		if ($description!="")
			echo '<p class="description">'.$description.'</p>';
		// iterate over tags
		$itemtags = dbGetInventoryItemTags($uid);
		if ($itemtags) {
			echo '<fieldset>
					<legend>Tags</legend>';
			for ($t = 0; $t <= count($itemtags); $t++) {
				echo '<a href="'.$basedir.'/'.encodeLink($categories[1]).'/1/'.encodeLink($itemtags[$t]).'">'.$itemtags[$t].'</a>';
			}
			echo '</fieldset>';
		}
		$qrpage = ceil(alphabetToNumber($id)/100);
		echo '<a href="'.$qrurl.'/'.$qrpage.'" class="qr" target="_blank">QR-Code</a>';
		echo '<a href="'.$basedir.'/'.encodeLink($categories[0]).'/bearbeiten/'.$id.'" class="edit">Bearbeiten</a>';
		echo '</div>';
	}
	
	switch ($mode) {
		case 0:
			// its a page
			renderInventoryPages($page);
			break;
		case 2:
			renderInventoryPages($page, $tags);
			break;
	}
}

function renderInventoryPages($page = 1, $tags = "") {
	global $basedir;
	global $categories;
	global $currentcategory;
	echo '<div class="pages">Seiten ';
	
	if ($tags == "") {
		$pages = dbGetInventoryPages();
		$itemcount = dbGetInventoryItems();
	} else {
		$pages = dbGetInventoryPages($tags);
		$itemcount = dbGetInventoryItems($tags);
	}
	$tags = utf8_decode(str_replace(" ", "+", $tags)); // the url eats the pluses
	
	for ($i = 1; $i <= $pages ; $i++) {
		if ($i == $page) {
			echo '<span>'.$i.'</span>';
		} else {
			if ($tags == "")
				echo '<a href="'.$basedir.'/'.encodeLink($categories[$currentcategory]).'/'.$i.'">'.$i.'</a>';
			else
				echo '<a href="'.$basedir.'/'.encodeLink($categories[$currentcategory]).'/'.$i.'/'.$tags.'">'.$i.'</a>';
		}
	}
	echo '<p class="itemcount">'.$itemcount.' Treffer</p>';
	echo '</div>';
}

function renderTags() {
	$tags = dbGetTags();
	
	echo '<div class="textwrap">';
	echo '<h2>Tags ausw&auml;hlen</h2>';
	
	if (!$tags) {
		echo '<p>Keine Tags in der Datenbank vorhanden.</p>';
	} else {
		echo '<div class="checklist">';
		echo '<form class="tags" method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">';
		
		for ($i = 0; $i < count($tags); $i++) {
			$tag = $tags[$i] -> {'name'};
			$id = (int)$tags[$i] -> {'uid'};
			
			echo '<input type="checkbox" id="tag'.$i.'" value="'.encodeLink($tag).'" name="tags-select[t'.$i.']">';
			echo '<label for="tag'.$i.'">'.$tag.'</label>';
		}
		
		echo '<input type="submit" value="Suchen &raquo;">';
		echo '</div>';
	}
	echo '</div>';
}

function renderHelp() {
	readfile('html/help.html');
}

function render404() {
	readfile('html/404.html');
}

function renderDebug() {
	global $debug;
	global $a;
	global $b;
	global $currentcategory;
	global $currentsubcategory;
	global $page;
	if ($debug) {
		echo "<div style=\"color:grey;clear:both;padding:48px\">";
		echo "a: $a <br/>";
		echo "b: $b <br/>";
		echo "currentcategory: $currentcategory <br/>";
		echo "currentsubcategory: $currentsubcategory <br/>";
		echo "page: $page <br/>";
		echo "auth: ".$_SESSION['auth']." <br/>";
		echo "</div>";
	}
}

function renderRedirect($url) {
		echo '<meta http-equiv="refresh" content="0; url='.$url.'">';
}


?>
