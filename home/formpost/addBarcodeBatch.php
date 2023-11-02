<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

$errors=array();
$success=array();
$_SESSION['form_post'] = $_POST;
if (!isset($mfg_by) || trim($mfg_by) == "") { $errors['mfg_by']='Please select a value for "Manufactured By"'; }
if (!isset($category) || trim($category) == "") { $errors['category']='Please select a value for "Category"'; }
if (!isset($design_no) || trim($design_no) == "") { $errors['design_no']='Please select a value for "Design No"'; }
if (!isset($num_units) || trim($num_units) == "") { $errors['num_units']='Please select a value for "Units"'; }
else if (!ctype_digit(strval($num_units))) { $errors['num_units']='Please enter a number for "Units"'; }
if (!isset($mrp) || trim($mrp) == "") { $errors['mrp']='Please select a value for "MRP"'; }
else if (!ctype_digit(strval($mrp))) { $errors['mrp']='Please enter a number for "MRP"'; }
if (!isset($brands) || count($brands) == 0) { $errors['brands']='Please select 1 or more values for "Brands"'; }
else { sort($brands); }
if (!isset($styles) || count($styles) == 0) { $errors['styles']='Please select 1 or more values for "Styles"'; }
else { sort($styles); }
if (!isset($sizes) || count($sizes) == 0) { $errors['sizes']='Please select 1 or more values for "Sizes"'; }
else { sort($sizes); }
if (!isset($prod_types) || count($prod_types) == 0) { $errors['prod_types']='Please select 1 or more values for "Production Types"'; }
else { sort($prod_types); }
if (!isset($materials) || count($materials) == 0) { $errors['materials']='Please select 1 or more values for "Materials"'; }
else { sort($materials); }
if (!isset($fabric_types) || count($fabric_types) == 0) { $errors['fabric_types']='Please select 1 or more values for "Fabric Types"'; }
else { sort($fabric_types); }
if (!isset($stocktype) || trim($stocktype) == "") { $errors['stocktype']='Please select a value for "stocktype"'; }
//if (!isset($hsncode) || trim($hsncode) == "") { $errors['hsncode']='Please a value for "Hsncode"'; }
$design_no=strtoupper($design_no);
$db = new DBConn();
$serverCh = new clsServerChanges();
$signature=md5(serialize(array($mfg_by,$category,$design_no,$mrp,$brands,$styles,$sizes,$prod_types,$materials,$fabric_types)));
$obj = $db->fetchObject("select * from it_barcode_batches where signature='$signature'");
if ($obj) { $errors['status'] = 'You have already created a batch for these values <a href="barcode/batch/id='.$obj->id.'/">Batch '.$obj->id.'</a>'; }
if (count($errors) == 0) {
try {                
        //mrp taxes
        $mqry = "select * from it_mrp_taxes where mrp = $mrp ";
        $mobj = $db->fetchObject($query);
        if(isset($mobj) && ! empty($mobj) && $mobj != null){
           //do nothing 
        }else{
            //insert
            if(trim($mrp) <= "1050"){
                    $tax_name = "GST 5%";
                    $tax_percent = "5";
                    $tax_rate = 0.05;
                    $validfrom = "2017-07-01 00:00:00";
              }else{
                    $tax_name = "GST 12%";
                    $tax_percent = "12";
                    $tax_rate = 0.12;
                    $validfrom = "2017-07-01 00:00:00";
              }
              
              $query = "insert into it_mrp_taxes set mrp = $mrp , tax_name = '$tax_name' , tax_percent = $tax_percent , tax_rate = $tax_rate , validfrom = '$validfrom' , createtime = now() ";                           
              $inserted_id = $db->execInsert($query); 
              
              //push to server changes
                $query = "select * from it_mrp_taxes where id = $inserted_id ";
                $obj = $db->fetchObject($query);
                if(isset($obj) && !empty($obj) && $obj != null){
                       $server = json_encode($obj);
                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                       $ser_type = changeType::mrptaxes;                                                         
                       //$serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
                       $serverCh->insert($ser_type, $server_ch ,$obj->id);
                }
              
        }
        $num_units= trim($num_units);
        $design_no= trim($design_no);
	$design_no=$db->safe($design_no);
        //$hsncode_db = $db->safe(trim($hsncode));
	$query = "insert into it_barcode_batches set mfg_by_id=$mfg_by, category_id=$category, design_no=$design_no, MRP=$mrp";
	$query .= ", brand_ids='".join(",",$brands)."'";
	$query .= ", style_ids='".join(",",$styles)."'";
	$query .= ", size_ids='".join(",",$sizes)."'";
	$query .= ", prod_type_ids='".join(",",$prod_types)."'";
	$query .= ", material_ids='".join(",",$materials)."'";
	$query .= ", fabric_type_ids='".join(",",$fabric_types)."'";
	$query .= ", signature='$signature'";
	$batch_id=$db->execInsert($query);
	
	foreach ($brands as $brand_id) {
	foreach ($styles as $style_id) {
	foreach ($sizes as $size_id) {
	foreach ($prod_types as $prod_type_id) {
	foreach ($materials as $material_id) {
	foreach ($fabric_types as $fabric_type_id) {
		$query = "insert into it_items set batch_id=$batch_id,mfg_id=$mfg_by,ctg_id=$category,design_no=$design_no,MRP=$mrp,num_units=$num_units";
		$query .= ",brand_id=$brand_id,style_id=$style_id,size_id=$size_id";
		$query .= ",prod_type_id=$prod_type_id";
                $query .= ",stock_type=$stocktype";
		$query .= ",material_id=$material_id";
		$query .= ",fabric_type_id=$fabric_type_id";
                //$query .= ",hsncode=$hsncode_db";
               // $query .= ",hsn_id=$hsncode";
                
		$item_id=$db->execInsert($query);
		$designfetch = "select * from it_ck_designs where design_no = $design_no and ctg_id=$category";
                $getdesign = $db->fetchObject($designfetch);
                if (count($getdesign) == 0) {
                    $designinsert = "insert into it_ck_designs set design_no=$design_no, ctg_id=$category, active=0";
                    $des_id = $db->execInsert($designinsert);
                    $obj = $db->fetchObject("select * from it_ck_designs where id = $des_id ");
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::ck_designs;
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
                }else{
                    $des_id = $getdesign->id;
                }
		$barcode=getEANCode($item_id);
		$db->execUpdate("update it_items set barcode='$barcode',design_id = $des_id where id=$item_id");
                $obj1 = $db->fetchObject("select * from it_items where id = $item_id");                
//                $obj1 = $db->fetchObject("select i.id,i.batch_id,i.barcode,i.mfg_id,i.ctg_id,d.id as design_id,i.MRP,i.brand_id,i.style_id,i.size_id,i.prod_type_id,i.material_id,i.fabric_type_id from it_items i , it_ck_designs d where i.design_no = d.design_no  and  i.ctg_id = d.ctg_id and i.id = $item_id ");                
                if(isset($obj1)){
                $server = json_encode($obj1);
                $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                $ser_type = changeType::items;
                $serverCh->insert($ser_type, $server_ch,$obj1->id);
        }
	}
	}
	}
	}
	}
	}
         $queryy = "insert into it_new_barcode_batch set batch_id=$batch_id,file_name='lin$batch_id.csv'";
        $new_bar_batch = $db->execInsert($queryy);

        $fp = fopen("../cron/b_batch/lin$batch_id.csv", "w");
        $result = $db->execQuery("select i.batch_id,i.id, i.barcode, i.num_units, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id left outer join it_new_barcode_batch k on i.batch_id=k.batch_id where i.barcode not like '1%' and i.barcode not like '001%' and i.barcode not like '2%' and k.batch_id=$batch_id  order by i.id;");

        fputs($fp, "Batch Id,Barcode,Manufacturer,Product,Design,MRP,Brand,Style,Size,Production Type,Material,Fabric Type,Units\n");
        while ($item = $result->fetch_object()) {
            fputs($fp, "$item->batch_id,$item->barcode,$item->mfg_by,$item->category,$item->design_no,$item->MRP,$item->brand,$item->style,$item->size,$item->prod_type,$item->material,$item->fabric_type,$item->num_units\n");
       
             $queryyy= "insert into it_new_barcode_batch_items set  bar_id=$new_bar_batch , batch_id='$batch_id',Barcode='$item->barcode', Manufacturer='$item->mfg_by',Product='$item->category',Design='$item->design_no',MRP='$item->MRP',Brand='$item->brand' ,Style='$item->style',Size ='$item->size',Production_Type = '$item->prod_type',Material = '$item->material',Fabric_Type = '$item->fabric_type',Units='$item->num_units'";
       
                 $new_bar_batchh = $db->execInsert($queryyy);
            
        }

        fclose($fp);
        system("../cron/b_batch/lin$batch_id.csv");
        
        
        
	$redirect="batch/id=$batch_id/";
	unset($_SESSION['form_post']);
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add batch:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect="newbatch";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
session_write_close();
header("Location: ".DEF_SITEURL."barcode/$redirect");
exit;

function getEANCode($item_id) {
$code = "890"; // EAN-India prefix
$code .= sprintf("%09d",$item_id); // 9 digits based on the item-id
/* last digit is a checksum calculated as 
The checksum is a Modulo 10 calculation:

Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
Multiply this result by 3.
Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
Sum the results of steps 2 and 3.
The check character is the smallest number which, when added to the result in step 4, produces a multiple of 10.
*/
$total=0;
for ($i=1; $i<=12;$i++) {
        $digit=intval(substr($code,$i-1,1));
        if (($i % 2) > 0) { // odd
                $total += $digit;
        } else { // even
                $total += 3 * $digit;
        }
}
$checksum = 10 - ($total % 10);
if ($checksum == 10) $checksum = 0;
return "$code$checksum";
}

?>
