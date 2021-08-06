<?php
 require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";

extract($_POST);
 $creditnoteid=0;
$errors=array(); 
if(isset($user_id) && $user_id !="")
{
    $userid=$user_id;
}else{$errors['userid'] = "Invalid user id";}

if(isset($creditnote_id) && $creditnote_id !="")
{ 
    $creditnoteid=$creditnote_id;
}else{$errors['creditnoteid'] = "Invalid creditnote id";}

try{
    if (count($errors) <= 0) {
    $db = new DBConn();
    $credit_no="CNLK-";
       $credit_num=0;
       $creditno=$db->fetchObject("select cn_no from creditnote_no where active=0"); 
      
       if(isset($creditno) && ! empty($creditno) && $creditno != null)
       {
           $credit_no .=$creditno->cn_no;
           $db->execUpdate("update creditnote_no set active=1");
           $credit_num=$creditno->cn_no+1;
     $result = callCredinotAPI($creditnoteid,$credit_no);
//    echo $result; //exit();
    $status = explode("::",$result);
//    print_r($status);
    if(strcmp($status[0], "0")==0){
    $errors['CreditNoteAPIError'] = $status[1];
//    print_r($errors);
    }else{
    $dquery="update it_portalinv_creditnote set is_approved=1,approve_dt=now() ,approve_by=$userid  ,invoice_no='".$credit_no."'  where id=$creditnoteid ";
//    echo $dquery;    exit();
     $db->execUpdate($dquery);
     $db->execUpdate("update creditnote_no set cn_no=$credit_num");
    }
     $db->execUpdate("update creditnote_no set active=0");
  }else {
           $errors['CnNum_error'] = "Credit Note Number used in another credit note,so please try after some time"; 
       }  
}
}catch (Exception $ex) {

}
$path="";
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        $path="dg/creditnote/id=".$creditnoteid;
} else {
    unset($_SESSION['form_errors']);
    $path="approve/dgcreditnotes";
}
session_write_close();
header("Location: ".DEF_SITEURL.$path);
exit;





