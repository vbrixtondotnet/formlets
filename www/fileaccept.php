<?php
include(realpath(__DIR__ . '/../libs/platform.inc.php'));
$pl = new Platform;
$data = array();

if(isset($_FILES['files'])) {

    // $normalizeChars = array(
    //     'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
    //     'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
    //     'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
    //     'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
    //     'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
    //     'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
    //     'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
    //     'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
    // );

    $error = false;
    $total = count($_FILES['files']['name']);

    $files = array();
    $fileNames = array();
    // Loop through each file
    for($i=0; $i<$total; $i++) {
        $file = $_FILES['files'];
        $fname = $_POST['file_names'][$i];
        //$fname = $file["name"][$i];
        //$fname = strtr($fname, $normalizeChars);

    	$parts=explode('.',$fname);
    	$id=$pl->insertId(32);
    	$filename=$id.'.'.$parts[count($parts)-1];

        $files[] = $filename;
        $fileNames[] = $fname;

    	$upload = move_uploaded_file($file["tmp_name"][$i], $GLOBALS['conf']['filepath_fileupload'].'/'.$filename);
        if(!$upload) {
            $error = true;
        }

        $uploaded = $error ? 'No':'Yes';
    }

    $data = ($error) ? array('error' => 'There was an error uploading your files') : array('file' => implode(';;',$files), 'filename'=>implode(';;',$fileNames));
} else {
    $data = array('error' => 'No file was submitted');
}

echo json_encode($data);
