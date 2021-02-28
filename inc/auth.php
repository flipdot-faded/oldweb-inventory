<?php

function auth() {
	session_start();
	if ($_POST['user'] && $_POST['pass']) {
		// check user / pass (md5)
		if (true) {
			// if correct set session and return true
			$_SESSION['auth'] = true;
			return true;
		} else {
			return false;
		}
	// check if session is authed
	} else if ($_SESSION['auth']) {
		return true;
	}
	
	return false;
}

?>
