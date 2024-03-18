<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';

//$cnt=0;
//$dealersList = array();
$table = "it_codes";
$db = new DBConn();
//$query = "select id,store_name,tally_name,IF(is_autorefill = 1, 'Yes', 'No') AS is_autorefill, IF(is_closed = 1, 'Yes', 'No') AS is_closed,IF(sbstock_active = 1, 'Yes', 'No') AS Standing_Base_Stock,owner,address,city,zipcode,phone,phone2,email,email2,vat,store_number,pancard_no,min_stock_level,server_change_id,createtime as Store_Create_Time, IF(inactive = 1, 'Yes', 'No') AS inactive,gstin_no from $table where usertype=4 order by createtime";
$query = "select c.id,c.store_name,c.monthlyrent,c.retail_saletally_name,c.retail_sale_cash_name,c.nach_limit,c.retail_sale_card_name,c.UMRN,c.facade,c.carpet,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,c.is_natch_required,c.tally_name,IF(c.is_autorefill = 1, 'Yes', 'No') AS is_autorefill, IF(c.is_closed = 1, 'Yes', 'No') AS is_closed,IF(c.sbstock_active = 1, 'Yes', 'No') AS Standing_Base_Stock,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.pancard_no,c.min_stock_level,c.max_stock_level,c.server_change_id,c.createtime as Store_Create_Time, IF(c.inactive = 1, 'Yes', 'No') AS inactive,c.gstin_no,sd.dealer_discount as Dealer_Discount,(select state from states where id=c.state_id)as state,(select region from region where id=c.region_id) as region ,c.status,c.area,c.location,c.distance,IF(c.is_claim = 1, 'Yes', 'No') as is_claim,IF(c.is_cash = 1, 'Yes', 'No') as is_cash,IF(c.tax_type=1, 'Within Maharashtra','Outside Maharashtra')as tax_type,IF(c.mask_margin=0, 'Regular','55% margin')as mask_margin,IF(c.composite_billing_opted = 1, 'Yes', 'No') AS composite_billing_opted,IF(c.is_tallyxml = 1, 'Yes', 'No') AS is_tallyxml,c.store_type from $table c,it_ck_storediscount sd where usertype=4 and is_closed=0 and c.id=sd.store_id order by createtime";
$alldealersobj = $db->fetchObjectArray($query);
//print_r($alldealersobj);
//return;
//$db->closeConnection();
$objPHPExcel = new PHPExcel();
if (!empty($alldealersobj)) {
    $fpath = createexcel($alldealersobj, $objPHPExcel);
    unset($dealersList);
}

