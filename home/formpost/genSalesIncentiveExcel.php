<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

$phpExcel = new PHPExcel();
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle('Sheet1');
 $db = new DBConn();
extract($_POST);
//print_r($singleqtybillsarray);
//print_r($_POST);
//exit();
if (isset($salesman)) {
    if (is_array($salesman[0])) {
        $salesmen = $salesman[0]; // it's a 2D array
    } else {
        $salesmen = $salesman; // it's a 1D array
    }
}

if (isset($totalincentivearray)) {
    if (is_array($totalincentivearray[0])) {
        $totalincentivearray = $totalincentivearray[0]; // it's a 2D array
    } else {
        $totalincentivearray = $totalincentivearray; // it's a 1D array
    }
}

if (isset($singleqtybillsarray)) {
    if (is_array($singleqtybillsarray[0])) {
        $singleqtybillsarray = $singleqtybillsarray[0]; // it's a 2D array
    } else {
        $singleqtybillsarray = $singleqtybillsarray; // it's a 1D array
    }
}
if (isset($singleqtybillvaluessarray)) {
    if (is_array($singleqtybillvaluessarray[0])) {
        $singleqtybillvaluessarray = $singleqtybillvaluessarray[0]; // it's a 2D array
    } else {
        $singleqtybillvaluessarray = $singleqtybillvaluessarray; // it's a 1D array
    }
}


if (isset($singleqtyincentiveamtarray)) {
    if (is_array($singleqtyincentiveamtarray[0])) {
        $singleqtyincentiveamtarray = $singleqtyincentiveamtarray[0]; // it's a 2D array
    } else {
        $singleqtyincentiveamtarray = $singleqtyincentiveamtarray; // it's a 1D array
    }
}

if (isset($multiQtyBillsarray)) {
    if (is_array($multiQtyBillsarray[0])) {
        $multiQtyBillsarray = $multiQtyBillsarray[0]; // it's a 2D array
    } else {
        $multiQtyBillsarray = $multiQtyBillsarray; // it's a 1D array
    }
}

if (isset($multiQtyarray)) {
    if (is_array($multiQtyarray[0])) {
        $multiQtyarray = $multiQtyarray[0]; // it's a 2D array
    } else {
        $multiQtyarray = $multiQtyarray; // it's a 1D array
    }
}

if (isset($multiQtyValuearray)) {
    if (is_array($multiQtyValuearray[0])) {
        $multiQtyValuearray = $multiQtyValuearray[0]; // it's a 2D array
    } else {
        $multiQtyValuearray = $multiQtyValuearray; // it's a 1D array
    }
}

if (isset($multiQtyIncentivearray)) {
    if (is_array($multiQtyIncentivearray[0])) {
        $multiQtyIncentivearray = $multiQtyIncentivearray[0]; // it's a 2D array
    } else {
        $multiQtyIncentivearray = $multiQtyIncentivearray; // it's a 1D array
    }
}


if (isset($firsthundlebillsarray)) {
    if (is_array($firsthundlebillsarray[0])) {
        $firsthundlebillsarray = $firsthundlebillsarray[0]; // it's a 2D array
    } else {
        $firsthundlebillsarray = $firsthundlebillsarray; // it's a 1D array
    }
}

if (isset($firsthurdleqtyarray)) {
    if (is_array($firsthurdleqtyarray[0])) {
        $firsthurdleqtyarray = $firsthurdleqtyarray[0]; // it's a 2D array
    } else {
        $firsthurdleqtyarray = $firsthurdleqtyarray; // it's a 1D array
    }
}

if (isset($firsthurdlevaluearray)) {
    if (is_array($firsthurdlevaluearray[0])) {
        $firsthurdlevaluearray = $firsthurdlevaluearray[0]; // it's a 2D array
    } else {
        $firsthurdlevaluearray = $firsthurdlevaluearray; // it's a 1D array
    }
}

if (isset($firsthurdleincentivearray)) {
    if (is_array($firsthurdleincentivearray[0])) {
        $firsthurdleincentivearray = $firsthurdleincentivearray[0]; // it's a 2D array
    } else {
        $firsthurdleincentivearray = $firsthurdleincentivearray; // it's a 1D array
    }
}

if (isset($secondhundlebillsarray)) {
    if (is_array($secondhundlebillsarray[0])) {
        $secondhundlebillsarray = $secondhundlebillsarray[0]; // it's a 2D array
    } else {
        $secondhundlebillsarray = $secondhundlebillsarray; // it's a 1D array
    }
}

