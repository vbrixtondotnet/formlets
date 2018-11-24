<?php

function outputFile($filename){
	$file=str_replace('..','',$filename);
	$filename = $_FILES["file"]["name"];
	$parts=explode('.',$file);
	$ex=$parts[count($parts)-1];

	switch($ex){
	    case "gif": $ctype="image/gif"; break;
	    case "png": $ctype="image/png"; break;
	    case "jpeg":
	    case "jpg": $ctype="image/jpeg"; break;
	    case "zip": $ctype="application/zip"; break;
	    case "xls": $ctype="application/excel"; break;
	    case "txt": $ctype="text/text"; break;
	    case "rtf": $ctype="text/richtext"; break;
	    case "pdf": $ctype="application/pdf"; break;
	    case "wmv": $ctype="video/msvideo"; break;
	    case "mp3": $ctype="audio/mpeg3"; break;
	    case "mp4": $ctype="video/mp4"; break;
	    case "mov": $ctype="video/quicktime"; break;
	    case "doc": $ctype="application/msword"; break;
	    case "pdf": $ctype="application/pdf"; break;
	    default:
	    	$ctype="download";
	}

	if($ctype && $ctype!="download"){
		header('Content-type: ' . $ctype);
		echo file_get_contents($GLOBALS['conf']['filepath_fileupload'].'/'.$file);
	} elseif($ctype && $ctype=="download"){
		header('Content-type: application/octet-stream');
		header("Content-Disposition: attachment; filename=".$filename);
	} else {
	  header("HTTP/1.0 404 Not Found");
	}
	exit;
}

outputFile($_POST['file_result']);