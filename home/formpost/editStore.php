<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

extract($_POST);
//print_r($_POST);
$errors = array();
$success = array();
$store = getCurrUser();
$clsLogger = new clsLogger();
$db = new DBConn();
$old_disc = "";
$is_disc_log = false;
$ipaddr1 = $_SERVER['REMOTE_ADDR'];
if (!$storeid) {
    print "Missing parameter. Please report this error.";
    return;
}

$storees = "select  discountset from it_codes where id = $storeid ";
//      print "***************$query1********************";
$store_check = $db->fetchObject($storees);

//    
//    
//    if(isset($dealerdisc)){
//        if(!is_numeric($dealerdisc)){
//           $errors['storec']="Please insert discount value numeric only e.g. 21"; 
//        }
//
//        $query="select * from it_ck_storediscount where store_id = $storeid ";//
//        $obj = $db->fetchObject($query);
//        $old_disc=$obj->dealer_discount;
//        //print "Disc:$old_disc";
//
//        if($old_disc!=$dealerdisc)
//        {
//          $query="insert into it_dealer_discount_log (modified_by,old_disc,new_disc,storeid,IpAddress,user_id) values('$usrname',$old_disc,$dealerdisc,$storeid,'$ipaddr1',$usrid)";
//          //print ">>>>$query>>>";
//          $db->execInsert($query);
//        }
//    }
//    


if ($store->usertype == UserType::Admin || $store->usertype == UserType::CKAdmin || $store->id == 128) {
    $newcashval = 0.0;
    $newnonclaim = 0.0;
    // $delerDisc=0.0; /////hiiiii  
    $effecteddisc = 0.0;
    if ($discval) {



        if (!is_numeric($discval)) {
            $errors['storec'] = "Please insert discount value numeric only e.g. 21";
        }


        if (isset($claimed) && isset($cashes)) {

            $newnonclaim = (100 - $discval) * 0.01;
            $newcashval = (100 - $discval) * 0.02;

            $effecteddisc = $discval + $newnonclaim + $newcashval;
        } elseif ((!isset($claimed)) && (!isset($cashes))) {

            $effecteddisc = $discval + $newnonclaim + $newcashval;
//      echo 'effected disc'.$effecteddisc;
//     exit;
        } elseif ((isset($claimed)) && (!isset($cashes))) {  //$claimed  //$cashes
            $newnonclaim = (100 - $discval) * 0.01;
            $effecteddisc = $discval + $newnonclaim + $newcashval;
            //  echo 'effected disc'.$effecteddisc;
            //  exit;
        } elseif ((!isset($claimed)) && (isset($cashes))) {

            $newcashval = (100 - $discval) * 0.02;
            $effecteddisc = $discval + $newnonclaim + $newcashval;
            //    echo 'effected disc'.$effecteddisc;
            // exit;
        }

        $query1 = "select  discountset from it_codes where id = $storeid ";
//      print "***************$query1******************";
        $obj1 = $db->fetchObject($query1);
        $old_discountset = "" . $obj1->discountset;

        $query = "select dealer_discount,cash,nonclaim from it_ck_storediscount where store_id = $storeid ";
        $obj = $db->fetchObject($query);
        $old_disc = "" . $obj->dealer_discount;
        $old_cashvalue = "" . $obj->cash;
        $old_nonclaimval = "" . $obj->nonclaim;

        if ($old_disc != $effecteddisc) {
            $query = "insert into it_dealer_discount_log (modified_by,old_disc,new_disc,storeid,IpAddress,user_id,old_cashvalue,new_cashvalue,old_nonclaimval,new_nonclaimval,old_discountset,new_discountset) values('$usrname',$old_disc,$effecteddisc,$storeid,'$ipaddr1',$usrid,$old_cashvalue,$newcashval,$old_nonclaimval,$newnonclaim,$old_discountset,$discval)";
            //  print ">>>>$query>>>";
            // exit;
            $db->execInsert($query);
        }
    }
}






