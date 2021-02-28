<?php

function initVariables() {
	global $a;
	global $b;
	global $categories;
	global $subcategories;
	global $currentcategory;
	global $currentsubcategory;
	
	global $username;
	$username = "Cthulu";
		
	// main category
	if ($_GET['a']) {
		$a = $_GET['a'];
	} else {
		$a = $categories[0];
		$currentcategory = 0;
	}

	// sub category
	if ($_GET['b'])
		$b = $_GET['b'];
	$currentsubcategory = -1;
	
	$i = 0;
	foreach ($categories as &$category) {
		if (encodeLink($category) == $a) {
			$currentcategory = $i;
			$j = 0;
			if ($_GET['b']) {
				foreach ($subcategories[$i] as &$subcategory) {
					if (encodeLink($subcategory) == $b) {
						$currentsubcategory = $j;
					}
					$j++;
				}
			}
		}
		$i++;
	}
}


?>
