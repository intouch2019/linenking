<?php

require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

function getCurrentStoreStock($storeid, $catid, $styleid, $sizeid) {
    $db = new DBConn();
    $store_stock_curr_qty_qry_style_sizewise = "select sum(quantity) as quantity from  it_current_stock where store_id = $storeid and ctg_id=$catid and style_id=$styleid and size_id=$sizeid";
    $store_stock_intransit_qty_qry_style_sizewise = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and oi.item_code=i.barcode and  o.is_procsdForRetail = 0 and o.invoice_type in ( 0 , 6 ) and o.store_id = $storeid and i.ctg_id=$catid and i.style_id=$styleid and i.size_id=$sizeid";
    $store_stock_active_picking_pickngcmplt_qty_qry_style_sizewise = "SELECT SUM(oi.order_qty) as total_qty FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.store_id = $storeid and i.ctg_id=$catid and i.style_id=$styleid and i.size_id=$sizeid AND o.status IN (" . OrderStatus::Active . ", " . OrderStatus::Picking . ", " . OrderStatus::Picking_Complete . ")";
//    print_r($store_stock_curr_qty_qry_style_sizewise);
//    print_r($store_stock_intransit_qty_qry_style_sizewise);
//    print_r($store_stock_active_picking_pickngcmplt_qty_qry_style_sizewise);
    
    $store_stock_curr_qty1 = $db->fetchObject($store_stock_curr_qty_qry_style_sizewise);
    $store_stock_intransit_qty1 = $db->fetchObject($store_stock_intransit_qty_qry_style_sizewise);
    $store_stock_active_picking_pickngcmplt_qty1 = $db->fetchObject($store_stock_active_picking_pickngcmplt_qty_qry_style_sizewise);

    $store_stock_curr_qty1 = empty($store_stock_curr_qty1->quantity) ? 0 : $store_stock_curr_qty1->quantity;
    $store_stock_intransit_qty1 = empty($store_stock_intransit_qty1->intransit_stock_value) ? 0 : $store_stock_intransit_qty1->intransit_stock_value;
    $store_stock_active_picking_pickngcmplt_qty1 = empty($store_stock_active_picking_pickngcmplt_qty1->total_qty) ? 0 : $store_stock_active_picking_pickngcmplt_qty1->total_qty;

    return $store_stock_curr_qty1 + $store_stock_intransit_qty1 + $store_stock_active_picking_pickngcmplt_qty1;
}

function getCurrentStoreStockctgwise($storeid, $catid) {
//    print_r("in master");exit();
    $db = new DBConn();

    $store_stock_curr_qty_qry = "select sum(quantity) as quantity from  it_current_stock where store_id = $storeid and ctg_id=$catid";
    $store_stock_intransit_qty_qry = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and oi.item_code=i.barcode and  o.is_procsdForRetail = 0 and o.invoice_type in ( 0 , 6 ) and o.store_id = $storeid and i.ctg_id=$catid";
    $store_stock_active_picking_pickngcmplt_qty_qry = "SELECT SUM(oi.order_qty) as total_qty FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.store_id = $storeid and i.ctg_id=$catid AND o.status IN (" . OrderStatus::Active . ", " . OrderStatus::Picking . ", " . OrderStatus::Picking_Complete .  ")";

    $store_stock_curr_qty = $db->fetchObject($store_stock_curr_qty_qry);
    $store_stock_intransit_qty = $db->fetchObject($store_stock_intransit_qty_qry);
    $store_stock_active_picking_pickngcmplt_qty = $db->fetchObject($store_stock_active_picking_pickngcmplt_qty_qry);

    $store_stock_curr_qty = empty($store_stock_curr_qty->quantity) ? 0 : $store_stock_curr_qty->quantity;
    $store_stock_intransit_qty = empty($store_stock_intransit_qty->intransit_stock_value) ? 0 : $store_stock_intransit_qty->intransit_stock_value;
    $store_stock_active_picking_pickngcmplt_qty = empty($store_stock_active_picking_pickngcmplt_qty->total_qty) ? 0 : $store_stock_active_picking_pickngcmplt_qty->total_qty;

    return $store_stock_curr_qty + $store_stock_intransit_qty + $store_stock_active_picking_pickngcmplt_qty;
}

function getMaterStoreStock($storeid, $catid, $styleid, $sizeid) {
    $db = new DBConn();
    $masterqry = "SELECT min_qty_allowed 
                  FROM stock_master_qty_wise 
                  WHERE store_id = $storeid 
                  AND category_id = $catid 
                  AND style_id = $styleid 
                  AND size_id = $sizeid";

    $stores_master = $db->fetchObject($masterqry);

    return $stores_master ? $stores_master->min_qty_allowed : 0;
}