function callCredinotAPI($crn_id,$credit_no)
{   
    try
    {
        $db = new DBConn();
    
    $Push_Data_List=Array();
    $data= Array();
    $cn_items=Array();
    $crn_item=0;
    $item_cnt=0;
    
    
    
    $query = "select * from it_portalinv_creditnote where id=$crn_id"; 
    $orders = $db->fetchObjectArray($query);
    
    foreach ($orders as $order)
                {
                  $tot_cgst=0;
                  $tot_sgst=0;
                  $tot_igst=0;
                  $Invoice_value=0;
                  $total_taxable=0;
                  
        
        $iquery="select cs.tax_type as taxtype,i.tax_rate*100 as tax,i.taxable_value as taxable_value,i.total_rate_qty as total_rate,i.cgst as cgst,i.sgst as sgst,i.igst as igst from it_portalinv_items_creditnote i,it_categories c,it_codes cs where i.ctg_id=c.id and i.store_id=cs.id and invoice_id = $order->id";
                     $items=$db->fetchObjectArray($iquery);
                                    
                    foreach($items as $item)
                        {
                        $total_taxable +=$item->total_rate;
                            if($item->taxtype == 1)
                        {
                            $Invoice_value += $item->taxable_value + $item->cgst +$item->sgst;
                            
                            $tot_cgst += $item->cgst;
                            $tot_sgst += $item->sgst;
                        }else{
                            $Invoice_value += $item->taxable_value + $item->igst;
                            $tot_igst += $item->igst;
                        }
                        }
                 $tot_cgst = round($tot_cgst ,2);
                 $tot_sgst = round($tot_sgst ,2);
                 $tot_igst = round($tot_igst ,2);
        
                    $iquery="select i.id,cs.state_id,cs.gstin_no,cs.address,cs.city,cs.zipcode,cs.phone,cs.email,c.name as ctg_name,i.desc_defects as dsc,c.it_hsncode as hsn,i.price as mrp,i.quantity as qty,i.disc_peritem as disc_peritm,i.rate as rate_peritm,i.total_price_qty ,i.price*i.quantity as  total_mrp,i.total_rate_qty as total_rate,i.taxable_value as taxable_value ,i.discount_val as total_disc,i.tax_rate*100 as tax,i.cgst as cgst,i.sgst as sgst,i.igst as igst,cs.tax_type as taxtype,i.createtime as dttime from it_portalinv_items_creditnote i,it_categories c,it_codes cs where i.ctg_id=c.id and i.store_id=cs.id and invoice_id = $order->id";
                     $items=$db->fetchObjectArray($iquery);
                                    
                        foreach($items as $item)
                            {
                        $item_cnt++;
                        $cnoi = new CreditNoteItems();
//                        $cnoi->set_GSTIN("27AAACW3775F007"); //for testing
                        $cnoi->set_GSTIN("27AAACC7418H1ZQ"); //for live
                        $cnoi->set_Irn("");
                        $cnoi->set_Tran_SupTyp("B2B");
                        if ($item->state_id == 22) {
                        $cnoi->set_Tran_RegRev("N");
                         } else {
                        $cnoi->set_Tran_RegRev("Y");
                         }
                        $cnoi->set_Tran_Typ("REG");
                        $cnoi->set_Tran_EcmGstin("");
                         if ($item->state_id == 22) {
                        $cnoi->set_Tran_IgstOnIntra("N");
                            } else {
                        $cnoi->set_Tran_IgstOnIntra("Y");
                       }
                        $cnoi->set_Doc_Typ("CRN");
//                        $cnoi->set_Doc_No($order->invoice_no);
                        $cnoi->set_Doc_No($credit_no);
//                        $timestamp = strtotime($order->approve_dt);
                        $date=date("d/m/Y");
//                        $date=str_replace("\\", "",$date);

                        $cnoi->set_Doc_Dt($date);
                        
                        
                        //NOTE: check all credential when we tested it from local otherwiaw it directly affect live data so do carefully while calling api from local
                
                        $cnoi->set_BillFrom_Gstin("27AAACC7418H1ZQ");  //for ck live gstn,  
//                        $cnoi->set_BillFrom_Gstin("27AAACW3775F007");  //for testing gstin

                        $cnoi->set_BillFrom_LglNm("Fashionking Brands Pvt.Ltd.");
                        $cnoi->set_BillFrom_TrdNm("Fashionking Brands Pvt.Ltd.");
                        $cnoi->set_BillFrom_Addr1("Textile Park");
                        $cnoi->set_BillFrom_Addr2("MIDC");
                        $cnoi->set_BillFrom_Loc("Baramati");
                        $cnoi->set_BillFrom_Pin("413133");
                        $cnoi->set_BillFrom_Stcd("27");
                        $cnoi->set_BillFrom_Ph("02112-244121");
                        $email = "info@linenking.in";
                        $cnoi->set_BillTo_Em($email);
                        $custGstIn = str_replace("\\s", "",$item->gstin_no);
                        $cnoi->set_BillTo_Gstin($custGstIn);
                        $cnoi->set_BillTo_LglNm($order->store_name);
                        $cnoi->set_BillTo_TrdNm($order->store_name);
                        $cnoi->set_BillTo_Pos("27"); // wh pos in mh so it 27
                        $cnoi->set_BillTo_Addr1($item->address);
                        $cnoi->set_BillTo_Addr2("");
                        $cnoi->set_BillTo_Loc($item->city);
                        $stateq="select tin from states where id=$item->state_id";
                        $stcodeobj=$db->fetchObject($stateq);
                        $state_code="";
                        if(isset($stcodeobj))
                        {
                          $state_code =  $stcodeobj->tin;
                        }
                        
                        $cnoi->set_BillTo_Stcd($state_code);
                        $cnoi->set_BillTo_Pin($item->zipcode);
                        $cnoi->set_BillTo_Ph($item->phone);
                        $cnoi->set_BillTo_Em($item->email);
                        $cnoi->set_Item_SlNo($item_cnt);
                        $cnoi->set_Item_PrdDesc($item->ctg_name);
                        $cnoi->set_Item_IsServc("N");
                        $cnoi->set_Item_HsnCd($item->hsn);
                        $cnoi->set_Item_Barcde("");
                        $cnoi->set_Item_Qty($item->qty);
                        $cnoi->set_Item_FreeQty("");
                        $cnoi->set_Item_Unit("PCS");
                        $item_price= round($item->mrp, 2);
                        $cnoi->set_Item_UnitPrice($item_price);
                        $total_amt= round(($item->qty * $item->mrp),2);
                        $cnoi->set_Item_TotAmt($total_amt);
                        $item_disc = round($item->total_disc ,2);
                        $cnoi->set_Item_Discount($item_disc);
                        $cnoi->set_Item_PreTaxVal("");
                        $cnoi->set_Item_AssAmt($item->total_rate);
                        $itemrates = round($item->tax , 2);
                        $cnoi->set_Item_GstRt($itemrates);
                        $total_item_value=0.0;
                        if($item->taxtype == 1)
                        {
                            $total_item_value=$item->total_rate + $item->cgst + $item->sgst;
                            $cnoi->set_Item_IgstAmt("");
                            $cnoi->set_Item_CgstAmt($item->cgst);
                            $cnoi->set_Item_SgstAmt($item->sgst); 
                        }else{
                            $total_item_value=$item->total_rate + $item->igst;
                            $cnoi->set_Item_IgstAmt($item->igst);
                            $cnoi->set_Item_CgstAmt("");
                            $cnoi->set_Item_SgstAmt("");
                        }
                        $total_item_value = round($total_item_value,2);
                        $cnoi->set_Item_CesRt("");
                        $cnoi->set_Item_CesAmt("");
                        $cnoi->set_Item_CesNonAdvlAmt("");
                        $cnoi->set_Item_StateCesRt("");
                        $cnoi->set_Item_StateCesAmt("");
                        $cnoi->set_Item_StateCesNonAdvlAmt("");
                        $cnoi->set_Item_OthChrg("");
                        $cnoi->set_Item_TotItemVal($total_item_value);////////////////
                        $cnoi->set_Item_OrdLineRef("");
                        $cnoi->set_Item_OrgCntry("");
                        $cnoi->set_Item_PrdSlNo("");
                        $cnoi->set_Item_Attrib_Nm("");
                        $cnoi->set_Item_Attrib_Val("");
                        $cnoi->set_Item_Bch_Nm("");
                        $cnoi->set_Item_Bch_ExpDt("");
                        $cnoi->set_Item_Bch_WrDt("");
//                        $tot_taxableVal = round($item->taxable_value ,2);
//                        $cnoi->set_Val_AssVal($tot_taxableVal);
                        $cnoi->set_Val_AssVal(round($total_taxable,2));
                       if ($item->taxtype == 1) {
                        $cnoi->set_Val_CgstVal($tot_cgst);
                        $cnoi->set_Val_SgstVal($tot_sgst);
                        $cnoi->set_Val_IgstVal("");

                        } else {
                        $cnoi->set_Val_CgstVal("");
                        $cnoi->set_Val_SgstVal("");
                        $cnoi->set_Val_IgstVal($tot_igst);
                        }
                        $cnoi->set_Val_CesVal("");
                        $cnoi->set_Val_StCesVal("");
                        $cnoi->set_Val_Discount("");
                        $cnoi->set_Val_OthChrg("");
                        
                        $Round_Invoice_val =round($Invoice_value);
                        $round = $Round_Invoice_val - $Invoice_value;
                        $round= round($round,2);
                        $cnoi->set_Val_RndOffAmt($round);
                        $cnoi->set_Val_TotInvVal($Round_Invoice_val);

                        $cnoi->set_Val_TotInvValFc("");
                        $cnoi->set_Pay_Nm("");
                        $cnoi->set_Pay_AccDet("");
                        $cnoi->set_Pay_Mode("");
                        $cnoi->set_Pay_FinInsBr("");
                        $cnoi->set_Pay_PayTerm("");
                        $cnoi->set_Pay_PayInstr("");
                        $cnoi->set_Pay_CrTrn("");
                        $cnoi->set_Pay_DirDr("");
                        $cnoi->set_Pay_CrDay("");
                        $cnoi->set_Pay_PaidAmt("");

                        $cnoi->set_Pay_PaymtDue("");
                        $cnoi->set_Ref_InvRm("");
                        $cnoi->set_Ref_InvStDt("");
                        $cnoi->set_Ref_InvEndDt("");
                        $cnoi->set_Ref_PrecDoc_InvNo("");
                        $cnoi->set_Ref_PrecDoc_InvDt("");
                        $cnoi->set_Ref_PrecDoc_OthRefNo("");
                        $cnoi->set_Ref_Contr_RecAdvRefr("");
                        $cnoi->set_Ref_Contr_RecAdvDt("");

                        $cnoi->set_Ref_Contr_TendRefr("");
                        $cnoi->set_Ref_Contr_ContrRefr("");
                        $cnoi->set_Ref_Contr_ExtRefr("");
                        $cnoi->set_Ref_Contr_ProjRefr("");
                        $cnoi->set_Ref_Contr_PORefr("");
                        $cnoi->set_Ref_Contr_PORefDt("");
                        $cnoi->set_AddlDoc_Url("");
                        $cnoi->set_AddlDoc_Docs("");
                        $cnoi->set_AddlDoc_Info("");
                        $cnoi->set_Ewb_TransId("");
                        $cnoi->set_Ewb_TransName("");
                        $cnoi->set_Ewb_TransMode("");
                        $cnoi->set_Ewb_Distance("");
                        $cnoi->set_Ewb_TransDocNo("");
                        $cnoi->set_Ewb_TransDocDt("");
                        $cnoi->set_Ewb_VehNo("");
                        $cnoi->set_Ewb_VehType("");
                        $cnoi->set_GetQRImg("1");

//---------------------------------------------------------------------------------------------------------
                //for Live credentials uncomment when deploy on live
//                
                $cnoi->set_CDKey("1695383");
                $cnoi->set_EInvUserName("FASHIONKIN_API_CKP");
                $cnoi->set_EInvPassword("Fashionking@29");
                $cnoi->set_EFUserName("B4FFC421-D76D-406D-9F09-8ACEC72BD3C1");
                $cnoi->set_EFPassword("3689CEA7-5DE2-41E7-A696-28F9FB3852B7");
//                
//---------------------------------------------------------------------------------------------------------
                //for testing credential comment when deploy on live
//
//                $cnoi->set_CDKey("1000687");
//                $cnoi->set_EInvUserName("27AAACW3775F007");
//                $cnoi->set_EInvPassword("Admin!23");
//                $cnoi->set_EFUserName("29AAACW3775F000");
//                $cnoi->set_EFPassword("Admin!23..");
//---------------------------------------------------------------------------------------------------------
 
                        
                        
                        
                        $cn_items[$crn_item++]=$cnoi;
                        }
                    
    $data_array['Data']=$cn_items;
    $Push_Data_List_array['Push_Data_List']=$data_array;
//    echo json_encode($Push_Data_List_array) ."</br></br> Response:</br></br>";
    $fields_string = json_encode($Push_Data_List_array);
//    exit();
    
   //-------------------------------API Call-------------------------------------------- 
    
    $url = "http://einvlive.webtel.in/v1.03/GenIRN";    //for live
//      $url = "http://einvSandbox.webtel.in/v1.03/GenIRN";    //for testing
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $output = curl_exec($ch);
//        print_r($output);
    
   
    //----------------------------------------API Response handling-----------------------
//    $output='[{"ErrorMessage":"","ErrorCode":"","Status":"1","GSTIN":"27AAACW3775F007","DocNo":"DCN-19200006","DocType":"CRN","DocDate":"07/04/2021","Irn":"ff9c134ddb97a20ea11a216629cb60f1075961d6faa8e67df3b478eca319e47e","AckDate":"2021-04-21 10:34:00","AckNo":122110043610599,"EwbNo":null,"EwbDt":null,"EwbValidTill":null,"SignedQRCode":"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","SignedInvoice":"eyJhbGciOiJSUzI1NiIsImtpZCI6IkVEQzU3REUxMzU4QjMwMEJBOUY3OTM0MEE2Njk2ODMxRjNDODUwNDciLCJ0eXAiOiJKV1QiLCJ4NXQiOiI3Y1Y5NFRXTE1BdXA5NU5BcG1sb01mUElVRWMifQ.eyJkYXRhIjoie1wiQWNrTm9cIjoxMjIxMTAwNDM2MTA1OTksXCJBY2tEdFwiOlwiMjAyMS0wNC0yMSAxMDozNDowM1wiLFwiSXJuXCI6XCJmZjljMTM0ZGRiOTdhMjBlYTExYTIxNjYyOWNiNjBmMTA3NTk2MWQ2ZmFhOGU2N2RmM2I0NzhlY2EzMTllNDdlXCIsXCJWZXJzaW9uXCI6XCIxLjFcIixcIlRyYW5EdGxzXCI6e1wiVGF4U2NoXCI6XCJHU1RcIixcIlN1cFR5cFwiOlwiQjJCXCIsXCJSZWdSZXZcIjpcIk5cIixcIklnc3RPbkludHJhXCI6XCJOXCJ9LFwiRG9jRHRsc1wiOntcIlR5cFwiOlwiQ1JOXCIsXCJOb1wiOlwiRENOLTE5MjAwMDA2XCIsXCJEdFwiOlwiMDcvMDQvMjAyMVwifSxcIlNlbGxlckR0bHNcIjp7XCJHc3RpblwiOlwiMjdBQUFDVzM3NzVGMDA3XCIsXCJMZ2xObVwiOlwiRmFzaGlvbmtpbmcgQnJhbmRzIFB2dC5MdGQuXCIsXCJUcmRObVwiOlwiRmFzaGlvbmtpbmcgQnJhbmRzIFB2dC5MdGQuXCIsXCJBZGRyMVwiOlwiVGV4dGlsZSBQYXJrXCIsXCJBZGRyMlwiOlwiTUlEQ1wiLFwiTG9jXCI6XCJCYXJhbWF0aVwiLFwiUGluXCI6NDEzMTMzLFwiU3RjZFwiOlwiMjdcIixcIlBoXCI6XCIwMjExMjI0NDEyMVwifSxcIkJ1eWVyRHRsc1wiOntcIkdzdGluXCI6XCIyN0FBT1BDNzk5NUMyWkNcIixcIkxnbE5tXCI6XCJTYW5pa2EgQ29sbGVjdGlvbi1BbXJhdmF0aSBhL2MgMTgxMVwiLFwiVHJkTm1cIjpcIlNhbmlrYSBDb2xsZWN0aW9uLUFtcmF2YXRpIGEvYyAxODExXCIsXCJQb3NcIjpcIjI3XCIsXCJBZGRyMVwiOlwiT3BwIFNhaGFrYXIgQmhhdmFuLCBOZWFyIEphaSBTdGFtYmhcIixcIkxvY1wiOlwiQW1yYXZhdGlcIixcIlBpblwiOjQ0NDYwNSxcIlBoXCI6XCI4ODA2Mzc2NTY0XCIsXCJFbVwiOlwiYWJoYW1hcmVAaW50b3VjaHJld2FyZHMuY29tXCIsXCJTdGNkXCI6XCIyN1wifSxcIkl0ZW1MaXN0XCI6W3tcIkl0ZW1Ob1wiOjAsXCJTbE5vXCI6XCIxXCIsXCJJc1NlcnZjXCI6XCJOXCIsXCJQcmREZXNjXCI6XCJKZWFuc1wiLFwiSHNuQ2RcIjpcIjYyMDMxOVwiLFwiUXR5XCI6MS4wLFwiVW5pdFwiOlwiUENTXCIsXCJVbml0UHJpY2VcIjoxOTk1LjAsXCJUb3RBbXRcIjoxOTk1LjAsXCJEaXNjb3VudFwiOjY3OS4xMixcIkFzc0FtdFwiOjEzMTUuODgsXCJHc3RSdFwiOjEyLjAsXCJDZ3N0QW10XCI6NzguOTUsXCJTZ3N0QW10XCI6NzguOTUsXCJUb3RJdGVtVmFsXCI6MTQ3My43OH1dLFwiVmFsRHRsc1wiOntcIkFzc1ZhbFwiOjEzMTUuODgsXCJDZ3N0VmFsXCI6NzguOTUsXCJTZ3N0VmFsXCI6NzguOTUsXCJJZ3N0VmFsXCI6MC4wLFwiQ2VzVmFsXCI6MC4wLFwiU3RDZXNWYWxcIjowLjAsXCJSbmRPZmZBbXRcIjowLjIyLFwiVG90SW52VmFsXCI6MTQ3NC4wfX0iLCJpc3MiOiJOSUMifQ.AvweGrbd9K-DyN1xbFIZYKJ3TGmiKZEmdb2qo9HarBuqkKe2CzHVH67ApN36wBM02Zx7fpIIlg-R7dR61R5xs2bkTYtUmwh1n0XUV10Tx8QmTGNs5bywMVo-AZ8633hvFqm_JoaosySfKxwWRKb9SD-65eAJZorx422V9K089_izJ5hdBJAx2YJ0UE5qTP1rrzJ4gvoK26kk7lE39GIy0tvUZL-dgAzbXQX2pOVmjn3qph9kW4Mi82r0LDLweo6Fo2bxmuxJCawJCZDPxgH7JVEDXr6IZwbJ17wTgBmbb4tqnXoSzA0THw6QKsPtWSf_c8qjTZl-H8Ao0d1soLcB8Q","IrnStatus":"ACT","InfoDtls":null,"Remarks":"","UniqueKey":""}]';
//     $output='[{"ErrorMessage":"For Sl. No. 2, Taxable Value (AssAmt) value is not equal to (TotAmt - Discount)","ErrorCode":"2193","Status":"0","GSTIN":"27AAACW3775F007","DocNo":"DCN-21220005","DocType":"CRN","DocDate":"21/04/2021","Irn":"","AckDate":"","AckNo":"","EwbNo":"","EwbDt":"","EwbValidTill":"","SignedQRCode":"","SignedInvoice":"","IrnStatus":"","InfoDtls":null,"Remarks":"","UniqueKey":""}]';
    //insert response logs
    
    $reslogs_ins="insert into irn_response_log set CreditNote_No='".$credit_no."' ,Response = '".$output."'";
    $db->execInsert($reslogs_ins);
      
      $response= json_decode($output);
//      print_r($response);
         $imagename="";
       if(strcmp($response[0]->Status, "1")==0){
           $SignedQRCode=$response[0]->SignedQRCode;
//           echo $SignedQRCode;
           $path='../images/QR_code/';
            $image_base64 = base64_decode($SignedQRCode);
            $imagename=$credit_no. '.png';
            $file = $path .$imagename;
            file_put_contents($file, $image_base64);
           
//           exit();
          
       }
    
      $imagename =$db->safe($imagename);
      $ErrorMessage=$db->safe($response[0]->ErrorMessage);
      $ErrorCode=$db->safe($response[0]->ErrorCode);
      $Status=$db->safe($response[0]->Status);
      $GSTIN=$db->safe($response[0]->GSTIN);
      $DocNo=$db->safe($response[0]->DocNo);
      $DocType=$db->safe($response[0]->DocType);
      $DocDate=$db->safe($response[0]->DocDate);
      $Irn=$db->safe($response[0]->Irn);
      $AckDate=$db->safe($response[0]->AckDate);
      $AckNo=$db->safe($response[0]->AckNo);
      $EwbNo=$db->safe($response[0]->EwbNo);
      $EwbDt=$db->safe($response[0]->EwbDt);
      $EwbValidTill=$db->safe($response[0]->EwbValidTill);
      $SignedQRCode=$db->safe($response[0]->SignedQRCode);
      $SignedInvoice=$db->safe($response[0]->SignedInvoice);
      $IrnStatus=$db->safe($response[0]->IrnStatus);
      $InfoDtls=$db->safe($response[0]->InfoDtls);
      $Remarks=$db->safe($response[0]->Remarks);
      $UniqueKey=$db->safe($response[0]->UniqueKey);
       
      
      $response_data="";
      if($ErrorMessage !="")
      {
          $response_data .=", ErrorMessage =$ErrorMessage ";
      }
      if($ErrorCode !="")
      {
          $response_data .=", ErrorCode =$ErrorCode ";
      }
      if($Status !="")
      {
          $response_data .=", Status =$Status ";
      }
      if($GSTIN !="")
      {
          $response_data .=", GSTIN =$GSTIN ";
      }
      if($DocNo !="")
      {
          $response_data .=", DocNo =$DocNo ";
      }
      if($DocType !="")
      {
          $response_data .=", DocType =$DocType ";
      }
      if($DocDate !="")
      {
          $response_data .=", DocDate =$DocDate ";
      }
      if($Irn !="")
      {
          $response_data .=", irn =$Irn ";
      }
      if($AckDate !="")
      {
          $response_data .=", AckDate =$AckDate ";
      }
      if($AckNo !="")
      {
          $response_data .=", AckNo =$AckNo ";
      }
      if($EwbNo !="")
      {
          $response_data .=", EwbNo =$EwbNo ";
      }
      if($EwbDt !="")
      {
          $response_data .=", EwbDt =$EwbDt ";
      }
      if($EwbValidTill !="")
      {
          $response_data .=", EwbValidTill =$EwbValidTill ";
      }
      if($SignedQRCode !="")
      {
          $response_data .=", SignedQRCode =$SignedQRCode ";
      }
      if($SignedInvoice !="")
      {
          $response_data .=", SignedInvoice =$SignedInvoice ";
      }
      if($IrnStatus !="")
      {
          $response_data .=", IrnStatus =$IrnStatus ";
      }
      if($InfoDtls !="")
      {
          $response_data .=", InfoDtls =$InfoDtls ";
      }
      if($Remarks !="")
      {
          $response_data .=", Remarks =$Remarks ";
      }
      if($UniqueKey !="")
      {
          $response_data .=", UniqueKey =$UniqueKey ";
      }
      
//      echo "</br>".$response_data;
      
      $exist =$db->fetchObject("select id from it_irncreditnote where CreditNote_ID=$order->id");
      if(isset($exist))
      {
         $update_query="update it_irncreditnote set CreditNote_ID=$order->id $response_data ,Qr_Image=$imagename where CreditNote_ID=$order->id" ;
//         echo "</br></br></br>".$update_query;
         $db->execUpdate($update_query);
      }else{
          $insert_query="insert into it_irncreditnote set CreditNote_ID=$order->id $response_data ,Qr_Image=$imagename, CREATETIME=now()" ;
//          echo "</br></br></br>".$insert_query;
          $db->execInsert($insert_query);
      }
      

      
//     echo strcmp($response[0]->Status, "1");
//     exit();
            if(strcmp($response[0]->Status, "1")==0)
      {
          return "1::Irn Generate Successfully";
      }else{
          return "0::Credit Note API error: $ErrorMessage";
      }
      
      
                        } 
    
   } catch (Exception $ex) {
     return "0::Exception Occure during API call";
    } 
}




