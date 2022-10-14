<?php


ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once "lib/db/DBConn.php";
//require_once "session_check.php";
extract($_GET);
//print_r($_GET);
//exit();

date_default_timezone_set("Asia/Calcutta");
//RG All Working Fine
echo " Start Time - " . date("Y-m-d H:i:sa") . "<br>";

try {
    $db = new DBConn();
    $json_objs = array();

    $store_id = isset($_GET['sid']) ? ($_GET['sid']) : false;

    $store_id = "-1";

    if (isset($store_id) && trim($store_id) != "") {

        if ($store_id == "-1") {

            $storeClause = " c.usertype ='4'";
        } else {
            $storeClause = " o.store_id in ($store_id) ";
        }

//    }else{
//         
//        $store_id = "-1";
//         $storeClause = " c.usertype ='4'";   
//         print "<br> STORE STANDING STOCK QRY: $store_id";
//    }


        while (true) {

            $sto1 = $db->fetchObjectArray("select o.id as id,o.store_id as store_id,c.store_name as store_name,c.usertype as usertype,c.store_type as store_type,c.is_bhmtallyxml as is_bhmtallyxml,m.name as mfg_by,o.user_id as user_id,o.salesman_code as salesman_code ,o.bill_no as bill_no,o.tickettype as tickettype,o.quantity as quantity,o.amount as amount,o.discount_val as discount_val,o.discount_pct as discount_pct,o.voucher_amt as voucher_amt,o.tax as tax,o.bill_datetime as bill_datetime,CONCAT(monthname(o.bill_datetime),'-',year(o.bill_datetime)) as month,o.inactive as inactive,o.ck_order_id as ck_order_id,o.total_taxable_value as total_taxable_value,o.total_tax_value as total_tax_value,o.total_cgst_value as total_cgst_value,o.total_sgst_value as total_sgst_value,o.total_igst_value as total_igst_value,o.sub_total as sub_total,o.net_total as net_total,o.cust_name as cust_name,o.cust_phone as cust_phone,c.Area as Area,c.city as city,c.Location as Location, c.status as status,if((select state from states where id=c.state_id) is null,'-', (select state from states where id=c.state_id)) as state ,if((select region from region where id=c.region_id) is null,'-', (select region from region where id=c.region_id)) as region,o.createtime as createtime,o.updatetime as updatetime from it_orders o,it_codes c,it_mfg_by m where $storeClause  and o.store_id = c.id and o.is_sent =0 and o.bill_datetime >= '2022-04-01 00:00:00' limit 100");

            if (isset($sto1) && !empty($sto1)) {

                foreach ($sto1 as $st) {

                    $sto2 = $db->execInsert("insert into it_salesreports set id=$st->id,store_id=$st->store_id,store_name='$st->store_name',usertype=$st->usertype,store_type=$st->store_type,is_bhmtallyxml=$st->is_bhmtallyxml,mfg_by='$st->mfg_by',user_id='$st->user_id',salesman_code='$st->salesman_code',bill_no='$st->bill_no',tickettype=$st->tickettype,quantity=$st->quantity,amount=$st->amount,discount_val='$st->discount_val',discount_pct='$st->discount_pct',voucher_amt='$st->voucher_amt',tax='$st->tax',bill_datetime='$st->bill_datetime',month='$st->month',inactive=$st->inactive,ck_order_id='$st->ck_order_id',total_taxable_value='$st->total_taxable_value',total_tax_value='$st->total_tax_value',total_cgst_value='$st->total_cgst_value',total_sgst_value='$st->total_sgst_value',total_igst_value='$st->total_igst_value',sub_total='$st->sub_total',net_total='$st->net_total',cust_name='$st->cust_name',cust_phone='$st->cust_phone',area ='$st->Area',city ='$st->city',location ='$st->Location',status='$st->status',state='$st->state',region='$st->region',createtime=now()");

                    $oii = $db->fetchObjectArray("select i.id as id,i.store_id as store_id,i.order_id as order_id,i.item_id as item_id,a.ctg_id  as itemctg,i.barcode as barcode,i.hsncode as hsncode,a.brand_id as brand_id,a.size_id as size_id,a.fabric_type_id as fabric_type_id,a.material_id as material_id,a.prod_type_id as prod_type_id,a.mfg_id as mfg_id,a.design_no as design_no,a.style_id as style,i.price as price,i.quantity as quantity,i.discount_val as discount_val,i.discount_pct  as discount_pct ,i.cgst_amount as cgst_amount,i.sgst_amount as sgst_amount,i.igst_amount as igst_amount,i.linetotal as linetotal,i.tax as tax,i.createtime as createtime from it_order_items i,it_items a,it_categories c,it_styles s where  i.order_id=$st->id and i.item_id = a.id and a.ctg_id = c.id and a.style_id =s.id");

                    foreach ($oii as $oi) {

                        $ri = $db->execInsert("insert into it_salereport_items set id=$oi->id,store_id=$oi->store_id,order_id=$oi->order_id,item_id=$oi->item_id,category=$oi->itemctg,barcode='$oi->barcode',hsncode='$oi->hsncode',brand_id=$oi->brand_id,size_id =$oi->size_id,fabric_type_id=$oi->fabric_type_id,material_id=$oi->material_id,prod_type_id=$oi->prod_type_id,mfg_id=$oi->mfg_id,design_no='$oi->design_no',style=$oi->style,price=$oi->price,quantity=$oi->quantity,discount_val='$oi->discount_val',discount_pct='$oi->discount_pct',cgst_amount='$oi->cgst_amount',sgst_amount='$oi->sgst_amount',igst_amount='$oi->igst_amount',linetotal='$oi->linetotal',tax='$oi->tax',createtime=now()");
                    }

                    $ordup = $db->execUpdate("update it_orders set is_sent=1 where id =$st->id");
                }
            } else {
                break;
            }
        }
        echo 'success...';
    }
} catch (Exception $ex) {
    print "1::Error" . $ex->getMessage();
}
echo " end Time - " . date("Y-m-d H:i:sa") . "<br>";