function createexcel($alldealersobj, $objPHPExcel) {

    $sheetIndex = 0;
    // Create new PHPExcel object
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Franchisees below MSL');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Id');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Number');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Tally Name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Is_Autorefill');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Is_Closed');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Standing_Base_Stock');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Owner');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Address');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'City');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Zipcode');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Phone');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Phone2');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Email');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Email2');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Vat');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'GSTIN No');
    $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Dealer Discount');
    $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Pancard No');
    $objPHPExcel->getActiveSheet()->setCellValue('T1', 'Min Stock Level');
    $objPHPExcel->getActiveSheet()->setCellValue('U1', 'Max Stock Level');
    $objPHPExcel->getActiveSheet()->setCellValue('V1', 'Server Change Id');
    $objPHPExcel->getActiveSheet()->setCellValue('W1', 'Store CreateTime');
    $objPHPExcel->getActiveSheet()->setCellValue('X1', 'Inactive');

    $objPHPExcel->getActiveSheet()->setCellValue('Y1', 'State');
    $objPHPExcel->getActiveSheet()->setCellValue('Z1', 'Region');
    $objPHPExcel->getActiveSheet()->setCellValue('AA1', 'Status');
    $objPHPExcel->getActiveSheet()->setCellValue('AB1', 'Area');
    $objPHPExcel->getActiveSheet()->setCellValue('AC1', 'Location');
    $objPHPExcel->getActiveSheet()->setCellValue('AD1', 'Distance');
    $objPHPExcel->getActiveSheet()->setCellValue('AE1', 'Non Claim');
    $objPHPExcel->getActiveSheet()->setCellValue('AF1', 'Cash');
    $objPHPExcel->getActiveSheet()->setCellValue('AG1', 'Store Type');
    $objPHPExcel->getActiveSheet()->setCellValue('AH1', 'Tax Type');
    $objPHPExcel->getActiveSheet()->setCellValue('AI1', 'Margin For Mask');
    $objPHPExcel->getActiveSheet()->setCellValue('AJ1', 'Composite Billing Opted');
    $objPHPExcel->getActiveSheet()->setCellValue('AK1', 'Tally Transfer Feature Enabled');
    $objPHPExcel->getActiveSheet()->setCellValue('AL1', 'Retail Sale Tally Name');
    $objPHPExcel->getActiveSheet()->setCellValue('AM1', 'Retail Sale Cash Name');
    $objPHPExcel->getActiveSheet()->setCellValue('AN1', 'Retail Sale Card Name');

    $objPHPExcel->getActiveSheet()->setCellValue('AO1', 'IS Nach Store');
    $objPHPExcel->getActiveSheet()->setCellValue('AP1', 'UMRN');
    $objPHPExcel->getActiveSheet()->setCellValue('AQ1', 'Cust. To Be Debited');
    $objPHPExcel->getActiveSheet()->setCellValue('AR1', 'Cust. IFSC/MCR');
    $objPHPExcel->getActiveSheet()->setCellValue('AS1', 'Cust. Debit Acc');
    $objPHPExcel->getActiveSheet()->setCellValue('AT1', 'Nach Limit');
    $objPHPExcel->getActiveSheet()->setCellValue('AU1', 'Stores Monthly Rent');
    $objPHPExcel->getActiveSheet()->setCellValue('AV1', 'Store Facade Area');
    

    $maxCarpetLength = 0;
    foreach ($alldealersobj as $dealer) {

        $carpetArray = explode(',', $dealer->carpet); // Split the string by comma
        $numberOfValues = count($carpetArray);
        if ($numberOfValues > $maxCarpetLength) {
            $maxCarpetLength = $numberOfValues;
        }
    }



    $startCombination = "AW";
    $numberOfCombinations = $maxCarpetLength + 1; // Change this to the desired number of combinations

    $currentCombination = $startCombination;
    $combinations = array();

    for ($i = 0; $i < $numberOfCombinations; $i++) {
        $combinations[] = $currentCombination;

        $nextCode = ord($currentCombination[1]) + 1;

        if ($nextCode > ord('Z')) {
            $nextCode = ord('A');
            $currentCombination[0] = chr(ord($currentCombination[0]) + 1);
        }

        $currentCombination[1] = chr($nextCode);
    }

    $cnt = 0;
    foreach ($combinations as $combination) {


        if ($cnt == 0) {
            $objPHPExcel->getActiveSheet()->setCellValue($combination . '1', 'Store Carpet Area');
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue($combination . '1', 'Floor ' . $cnt . 'Carpet Area');
        }
        $cnt++;
        if ($cnt > $maxCarpetLength) {

            $objPHPExcel->getActiveSheet()->setCellValue($combination . '1', 'Total Carpet Area');
        }
    }


    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);

    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AS')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AT')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AU')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Av')->setWidth(20);

    foreach ($combinations as $combination) {

        $objPHPExcel->getActiveSheet()->getColumnDimension($combination)->setWidth(20);
    }
    $objPHPExcel->getActiveSheet()->getColumnDimension($combination)->setWidth(20);

    $colCount = 0;
    $rowCount = 2;

    //foreach ($alldealersobj as $key => $value){
    foreach ($alldealersobj as $dealer) {

        /*      print_r($dealer);
          return; */

        $diff = 0;
        //$arr = explode("::",$value);
        $id = trim($dealer->id);
        $store_name = trim($dealer->store_name);
        $tally_name = trim($dealer->tally_name);
        $is_autorefill = trim($dealer->is_autorefill);
        $is_natch = trim($dealer->is_natch_required);
        $is_natch_required = "";
        if ($is_natch == 0) {
            $is_natch_required = "No";
        } else {
            $is_natch_required = "Yes";
        }
        $is_closed = trim($dealer->is_closed);
        $Standing_Base_Stock = trim($dealer->Standing_Base_Stock);
        $owner = trim($dealer->owner);
        $address = trim($dealer->address);
        $city = trim($dealer->city);
        $zipcode = trim($dealer->zipcode);
        $phone = trim($dealer->phone);
        $phone2 = trim($dealer->phone2);
        $email = trim($dealer->email);
        $email2 = trim($dealer->email2);
        $vat = trim($dealer->vat);
        $store_number = trim($dealer->store_number);
        $pancard_no = trim($dealer->pancard_no);
        $min_stock_level = trim($dealer->min_stock_level);
        $max_stock_level = trim($dealer->max_stock_level);
        $server_change_id = trim($dealer->server_change_id);
        $Store_Create_Time = trim($dealer->Store_Create_Time);
        $inactive = trim($dealer->inactive);
        $gstin_no = trim($dealer->gstin_no);
        $dealer_disc = trim($dealer->Dealer_Discount);

        $state = trim($dealer->state);
        $region = trim($dealer->region);

        $statusid = trim($dealer->status);
        $statusname = StoreStatus::getName($statusid);
        $status = "";
        if ($statusname == 'Unknown') {
            $status = "";
        } else {
            $status = $statusname;
        }
        $area = trim($dealer->area);
        $location = trim($dealer->location);
        $distance = trim($dealer->distance);
        $is_claim = trim($dealer->is_claim);
        $is_cash = trim($dealer->is_cash);

        $storetypeid = trim($dealer->store_type);
        $storetypename = StoreType::getName($storetypeid);
        $storetype = "";
        if ($storetypename == 'Undefine') {
            $storetype = "";
        } else {
            $storetype = $storetypename;
        }
        $tax_type = trim($dealer->tax_type);
        $mask_margin = trim($dealer->mask_margin);
        $composite_billing_opted = trim($dealer->composite_billing_opted);
        $is_tallyxml = trim($dealer->is_tallyxml);

        $retail_saletally_name = trim($dealer->retail_saletally_name);
        $retail_sale_card_name = trim($dealer->retail_sale_cash_name);
        $retail_sale_cash_name = trim($dealer->retail_sale_card_name);
        $UMRN = trim($dealer->UMRN);
        $cust_tobe_debited = trim($dealer->cust_tobe_debited);
        $cust_ifsc_or_mcr = trim($dealer->cust_ifsc_or_mcr);
        $cust_debit_account = trim($dealer->cust_debit_account);
        $monthlyrent = trim($dealer->monthlyrent);
        $facade = trim($dealer->facade);
        $carpet = trim($dealer->carpet);
        $nach_limits = trim($dealer->nach_limit);
        $carpetarr = explode(",", $carpet);

        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_number);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $tally_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $is_autorefill);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $is_closed);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $Standing_Base_Stock);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $owner);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $address);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowCount, $city);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $rowCount, $zipcode);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowCount, $phone);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowCount, $phone2);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowCount, $email);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowCount, $email2);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $rowCount, $vat);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $rowCount, $gstin_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $rowCount, $dealer_disc);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $rowCount, $pancard_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(19, $rowCount, $min_stock_level);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(20, $rowCount, $max_stock_level);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(21, $rowCount, $server_change_id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(22, $rowCount, $Store_Create_Time);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(23, $rowCount, $inactive);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(24, $rowCount, $state);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(25, $rowCount, $region);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(26, $rowCount, $status);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(27, $rowCount, $area);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(28, $rowCount, $location);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(29, $rowCount, $distance);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(30, $rowCount, $is_claim);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(31, $rowCount, $is_cash);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(32, $rowCount, $storetype);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(33, $rowCount, $tax_type);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(34, $rowCount, $mask_margin);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(35, $rowCount, $composite_billing_opted);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(36, $rowCount, $is_tallyxml);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(37, $rowCount, $retail_saletally_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(38, $rowCount, $retail_sale_cash_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(39, $rowCount, $retail_sale_card_name);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(40, $rowCount, $is_natch_required);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(41, $rowCount, $UMRN);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(42, $rowCount, $cust_tobe_debited);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(43, $rowCount, $cust_ifsc_or_mcr);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(44, $rowCount, $cust_debit_account);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(45, $rowCount, $nach_limits);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(46, $rowCount, $monthlyrent);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(47, $rowCount, $facade);

        $colcount = 48;
        $sumcarpet = 0;

        for ($i = 0; $i < $numberOfCombinations; $i++) {
            if (!isset($carpetarr[$i])) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colcount, $rowCount, "");
                $colcount++;
            } else {
                $sumcarpet += $carpetarr[$i];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colcount, $rowCount, $carpetarr[$i]);
                $colcount++;
            }
        }


        if ($sumcarpet == 0) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(--$colcount, $rowCount, "");
        } else {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(--$colcount, $rowCount, $sumcarpet);
        }

        $rowCount++;
    }
}

//echo "Row Count=======>".$rowCount."\n";
$filename = "StoreDetail_" . date('Y-m-d H:i:s') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');