class CreditNoteItems {
    public $GSTIN;
    public $Irn;
    public $Tran_SupTyp;
    public $Tran_RegRev;
    public $Tran_Typ;
    public $Tran_EcmGstin;
    public $Tran_IgstOnIntra;
    public $Doc_Typ;
    public $Doc_No;
    public $Doc_Dt;
    public $BillFrom_Gstin;
    public $BillFrom_LglNm;
    public $BillFrom_TrdNm;
    public $BillFrom_Addr1;
    public $BillFrom_Addr2;
    public $BillFrom_Loc;
    public $BillFrom_Pin;
    public $BillFrom_Stcd;
    public $BillFrom_Ph;
    public $BillTo_Gstin;
    public $BillTo_LglNm;
    public $BillTo_TrdNm;
    public $BillTo_Pos;
    public $BillTo_Addr1;
    public $BillTo_Addr2;
    public $BillTo_Loc;
    public $BillTo_Pin;
    public $BillTo_Stcd;
    public $BillTo_Ph;
    public $BillTo_Em;
    public $Item_SlNo;
    public $Item_PrdDesc;
    public $Item_IsServc;
    public $Item_HsnCd;
    public $Item_Bch_Nm;
    public $Item_Bch_ExpDt;
    public $Item_Bch_WrDt;
    public $Item_Barcde;
    public $Item_Qty;
    public $Item_FreeQty;
    public $Item_Unit;
    public $Item_UnitPrice;
    public $Item_TotAmt;
    public $Item_Discount;
    public $Item_PreTaxVal;
    public $Item_AssAmt;
    public $Item_GstRt;
    public $Item_IgstAmt;
    public $Item_CgstAmt;
    public $Item_SgstAmt;
    public $Item_CesRt;
    public $Item_CesAmt;
    public $Item_CesNonAdvlAmt;
    public $Item_StateCesRt;
    public $Item_StateCesAmt;
    public $Item_StateCesNonAdvlAmt;
    public $Item_OthChrg;
    public $Item_TotItemVal;
    public $Item_OrdLineRef;
    public $Item_OrgCntry;
    public $Item_PrdSlNo;
    public $Item_Attrib_Nm;
    public $Item_Attrib_Val;
    public $Val_AssVal;
    public $Val_CgstVal;
    public $Val_SgstVal;
    public $Val_IgstVal;
    public $Val_CesVal;
    public $Val_StCesVal;
    public $Val_Discount;
    public $Val_OthChrg;
    public $Val_RndOffAmt;
    public $Val_TotInvVal;
    public $Val_TotInvValFc;
    public $Pay_Nm;
    public $Pay_AccDet;
    public $Pay_Mode;
    public $Pay_FinInsBr;
    public $Pay_PayTerm;
    public $Pay_PayInstr;
    public $Pay_CrTrn;
    public $Pay_DirDr;
    public $Pay_CrDay;
    public $Pay_PaidAmt;
    public $Pay_PaymtDue;
    public $Ref_InvRm;
    public $Ref_InvStDt;
    public $Ref_InvEndDt;
    public $Ref_PrecDoc_InvNo;
    public $Ref_PrecDoc_InvDt;
    public $Ref_PrecDoc_OthRefNo;
    public $Ref_Contr_RecAdvRefr;
    public $Ref_Contr_RecAdvDt;
    public $Ref_Contr_TendRefr;
    public $Ref_Contr_ContrRefr;
    public $Ref_Contr_ExtRefr;
    public $Ref_Contr_ProjRefr;
    public $Ref_Contr_PORefr;
    public $Ref_Contr_PORefDt;
    public $AddlDoc_Url;
    public $AddlDoc_Docs;
    public $AddlDoc_Info;
    public $Ewb_TransId;
    public $Ewb_TransName;
    public $Ewb_TransMode;
    public $Ewb_Distance;
    public $Ewb_TransDocNo;
    public $Ewb_TransDocDt;
    public $Ewb_VehNo;
    public $Ewb_VehType;
    public $GetQRImg; 
    public $CDKEY;
    public $EFUSERNAME;
    public $EFPASSWORD;
    public $EINVUSERNAME;
    public $EINVPASSWORD;


