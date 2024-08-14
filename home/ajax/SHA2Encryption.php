<?php

try{
    if (isset($_POST['value1']))
	$value1 = $_POST['value1'];
    
      $data= hash("sha256",$value1);
    
    echo json_encode(array("error" => "0", "encrypt_message" => $data)); 
    

}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}