<?php
try{
    if (isset($_POST['value1']))
	$value1 = $_POST['value1'];
//    phpinfo();
    error_reporting(E_ERROR | E_PARSE);
//    $value1="ASDFGHJKL ZXCVBNM";
  $encryptionMethod = "AES-256-ECB";
  $secretHash = "k2hLr4X0ozNyZByj5DT66edtCEee1x+6";
  //To encrypt
//  $ivlen = openssl_cipher_iv_length($encryptionMethod);
//   echo $ivlen;
  
    $iv = openssl_random_pseudo_bytes(16);
            $encryptedMessage = openssl_encrypt($value1, $encryptionMethod, $secretHash);

            //convert base64 to Hex
            $binary = base64_decode($encryptedMessage);
            $hex = bin2hex($binary);    
    echo json_encode(array("error" => "0", "encrypt_message" => $hex)); 
    

}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}
