<?php
require_once "../../it_config.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/db/DBConn.php";

extract($_POST);

//extract($_GET);

$orderid=false; $storeid=false; $userid = false;

if (isset($_POST['orderid'])){
	$orderid = $_POST['orderid'];
}
if (isset($_POST['storeid'])){
	$storeid = $_POST['storeid'];
}
if(isset($_POST['userid'])){
        $userid = $_POST['userid'];
}


try{
    $db = new DBConn();
   // print $orderid." ".$storeid." ".$userid;
   // return;
    
    if($orderid){
        $query = "select id,order_no,store_id,active_time from it_ck_orders where id=$orderid and status=".OrderStatus::Active;
        $obj = $db->fetchObject($query);
        if (!$obj) { print "1::Order [$orderid] not found. Please report this error"; return; }
        $order_ids = $obj->id;
        $order_nos = $obj->order_no;
        $storeid = $obj->store_id; 
        $active_time = $obj->active_time;
    }else if($storeid){
	$query = "select id,order_no,active_time from it_ck_orders where store_id=$storeid and status=".OrderStatus::Active." order by active_time";
	$objs = $db->fetchObjectArray($query);
	if (count($objs) == 0) { print "1::No orders in Active state"; return; }
	$ids = array();
	$nos = array();
	foreach ($objs as $obj) {
		$ids[] = $obj->id;
		$nos[] = $obj->order_no;
		$active_time = $obj->active_time;
	}
	$order_ids = implode(",", $ids);
	$order_nos = implode(", ", $nos);
    }else{
	print "1::ERROR: missing parameters. Please report this."; return;        
    }
    
    $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, "
            . "count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id in ($order_ids) and "
            . "oi.item_id = i.id and i.ctg_id != 21";
    $summary = $db->fetchObject($query);
    if (!$summary) { print "1::Summary info not found for orders [$order_ids]. Please report this error."; return; }
    $query = "insert into it_ck_pickgroup set storeid = $storeid, dispatcher_id=76, "
            . "order_ids = '$order_ids', order_nos='$order_nos', order_qty = $summary->tot_qty, "
            . "order_amount = $summary->tot_amt, num_designs = $summary->num_designs,picker_id = 92, picking_time = now(), active_time='$active_time'";
    $insert_id = $db->execInsert($query);
    $query = "update it_ck_orders set status=2, pickgroup=$insert_id, updatetime = now() where id in ($order_ids)";
    $db->execUpdate($query);

    $query = "select p.*, o.status, max(o.active_time) as active_time, c.store_name from it_ck_orders o, it_ck_pickgroup p,"
            . " it_codes c where o.store_id = c.id and o.pickgroup = p.id and p.id=$insert_id group by o.pickgroup";
    
    $obj_picked_order = $db->fetchObject($query);
    
    $obj_json = array();
    $obj_json['pick_group_id'] = $obj_picked_order->id;
    $obj_json['store_id'] = $obj_picked_order->storeid;
    $obj_json['order_ids'] = $obj_picked_order->order_ids;
    $obj_json['order_nos'] = $obj_picked_order->order_nos;
    $obj_json['order_qty'] = $obj_picked_order->order_qty;
    $obj_json['order_amount'] = $obj_picked_order->order_amount;
    $obj_json['num_designs'] = $obj_picked_order->num_designs;
    $obj_json['dispatcher_id'] = $obj_picked_order->dispatcher_id;
    $obj_json['active_time'] = $obj_picked_order->active_time;
    $obj_json['picking_time'] = $obj_picked_order->picking_time;
    $obj_json['pcomplete_time'] = $obj_picked_order->pickingComplete_time;
    $obj_json['status'] = $obj_picked_order->status;
    $obj_json['store_name'] = $obj_picked_order->store_name;
    
    
    $query = "SELECT $insert_id as pick_group_id, o.order_id,convert(d.lineno, unsigned int) dlineno, convert(d.rackno, unsigned int) drackno,"
            . " i.barcode,i.design_no, i.MRP as mrp, ctg.id as ctgid, ctg.name as category,br.name as brand,pt.name as prodtype,"
            . " sum(o.order_qty) as order_qty, i.style_id, i.size_id, ctg.vat_id, ctg.cst_id from it_ck_designs d, it_items i, it_categories ctg,"
            . " it_ck_orderitems o,it_brands br, it_fabric_types ft, it_materials mt,it_prod_types pt where o.item_id=i.id and i.ctg_id=ctg.id"
            . " and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and d.id = i.design_id"
            . " and i.ctg_id=d.ctg_id and o.order_id in ($order_ids) group by o.item_id order by d.lineno, d.rackno";
    
    
    $obj_order_items = $db->fetchObjectArray($query);
    
    $obj_items = array();
    
    foreach($obj_order_items as $item){
        $it = array();
        foreach($item as $key => $value){
            $it[$key] = $value;
        }
        $query = "select cs.id, sz.id as size_id, sz.name,cs.sequence from it_ck_sizes cs, it_sizes sz where cs.size_id = sz.id and cs.ctg_id = $item->ctgid order by cs.sequence";
        $obj_sizes = $db->fetchObjectArray($query);
        
        $it["size_info"] = $obj_sizes;
        
        $query = "select cs.id, st.id  as style_id, st.name,cs.sequence from it_ck_styles cs, it_styles st where cs.style_id = st.id and cs.ctg_id = $item->ctgid order by cs.sequence";
        $obj_styles = $db->fetchObjectArray($query);

        $it["style_info"] = $obj_styles;
        
        $obj_items[] = $it;
    }
    
    //$obj_json['orderItems'] = $obj_order_items;

    $obj_json['orderItems'] = $obj_items;
    print "0::".json_encode($obj_json);
    return;

    
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}
?>