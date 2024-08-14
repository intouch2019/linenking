<?php



if (isset($_POST['at_value']))
	$at_value = $_POST['at_value'];
if (isset($_POST['pattern']))
	$pattern = $_POST['pattern'];
if (isset($_POST['att_name']))
	$att_name = $_POST['att_name'];



if(preg_match($pattern, $at_value)) {
 
    echo json_encode(array("error" => "0", "message" => "")); 
}  else {
    
     echo json_encode(array("error" => "1", "message" => "please entere valid ".$att_name)); 

}