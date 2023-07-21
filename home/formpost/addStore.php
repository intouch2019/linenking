<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';

$_SESSION['form_post'] = $_POST;
extract($_POST);
//print_r($_POST);
//
//exit;

$errors = array();
$success = array();
$db = new DBConn();
$store = getCurrUser();
$userpage = new clsUsers();
//if ($store->usertype != UserType::Admin && $store->usertype != UserType::CKAdmin) { print "You are not authorized to add a Store"; return; }
//
///*//////
// * $pagecode = $_SESSION['pagecode'];
// * $page = select * from it_pages where pagecode = '$pagecode'
// * $allowed = select * from it_user_pages where user_id=$store->id and page_id = $page->id
// * if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
// */
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if ($page) {
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) {
        header("Location: " . DEF_SITEURL . "unauthorized");
        return;
    }
} else {
    header("Location:" . DEF_SITEURL . "nopagefound");
    return;
}

//if(!is_numeric($dealer_discount)){
//   $errors['storec']="Please insert discount value numeric only e.g. 21"; 
//}



$newcashval = 0.0;
$newnonclaim = 0.0;
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
        // exit;///
    }
}


if (!$storec || !$dealer_name || !$address || !$city || !$zip || !$name || !$phone || !$email || !$gstin_no || !$taxtype || !$nstate || !$region || !$status || trim($discval) == "") {
    $errors['storec'] = "Please enter value for all required field marked with *";
} else {
    try {

        $serverCh = new clsServerChanges();
        $storec = $db->safe(trim($storec));
        $storep = trim($storep);
        $pass = $db->safe(md5($storep));
        $nstate = $db->safe($nstate);
        $dealer_name = $db->safe($dealer_name);
        $address = $db->safe($address);
        $city = $db->safe($city);
        $area = $db->safe($area);
        $location = $db->safe($location);
        $zipcode = $db->safe($zip);
        if (isset($is_natch) && $is_natch == '1') {
            $is_natch1 = 1;
        } else {
            $is_natch1 = 0;
        }
        $umrn = $db->safe($umrn);
        $cust_tobe_debtd = $db->safe($cust_tobe_debtd);
        $cust_ifsc_mcr = $db->safe($cust_ifsc_mcr);
        $cust_debit_account = $db->safe($cust_debit_account);
        $name = $db->safe($name);
        $phone = $db->safe($phone);
        $phone2 = $db->safe($phone2);
        $email = $db->safe($email);
        $email2 = $db->safe($email2);
        $vat = $db->safe($vat);
        $gstin_no = $db->safe($gstin_no);
        $tally_name = $db->safe($tally_name);

        $distance = $db->safe($distance);
        $is_tallyxml = $db->safe($is_tallyxml);

        $status = $db->safe($status);

        $upi_name = $db->safe($upi_name);
        $upi_id = $db->safe($upi_id);
//        $retail_saletally_name = $db->safe($retail_saletally_name);
//        $retail_sale_cash_name = $db->safe($retail_sale_cash_name);
//        $retail_sale_card_name = $db->safe($retail_sale_card_name);



        $discquery = "";
        if (isset($additional_discount) && trim($additional_discount) != "") {
            $discquery .= " , additional_discount = $additional_discount ";
        }
        if (isset($transport) && trim($transport) != "") {
            $discquery .= " , transport = $transport ";
        }
        if (isset($octroi) && trim($octroi) != "") {
            $discquery .= " , octroi = $octroi ";
        }
//        if(isset($cash) && trim($cash) != ""){ $discquery .= " , cash = $cash " ; }
//        if(isset($nonclaim) && trim($nonclaim) != ""){ $discquery .= " , nonclaim = $nonclaim " ; }
//       

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



        $sClause = "";
        if ($store->usertype == UserType::Admin || $store->usertype == UserType::CKAdmin) {
            //add msl permission to=>it-admin,koushik,kunal
            if (trim($msl) != "") {
                $sClause = ", min_stock_level = " . doubleval($msl);
            }
            if (isset($storetype) && trim($storetype) != "") {
                $sClause .= " , store_type = $storetype";
            }


            if (isset($is_autorefill) && trim($is_autorefill) != "") {
                $sClause .= " , is_autorefill = $is_autorefill";
            }
            if (isset($sbstock_active) && trim($sbstock_active) != "") {
                $sClause .= " , sbstock_active = $sbstock_active";
            }  //$is_autorefill  $sbstock_active $composite_billing_opted
            if (isset($composite_billing_opted) && trim($composite_billing_opted) != "") {
                $sClause .= " , composite_billing_opted = $composite_billing_opted";
            }
        }



        $addquery = "";
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
        }

        if (isset($is_closed) && trim($is_closed) != "") {
            $addquery .= " , is_closed = $is_closed";
        }

        //,retail_saletally_name=$retail_saletally_name,
        //retail_sale_cash_name=$retail_sale_cash_name,
        //retail_sale_card_name=$retail_sale_card_name 

        if (isset($retail_saletally_name) && trim($retail_saletally_name) != "") {
            $addquery .= " , retail_saletally_name = $retail_saletally_name";
        }


        if (isset($retail_sale_cash_name) && trim($retail_sale_cash_name) != "") {
            $addquery .= " , retail_sale_cash_name = $retail_sale_cash_name";
        }


        if (isset($retail_sale_card_name) && trim($retail_sale_card_name) != "") {
            $addquery .= " , retail_sale_card_name = $retail_sale_card_name";
        }







        if (isset($pancard_no) && trim($pancard_no) != "") {
            //chk for duplicate
            $pancard_no_db = $db->safe(trim($pancard_no));
//             $pqry = "select * from it_codes where pancard_no = $pancard_no_db ";
//             $pobj = $db->fetchObject($pqry);
//             if(isset($pobj)){
//                 $errors['pancard'] = "Duplicate PANCARD No not allowed ";
//             }else{
            //chk for length
            $strlen = strlen(trim($pancard_no));
            if ($strlen != 10) {
                $errors['plen'] = "Pancard should be of 10 characters only";
            } else {
                $sClause .= " , pancard_no = $pancard_no_db";
            }
//             }
        }





        //$tallyname = $db->safe($tally);
        $exist = $db->fetchObject("select * from it_codes where usertype=" . UserType::Dealer . " and code=$storec");
        $texist = $db->fetchObject("select * from it_codes where usertype= " . UserType::Dealer . " and tally_name=$tally_name");
        $maxseq_querry = "select max(sequence)as maxseq from it_codes";
        $maxsequence = $db->fetchObject($maxseq_querry);
        $maxseq = $maxsequence->maxseq + 1;
        // error_log("select * from it_codes where usertype=4 and code=$storec",3,"tmp.txt"); 
        if (!$exist && !$texist && (!$storep || !$storep2)) {
            $errors['pass'] = 'Password cannot be empty';
        } else if ($storep != $storep2) {
            $errors['pass'] = 'Passwords do not match';
        } else if ($exist) {

            $errors['pass'] = 'Duplicate Store creation not allowed';
        } else if ($texist) {
            $errors['pass'] = 'Duplicate Store Tally name not allowed';
        } else {
            if (count($errors) == 0) {
                $obj = $db->fetchObject("select store_number from it_codes where usertype=4 order by id desc limit 1");
                $store_number = 1;
                $lastids = $db->fetchObject("select id from it_codes order by id desc limit 1"); //to get last id 
                $id = intval($lastids->id) + 1;
                $license = generateRandomString();
                if ($obj) {
                    $store_number = intval($obj->store_number) + 1;
                }
                $new_store_number = $db->safe(sprintf("%03d", $store_number));
                $qry = "insert into it_codes set  created_by=$store->id, code=$storec, password=$pass,license=md5(concat($id,'$license')), store_name=$dealer_name, store_number=$new_store_number, address=$address, state_id=$nstate, region_id=$region, city=$city, zipcode = $zipcode , owner=$name, phone=$phone, phone2=$phone2, email=$email, email2=$email2, status=$status, upi_name =$upi_name, upi_id =$upi_id, vat=$vat,gstin_no=$gstin_no, usertype=" . UserType::Dealer . " , tally_name = $tally_name ,UMRN=$umrn,cust_tobe_debited=$cust_tobe_debtd,cust_ifsc_or_mcr=$cust_ifsc_mcr,cust_debit_account=$cust_debit_account,Area=$area,Location=$location,is_natch_required=$is_natch1,sequence=$maxseq,tax_type = $taxtype $sClause $addquery ";
                // print "<br>QUERY: $qry";
                //   print "insert into it_ck_storediscount set store_id = 125 , dealer_discount = $effecteddisc $discquery";
                // exit;
                $inscode = $db->execInsert($qry);
                 //assign user to store start
                
             
if($selectRight){
    foreach ($selectRight as $uniqid){
        $assnexecutive="INSERT INTO executive_assign (store_id,exe_id) values ($inscode,$uniqid)";
         $order_id = $db->execInsert($assnexecutive);
         
         
    }
}
//assign user to store end
                if ($inscode) {
                    //print "<br>NEW STORE ID: $inscode";
                    $discquery = "insert into it_ck_storediscount set store_id = $inscode , dealer_discount = $effecteddisc $discquery ";
                    // error_log("DIS query: $discquery",3,"tmp.txt");
                    $discinsert = $db->execInsert($discquery);
                    //   $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_natch_required,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim , c.state_id, c.region_id, c.composite_billing_opted from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$inscode;
                    //new dev


                    $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_natch_required,d.dealer_discount,c.distance,c.is_closed, c.state_id, c.region_id, c.composite_billing_opted,c.store_type from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = " . $inscode;

                    // print "<br>$query";                   
                    $obj = $db->fetchObject($query);
                    if (isset($obj)) {
                        // print_r($obj);
                        $server_ch = "[" . json_encode($obj) . "]";
                        $ser_type = changeType::store;
                        if ($obj->store_type == 2) {
                            $store_id = DEF_50CK_WAREHOUSE_ID;
                        } else {
                            $store_id = DEF_CK_WAREHOUSE_ID;
                        }
                      //  $store_id = DEF_CK_WAREHOUSE_ID; //15-03
                        //here obj->store_id is  the id of table it_codes so it will become the data_id.
                        $serverCh->save($ser_type, $server_ch, $store_id, $obj->store_id);
                        $store_id = $inscode; // data for that store specifically
                        $serverCh->save($ser_type, $server_ch, $store_id, $obj->store_id);
                    }
                    $ratio_query = "select id,name from it_categories where active=1";
                    $cat_objs = $db->fetchObjectArray($ratio_query);

                    foreach ($cat_objs as $rq) {
                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$rq->id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                        //  $no_styles = count($styleobj);
                        foreach ($styleobj as $so) {                                 // print_r($styleobj);
                            $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$rq->id and s1.size_id=s2.id order by s1.sequence");
                            foreach ($sizeobj as $sz) {
//                                $sz;
                                $ins = "insert into it_store_ratios set store_id=$inscode,ctg_id=$rq->id,"
                                        . "design_id=-1,style_id=$so->style_id,size_id=$sz->size_id,"
                                        . "ratio_type=2,ratio=1,updated_by=$store->id,createtime=now()";
                                // print "<br>" . $ins;
                                $db->execInsert($ins);
                            }
                        }
                    }
                    //code to assign default pages to new store
                    if ($inscode) {
                        $query = " select id, menuhead, pagename from it_pages where id in (select page_id from it_usertype_pages where usertype = " . UserType::Dealer . ") group by menuhead,pagename";
                        //                error_log("\nST PGS $query\n",3,"../ajax/tmp.txt");;;
                        $allpgs = $db->fetchObjectArray($query);
                        foreach ($allpgs as $pg) {
                            $iq = "insert into it_user_pages set page_id = $pg->id , user_id = $inscode";
                            //                              error_log("\nINS PGS QRY: $iq\n",3,"tmp.txt");
                            $db->execInsert($iq);
                        }
                        $iq = "insert into it_user_pages set page_id = 247 , user_id = $inscode";
                        //                              error_log("\nINS PGS QRY: $iq\n",3,"tmp.txt");
                        $db->execInsert($iq);
                    }
                }
                $success = 'Store has been added';
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
    $redirect = "admin/stores/add";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/stores";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;

function generateRandomString($length = 28) { //code for random string generation
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>