    function set_GSTIN($GSTIN) {
        $this->GSTIN = $GSTIN;
    }

    function set_Irn($Irn) {
        $this->Irn = $Irn;
    }

    function set_Tran_SupTyp($Tran_SupTyp) {
        $this->Tran_SupTyp = $Tran_SupTyp;
    }

    function set_Tran_RegRev($Tran_RegRev) {
        $this->Tran_RegRev = $Tran_RegRev;
    }

    function set_Tran_Typ($Tran_Typ) {
        $this->Tran_Typ = $Tran_Typ;
    }

    function set_Tran_EcmGstin($Tran_EcmGstin) {
        $this->Tran_EcmGstin = $Tran_EcmGstin;
    }

    function set_Tran_IgstOnIntra($Tran_IgstOnIntra) {
        $this->Tran_IgstOnIntra = $Tran_IgstOnIntra;
    }

    function set_Doc_Typ($Doc_Typ) {
        $this->Doc_Typ = $Doc_Typ;
    }

    function set_Doc_No($Doc_No) {
        $this->Doc_No = $Doc_No;
    }

    function set_Doc_Dt($Doc_Dt) {
        $this->Doc_Dt = $Doc_Dt;
    }

    function set_BillFrom_Gstin($BillFrom_Gstin) {
        $this->BillFrom_Gstin = $BillFrom_Gstin;
    }

