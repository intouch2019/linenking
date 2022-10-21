<?php

//require_once "../../it_config.php";
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

        $st_date = "bill_datetime >= '2022-04-01 00:00:00' and bill_datetime <= '2022-05-01 23:59:59'";

        while (true) {

            $sto1 = $db->fetchObjectArray("select o.id as id,o.store_id as store_id,c.store_name as store_name,c.usertype as usertype,c.store_type as store_type,c.is_bhmtallyxml as is_bhmtallyxml,o.user_id as user_id,o.salesman_code as salesman_code ,o.bill_no as bill_no,o.tickettype as tickettype,o.quantity as quantity,o.amount as amount,o.discount_val as discount_val,o.discount_pct as discount_pct,o.voucher_amt as voucher_amt,o.tax as tax,o.bill_datetime as bill_datetime,o.inactive as inactive,o.ck_order_id as ck_order_id,o.total_taxable_value as total_taxable_value,o.total_tax_value as total_tax_value,o.total_cgst_value as total_cgst_value,o.total_sgst_value as total_sgst_value,o.total_igst_value as total_igst_value,o.sub_total as sub_total,o.net_total as net_total,o.cust_name as cust_name,o.cust_phone as cust_phone,c.Area as Area,c.city as city,c.Location as Location, c.status as status,if((select state from states where id=c.state_id) is null,'-', (select state from states where id=c.state_id)) as state ,if((select region from region where id=c.region_id) is null,'-', (select region from region where id=c.region_id)) as region,o.createtime as createtime,o.updatetime as updatetime from it_orders o,it_codes c where $storeClause  and o.store_id = c.id and o.is_sent =0 and $st_date  limit 100");

            if (isset($sto1) && !empty($sto1)) {


                foreach ($sto1 as $st) {

                    $mo = date('F', strtotime($st->bill_datetime)) . "-" . date('Y', strtotime($st->bill_datetime));

                    $salesman_cde = "";
                    $disc_val = "";
                    $disc_pct = "";
                    $vou_amt = "";
                    $usr_type = "";
                    $stre_type = "";
                    $is_bhmtally = "";
                    $usr_id = "";
                    $billl_no = "";
                    $tickettypee = "";
                    $quantityy = "";
                    $amountt = "";
                    $taxx = "";
                    $inactiv = "";
                    $ckorder_id = "";
                    $total_taxable_valu = "";
                    $total_tax_valu = "";
                    $total_cgst_valu = "";
                    $total_sgst_valu = "";
                    $total_igst_valu = "";
                    $sub_ttl = "";
                    $net_ttl = "";
                    $cust_nme = "";
                    $cust_phne = "";
                    $Ara = "";
                    $cty = "";
                    $Loction = "";
                    $stus = "";
                    $stte = "";
                    $regon = "";
                    $stor_id = "";
                    $storenam = "";

                    if ($st->store_id != null) {
                        $stor_id = "store_id=" . $st->store_id . ",";
                    }
                    if ($st->store_name != null) {
                        $storenam = "store_name='" . $st->store_name . "',";
                    }
                    if ($st->salesman_code != null) {
                        $salesman_cde = "salesman_code=" . $st->salesman_code . ",";
                    }
                    if ($st->discount_val != null) {
                        $disc_val = "discount_val=" . $st->discount_val . ",";
                    }
                    if ($st->discount_pct != null) {
                        $disc_pct = "discount_pct=" . $st->discount_pct . ",";
                    }
                    if ($st->voucher_amt != null) {
                        $vou_amt = "voucher_amt=" . $st->voucher_amt . ",";
                    }
                    if ($st->usertype != null) {
                        $usr_type = "usertype=" . $st->usertype . ",";
                    }
                    if ($st->store_type != null) {
                        $stre_type = "store_type=" . $st->store_type . ",";
                    }
                    if ($st->is_bhmtallyxml != null) {
                        $is_bhmtally = "is_bhmtallyxml=" . $st->is_bhmtallyxml . ",";
                    }
                    if ($st->user_id != null) {
                        $usr_id = "user_id=" . $st->user_id . ",";
                    }
                    if ($st->bill_no != null) {
                        $billl_no = "bill_no='" . $st->bill_no . "',";
                    }
                    if ($st->tickettype != null) {
                        $tickettypee = "tickettype=" . $st->tickettype . ",";
                    }
                    if ($st->quantity != null) {
                        $quantityy = "quantity=" . $st->quantity . ",";
                    }
                    if ($st->amount != null) {
                        $amountt = "amount=" . $st->amount . ",";
                    }
                    if ($st->tax != null) {
                        $taxx = "tax=" . $st->tax . ",";
                    }
                    if ($st->inactive != null) {
                        $inactiv = "inactive=" . $st->inactive . ",";
                    }
                    if ($st->ck_order_id != null) {
                        $ckorder_id = "ck_order_id=" . $st->ck_order_id . ",";
                    }
                    if ($st->total_taxable_value != null) {
                        $total_taxable_valu = "total_taxable_value=" . $st->total_taxable_value . ",";
                    }
                    if ($st->total_tax_value != null) {
                        $total_tax_valu = "total_tax_value=" . $st->total_tax_value . ",";
                    }
                    if ($st->total_cgst_value != null) {
                        $total_cgst_valu = "total_cgst_value=" . $st->total_cgst_value . ",";
                    }
                    if ($st->total_sgst_value != null) {
                        $total_sgst_valu = "total_sgst_value=" . $st->total_sgst_value . ",";
                    }
                    if ($st->total_igst_value != null) {
                        $total_igst_valu = "total_igst_value=" . $st->total_igst_value . ",";
                    }
                    if ($st->sub_total != null) {
                        $sub_ttl = "sub_total=" . $st->sub_total . ",";
                    }
                    if ($st->net_total != null) {
                        $net_ttl = "net_total=" . $st->net_total . ",";
                    }
                    if ($st->cust_name != null) {
                        $cust_nme = "cust_name='" . $st->cust_name . "',";
                    }
                    if ($st->cust_phone != null) {
                        $cust_phne = "cust_phone='" . $st->cust_phone . "',";
                    }
                    if ($st->Area != null) {
                        $Ara = "Area='" . $st->Area . "',";
                    }
                    if ($st->city != null) {
                        $cty = "city='" . $st->city . "',";
                    }
                    if ($st->Location != null) {
                        $Loction = "Location='" . $st->Location . "',";
                    }
                    if ($st->status != null) {
                        $stus = "status='" . $st->status . "',";
                    }
                    if ($st->state != null) {
                        $stte = "state='" . $st->state . "',";
                    }
                    if ($st->region != null) {
                        $regon = "region='" . $st->region . "',";
                    }

                    $upq = $db->fetchObject("select id from it_salesreports where id=$st->id");
                    if (isset($upq) && !empty($upq)) {




                        $upq1 = $db->execUpdate("update it_salesreports set $stor_id $storenam $usr_type $stre_type $is_bhmtally $usr_id $salesman_cde $billl_no  $tickettypee $quantityy $amountt $disc_val $disc_pct $vou_amt $taxx bill_datetime='$st->bill_datetime',month='$mo',$inactiv $ckorder_id $total_taxable_valu $total_tax_valu $total_cgst_valu $total_sgst_valu $total_igst_valu $sub_ttl $net_ttl $cust_nme $cust_phne $Ara $cty  $Loction $stus $stte $regon updatetime=now() where id=$st->id");

                        $deliqry = $db->execQuery("delete from it_salereport_items where order_id=$st->id");
                    } else {

                        $sto2 = $db->execInsert("insert into it_salesreports set id=$st->id,$stor_id $storenam $usr_type $stre_type $is_bhmtally $usr_id $salesman_cde $billl_no  $tickettypee $quantityy $amountt $disc_val $disc_pct $vou_amt $taxx bill_datetime='$st->bill_datetime',month='$mo',$inactiv $ckorder_id $total_taxable_valu $total_tax_valu $total_cgst_valu $total_sgst_valu $total_igst_valu $sub_ttl $net_ttl $cust_nme $cust_phne $Ara $cty  $Loction $stus $stte $regon createtime=now()");
                    }

                    $oii = $db->fetchObjectArray("select i.id as id,i.store_id as store_id,i.order_id as order_id,i.item_id as item_id,a.ctg_id  as itemctg,i.barcode as barcode,i.hsncode as hsncode,a.brand_id as brand_id,a.size_id as size_id,a.fabric_type_id as fabric_type_id,a.material_id as material_id,a.prod_type_id as prod_type_id,a.mfg_id as mfg_id,a.design_no as design_no,a.style_id as style,i.price as price,i.quantity as quantity,i.discount_val as discount_val,i.discount_pct  as discount_pct ,i.cgst_amount as cgst_amount,i.sgst_amount as sgst_amount,i.igst_amount as igst_amount,i.linetotal as linetotal,i.tax as tax,i.createtime as createtime from it_order_items i,it_items a,it_categories c,it_styles s where  i.order_id=$st->id and i.item_id = a.id and a.ctg_id = c.id and a.style_id =s.id");

                    foreach ($oii as $oi) {

                        $disc_val2 = "";
                        $disc_pct = "";
                        $cgst_amt = "";
                        $sgst_amt = "";
                        $igst_amt = "";
                        $taxx = "";
                        $stre_id = "";
                        $orderr_id = "";
                        $itemm_id = "";
                        $categoryy = "";
                        $barcodee = "";
                        $hsncodee = "";
                        $brand_idd = "";
                        $size_idd = "";
                        $fabric_type_idd = "";
                        $material_idd = "";
                        $prod_type_idd = "";
                        $mfg_idd = "";
                        $design_noo = "";
                        $stylee = "";
                        $pricee = "";
                        $quanty = "";
                        $linetotall = "";

                        if ($oi->store_id != null) {
                            $stre_id = "store_id=" . $oi->store_id . ",";
                        }
                        if ($oi->order_id != null) {
                            $orderr_id = "order_id=" . $oi->order_id . ",";
                        }
                        if ($oi->discount_val != null) {
                            $disc_val2 = "discount_val=" . $oi->discount_val . ",";
                        }
                        if ($oi->discount_pct != null) {
                            $disc_pct = "discount_pct=" . $oi->discount_pct . ",";
                        }
                        if ($oi->cgst_amount != null) {
                            $cgst_amt = "cgst_amount=" . $oi->cgst_amount . ",";
                        }
                        if ($oi->sgst_amount != null) {
                            $sgst_amt = "sgst_amount=" . $oi->sgst_amount . ",";
                        }
                        if ($oi->igst_amount != null) {
                            $igst_amt = "igst_amount=" . $oi->igst_amount . ",";
                        }
                        if ($oi->tax != null) {
                            $taxx = "tax=" . $oi->tax . ",";
                        }
                        if ($oi->item_id != null) {
                            $itemm_id = "item_id=" . $oi->item_id . ",";
                        }
                        if ($oi->itemctg != null) {
                            $categoryy = "category=" . $oi->itemctg . ",";
                        }
                        if ($oi->barcode != null) {
                            $barcodee = "barcode='" . $oi->barcode . "',";
                        }
                        if ($oi->hsncode != null) {
                            $hsncodee = "hsncode='" . $oi->hsncode . "',";
                        }
                        if ($oi->brand_id != null) {
                            $brand_idd = "brand_id=" . $oi->brand_id . ",";
                        }
                        if ($oi->size_id != null) {
                            $size_idd = "size_id=" . $oi->size_id . ",";
                        }
                        if ($oi->fabric_type_id != null) {
                            $fabric_type_idd = "fabric_type_id=" . $oi->fabric_type_id . ",";
                        }
                        if ($oi->material_id != null) {
                            $material_idd = "material_id=" . $oi->material_id . ",";
                        }
                        if ($oi->prod_type_id != null) {
                            $prod_type_idd = "prod_type_id=" . $oi->prod_type_id . ",";
                        }
                        if ($oi->mfg_id != null) {
                            $mfg_idd = "mfg_id=" . $oi->mfg_id . ",";
                        }
                        if ($oi->design_no != null) {
                            $design_noo = "design_no='" . $oi->design_no . "',";
                        }
                        if ($oi->style != null) {
                            $stylee = "style=" . $oi->style . ",";
                        }
                        if ($oi->price != null) {
                            $pricee = "price=" . $oi->price . ",";
                        }
                        if ($oi->quantity != null) {
                            $quanty = "quantity=" . $oi->quantity . ",";
                        }
                        if ($oi->linetotal != null) {
                            $linetotall = "linetotal='" . $oi->linetotal . "',";
                        }



                        $ri = $db->execInsert("insert into it_salereport_items set id=$oi->id,$stre_id $orderr_id $itemm_id $categoryy $barcodee $hsncodee $brand_idd $size_idd $fabric_type_idd $material_idd  $prod_type_idd $mfg_idd $design_noo $stylee $pricee $quanty $disc_val2 $disc_pct $cgst_amt $sgst_amt $igst_amt $linetotall $taxx createtime=now()");
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
