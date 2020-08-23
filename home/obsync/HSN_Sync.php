<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set('max_execution_time', 300);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$query="select cn_no,ref_no from it_creditnote_ds";
$objs=$db->fetchObjectArray($query);

foreach($objs as $obj){

   
$strhsn="-";
$invoice_id_query="select id from it_invoices where invoice_no=$obj->ref_no";
//print "$invoice_id_query<br/>";
$invobj=$db->fetchObject($invoice_id_query);
                            if(isset($invobj)){
                            
                                //print "HSNcode";
                            $hsnquery="select item_code from it_invoice_items where invoice_id=$invobj->id order by id desc limit 1";
                            $hsnobj=$db->fetchObjectArray($hsnquery);
                            //print_r($hsnobj);
                            if(isset($hsnobj)){
                            
                            $strhsn="(";
                            $cnt=1;
                            foreach($hsnobj as $hsnbb)
                            {
                                if($cnt==1)
                                {
                                    $strhsn.="$hsnbb->item_code";
                                }
                                else {
                                       $strhsn.=",$hsnbb->item_code";
                                 }
                                $cnt=0;
                            }
                            $strhsn.=")";
                            //print "$strhsn";
                            }
                            $finalQuery="select c.it_hsncode from it_items i left outer join it_categories c on i.ctg_id = c.id 
                                         where i.barcode in$strhsn group by c.it_hsncode";
                            //print "$finalQuery";
                            $hsnobj=$db->fetchObjectArray($finalQuery);
                            //print_r($hsnobj);
                            if(isset($hsnobj) && $hsnobj!=null){
                            
                                $strhsn="";
                                    $cnt=1;
                                    foreach($hsnobj as $hsnbb)
                                        {
                                            if($cnt==1)
                                                {
                                                    $strhsn.="$hsnbb->it_hsncode";
                                                }
                                                else {
                                                        $strhsn.=",$hsnbb->it_hsncode";
                                                    }
                                            $cnt=0;
                                        }
                                            $strhsn.="";
                            }
                            else
                            {
                                $strhsn="-";
                            }
                            
                            //print "str:$strhsn<br/>";
                     }
                     
$upqry="update it_creditnote_ds set hsncode='$strhsn' where cn_no=$obj->cn_no";                     
$i=$db->execUpdate($upqry);
print "updated:$i</br>";
}