    function set_BillFrom_LglNm($BillFrom_LglNm) {
        $this->BillFrom_LglNm = $BillFrom_LglNm;
    }

    function set_BillFrom_TrdNm($BillFrom_TrdNm) {
        $this->BillFrom_TrdNm = $BillFrom_TrdNm;
    }

    function set_BillFrom_Addr1($BillFrom_Addr1) {
        $this->BillFrom_Addr1 = $BillFrom_Addr1;
    }

    function set_BillFrom_Addr2($BillFrom_Addr2) {
        $this->BillFrom_Addr2 = $BillFrom_Addr2;
    }

    function set_BillFrom_Loc($BillFrom_Loc) {
        $this->BillFrom_Loc = $BillFrom_Loc;
    }

    function set_BillFrom_Pin($BillFrom_Pin) {
        $this->BillFrom_Pin = $BillFrom_Pin;
    }

    function set_BillFrom_Stcd($BillFrom_Stcd) {
        $this->BillFrom_Stcd = $BillFrom_Stcd;
    }

    function set_BillFrom_Ph($BillFrom_Ph) {
        $this->BillFrom_Ph = $BillFrom_Ph;
    }

    function set_BillFrom_Em($BillFrom_Em) {
        $this->BillFrom_Em = $BillFrom_Em;
    }

