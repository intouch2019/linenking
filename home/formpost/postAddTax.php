<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

//extract($_POST);
$errors = array();
$success = "";

try{
    $_SESSION['form_post']=$_POST;
    extract($_POST);
    $db = new DBConn();
    $serverCh = new clsServerChanges();
//    $taxtype = trim($taxtype);
//    $taxper = trim($taxper);
    
    if(!isset($taxtype) && trim($taxtype) == ""){
        $errors['tax'] = "Please select tax type ";
    }
    if(!isset($taxper) && trim($taxper) == ""){
        $errors['per'] = "Please enter tax percent";
    }
    if(count($errors) == 0){
        if($taxtype == taxType::VAT){ $name = "VAT"; }else{ $name = "CST"; }
        $query = "select count(*) as count from it_taxes where tax_type = $taxtype";
        //error_log("\n chk qry:- ".$query."\n",3,"tmp.txt");
        $exist = $db->fetchObject($query);
        if($exist){
            $no = $exist->count;
            $no++;
            $name .= "".$no;
        }else{ $name .= "1"; }//for  first time for cst n vat type
        $name = $db->safe($name);
        $taxper = $taxper/100;  
        $inserttax = "insert into it_taxes set  tax_type = $taxtype , name = $name , percent = $taxper , createtime = now() ";
        //error_log("\n chk qry:- ".$inserttax."\n",3,"tmp.txt");
        $inserted = $db->execInsert($inserttax);
        if($inserted){
                $obj = $db->fetchObject("select * from it_taxes where id = $inserted");
                $server_ch = "[".json_encode($obj)."]";
               // $ser_type = changeType::store_updated;
                $ser_type = changeType::taxes;
                //$store_id = DEF_WAREHOUSE_ID;
                //$serverCh->save($ser_type, $server_ch,$store_id);
                $serverCh->insert($ser_type, $server_ch,$obj->id);
                $success = "New tax created named ".$name." for tax_type ".taxType::getName($taxtype);
        }
    }
    
}catch(Exception $xcp){
//    print $xcp;
//    $errors['xcp'] = $xcp->getMessage();
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
}
else
{
    $_SESSION['form_success'] = $success;
}

session_write_close();
header("Location: ".DEF_SITEURL."admin/addtax");
exit;
?>
