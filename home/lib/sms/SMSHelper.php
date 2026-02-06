<?php
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";


class SMSHelper {
    
        public function sendSMS($phoneno,$message,$incomingid=false){
            $db = new DBConn();
//            $senderID = "COTKNG";
            //step 1 insert into sms table
            $set_columns = "";
            if($incomingid != false){$set_columns .= " ,incomingid=$incomingid";}
            $query="insert into it_sms set phoneno=$phoneno,message='$message' $set_columns";            
            $stsms_id=$db->execInsert($query);
            // below is the sms delivery url
//            $durl2 = "http://192.168.0.26/wassup/home/getSMS/?id=".$stsms_id."&status=%d&delivery_date=%t";
//            $en_durl2 = urlencode($durl2);
            //params
            $fields2 = array(                
                'apikey' => 'XmgI1w1sGA0Jghsx',
                'senderid' => 'CTNKNG',
                'number' => $phoneno,
                'message' => $message,
            );
            
            
        $fields_string = http_build_query($fields2);
//            $url = "http://insta.nspiresoft.com/http-api.php?username=cottonking&password=cot?321&senderid=COTKNG&route=1&number=$phoneno&message=$message";
            $url = "http://alert.nspiresoft.com/V2/http-api.php";
//            $url_db=$db->safe(trim($url.$fields_string));    
            //update the url created in db
//            $db->execUpdate("update it_sms set url=$url_db where id = $stsms_id");
                        
            //open connection
            $ch = curl_init();
//            $options = array (CURLOPT_RETURNTRANSFER => true);
            //set the url, number of POST vars, POST data
//            curl_setopt($ch,CURLOPT_URL, $url);
//            curl_setopt_array ( $ch, $options );
//            curl_setopt($ch,CURLOPT_POST, count($fields2));
//            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
//            print_r($fields2);
            //execute post
//            echo "Final URL: " . $url . $fields_string . "\n";

            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "Accept: application/json",
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36"
                ]
            ]);

            
            $resp = curl_exec($ch);
//            print_r($resp);
            //close connection
            curl_close($ch);
            //update the resp
            $db->execUpdate("update it_sms set sent_sms_response = '$resp',updatetime=now() where id = $stsms_id");
            $logger = new clsLogger();
            $logger->logInfo("sendSMS:$message:$resp");
            return $resp;
        }
}

?>
