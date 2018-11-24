<?php
// BaseConfig
// Copyright 2002-2007 Cbel-Oxopia
//-> Purpose: This code reads the request URI and devides it into urlparts, it also sets the level var

error_reporting(E_ALL ^ E_NOTICE);

	if(($_GET["bench"]=="y")||($_GET["benchskin"]=="y")){

	function benchit()
	{
	    $mtime = explode( ' ', microtime() );
	    $msec = ( double ) $mtime[0];
	    $sec = ( double ) $mtime[1];
	    return( $sec + $msec );
	}
	$bench_start = benchit();
	}

$uripart	=explode("?",$_SERVER['REQUEST_URI']);
$urlpart	=explode("/",$uripart[0]);
//-> Loop the urlparts
for ($u=1;$u<count($urlpart);$u++){
	if($u>1){$level.="../";}
	$hurl.="/".$urlpart[$u];
	if (($urlpart[$u]=="")||(((substr($urlpart[$u],-5,1)==".")||(substr($urlpart[$u],-4,1)==".")||(substr($urlpart[$u],-3,1)=="."))&&($u+1==count($urlpart)))) {
			// .mpeg / .jpeg / .zip / .doc / .js / .gz only when at the end of the url
			//-> Inside loop : check if at the end of url
			//-> Include "../index_central.inc.php"
			include("../index_central.inc.php");
			exit;
	} else if(!isset($urlpart[($u+1)])){
		if($_POST) {
			include("../index_central.inc.php");
			exit;
		}
			//-> we don't have a slash behind our dir .. lets put one
			//-> Header redirect to add slash at end of URL
			if($_SERVER['QUERY_STRING']){$parttwo="?".$_SERVER['QUERY_STRING']; }
			header("HTTP/1.1 301 Moved Permanently");
			Header( "Location:  //".$_SERVER["HTTP_HOST"].$hurl."/".$parttwo);
			exit;
	}
}
?>
