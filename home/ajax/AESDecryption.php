<?php
try{
if (isset($_POST['value1']))
        {
	$value1 = $_POST['value1'];
        }
//$value1='f13e54994e89f68e32521d4025589f91';

            $encryptionMethod = "AES-256-ECB";
            $secretHash = "k2hLr4X0ozNyZByj5DT66edtCEee1x+6";

            $decodehexToBase64 = base64_encode(pack('H*',$value1));

            $decryptedMessage = openssl_decrypt($decodehexToBase64, $encryptionMethod, $secretHash);

      echo json_encode(array("error" => "0", "decrypt_message" => $decryptedMessage)); 
    

}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}
