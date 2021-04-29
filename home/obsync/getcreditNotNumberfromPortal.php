<?php
//include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

try{
  
     $db = new DBConn();
         $creditnote_num = $db->fetchObject("select cn_no from creditnote_no where active=0");
          
          if(isset($creditnote_num) && trim($creditnote_num->cn_no) !="")
             {
               $creditnote_num = $creditnote_num->cn_no;
               echo "0::".$creditnote_num;
                }else{
                   echo "1::Failed"; 
                }
          $db->closeConnection(); //
} catch (Exception $ex) {

}