    function set_BillTo_Gstin($BillTo_Gstin) {
        $this->BillTo_Gstin = $BillTo_Gstin;
    }

    function set_BillTo_LglNm($BillTo_LglNm) {
        $this->BillTo_LglNm = $BillTo_LglNm;
    }

    function set_BillTo_TrdNm($BillTo_TrdNm) {
        $this->BillTo_TrdNm = $BillTo_TrdNm;
    }

    function set_BillTo_Pos($BillTo_Pos) {
        $this->BillTo_Pos = $BillTo_Pos;
    }

    function set_BillTo_Addr1($BillTo_Addr1) {
        $this->BillTo_Addr1 = $BillTo_Addr1;
    }

    function set_BillTo_Addr2($BillTo_Addr2) {
        $this->BillTo_Addr2 = $BillTo_Addr2;
    }

    function set_BillTo_Loc($BillTo_Loc) {
        $this->BillTo_Loc = $BillTo_Loc;
    }

    function set_BillTo_Pin($BillTo_Pin) {
        $this->BillTo_Pin = $BillTo_Pin;
    }

    function set_BillTo_Stcd($BillTo_Stcd) {
        $this->BillTo_Stcd = $BillTo_Stcd;
    }

    function set_BillTo_Ph($BillTo_Ph) {
        $this->BillTo_Ph = $BillTo_Ph;
    }

    function set_BillTo_Em($BillTo_Em) {
        $this->BillTo_Em = $BillTo_Em;
    }
 

    function set_Item_SlNo($Item_SlNo) {
        $this->Item_SlNo = $Item_SlNo;
    }

    function set_Item_PrdDesc($Item_PrdDesc) {
        $this->Item_PrdDesc = $Item_PrdDesc;
    }

