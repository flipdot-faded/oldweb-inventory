<?php

function redirectTags() {
	global $basedir;
	global $categories;
	
	$tags = $_POST['tags-select'];
	$url = $basedir.'/'.encodeLink($categories[1]).'/1/';
	$i = 0;
	
	foreach ($tags as &$tag) {
		if ($i < count($tags) -1 )
			$url .= $tag."+";
		else
			$url .= $tag;
		$i++;
	}
	
	renderRedirect($url);
}

function redirect() {
	// redirect tags
	if ($_POST['tags-select']) {
		redirectTags();
		return true;
	}
	return false;
}

function specialPage() {
	global $a;
	if ($a == 'getnewid') {
		renderNewID();
		return true;
	}
	if ($a == 'logout') {
		session_destroy(); // clearing authentication
		renderLogout();
		return true;
	}
	return false;
}

?>
