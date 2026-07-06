<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

function generateEbillHTML($billno, $store_id) {
 
    extract($_POST);
    $errors = array();
    $success = array();
    $db = new DBConn();
    $conv = new CurrencyConv();

    $query1 = "select o.id , o.store_id , o.user_id , o.salesman_code , o.orderinfo , o.bill_no , o.tickettype , o.quantity , o.amount , o.discount_val , o.discount_pct , o.voucher_amt , o.tax , o.bill_datetime , o.inactive , o.ck_order_id , o.total_taxable_value , o.total_tax_value , o.total_cgst_value , o.total_sgst_value , o.total_igst_value , o.sub_total , o.net_total , o.cust_name , o.cust_phone , o.createtime , o.updatetime ,tc.store_name,tc.address,tc.gstin_no, tc.phone from it_orders o join it_codes tc on o.store_id=tc.id where o.store_id='$store_id' and o.bill_no='$billno'";
    $order = $db->fetchObjectArray($query1);

    $q = $db->fetchObject("select store_id , user_id , salesman_code , orderinfo , bill_no , tickettype , quantity , amount , discount_val , discount_pct , voucher_amt , tax , bill_datetime , inactive , total_taxable_value , total_tax_value , total_cgst_value , total_sgst_value , total_igst_value , sub_total , net_total , cust_name , cust_phone , createtime from it_orders where store_id=$store_id and bill_no='$billno'");
    $ord = $q->orderinfo;

    $json_string = $ord;
    $json_obj = json_decode($json_string, true);

    $data = json_decode($json_string, true);
    $creditNoteUsed = "";
    $creditNoteValue = "";
    $scheme = "";
    $ref_no="";

    if (isset($data['gstin'])) {
        $gstin = $data['gstin'];
        $address = $data['address'];
    }
    
    $header = "";
    $isalter = "";
    $shirtalterbarcode = array();
    $trouserjeansalterbarcode = array();
    $otheralterbarcode = array();

    $qa = $db->fetchObject("select composite_billing_opted from it_codes where id = $store_id");
    $composite_billing_opted = $qa->composite_billing_opted;
    $salesman_code="";
    if ($data && isset($data['ticketlines'])) {
      foreach ($data['ticketlines'] as $ticketline) {
        $salesman_code = $ticketline['salesman_person'];
        break;
      }
    }
    if (isset($salesman_code)) {
        $salesman_code = "S: " .$salesman_code;
    }else{
        $salesman_code = "";
    }

    if (isset($data['creditNoteUsed'])) {
        $creditNoteUsed = $data['creditNoteUsed'];
        $creditNoteValue = $data['creditNoteValue'];
    }
    if (isset($data['ticketType'])) {
        $tickettype = $data['ticketType'];
    }

    if (isset($data['scheme'])) {
        $scheme = $data['scheme'];
    }
    $m_returnMessage = "";
    $m_dTicket = "";
    $total_dis = 0;

    foreach ($order as $orderobj) {
        if (isset($data['header'])) {
            $data['header'] = nl2br($data['header']);
            $header = $data['header'];
        }else{
            $header = "<h3 style='color: #1a202c; font-size: 17px; margin-bottom: 4px; font-weight: bold;'>$orderobj->store_name</h3>
            <p style='margin:0; padding:0;'>$orderobj->address</p>
            <p style='margin:0; padding:0;'><strong>GSTIN:</strong> $orderobj->gstin_no | <strong>Phone:</strong> $orderobj->phone</p>";
        }
        $je = json_decode($orderobj->orderinfo);

        $outputheader = "";
        $outputheaderforitems = "";
        $output = "";
        $net_total = round($orderobj->net_total);
        if (isset($data['proxy'])) {
            $ref_no = 'Ref No.' . $data['proxy'];
        }
        $output = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8' />
    <title>E-Bill</title>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js'></script>
    <style>
        * { box-sizing: border-box; }
        body { background-color: #ffffff; color: #333; font-family: dejavusans, sans-serif; margin: 0; padding: 0; }
        .container { width:85%; margin: 0; padding: 0; background: #ffffff; border: none; }
        .logo-container { text-align: center; margin-bottom: 15px; }
        .block { margin-bottom: 15px; }
        .header-block { text-align: center; border-bottom: 1px dashed #cbd5e1; color: #4a5568; font-size: 13px; line-height: 1.5; padding-bottom: 10px; }
        .summary-table { width: 90%; background: #f8fafc; border-radius: 6px; border: 1px solid #edf2f7; }
        .summary-table td { padding: 12px; vertical-align: top; }
        .summary-label { font-size: 11px; color: #718096; text-transform: uppercase; margin-bottom: 3px; }
        .summary-value { font-size: 18px; color: #1a202c; font-weight: bold; margin: 0; }
        .invoice-meta { font-size: 13px; font-weight: bold; color: #2d3748; padding: 8px 0; border-bottom: 1px solid #edf2f7; }
        .invoice-meta span { font-weight: normal; color: #4a5568; }
        .customer-card { background: #f7fafc; border-left: 4px solid #4a5568; padding: 10px; border-radius: 0 6px 6px 0; font-size: 13px; line-height: 1.4; }
        .customer-card-title { font-weight: bold; margin-bottom: 4px; color: #2d3748; }
        .bill-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .bill-table th { font-size: 12px; color: #718096; font-weight: bold; padding: 4px 0; border-bottom: 2px solid #edf2f7; }
        .item-row td { font-size: 13px; padding: 4px 0; font-weight: bold; color: #2d3748; }
        .details-row td { padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
        .item-details { font-size: 11px; color: #718096; line-height: 1.4; }
        .item-details span { margin-right: 5px; }
        .financial-row td { padding: 4px 0; font-size: 13px; color: #4a5568; }
        .financial-row .label-col { text-align: right; font-weight: bold; padding-right: 5px; }
        .financial-row .val-col { text-align: right; font-weight: bold; color: #1a202c; }
        .grand-total td { font-size: 15px; color: #1a202c; padding: 6px 0; border-top: 2px solid #2d3748; border-bottom: 2px solid #2d3748; }
        .alteration-block { margin-top: 12px; background: #fffaf0; border: 1px solid #feebc8; border-radius: 6px; padding: 12px; }
        .alteration-block h4 { font-size: 13px; color: #c05621; margin: 0 0 6px 0; font-weight: bold; }
        .alteration-table { width: 90%; font-size: 12px; border-collapse: collapse; }
        .alteration-table td { padding: 5px 0; border-bottom: 1px solid #feebc8; }
        .alteration-table td:first-child { font-weight: bold; color: #7b341e; width: 110px; }
        .btn-container { text-align: center; margin: 15px 0; }
        #b2 { background-color: #2b6cb0; color: white; border: none; padding: 12px 24px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; }
        .scheme-text { text-align: center; color: #38a169; font-size: 14px; font-weight: bold; background: #f0fff4; padding: 8px; border-radius: 6px; margin-bottom: 12px; }
        .footer-message { text-align: center; font-family: freeserif, cursive, serif; font-size: 15px; color: #4a5568; margin: 12px 0; }
        .ad-container img { width: 100%; border-radius: 6px; margin: 0px 0; }
        .expiry-text { font-size: 11px; color: #e53e3e; text-align: center; font-weight: bold; margin-top: 5px; }
        #terms { font-size: 12px; color: #718096; text-align: center; text-decoration: underline; cursor: pointer; margin-top: 8px; }
        .dialog { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; border-radius: 8px; padding: 20px; width: 320px; max-width: 90%; z-index: 1000; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); display: none; }
        .dialog h2 { margin-top: 0; font-size: 15px; color: #1a202c; }
        .dialog p { font-size: 12px; color: #4a5568; line-height: 1.5; }
        .dialog button { margin-top: 15px; padding: 8px 12px; border: none; border-radius: 4px; background-color: #2b6cb0; color: #fff; font-weight: bold; cursor: pointer; width: 100%; }
        #termsForPdf { display: none; }
    </style>
</head>
<body>
    <div class='container' id='content'>
        <div style='padding:25px;' class='logo-container'>
            <img src='../images/LK_LOGO.jpg' alt='cklogo' style='width: 100%;' />
        </div>
        
        <div class='block header-block'>
            $header
        </div>
        
        <div class='block'>
            <table class='summary-table'>
                <tr>
                    <td style='text-align: left; border: none;'>
                        <div class='summary-label'>Amount Paid</div>
                        <div class='summary-value'>Rs. $net_total</div>
                    </td>
                    <td style='text-align: right; border: none;'>
                        <div class='summary-label'>$orderobj->bill_datetime</div>
                        <div class='summary-value'>QTY: $orderobj->quantity</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class='invoice-meta'>Invoice No: <span>$orderobj->bill_no</span></div><br>";

        if ((!empty($gstin) || isset($gstin)) && $tickettype == '6') {
            $output .= "<div class='block customer-card'>
                <div class='customer-card-title'>Customer Details</div>
                <div><strong>Name:</strong> $orderobj->cust_name</div>
                <div><strong>Phone:</strong> $orderobj->cust_phone</div>
                <div><strong>GSTIN:</strong> $gstin</div>
                <div><strong>Address:</strong> $address</div>
            </div>";
        }

        if (isset($tickettype) && $tickettype == '0') {
            $output .= "<div class='block customer-card'>
                <div class='customer-card-title'>Customer Details</div>
                <div><strong>Name:</strong> $orderobj->cust_name</div>
                <div><strong>Phone:</strong> $orderobj->cust_phone</div>
            </div>";
        }

        $query2 = "select * from it_order_items oi join it_items i on oi.item_id=i.id join it_categories c on i.ctg_id = c.id where oi.order_id=$orderobj->id and oi.store_id=$orderobj->store_id";
        $orderlines = $db->fetchObjectArray($query2);

        $output .= "<table class='bill-table'>
            <thead>
                <tr>
                    <th style='width: 5%; text-align: center;'>#</th>
                    <th style='width: 55%; text-align: left;'>Description</th>
                    <th style='width: 15%; text-align: center;'>Qty</th>
                    <th style='width: 25%; text-align: right;'>Price</th>
                </tr>
            </thead>
            <tbody>";
            
        $count = 1;
        $total_mrp = 0.0;
        foreach ($orderlines as $orderline) {
//            if($orderline->ctg_id==70 || $orderline->ctg_id==79){
//                $taxamt1=0.18;
//            }else{
                if ($orderline->price > 2800) {
                    $taxamt1 = 0.18;
                } else {
                    $taxamt1 = 0.05;
                }
//            }
            
            $query3 = "select tax_name,tax_rate from it_mrp_taxes where tax_rate=$taxamt1 order by id desc limit 1";
            $taxamt = $db->fetchObject($query3);

            $taxq = "select tax_name from it_category_taxes where tax_rate=$taxamt1 limit 1";
            $taxnamefetch = $db->fetchObject($taxq);
            $taxname = $taxnamefetch->tax_name;
            
            $lamt = $orderline->lineTotal;
            $tax = $lamt - ($lamt / (1 + $taxamt->tax_rate));
            $rate = $orderline->lineTotal - $tax;

            $cgst_tax = $tax / 2;
            $sgst_tax = $tax / 2;
            $igst_tax = $tax;
            $discount_val = $orderline->discount_val;
            if (isset($discount_val)) {
                $total_dis = $total_dis + $discount_val;
            }

            $lineTotal = $orderline->lineTotal;
            $total_mrp += $orderline->MRP * $orderline->quantity;
            
            $output .= "<tr class='item-row'>
                <td style='width: 5%; text-align: center;'>$count</td>
                <td style='width: 55%; text-align: left;'>$orderline->name</td>
                <td style='width: 15%; text-align: center;'>$orderline->quantity</td>
                <td style='width: 25%; text-align: right;'>Rs. " . round($lineTotal, 2) . "</td>
            </tr>
            <tr class='details-row'>
                <td></td>
                <td colspan='3'>
                    <div class='item-details'>
                        <span><strong>Barcode:</strong> $orderline->barcode</span> &nbsp;&nbsp;
                        <span><strong>HSN:</strong> $orderline->hsncode</span><br>";
            
            if($composite_billing_opted == "0"){
                $output .= "<span><strong>CGST:</strong> Rs. " . round($cgst_tax, 2) . "</span> &nbsp;&nbsp;
                    <span><strong>SGST:</strong> Rs. " . round($sgst_tax, 2) . "</span> &nbsp;&nbsp;
                    <span><strong>Tax Total:</strong> Rs. " . round($tax, 2) . " ($taxname)</span><br>
                    <span><strong>Rate:</strong> Rs. " . round($rate, 2) . "</span> &nbsp;&nbsp;
                    <span><strong>MRP:</strong> Rs. " . round($orderline->MRP, 2) . "</span>";
            } else if($composite_billing_opted == "1"){
                $output .= "<span><strong>MRP:</strong> Rs. " . round($orderline->MRP, 2) . "</span>";
            }
            
            if (!empty($discount_val)) {
                $output .= "<br><span style='color: #e53e3e;'><strong>Discount:</strong> Rs. " . round($discount_val, 2) . "</span>";
            }
            
            $output .= "</div></td></tr>";
            $count++;
        }
        $mrptotal = $total_mrp;

        $output .= "<tr class='financial-row'>
            <td colspan='2' class='label-col'>Total Quantity</td>
            <td style='text-align: center; font-weight: bold;'>$orderobj->quantity</td>
            <td></td>
        </tr>
        <tr class='financial-row'>
            <td colspan='3' class='label-col'>MRP Total</td>
            <td class='val-col'>Rs. $mrptotal</td>
        </tr>";

        if($composite_billing_opted == "0"){
            $output .= "<tr class='financial-row'>
                <td colspan='3' class='label-col'>Taxable Value</td>
                <td class='val-col'>Rs. $orderobj->total_taxable_value</td>
            </tr>
            <tr class='financial-row'>
                <td colspan='3' class='label-col'>Total CGST</td>
                <td class='val-col'>Rs. $orderobj->total_cgst_value</td>
            </tr>
            <tr class='financial-row'>
                <td colspan='3' class='label-col'>Total SGST</td>
                <td class='val-col'>Rs. $orderobj->total_sgst_value</td>
            </tr>";
        }
        
        if ($total_dis != 0) {
            $rounded_discount = round($total_dis, 2);
            $output .= "<tr class='financial-row'>
                <td colspan='3' class='label-col' style='color: #e53e3e;'>Discount</td>
                <td class='val-col' style='color: #e53e3e;'>Rs. $rounded_discount</td>
            </tr>";
        }

        if ($data && isset($data['paymentinfo'])) {
            foreach ($data['paymentinfo'] as $payment) {
                $m_dTicket = $payment['m_dTicket'];
                if (isset($payment['m_returnMessage'])) {
                    $m_returnMessage = $payment['m_returnMessage'];
                }
                $m_sName = $payment['m_sName'];
                if ((isset($m_dTicket) || $m_dTicket != "") && (isset($m_sName) && $m_sName == "paperin")) {
                    $output .= "<tr class='financial-row'>
                        <td colspan='3' class='label-col'>Credit note $m_returnMessage used</td>
                        <td class='val-col'>- Rs. $m_dTicket</td>
                    </tr>";
                }
                if ((isset($m_dTicket) || $m_dTicket != "") && (isset($m_sName) && $m_sName == "giftcoupon")) {
                    $output .= "<tr class='financial-row'>
                        <td colspan='3' class='label-col'>Gift Voucher $m_returnMessage used</td>
                        <td class='val-col'>- Rs. $m_dTicket</td>
                    </tr>";
                }
            }
        }

        $output .= "<tr class='financial-row grand-total'>
                <td colspan='3' class='label-col'>Net Amount</td>
                <td class='val-col'>Rs. $net_total</td>
            </tr>

        </tbody>
        </table>";

        if (isset($data['ticketlines'])) {
            foreach ($data['ticketlines'] as $ticketline) {
                if(isset($ticketline['alter'])){
                    $isalter = $ticketline['alter'];
                    if ($isalter == "1") {
                        $brcc = $ticketline['prodcode'];
                        $brcategory = $db->fetchObject("select ctg_id from it_items where barcode = $brcc");
                        if (($brcategory->ctg_id == "20") || ($brcategory->ctg_id == "27") || ($brcategory->ctg_id == "46") || ($brcategory->ctg_id == "2") || ($brcategory->ctg_id == "3")) {
                            array_push($trouserjeansalterbarcode, $brcc);
                        } else if (($brcategory->ctg_id == "8") || ($brcategory->ctg_id == "4") || ($brcategory->ctg_id == "10") || ($brcategory->ctg_id == "1") || ($brcategory->ctg_id == "59") || ($brcategory->ctg_id == "7") || ($brcategory->ctg_id == "5") || ($brcategory->ctg_id == "22") || ($brcategory->ctg_id == "16")) {
                            array_push($shirtalterbarcode, $brcc);
                        } else {
                            array_push($otheralterbarcode, $brcc);
                        }
                    }
                }
            }
        }
            
        if (!empty($shirtalterbarcode) || !empty($trouserjeansalterbarcode) || !empty($otheralterbarcode)) {
            $output .= "<div class='alteration-block'>
                <h4>Alteration Details</h4>
                <table class='alteration-table'>";

            if (!empty($shirtalterbarcode)) {
                $brc = implode(" , ", $shirtalterbarcode);
                $output .= "<tr><td>Shirt</td><td>$brc</td></tr>";
            }

            if (!empty($trouserjeansalterbarcode)) {
                $brc = implode(" , ", $trouserjeansalterbarcode);
                $output .= "<tr><td>Trouser/Jeans</td><td>$brc</td></tr>";
            }

            if (!empty($otheralterbarcode)) {
                $brc = implode(" , ", $otheralterbarcode);
                $output .= "<tr><td>Other</td><td>$brc</td></tr>";
            }

            $output .= "</table></div>";
        }


        if (!empty($scheme) || $scheme != "") {
            $scheme = str_replace(" - ", "<br>", $scheme);
            $scheme = str_replace(". ", ".<br><br>", $scheme);
            $words = explode(' ', $scheme);
            $chunks = array_chunk($words, 4);

            $formatted_scheme = '';
            foreach ($chunks as $chunk) {
                $formatted_scheme .= implode(' ', $chunk) . "<br>";
            }
            $output .= "<div class='scheme-text'>$formatted_scheme</div>";
        }

        if ($data && isset($data['paymentinfo']) && $tickettype != 6) {
            $output .= "<br><div style='font-size:12px; color:#4a5568; line-height:1.4; margin-bottom:12px;'>";
            foreach ($data['paymentinfo'] as $payment) {
                $m_sName = $payment['m_sName'];
                $m_dTicket = $payment['m_dTicket'];
                if ((isset($m_sName) || $m_sName != "")) {
                    if ($m_sName == "magcard") { $m_sName = "card"; }
                    if ($m_sName == "paperin") { $m_sName = "credit note"; }
                    $output .= "Paid by " . ucfirst($m_sName) . " : Rs. $m_dTicket<br>";
                }
            }
            if(!empty($ref_no)){
                $output .= "$ref_no<br>";
                $output .= "            
                
                <H5 colspan='2' style='text-align: left; font-size: 11px; color: #718096; padding-top: 0px;'>$salesman_code</H5><br>";
            }
            $output .= "</div>";
        }
        
        $termsconditions = "I/we hereby certify that my/our registration certificate under the Maharashtra Value Added Tax Act, 2002 is "
                        . "in force on the date on which the sale of goods specified in this tax invoice is made by me/us & "
                        . "that the transaction of sale covered by this tax invoice has been effected by me/us & "
                        . "it shall be accounted for in the turnover of sale while filling of return & the due tax, "
                        . "if any payable on the sale has been paid or shall be paid.<br><br>"
                        . "E & O.E. <br> The garments can be exchanged within 1 (one) week from the date of purchase. "
                        . "Please do not remove the price tag & produce the original bill for exchange. "
                        . "No refund, No colour guarantee. Garments once sold will not be taken back. Working hours: 10.00 a.m. to 9.00 p.m.";
                        
        $output .= "<p class='footer-message'>Thank you for shopping at Linenking.</p>
        <div style='padding:25px;' class='ad-container'>
            <a href='https://linenking.com/'><img src='../images/LK_AD.jpg' alt='ad' /></a>
        </div>
        
        <div id='termsForPdf'>
            <div style='text-align: center;padding:25px; font-weight: bold; margin-top:15px; font-size:13px;'>Terms and Conditions</div>
            <div style='padding: 25px; font-size: 11px; color:#4a5568; line-height:1.4;'>$termsconditions</div>
        </div>
    </div>

    <script>
        function showDialog(dialogId, dialogHtml) {
            let dialog = document.getElementById(dialogId);
            if (!dialog) {
                dialog = document.createElement('div');
                dialog.id = dialogId;
                dialog.className = 'dialog';
                dialog.innerHTML = dialogHtml;
                
                let closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.onclick = function() {
                    dialog.style.display = 'none';
                };
                dialog.appendChild(closeButton);
                document.body.appendChild(dialog);
            }
            dialog.style.display = 'block';
        }

        document.getElementById('b2').addEventListener('click', function () {
            var element = document.getElementById('content');
            var b2 = document.getElementById('b2');
            b2.style.display = 'none';
            document.getElementById('termsForPdf').style.display = 'block';

            var opt = {
                margin:       [0.3, 0.3, 0.3, 0.3],
                filename:     'bill_$orderobj->bill_no.pdf',
                image:        { type: 'jpeg', quality: 1 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save().then(function() {
                b2.style.display = 'block';
                document.getElementById('termsForPdf').style.none = 'none';
            });
        });
    </script>
</body>
</html>";
    }

    // --- Server-Side Backend Processing & Save PDF to Local Location ---
    require_once dirname(__FILE__) . "/../Classes/html2pdf/html2pdf.class.php";
    try {
        // Strip scripts, dynamic event behaviors, and interactives that break HTML2PDF compilation
        $clean_output = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $output);
        $clean_output = preg_replace('/onclick\s*=\s*"[^"]*"/is', '', $clean_output);
        $clean_output = preg_replace('#<button(.*?)>(.*?)</button>#is', '', $clean_output);
        
        // Setup engine structure
        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->writeHTML($clean_output);
        
        // Generate and safely record to path destination
        $localPdfPath =  "../ebill/bill_$store_id"."-" . $billno . ".pdf";
        $html2pdf->Output($localPdfPath, "F");

        $pdflink = DEF_SITEURL . "ebill/bill_$store_id"."-" . $billno . ".pdf"; // Default fallback


        // WhatsApp Sending Logic
        $token = "7f10922d0999425f96d5ff6697c0681d";
        $channelId = "6929420b1964d800dc1b9488";
        $cust_phone = isset($orderobj->cust_phone) ? $orderobj->cust_phone : "";
        $cust_name = isset($orderobj->cust_name) ? $orderobj->cust_name : "Customer";
        
        if (!empty($cust_phone)) {
            if (strlen($cust_phone) == 10) {
                $cust_phone = "91" . $cust_phone;
            }
            $payload = [
                "messaging_product" => "whatsapp",
                "to" => $cust_phone,
                "type" => "template",
                "template" => [
                    "name" => "lk_purchase_invoice_new",
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "document",
                                    "document" => [
                                        "link" => $pdflink,
                                        "filename" => "bill_$store_id"."-" . $billno . ".pdf"
                                    ]
                                ]
                            ]
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => "Sir/Madam"
                                ],
                                [
                                    "type" => "text",
                                    "text" => $billno
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $ch = curl_init("https://messaginghub.solutions/relaybridge/api/v1/meta/".$channelId."/messages");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-API-KEY: ".$token,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            curl_close($ch);
        }

    } catch (HTML2PDF_exception $e) {
        file_put_contents("../tmp/pdf_error.log", $e->getMessage() . "\n", FILE_APPEND);
    }

    return $output;
}
?>