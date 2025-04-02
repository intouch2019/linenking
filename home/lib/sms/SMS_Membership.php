<?php
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";

class SMS_Membership {
    
        public function sendSMS($phoneno,$message,$incomingid=false){
            $db = new DBConn();
            $set_columns = "";
            if($incomingid != false){$set_columns .= " ,incomingid=$incomingid";}
            $query="insert into it_sms set phoneno=$phoneno,message='$message' $set_columns";            
            $stsms_id=$db->execInsert($query);
            //params
            $fields2 = array(                
                'apikey' => 'XmgI1w1sGA0Jghsx',
                'senderid' => 'LINKNG',
                'number' => $phoneno,
                'message' => $message,
                 'format' => 'json'
            );
            
            
        
            
            
            
            $fields_string="";
            $params = array();
            foreach($fields2 as $key=>$value) { $params[] = $key.'='.$value; }
            $fields_string = implode('&', $params);

//            $url = "http://insta.nspiresoft.com/http-api.php?username=cottonking&password=cot?321&senderid=COTKNG&route=1&number=$phoneno&message=$message";
            $url = "http://alert.nspiresoft.com/V2/http-api.php?";
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
//            print_r($fields2);
            //execute post
//            echo "Final URL: " . $url . $fields_string . "\n";

            $resp = curl_exec($ch);
//            print_r($fields_string);exit();
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
