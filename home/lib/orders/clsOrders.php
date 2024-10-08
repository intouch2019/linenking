<?php

require_once "lib/db/dbobject.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/Constants.php";


class clsOrders extends dbobject {

	function getCartt($store_id) {
		$query = "select * from it_ck_orders where store_id=$store_id and status=".OrderStatus::InCart;
		return $this->fetchObject($query);
		if ($obj) { return $obj; }
	}

	function getCart($store_id) {
		$query = "select id,order_no,order_qty,order_amount,num_designs,msl_ack from it_ck_orders where store_id=$store_id and status=".OrderStatus::InCart;
//print "$query<br />"; return null;
		$obj = $this->fetchObject("select * from it_ck_orders where store_id=$store_id and status=".OrderStatus::InCart);
		if ($obj) { return $obj; }

		$store = $this->fetchObject("select store_number from it_codes where id=$store_id");
		if (!$store || !$store->store_number) { return null; }
		$store_number = $store->store_number;

		$obj = $this->fetchObject("select order_no from it_ck_orders where store_id=$store_id order by id desc limit 1");
		$new_order_no = 1;
		if ($obj) {
			$new_order_no = intval(substr($obj->order_no,3)) + 1;
		}
		if ($new_order_no == 1000) { $new_order_no = 1; }
		$order_no = $this->safe(sprintf("%03d%03d", $store_number, $new_order_no));
		$order_id=$this->execInsert("insert into it_ck_orders set store_id=$store_id, status=".OrderStatus::InCart.", order_no=$order_no, order_qty=0");
		return $this->fetchObject("select id,order_no,order_qty,order_amount,num_designs,msl_ack from it_ck_orders where id=$order_id");
	}

	function getCartItems($order_id) {
		return $this->fetchObjectArray("select * from it_ck_orderitems where order_id=$order_id");
	}

	function getCartInfo($store_id) {
		$order = $this->getCart($store_id);
		if ($order) {
			return (object) array(
				"order_no" => $order->order_no,
				"quantity" => $order->order_qty,
				"amount" => $order->order_amount,
				"num_designs" => $order->num_designs
			);
		} else {
			return (object) array(
				"order_no" => false,
				"quantity" => 0,
				"amount" => 0,
			);
		}
	}

	function printCartInfo($cartInfo) {
		$str = "Quantity: $cartInfo->quantity | Amount: $cartInfo->amount";
		if ($cartInfo->order_no) {
			$str = "Order No: $cartInfo->order_no | $str";
		}
		return $str;
	}

	function updateCartTotals($cart_id) {
		// update the cart with the totals
		$query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$cart_id";
		$obj = $this->fetchObject($query);
		$tot_qty = 0; $tot_amt = 0; $num_designs = 0;
		if ($obj && $obj->tot_qty && $obj->tot_amt && $obj->num_designs) {
			$tot_qty = $obj->tot_qty;
			$tot_amt = $obj->tot_amt;
			$num_designs = $obj->num_designs;
		}
		$query = "update it_ck_orders set order_qty=$tot_qty, order_amount=$tot_amt, num_designs=$num_designs where id=$cart_id";
		$this->execUpdate($query);
	}

}
?>
