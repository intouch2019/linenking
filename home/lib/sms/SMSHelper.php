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
                'username' => 'cottonking',
                'password' => 'cot?321',
                'senderid' => 'LINNKG',
                'route' => '1',                
                'number' => $phoneno,
                'message' => urlencode($message)
            );
            $fields_string="";
            //url-ify the data for the POST
            $params = array();
            foreach($fields2 as $key=>$value) { $params[] = $key.'='.$value; }
            $fields_string = implode('&', $params);

//            $url = "http://insta.nspiresoft.com/http-api.php?username=cottonking&password=cot?321&senderid=COTKNG&route=1&number=$phoneno&message=$message";
            $url = "http://insta.nspiresoft.com/http-api.php?";
//            $url_db=$db->safe(trim($url.$fields_string));    
            //update the url created in db
//            $db->execUpdate("update it_sms set url=$url_db where id = $stsms_id");
                        
            //open connection
            $ch = curl_init();
            $options = array (CURLOPT_RETURNTRANSFER => true);
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt_array ( $ch, $options );
            curl_setopt($ch,CURLOPT_POST, count($fields2));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

            //execute post
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
