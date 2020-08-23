<?php
require_once "lib/db/dbobject.php";

class clsItems extends dbobject {

	public function addCategory($storeid, $scenarioid, $ctgname) {
		$ctgname = $this->safe($ctgname);
		return $this->execInsert("insert into it_categories set storeid=$storeid, scenarioid=$scenarioid, name=$ctgname");
	}

	public function getUnassignedItems($scenarioid, $storeid, $searchtext=false) {
		$allitems = array();
		$search="";
		if ($searchtext) { $search = " and itemname like '%$searchtext%' "; }
		$objs = $this->fetchObjectArray("select id from it_rawitems where storeid=$storeid $search");
		foreach ($objs as $obj) {
			$allitems[] = $obj->id;
		}

		$assigneditems = array();
		$objs = $this->fetchObjectArray("select distinct ci.itemid from it_categories c, it_catitems ci where c.scenarioid = $scenarioid and c.id = ci.catid");
		foreach ($objs as $obj) {
			$assigneditems[] = $obj->itemid;
		}
		$unassigned = array_diff($allitems, $assigneditems);
		$idstr = implode(",", $unassigned);
		return $this->fetchObjectArray("select * from it_rawitems where id in ($idstr)");
	}

//	public function getAllCategories($scenarioid) {
//		return $this->fetchObjectArray("select * from it_categories where scenarioid=$scenarioid and inactive=0");
//	}
        public function getAllCategories() {
            return $this->fetchObjectArray("select id,name from it_categories where active=1");
        }
        
        public function getCategoryNameFromId($id) {
            $id = $this->safe($id);
            return $this->fetchObject("select name from it_categories where id=$id");
        }

	public function assignItems($storeid, $ctgid, $itemids) {
		if (count($itemids) == 0) { return; }
		$idstr = implode(",", $itemids);
		foreach ($itemids as $itemid) {
			$query = "insert into it_catitems set catid=$ctgid, itemid=$itemid";
			$this->execUpdate($query);
		}
	}

	public function getScenarios($storeid) {
		return $this->fetchObjectArray("select * from it_scenarios where storeid=$storeid");
	}

	public function addScenario($storeid, $title) {
		$title = $this->safe($title);
		return $this->execInsert("insert into it_scenarios set storeid=$storeid, title=$title");
	}

	public function getMatchingItems($letters,$storeid) {
		$query = "select * from it_rawitems where storeid=$storeid and itemname like '%$letters%'";
		return $this->fetchObjectArray($query);
	}
	
	public function getAllRawItems($storeid) {
		$query = "select * from it_rawitems where storeid=$storeid order by itemname";
		return $this->fetchObjectArray($query);
	}
	
	public function getAllShipments($storeid) {
		$query = "select s.id,s.arrival_date, s.stocked_date from it_shipments s, it_inventory i where s.id=i.shipmentid and s.storeid=$storeid group by s.arrival_date,s.stocked_date";
		return $this->fetchObjectArray($query);
	}
	
	public function addShipment($storeid,$shipmentInfo) {
		$str="";
		foreach($shipmentInfo as $key => $value) {
			$value=$this->safe($value);
			$str .= "$key=$value,";
		}
		$query="insert into it_shipments set $str createtime=now()";
		return $this->execInsert($query);
	}
	
	public function getAllShipmentInfo($storeid) {
		$query=" select * from it_shipments where storeid = $storeid order by arrival_date desc";
		return $this->fetchObjectArray($query);	
	}
	
	public function getShipmentById($id) {
		$query=" select * from it_shipments where id = $id";
		return $this->fetchObject($query);	
	}
	
	public function getNumberOfItemsInShipments($shId) {
		$query=" select count(*) as numItems from it_inventory where shipmentid = $shId";
		return $this->fetchObject($query);	
	}

	public function getItemsByShipmentId($shId,$storeId) {
		$query=" select i.*,ri.itemname from it_inventory i, it_rawitems ri where i.shipmentid = $shId and i.storeid=$storeId and i.rawitemid=ri.id";
		return $this->fetchObjectArray($query);	
	}

	public function addStock($stockInfo) {
		$storeId=$stockInfo['storeid'];
		$itemId=$stockInfo['rawitemid'];
		$shipmentId = $stockInfo['shipmentid'];
		$quantity = $stockInfo['quantity'];
		$query="select curr_quantity from it_rawitems where id=$itemId";
		$obj = $this->fetchObject($query);
		if($obj->curr_quantity == Null) {
			$currQty = $quantity;
		} else {
			$currQty = $obj->curr_quantity+$quantity;
		}
		$query="update it_rawitems set curr_quantity=$currQty where  id=$itemId";
		$this->execUpdate($query);
		$query="insert into it_inventory set shipmentid=$shipmentId, rawitemid=$itemId, storeid=$storeId, quantity=$quantity, createtime=now()";
		return $this->execInsert($query);
	}

	public function getInventoryByShipmentId($storeid,$shipmentid) {
		$query=" select i.*, ri.itemname from it_inventory i, it_rawitems ri where i.shipmentid=$shipmentid and i.storeid = $storeid and i.storeid = ri.storeid and i.rawitemid = ri.id";
		return $this->fetchObjectArray($query);	
	}
	
	public function getQuantityByItemId($id) {
		//Calculate quantity by subtracting sum(linequantity)  from quantity in inventory
		$query="select sum(quantity) as iqty from it_inventory where rawitemid=$id";
		$obj1 = $this->fetchObject($query);	
		if(!$obj1) {
			$iqty = 0;
		} else {
			$iqty = $obj1->iqty;
		}	
		$query="select sum(linequantity) as lqty from it_rawitemlines where rawitemid=$id";
		$obj2 = $this->fetchObject($query);
		if(!$obj2) {
			$lqty = 0;
		} else {
			$lqty = $obj2->lqty;
		}
		$query="select $iqty-$lqty as quantity";	
		return $this->fetchObject($query);	
	}
	
	public function updateItemDetails($storeid,$itemid,$reorderlevel,$supplierid) {
		$query="update it_rawitems set reorderlevel=$reorderlevel, supplierid=$supplierid where id=$itemid and storeid=$storeid";
		return $this->execUpdate($query);
	}

	public function getItemsToReorder() {
		$query="select * from it_rawitems";
		$itemObj = $this->fetchObjectArray($query);
		$roArr = array();
		foreach($itemObj as $obj) {
			if($obj->curr_quantity<=$obj->reorderlevel) {
				$roArr[] = $obj->id;
			}	
		}
		$itemIds = implode(",",$roArr);
		$query="select * from it_rawitems where id in ($itemIds)";
		return $this->fetchObjectArray($query);
	}

	public function getItemsToReorderBySupplierId($supId,$storeid) {
			$query="select * from it_rawitems";
			$itemObj = $this->fetchObjectArray($query);
			$roArr = array();
			foreach($itemObj as $obj) {
				if($obj->curr_quantity<=$obj->reorderlevel) {
					$roArr[] = $obj->id;
				}	
			}
			$itemIds = implode(",",$roArr);
		if($supId=="") {
			$query="select * from it_rawitems where id in ($itemIds)";
		} else {
			$query="select * from it_rawitems where id in ($itemIds) and supplierid=$supId and storeid=$storeid";
		}
		return $this->fetchObjectArray($query);
	}
	
	public function getItemById($id) {
		return $this->fetchObject("select * from it_rawitems where id=$id");
	}
}
?>
