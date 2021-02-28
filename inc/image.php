<?php

function processImageUpload($image, $id) { // tmp path to the uploaded image
	global $upload_size;
	$tmpname = $image['tmp_name'];
	$size = $image['size'];
	$type = $image['type'];
	$error = $image['error'];
	
	// check upload errors
	$message = "";
	switch ($error) {
		case UPLOAD_ERR_INI_SIZE:
			$message = "Die Datei ist größer als maximal erlaubt (".$upload_size."MB)";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message = "Die Datei ist größer als maximal erlaubt (".$upload_size."MB)";
			break;
		case UPLOAD_ERR_PARTIAL:
			$message = "Datei wurde nur teilweise hochgeladen.";
			break;
		case UPLOAD_ERR_NO_FILE:
			// no file uploaded, so nothing has to be handled
			return true;
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message = "Kein temporäres Verzeichnis.";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message = "Konnte Datei nicht schreiben.";
			break;
		case UPLOAD_ERR_EXTENSION:
			$message = "Dateiupload wurde von Erweiterung abgebrochen.";
			break; 
	}
	if ($message != "") {
		echo "Fehler beim Upload: $message<br>";
		return false;
	}
	
	// check image type
	if ($type != 'image/jpeg')
		return false;
		
	/*
	$size = getimagesize($tmpname);
	$w = $size[0];
	$h = $size[1];
	echo "IMAGE: $image<br/>";
	echo "TMPNAME: $tmpname<br/>";
	echo "SIZE: $size<br/>";
	echo "MIME: $type<br/>";
	echo "ERROR: $error<br/>";
	echo "DIMENSIONS: $w x $h<br/>";
	var_dump($image);
	*/
	
	if (imageResize($tmpname, $id) && imageThumbnail($id))
		return true;
	return false;
}

function imageThumbnail ($id) {
	global $imgdir;
	global $tmbdir;

	$input =  getcwd()."/$imgdir/$id.jpg";
	$output =  getcwd()."/$tmbdir/$id.jpg";
	$cmd = "convert -thumbnail 240x180 $input $output";
	$exec = exec($cmd, $out, $ret);
	if ($ret == 0)
		return true;
	else
		return false;
}

function imageResize ($path, $id) {
	global $imgdir;
	
	$output = getcwd()."/$imgdir/$id.jpg";
	
	$cmd = "convert -thumbnail 1024x786 $path $output";
	$exec = exec($cmd, $out, $ret);
	if ($ret == 0)
		return true;
	else
		return false;
}

function imageDelete ($id) {
	global $imgdir;
	global $tmbdir;
	
	$img = getcwd()."/$imgdir/$id.jpg";
	$tmb = getcwd()."/$tmbdir/$id.jpg";
	
	@unlink($img);
	@unlink($tmb);
	
	return true;
}

function imageMove ($oldid, $newid) {
	global $imgdir;
	global $tmbdir;
	
	$imgpath = getcwd()."/$imgdir";
	$tmbpath = getcwd()."/$tmbdir";
	
	@rename("$imgpath/$oldid.jpg", "$imgpath/$newid.jpg");
	@rename("$tmbpath/$oldid.jpg", "$tmbpath/$newid.jpg");
	
	return true;
}

?>
