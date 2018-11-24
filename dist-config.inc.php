<?php

//-> This is the platform config

//-> The Platform database connect
if($_SERVER['SERVER_NAME']=='formlets.local'){
  $conf["env"]="local";
    $conf['db']["user"]		="root";
    $conf['db']["pass"]		="";
    $conf['db']["host"]		="localhost";
    $conf['db']["name"]		="formlets";
    $conf['db']["port"]		=null;
    $conf['db']["socket"]	=null;
    $conf['filepath_support']           ='../appengine/localfilestorage/support';
    $conf['filepath_img']               ='../appengine/localfilestorage/img';
    $conf['filepath_fileupload']        ='../appengine/localfilestorage/fileupload';
    $conf['gs_bucket_name']             ='';
    $conf['protocol'] = 'http';
    $conf['force_www'] = false;
    $conf['timezone']="Asia/Manila";
    $conf['forms_url']="";
} else {
    $conf["env"]="production";
    $conf['db']["user"]		="root";
    $conf['db']["pass"]		="";
    $conf['db']["host"]		=null;
    $conf['db']["name"]		="formlets";
    $conf['db']["port"]		=null;
    $conf['db']["socket"]	='/cloudsql/cintrone-986:master';
    $conf['filepath_support']           ='';
    $conf['filepath_img']               ='';
    $conf['filepath_fileupload']        ='';
    $conf['gs_bucket_name']             ='';
    $conf['protocol'] = 'http';
    $conf['force_www'] = false;
    $conf['timezone']="Europe/Brussels";
    $conf['forms_url']="https://forms-formlets-dot-formlets-1260.appspot.com";
}


$conf["sendgrid"]["user"]   ='fscmail';
$conf["sendgrid"]["key"]    ='';
$conf['stripe_secret_key'] = '';
$conf['stripe_publishable_key'] = '';
$conf['google_captcha_site_key'] = '';
$conf['google_captcha_secret_key'] = '';
$conf['enable_formupdatelog'] = true;

//php ses api
$conf['ses']['key'] = '';
$conf['ses']['secret'] = '';
$conf['ses']['region'] = 'us-west-2';

?>
