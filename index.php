<?php

// include common files
require "inc/auth.php";
require "inc/categories.php";
require "inc/config.php";
require "inc/convert.php";
require "inc/database.php";
require "inc/image.php";
require "inc/init.php";
require "inc/redirect.php";
require "inc/render.php";

// get the variables used for the main category and other parameters
initVariables();

// if authorized
if (auth()) {
	// render the appropriate category
	render(); // defined in 'inc/render.php'
	//renderDebug();
} else {
	renderLogin();
	//renderDebug();
}
	
?>