// $order_id = id of newly created orders id and style size and ctg are of currently being released items which we need to check against items in 
// ST order as items could not be more than master stock (current system add all items against order_id and keep status standing order hence we need to 
// check if qty exceed in standing order status compared to master stock)
// $curr_ctg_id, $curr_style_id, $curr_size_id this are the current item being inserted into the it_ck_orderitems we need to check if already order is
// placed for same items
function getNotActiveSTOrderItems($order_id, $storeid, $curr_ctg_id, $curr_style_id, $curr_size_id){
    $db = new DBConn();           
    
    // This query will take current item being placed as ST order and check with already placed ST order items if they are same based on ctg, style and 
    // size it will return the sum of placed(not active but inserted in it_ck_orederitems) orders
    $ST_order_inserted_items_qry="select sum(oi.order_qty) as qty from it_ck_orderitems oi inner join it_items i on i.id=oi.item_id where "
            . "i.ctg_id=$curr_ctg_id and i.style_id=$curr_style_id and i.size_id=$curr_size_id and oi.store_id=$storeid and oi.order_id=$order_id";
    $ST_order_inserted_items=$db->fetchObjectArray($ST_order_inserted_items_qry);
    
    if(!empty($ST_order_inserted_items->qty)){
        return $ST_order_inserted_items->qty;
    }else{
        return 0;
    }
}

function getMasterStackEligibleStores(){
    $db = new DBConn();
    $sql = "select store_id from it_master_stack_eligible_stores";
    $master_stack_eligible_stores =$db->fetchObjectArray($sql);
    
    if (!empty($master_stack_eligible_stores)) {
        // Extract store_id values into an array
        $eligible_store_ids = array_map(function($obj) {
            return $obj->store_id;
        }, $master_stack_eligible_stores);
        return $eligible_store_ids; // Return the array
    } else {
        return []; // Return empty array
    }
}

function checkEligibleCategories($catid){
    $db = new DBConn();
    $catObj = $db->fetchObjectArray("select id from it_categories where (setaccesories = 1 or setotheractive = 1) and active=1 order by id");
    
    if (!empty($catObj)) {
        // Extract categories values into an array
        $not_eligible_categories = array_map(function($obj) {
            return $obj->id;
        }, $catObj);
        if(in_array($catid, $not_eligible_categories)){
            return false; //return false if current category is from other/accessories catlog - to skip those catlog in master stack capacity check
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function getLastThreeMonthSale($storeid, $catid, $styleid, $sizeid) {
    $db = new DBConn();
    
    $masterSumquery = "select sum(min_qty_allowed) as totalqty from stock_master_qty_wise where store_id=$storeid and category_id=$catid";
    $masterSumCategorywise = $db->fetchObject($masterSumquery);
    if (!empty($masterSumCategorywise)) {
        $bufferTotal = $masterSumCategorywise->totalqty * 0.2;
    } else {
        $bufferTotal = 0;
    }

    $saleQuery = "SELECT sum(quantity) as totalSaleQty FROM it_store_threemonthsale_summary WHERE store_id = $storeid AND category_id = $catid";
    $saleSumCategorywise = $db->fetchObject($saleQuery);
    if(!empty($saleSumCategorywise)){
        $totalSaleCategoryWise = $saleSumCategorywise->totalSaleQty;
    } else {
        $totalSaleCategoryWise = 0;
    }
    
    $query = "SELECT quantity FROM it_store_threemonthsale_summary WHERE store_id = $storeid AND category_id = $catid AND style_id = $styleid AND size_id = $sizeid";
    $stores_sales = $db->fetchObject($query);
    if (!empty($stores_sales)){
        $saleQtyCatStyleSizeWise= $stores_sales->quantity;
    } else {
        $saleQtyCatStyleSizeWise= 0;
    }
    
    //Formula to calculate individual buffer value for each style, size combo for respective category
    // 20 % of Total Master Stack capacity qty for category / sum of qty of last three month sale for category * qty from last three month sale of category for particular style, size wise.
//    for e.g Total master stack capacity for Regular Shirt = 661
//    20% of 661 = 132.2
//    sum of qty of last three month sale for Regular Shirt  = 1919
//    qty from last three month sale for Regular Shirt (Style- F/S, Size-39) = 153 
//    $buffer = 132.2 / 1919 * 153 = 10.5 = Round Value = 11
    if ($totalSaleCategoryWise != 0 && $bufferTotal != 0 && $saleQtyCatStyleSizeWise != 0) {
        $buffer = round($bufferTotal / $totalSaleCategoryWise * $saleQtyCatStyleSizeWise);
    } else {
        $buffer = 0;
    }

    return $buffer ? $buffer : 0;
}


function getCurrentStoreSale($storeid, $catid, $styleid, $sizeid,$sdate,$edate) {
    $db = new DBConn();
    $store_sale = "SELECT  SUM(CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END) AS quantity, SUM(CASE WHEN (o.discount_pct IS NOT NULL) THEN ((((100 - o.discount_pct) / 100) * oi.price) * (CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END)) ELSE oi.price * (CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END) END) AS totalvalue FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_sizes sz ON sz.id = i.size_id JOIN it_codes c ON o.store_id = c.id join it_categories ic on i.ctg_id = ic.id join it_styles sy on sy.id = i.style_id  WHERE i.style_id=$styleid and i.size_id=$sizeid and i.ctg_id=$catid and o.store_id IN ($storeid) AND o.bill_datetime BETWEEN '$sdate' AND '$edate'  and c.id=$storeid GROUP BY c.id,ic.name,sy.name, i.size_id ORDER BY ic.name,sz.name";
//      print_r($store_sale);      exit();
    
    $store_sale_qty1 = $db->fetchObject($store_sale);
    
    $store_sales_qty1 = empty($store_sale_qty1->quantity) ? 0 : $store_sale_qty1->quantity;
   
    return $store_sales_qty1;
}