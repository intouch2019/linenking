<?php
ini_set('max_execution_time', 60);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';
//require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
//print_r($_POST);exit();
       	$db = new DBConn(); 
$a = $_POST['startdate'];
$b = $_POST['enddate'];
$errors=array();


if($a > $b){
            $errors["startdate"]='Enter valid date range';
} else {

    $start_date = $a." 00:00:00";
    $end_date = $b." 23:59:59";

                  foreach ($_POST['store'] as $selectedOption){ 
                    if($selectedOption==-1){
                        $query = "update it_codes set saleback_starttime = '$start_date' , saleback_endtime = '$end_date' where   is_closed=0  and usertype = ".UserType::Dealer."";
                        $updated_row=$db->execUpdate($query);
                       break;
                    }else{
                        foreach ($_POST['store'] as $selectedOption){
                            $query = "update it_codes set saleback_starttime = '$start_date' , saleback_endtime = '$end_date'  where  is_closed=0 and usertype = ".UserType::Dealer." and id=$selectedOption";
                            $updated_row=$db->execUpdate($query);
                        }
                       break;
                    } 
                }
                $success = 'Status changed';
                }
                if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "admin/salebackStatusChange";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "admin/salebackStatusChange";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
    
    