if (!$store_name || !$address || !$city || !$zip || !$owner || !$phone || !$email || !$gstin_no || !$taxtype || !$nstate || !$region || !$status) {
    $errors['storec'] = "Please enter value for all required field marked with *";
} else {
    try {

        $serverCh = new clsServerChanges();
        $password = isset($password) ? $password : false;
        $password2 = isset($password2) ? $password2 : false;
        $store_name = $db->safe($store_name);
        $umrn = $db->safe($umrn);
        $cust_tobe_debtd = $db->safe($cust_tobe_debtd);
        $cust_ifsc_mcr = $db->safe($cust_ifsc_mcr);
        $cust_debit_account = $db->safe($cust_debit_account);
        $area = $db->safe($area);
        $location = $db->safe($location);
        $is_tallyxml = $db->safe($is_tallyxml);

        $status = $db->safe($status);
        $upiid = $db->safe($upi_id);
        $upiname = $db->safe($upi_name);
        // if($umrn && $cust_tobe_debtd && $cust_ifsc_mcr && $cust_debit_account)
        //{ 
        //$is_natch1=1;
        //}
        if (isset($is_natch)) {
            $is_natch1 = $is_natch;
        }

        $address = $db->safe($address);
        $city = $db->safe($city);
        $zipcode = $db->safe($zip);
        $owner = $db->safe($owner);
        $phone = $db->safe($phone);
        $phone2 = $db->safe($phone2);
        $email = $db->safe($email);
        $email2 = $db->safe($email2);
        $vat = $db->safe($vat);
        $gstin_no = $db->safe($gstin_no);
        $tally_name = $db->safe($tally_name);
        $distance = $db->safe($distance);
        $level = $db->safe($level);
        //        $tallyname = $db->safe($tally);
        //$zipcode = isset($zip) ? $db->safe(trim($zip)) : false;

        if ($store->usertype == UserType::Admin || $store->usertype == UserType::CKAdmin || $store->id == 128) {
            $discquery = "";
            if (isset($adddisc) && trim($adddisc) != "") {
                $discquery .= " , additional_discount = $adddisc ";
            }
            if (isset($transport) && trim($transport) != "") {
                $discquery .= " , transport = $transport ";
            }
            if (isset($octroi) && trim($octroi) != "") {
                $discquery .= " , octroi = $octroi ";
            }
//            if(isset($cash) && trim($cash) != ""){ $discquery .= " , cash = $cash " ; }
//            if(isset($nonclaim) && trim($nonclaim) != ""){ $discquery .= " , nonclaim = $nonclaim " ; }

            if (isset($cashes) && trim($cashes) != "") {  //$cashes
                $discquery .= " , cash =  $newcashval ";
            } else {
                $discquery .= " , cash =0.0";
            }


            if (isset($claimed) && trim($claimed) != "") {  //$newnonclaim
                $discquery .= " , nonclaim = $newnonclaim ";
            } else {
                $discquery .= " , nonclaim = 0.0 ";
            }
        }


        $sClause = "";
        if ($store->usertype == UserType::Admin || $store->usertype == UserType::CKAdmin || $store->id == 128) {
            //add msl permission to=>it-admin,koushik,kunal
            if (trim($msl) != "") {
                $sClause .= ", min_stock_level =" . doubleval($msl);
            }
            if (trim($maxsl) != "") {
                $sClause .= ", max_stock_level =" . doubleval($maxsl);
            }
        }
        $addquery = "";
        if (isset($is_closed) && trim($is_closed) != "") {
            $addquery .= " , is_closed = $is_closed";
        }
        //if(isset($umrn) && trim($umrn)!=""){ $addquery .= " , UMRN = $umrn"; }
        if (isset($storetype) && trim($storetype) != "") {
            $addquery .= " , store_type = $storetype";
        }
        if (isset($is_autorefill) && trim($is_autorefill) != "") {
            $addquery .= " , is_autorefill = $is_autorefill";
        }
        if (isset($sbstock_active) && trim($sbstock_active) != "") {
            $addquery .= " , sbstock_active = $sbstock_active";
        }

        if (isset($composite_billing_opted) && trim($composite_billing_opted) != "") {
            $addquery .= " , composite_billing_opted = $composite_billing_opted";
        }

        if (isset($mask_margin_type) && trim($mask_margin_type) != "") {
            $addquery .= " , mask_margin = $mask_margin_type";
        }

        if (isset($status) && trim($status) != "") {
            $addquery .= " , status = $status";
        }
        if (isset($upiid) && trim($upiid) != "") {
            $addquery .= " , upi_id = $upiid";
        }
        if (isset($upiname) && trim($upiname) != "") {
            $addquery .= " , upi_name = $upiname";
        }



        if ($store->usertype == UserType::Admin || $store->usertype == UserType::CKAdmin || $store->id == 128) {


            if (isset($claimed) && trim($claimed) != "") {  ///////////////////////$claimed  //$cashes
                $addquery .= " , is_claim = $claimed";
            } else {

                $addquery .= " , is_claim = 0";
            }


            if (isset($cashes) && trim($cashes) != "") {  ///////////////////////$claimed  //$cashes
                $addquery .= " , is_cash = $cashes";
            } else {


                $addquery .= " , is_cash = 0";
            }

            if (isset($discval) && trim($discval) != "") {  ///////////////////////$claimed  //$cashes $discval
                $addquery .= " , discountset = $discval";
            } else {
                if (isset($store_check->discountset)) {
                    $addquery .= " , discountset = $store->discountset";
                }
            }
        }



        if (isset($pancard_no) && trim($pancard_no) != "") {
            //chk for duplicate
            $pancard_no_db = $db->safe(trim($pancard_no));

            $strlen = strlen(trim($pancard_no));
            if ($strlen != 10) {
                $errors['plen'] = "Pancard should be of 10 characters only";
            } else {
                $addquery .= " , pancard_no = $pancard_no_db";
            }
            //             }
        }
        if ($password && $password != $password2) {
            $errors['password'] = 'Passwords do not match';
        } else {
            if (count($errors) == 0) {
                $query1 = "select autorefil_dttm from it_codes where id = $storeid";
                $storeobj = $db->fetchObject($query1);
                if (trim($is_autorefill) == 1 && trim($storeobj->autorefil_dttm) == "") {
                    //fetch check if not set then only update
                    $aClause = " , autorefil_dttm= now()";
                } else if (trim($is_autorefill) == 0 && trim($storeobj->autorefil_dttm) != "") {
                    $aClause = " , autorefil_dttm=null";
                } else {
                    $aClause = "";
                }
                $query = "update it_codes set store_name=$store_name, address=$address, city=$city, zipcode = $zipcode , owner=$owner, phone=$phone, phone2=$phone2, email=$email, email2=$email2,gstin_no=$gstin_no, tax_type = $taxtype , tally_name=$tally_name,distance= $distance,UMRN=$umrn,cust_tobe_debited=$cust_tobe_debtd,cust_ifsc_or_mcr=$cust_ifsc_mcr,cust_debit_account=$cust_debit_account,is_natch_required=$is_natch1,Area=$area,Location=$location,is_tallyxml=$is_tallyxml,state_id=$nstate,region_id=$region,level=$level $aClause $sClause $addquery ";  //, tally_name=$tallyname
//                   
                if ($password) {
                    $query .= ",password=" . $db->safe(md5($password));
                }
                //if ($zipcode) { $query .= ",zipcode=$zipcode"; }
                $query .= " where id=$storeid";
//                     print "$query";  exit();
                $db->execUpdate($query);
                $query = $db->safe($query);
                $logquery = "insert into it_codes_log(store_id,modified_by,message,ipaddr,createtime) values('$storeid','$usrname',$query,'$ipaddr1',now())";
                //print $logquery;
                $db->execInsert($logquery);

                //error_log("\nUPDATE STORE:-".$query."\n",3,"tmp.txt");
                $discquery = " update it_ck_storediscount set  dealer_discount = $effecteddisc $discquery  where store_id = $storeid ";
                //print ">>>>>>>>>$discquery>>>>>>";
                // error_log("\nUPDATE STORE DISC:-".$discquery."\n",3,"tmp.txt");
                //--> code to log it_ck_storediscount update track
                $ipaddr = $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;
                $clsLogger->logInfo($discquery, $store->id, $pg_name, $ipaddr);
                //--> log code ends here
                $db->execUpdate($discquery);
                $discquery = $db->safe($discquery);
                $logquery1 = "insert into it_codes_log(store_id,modified_by,message,ipaddr,createtime) values('$storeid','$usrname',$discquery,'$ipaddr1',now())";
                //print $logquery1;
                $db->execInsert($logquery1);

                //old 
                //$query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_closed,c.is_natch_required,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim,c.distance,c.state_id , c.composite_billing_opted ,c.region_id from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$storeid;
                //new from discount formulaue
                $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_closed,c.is_natch_required,d.dealer_discount,c.distance,c.state_id,c.composite_billing_opted ,c.region_id,IF(c.store_type =3, 1,0) as is_companystore,c.mask_margin,c.upi_id,c.upi_name,c.store_type from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = " . $storeid;

                $obj = $db->fetchObject($query);
                $server_ch = "[" . json_encode($obj) . "]";
                $ser_type = changeType::store;
                if ($obj->store_type == 2) {
                    $store_id = DEF_50CK_WAREHOUSE_ID;
                } else {
                    $store_id = DEF_CK_WAREHOUSE_ID;
                }
                
                // $store_id = DEF_CK_WAREHOUSE_ID; //changed on 15-03
                //here obj->store_id is  the id of table it_codes so it will become the data_id.
                $serverCh->save($ser_type, $server_ch, $store_id, $obj->store_id);
                
                $store_id = $storeid; // data for that store specifically
                $serverCh->save($ser_type, $server_ch, $storeid, $obj->store_id);
                $success = 'Store information updated.';
            }
        }
    } catch (Exception $xcp) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to add $storecode:" . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/stores/edit/id=$storeid";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/stores";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>