    function set_Item_IsServc($Item_IsServc) {
        $this->Item_IsServc = $Item_IsServc;
    }

    function set_Item_HsnCd($Item_HsnCd) {
        $this->Item_HsnCd = $Item_HsnCd;
    }

    function set_Item_Bch_Nm($Item_Bch_Nm) {
        $this->Item_Bch_Nm = $Item_Bch_Nm;
    }

    function set_Item_Bch_ExpDt($Item_Bch_ExpDt) {
        $this->Item_Bch_ExpDt = $Item_Bch_ExpDt;
    }

    function set_Item_Bch_WrDt($Item_Bch_WrDt) {
        $this->Item_Bch_WrDt = $Item_Bch_WrDt;
    }

    function set_Item_Barcde($Item_Barcde) {
        $this->Item_Barcde = $Item_Barcde;
    }

    function set_Item_Qty($Item_Qty) {
        $this->Item_Qty = $Item_Qty;
    }

    function set_Item_FreeQty($Item_FreeQty) {
        $this->Item_FreeQty = $Item_FreeQty;
    }

    function set_Item_Unit($Item_Unit) {
        $this->Item_Unit = $Item_Unit;
    }

    function set_Item_UnitPrice($Item_UnitPrice) {
        $this->Item_UnitPrice = $Item_UnitPrice;
    }

    function set_Item_TotAmt($Item_TotAmt) {
        $this->Item_TotAmt = $Item_TotAmt;
    }

    function set_Item_Discount($Item_Discount) {
        $this->Item_Discount = $Item_Discount;
    }

    function set_Item_PreTaxVal($Item_PreTaxVal) {
        $this->Item_PreTaxVal = $Item_PreTaxVal;
    }

    function set_Item_AssAmt($Item_AssAmt) {
        $this->Item_AssAmt = $Item_AssAmt;
    }

    function set_Item_GstRt($Item_GstRt) {
        $this->Item_GstRt = $Item_GstRt;
    }

    function set_Item_IgstAmt($Item_IgstAmt) {
        $this->Item_IgstAmt = $Item_IgstAmt;
    }

    function set_Item_CgstAmt($Item_CgstAmt) {
        $this->Item_CgstAmt = $Item_CgstAmt;
    }

    function set_Item_SgstAmt($Item_SgstAmt) {
        $this->Item_SgstAmt = $Item_SgstAmt;
    }

    function set_Item_CesRt($Item_CesRt) {
        $this->Item_CesRt = $Item_CesRt;
    }

    function set_Item_CesAmt($Item_CesAmt) {
        $this->Item_CesAmt = $Item_CesAmt;
    }

    function set_Item_CesNonAdvlAmt($Item_CesNonAdvlAmt) {
        $this->Item_CesNonAdvlAmt = $Item_CesNonAdvlAmt;
    }

    function set_Item_StateCesRt($Item_StateCesRt) {
        $this->Item_StateCesRt = $Item_StateCesRt;
    }

    function set_Item_StateCesAmt($Item_StateCesAmt) {
        $this->Item_StateCesAmt = $Item_StateCesAmt;
    }

    function set_Item_StateCesNonAdvlAmt($Item_StateCesNonAdvlAmt) {
        $this->Item_StateCesNonAdvlAmt = $Item_StateCesNonAdvlAmt;
    }

    function set_Item_OthChrg($Item_OthChrg) {
        $this->Item_OthChrg = $Item_OthChrg;
    }

    function set_Item_TotItemVal($Item_TotItemVal) {
        $this->Item_TotItemVal = $Item_TotItemVal;
    }

    function set_Item_OrdLineRef($Item_OrdLineRef) {
        $this->Item_OrdLineRef = $Item_OrdLineRef;
    }

    function set_Item_OrgCntry($Item_OrgCntry) {
        $this->Item_OrgCntry = $Item_OrgCntry;
    }

    function set_Item_PrdSlNo($Item_PrdSlNo) {
        $this->Item_PrdSlNo = $Item_PrdSlNo;
    }

    function set_Item_Attrib_Nm($Item_Attrib_Nm) {
        $this->Item_Attrib_Nm = $Item_Attrib_Nm;
    }

    function set_Item_Attrib_Val($Item_Attrib_Val) {
        $this->Item_Attrib_Val = $Item_Attrib_Val;
    }

    function set_Val_AssVal($Val_AssVal) {
        $this->Val_AssVal = $Val_AssVal;
    }

    function set_Val_CgstVal($Val_CgstVal) {
        $this->Val_CgstVal = $Val_CgstVal;
    }

    function set_Val_SgstVal($Val_SgstVal) {
        $this->Val_SgstVal = $Val_SgstVal;
    }

    function set_Val_IgstVal($Val_IgstVal) {
        $this->Val_IgstVal = $Val_IgstVal;
    }

    function set_Val_CesVal($Val_CesVal) {
        $this->Val_CesVal = $Val_CesVal;
    }

    function set_Val_StCesVal($Val_StCesVal) {
        $this->Val_StCesVal = $Val_StCesVal;
    }

    function set_Val_Discount($Val_Discount) {
        $this->Val_Discount = $Val_Discount;
    }

    function set_Val_OthChrg($Val_OthChrg) {
        $this->Val_OthChrg = $Val_OthChrg;
    }

    function set_Val_RndOffAmt($Val_RndOffAmt) {
        $this->Val_RndOffAmt = $Val_RndOffAmt;
    }

    function set_Val_TotInvVal($Val_TotInvVal) {
        $this->Val_TotInvVal = $Val_TotInvVal;
    }

    function set_Val_TotInvValFc($Val_TotInvValFc) {
        $this->Val_TotInvValFc = $Val_TotInvValFc;
    }

    function set_Pay_Nm($Pay_Nm) {
        $this->Pay_Nm = $Pay_Nm;
    }

    function set_Pay_AccDet($Pay_AccDet) {
        $this->Pay_AccDet = $Pay_AccDet;
    }

