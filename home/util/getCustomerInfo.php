<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";


global $storeid, $storeNM;
$storeid=$argv[1];
   
try {
    getCustomer($storeid);        
 }
  catch(Exception $xcp){
   $xcp->getMessage(); 
}


function getCustomer($storeid){
$db = new DBConn();
$cnt = 0;
$Cust_list=array();

 //fetch orderinfo from it+_orders having given storeid   
 $qry = "select orderinfo from it_orders where store_id = $storeid order by id ";
 $result = $db->execQuery($qry);
  while($obj = $result->fetch_object()){
     if($obj)
     {
        $str=$obj->orderinfo  ; 
        $substr=  GetStringBetween($str, "<==>","<==>");           
            if(trim($substr)!="")
               {
                 $cnt++;
                    $getelement= explode("<>",$substr); 
                    if((trim($getelement[1]!=""))&&(trim($getelement[2]!=""))) 
                    {
                        $custdata=$getelement[1].','.$getelement[2];
                        array_push($Cust_list,$custdata);
                    }

                }
        }
    }     
  //csv wrinting section       
      $qry1 = "select tally_name from it_codes where id = $storeid";
      $result1 = $db->execQuery($qry1);
      
      while($obj1 = $result1->fetch_object()){
       $storeNM=$obj1->tally_name;      
      }
     
    $file = fopen($storeNM.'.csv', 'w'); 
    // save the column headers
    fputcsv($file, array('Name','PhoneNo')); 
    // save each row of the data
    foreach ($Cust_list as $row){        
    //fputcsv($file, $row);
        fputcsv($file,explode(',',$row));   
} 
// Close the file
    fclose($file);
//output    
     print"file writing done";
     print "\n Tot_customers_info: ".$cnt;
}

function GetStringBetween ($string, $start, $finish) {
             $string = " ".$string;
             $position = strpos($string, $start);
             if ($position == 0) return "";
             $position += strlen($start);
             $length = strpos($string, $finish, $position) - $position;
             return substr($string, $position, $length);
}
