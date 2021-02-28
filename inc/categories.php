<?php

$categories = array(
    "Inventar",
    "Tags",
    "Personen",
    "Hilfe"
  );

$categories_callbacks = array(
    "inventory",
    "tags",
    "people",
    "help"
  );

$subcategories = array(
	// Inventar
    array( "Neu"),
	// Tags
    array("Neu",
           "Löschen"),
	// Personen
    array( "Hinzufügen",
			"Mein Profil"),
	// Hilfe
    NULL
  );

$subcategories_callbacks = array(
	// Inventar
    array( "inventory-new"),
	// Tags
    array(	"tags-new",
			"tags-delete"),
	// Personen
    array( "people-new",
			"people-profile"),
	// Hilfe
    NULL
  );

?>