if (isset($secondhurdleqtyarray)) {
    if (is_array($secondhurdleqtyarray[0])) {
        $secondhurdleqtyarray = $secondhurdleqtyarray[0]; // it's a 2D array
    } else {
        $secondhurdleqtyarray = $secondhurdleqtyarray; // it's a 1D array
    }
}

if (isset($secondhurdlevaluearray)) {
    if (is_array($secondhurdlevaluearray[0])) {
        $secondhurdlevaluearray = $secondhurdlevaluearray[0]; // it's a 2D array
    } else {
        $secondhurdlevaluearray = $secondhurdlevaluearray; // it's a 1D array
    }
}

if (isset($secondhurdleincentivearray)) {
    if (is_array($secondhurdleincentivearray[0])) {
        $secondhurdleincentivearray = $secondhurdleincentivearray[0]; // it's a 2D array
    } else {
        $secondhurdleincentivearray = $secondhurdleincentivearray; // it's a 1D array
    }
}


//print_r($salesmen);
//exit();
// Define your dynamic salesman data
//$salesmen = $salesman[0];
//print_r($salesmen);
//exit();

$data = [
   array_merge(['Salesman No. / Parameters'], $salesmen,['Total']),
    
    
    array_merge(['Total Incentive'], $totalincentivearray, [array_sum($totalincentivearray)]),
    
    array_merge(['Single Qty Bills'],$singleqtybillsarray, [array_sum($singleqtybillsarray)]),
    array_merge(['Qty in Single Bills'],$singleqtybillsarray, [array_sum($singleqtybillsarray)]),
    array_merge(['Single Qty Bills value'],$singleqtybillvaluessarray, [array_sum($singleqtybillvaluessarray)]),
    array_merge(['Single Qty Incentive Amt'],$singleqtyincentiveamtarray, [array_sum($singleqtyincentiveamtarray)]),
    
       
    array_merge(['Multiple Qty No Membership Bills'],$multiQtyBillsarray,[array_sum($multiQtyBillsarray)]),
    array_merge(['Qty in multiple bills'],$multiQtyarray,[array_sum($multiQtyarray)]),
    array_merge(['Multiple Qty Bill Value'],$multiQtyValuearray,[array_sum($multiQtyValuearray)]),
   array_merge(['Multiple Qty Incentive Amt'],$multiQtyIncentivearray,[array_sum($multiQtyIncentivearray)]),
    
     
     
     
    
    array_merge(['Membership Bills 1st Hurdle (4999₹)'],$firsthundlebillsarray,[array_sum($firsthundlebillsarray)]),
      array_merge(['Qty 1st Hurdle'],$firsthurdleqtyarray,[array_sum($firsthurdleqtyarray)]),
     array_merge(['Value 1st Hurdle '],$firsthurdlevaluearray,[array_sum($firsthurdlevaluearray)]),
     array_merge(['Membership 1st Hurdle Incentive Amt'],$firsthurdleincentivearray,[array_sum($firsthurdleincentivearray)]),
    
     
      
     array_merge(['Membership Bills 2nd Hurdle (7999₹)'],$secondhundlebillsarray,[array_sum($secondhundlebillsarray)]),
     array_merge(['Qty 2nd Hurdle'],$secondhurdleqtyarray,[array_sum($secondhurdleqtyarray)]),
     array_merge(['Value 2nd Hurdle'],$secondhurdlevaluearray,[array_sum($secondhurdlevaluearray)]),
    array_merge(['Membership 2nd Hurdle Incentive Amt'],$secondhurdleincentivearray,[array_sum($secondhurdleincentivearray)]),
    
    
    
];

// Write data to sheet
$rowNum = 1;
$sheet->getStyle('A1:Z1000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:Z1000')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getColumnDimension('A')->setWidth(50);
foreach ($data as $row) {
    $col = 'A';
    foreach ($row as $cell) {
        $sheet->setCellValue($col.$rowNum, $cell);
        $col++;
    }
    $rowNum++;
}
//$date = new DateTime(); // current time
//echo $date->format('Y-m-d H:i:s');
// Clean output buffer if needed
if (ob_get_length()) ob_end_clean();

$storeid=$db->fetchObject("select store_name from it_codes where id=");
// Headers for Excel file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"Salesman_Incentive_Report.xlsx\"");
header('Cache-Control: max-age=0');

// Output file to browser
$writer = new PHPExcel_Writer_Excel2007($phpExcel);
$writer->save('php://output');
exit;

?>
