<?php
$errors=array();

$result=array();

        $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
	$fileTypes  = str_replace(';','|',$fileTypes);
	$typesArray = split('\|',$fileTypes);
	$fileParts  = pathinfo($_FILES['Filedata']['name']);

    if ( (in_array($fileParts['extension'],$typesArray))
        && ($_FILES['Filedata']['size'] < 512400) ) {
        $image_name=($_FILES['Filedata']['name']);

        if ($_FILES['Filedata']['error'] != "0") {
            if ($_FILES['Filedata']['error']==4)
                $result = array('error' => 'Please enter an image file to upload');
            else
                $result = array('error' => "Image error : ".$_FILES["Filedata"]["error"]);
        } else
        if (count($errors) == 0) {
            $extn = $fileParts['extension'];
            $image="123.456.$extn";
            move_uploaded_file($_FILES['Filedata']['tmp_name'], "$image");
            $result = array('success'=>true);
            print "1";
        } else {
            $result = array('error' => 'General Failure');
            print "0";
        }
    }
    else
        $result = array('error' => 'Please enter an image file(gif/jpeg/png) and enter within 500kb size');
?>