    function set_Pay_Mode($Pay_Mode) {
        $this->Pay_Mode = $Pay_Mode;
    }

    function set_Pay_FinInsBr($Pay_FinInsBr) {
        $this->Pay_FinInsBr = $Pay_FinInsBr;
    }

    function set_Pay_PayTerm($Pay_PayTerm) {
        $this->Pay_PayTerm = $Pay_PayTerm;
    }

    function set_Pay_PayInstr($Pay_PayInstr) {
        $this->Pay_PayInstr = $Pay_PayInstr;
    }

    function set_Pay_CrTrn($Pay_CrTrn) {
        $this->Pay_CrTrn = $Pay_CrTrn;
    }

    function set_Pay_DirDr($Pay_DirDr) {
        $this->Pay_DirDr = $Pay_DirDr;
    }

    function set_Pay_CrDay($Pay_CrDay) {
        $this->Pay_CrDay = $Pay_CrDay;
    }

    function set_Pay_PaidAmt($Pay_PaidAmt) {
        $this->Pay_PaidAmt = $Pay_PaidAmt;
    }

    function set_Pay_PaymtDue($Pay_PaymtDue) {
        $this->Pay_PaymtDue = $Pay_PaymtDue;
    }

    function set_Ref_InvRm($Ref_InvRm) {
        $this->Ref_InvRm = $Ref_InvRm;
    }

    function set_Ref_InvStDt($Ref_InvStDt) {
        $this->Ref_InvStDt = $Ref_InvStDt;
    }

    function set_Ref_InvEndDt($Ref_InvEndDt) {
        $this->Ref_InvEndDt = $Ref_InvEndDt;
    }

    function set_Ref_PrecDoc_InvNo($Ref_PrecDoc_InvNo) {
        $this->Ref_PrecDoc_InvNo = $Ref_PrecDoc_InvNo;
    }

    function set_Ref_PrecDoc_InvDt($Ref_PrecDoc_InvDt) {
        $this->Ref_PrecDoc_InvDt = $Ref_PrecDoc_InvDt;
    }

    function set_Ref_PrecDoc_OthRefNo($Ref_PrecDoc_OthRefNo) {
        $this->Ref_PrecDoc_OthRefNo = $Ref_PrecDoc_OthRefNo;
    }

    function set_Ref_Contr_RecAdvRefr($Ref_Contr_RecAdvRefr) {
        $this->Ref_Contr_RecAdvRefr = $Ref_Contr_RecAdvRefr;
    }

    function set_Ref_Contr_RecAdvDt($Ref_Contr_RecAdvDt) {
        $this->Ref_Contr_RecAdvDt = $Ref_Contr_RecAdvDt;
    }

    function set_Ref_Contr_TendRefr($Ref_Contr_TendRefr) {
        $this->Ref_Contr_TendRefr = $Ref_Contr_TendRefr;
    }

    function set_Ref_Contr_ContrRefr($Ref_Contr_ContrRefr) {
        $this->Ref_Contr_ContrRefr = $Ref_Contr_ContrRefr;
    }

    function set_Ref_Contr_ExtRefr($Ref_Contr_ExtRefr) {
        $this->Ref_Contr_ExtRefr = $Ref_Contr_ExtRefr;
    }

    function set_Ref_Contr_ProjRefr($Ref_Contr_ProjRefr) {
        $this->Ref_Contr_ProjRefr = $Ref_Contr_ProjRefr;
    }

    function set_Ref_Contr_PORefr($Ref_Contr_PORefr) {
        $this->Ref_Contr_PORefr = $Ref_Contr_PORefr;
    }

    function set_Ref_Contr_PORefDt($Ref_Contr_PORefDt) {
        $this->Ref_Contr_PORefDt = $Ref_Contr_PORefDt;
    }

    function set_AddlDoc_Url($AddlDoc_Url) {
        $this->AddlDoc_Url = $AddlDoc_Url;
    }

    function set_AddlDoc_Docs($AddlDoc_Docs) {
        $this->AddlDoc_Docs = $AddlDoc_Docs;
    }

    function set_AddlDoc_Info($AddlDoc_Info) {
        $this->AddlDoc_Info = $AddlDoc_Info;
    }

  

    function set_Ewb_TransId($Ewb_TransId) {
        $this->Ewb_TransId = $Ewb_TransId;
    }

    function set_Ewb_TransName($Ewb_TransName) {
        $this->Ewb_TransName = $Ewb_TransName;
    }

    function set_Ewb_TransMode($Ewb_TransMode) {
        $this->Ewb_TransMode = $Ewb_TransMode;
    }

    function set_Ewb_Distance($Ewb_Distance) {
        $this->Ewb_Distance = $Ewb_Distance;
    }

    function set_Ewb_TransDocNo($Ewb_TransDocNo) {
        $this->Ewb_TransDocNo = $Ewb_TransDocNo;
    }

    function set_Ewb_TransDocDt($Ewb_TransDocDt) {
        $this->Ewb_TransDocDt = $Ewb_TransDocDt;
    }

    function set_Ewb_VehNo($Ewb_VehNo) {
        $this->Ewb_VehNo = $Ewb_VehNo;
    }

    function set_Ewb_VehType($Ewb_VehType) {
        $this->Ewb_VehType = $Ewb_VehType;
    }

    function set_GetQRImg($GetQRImg) {
        $this->GetQRImg = $GetQRImg;
    }
 
    function set_CDKEY($CDKEY) {
        $this->CDKEY = $CDKEY;
    }

    function set_EFUSERNAME($EFUSERNAME) {
        $this->EFUSERNAME = $EFUSERNAME;
    }

    function set_EFPASSWORD($EFPASSWORD) {
        $this->EFPASSWORD = $EFPASSWORD;
    }

    function set_EINVUSERNAME($EINVUSERNAME) {
        $this->EINVUSERNAME = $EINVUSERNAME;
    }

    function set_EINVPASSWORD($EINVPASSWORD) {
        $this->EINVPASSWORD = $EINVPASSWORD;
    }


    
}