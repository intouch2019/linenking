<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "session_check.php";
require_once ("lib/core/Constants.php");

class cls_admin_cancel_order extends cls_renderer{

		var $currUser;
		var $storeid;
		var $params;
                
		function __construct($params=null) {
                        $this->currUser = getCurrUser();    		
                        if($params && isset($params['storeid'])){
                            $this->storeid = $params['storeid'];
                        }
		}

	function extraHeaders() {
		?>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">

 function loadOrders(storeid){
//     alert(storeid);
    window.location.href ="admin/cancel/order/storeid="+storeid;
 }
</script>
		
		<?php
		}

		public function pageContent() {
			$menuitem = "cancelOrder";
			include "sidemenu.".$this->currUser->usertype.".php";
                        $db = new DBConn();
                        $formResult = $this->getFormResult();
?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
     <fieldset>
        <legend>Cancel Order</legend>
        <form id="cancelorder" name="cancelorder"  method="post" action="formpost/cancelStoreOrder.php">
            <p>
             Select Store:
             <select name="store_id" data-placeholder="Choose Store..." class="chzn-select" single style="width:100%;" onchange="loadOrders(this.value);">
               <option value=""></option> 
               <?php
                 $query="select id,store_name from it_codes where id in (select distinct(store_id) from it_ck_orders) and usertype = 4";
                 $objs = $db->fetchObjectArray($query);                 
                 foreach($objs as $obj){
                     $selected="";
                     if($obj->id == $this->storeid){ $selected = "selected";}
               ?>
               <option value="<?php echo $obj->id;?>" <?php echo $selected; ?>><?php echo $obj->store_name;?></option>
                 <?php } ?>
             </select>
           </p><br/>
           <p>
             Select Order no:
             <select name="order_id" data-placeholder="Choose Order..." class="chzn-select" single style="width:100%;">
               <option value=""></option> 
               <?php
                  if(isset($this->storeid) && ($this->storeid > 0 )){
                      $query = " select id, order_no from it_ck_orders where store_id = ".$this->storeid." and status = ".OrderStatus::Active;
                      $objs = $db->fetchObjectArray($query);
                      foreach($objs as $obj){                                                                                  
               ?>
               <option value="<?php echo $obj->id;?>"><?php echo $obj->order_no;?></option>
                  <?php } }  ?>
             </select>
           </p><br/><br/><br/><br/>
           <p>
             <input type ="submit" name="canOrd" value="Cancel" style="width:75px"/>  
             <input type="hidden" name="form_id" value="cancelorder"/>
           </p><br/> 
           <?php if($formResult) {?>
            <p>
                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
            </p>   
           <?php } ?>
        <form>    
      </fieldset>   
    </div>    
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<?php
	}
}
?>
