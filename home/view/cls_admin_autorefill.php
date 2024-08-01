<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once ("lib/db/DBConn.php");
require_once "session_check.php";
require_once "lib/core/Constants.php";

class cls_admin_autorefill extends cls_renderer {

    var $params;
    var $currStore;
    var $store_id;
    var $store_type;

    function __construct($params = null) {
        $this->currStore = getCurrUser();       
        $this->params = $params;
        $this->store_id = getCurrUserId();
    }

    function extraHeaders() {
        ?>
        <script type="text/javascript">
            function showUpload(value) {
                document.getElementById('loader').style.display="block";    
                document.getElementById("uploadBtn").style.visibility = "hidden";
                if (value == "0") {
                    $('#dis').empty();
                    document.getElementById("uploadBtn").style.visibility = "hidden";
                } else { 
//                    alert("here"+value);
                    $.ajax({
                        url:'ajax/displayAROrderDetails.php',
                        data: ({sid: value}),
                        dataType: 'json',
                        type: 'POST',
                        success: function(output) {
//                            alert(output);
//                            console.log("OUTPUT: "+output);
                        document.getElementById('loader').style.display="none";
                        if(output.error==0){
                                    $('#dis').html(
                                            "Total Item(s) are " + output.num_item + "</br> Total ordered quantity is " + output.orderqty + 
                                            "</br> Total Available Stock is " + output.availstock + "<br/> Last Sync: "+output.lasttime +
                                            "</br> Cart Quantity = "+ output.cart_qty + "</br> Cart Amount = "+ output.cart_amt           
                                        );
                            document.getElementById("uploadBtn").style.visibility = "visible";
                        }else{
                            $('#dis').html(output.msg);
                        }    
                            $('#resform').empty();
                        }
                        
                    });
//                    alert("here1");
                }
            }
        </script>    


        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/ajax-dynamic-list.js"></script>     


    <?php
    }

// extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "autorefill";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();
        ?>
        <div class="grid_10">

            <div class="grid_3">&nbsp;</div>
            <div class="grid_5">
                <fieldset style="background-color:#b0b0b0;">
                    <legend>Auto Refill Orders</legend>
                    <form id="loadarstock" name="loadarstock"  method="post" action="formpost/loadAROrders.php">
                        <div class="clsDid">Select Store<br />(Only stores that have pending sales will be shown in the list below)</div>
                        <div class="clsText">                                               
                            <select   id="storesel" name="storesel"  style="width:100%;" onchange="showUpload(this.value);">
                                <option value="0">Select</option>
                                <?php
                               // $query = "Select c.store_name,ar.store_id, sum(ar.quantity) as qty from it_codes c,it_orders ar where c.id=ar.store_id and ar.ck_order_id is null group by c.id";


				 $dbProperties = new dbProperties();
                                                                 
                                if(!$dbProperties->getBoolean(Properties::DisableUserLogins)){
                                
                                
                                $storeid_query="select usertype from it_codes where id=".$this->store_id."";
                                $objs_store = $db->fetchObjectArray($storeid_query);
                                
                                foreach ($objs_store as $objst) {
                                    $store_type=$objst->usertype;
                                }
                                if($store_type==4){
                                    //$store_id = getCurrUserId();
                                    $query = "select c.store_name,o.store_id,sum(oi.quantity) as qty from it_codes c,it_orders o,it_order_items oi where c.is_autorefill=1 and c.id=".$this->store_id." and c.inactive = 0  and is_closed = 0 and c.id=o.store_id and o.ck_order_id is null and oi.order_id = o.id and oi.quantity > 0 group by c.id";
                                
//                                    $query = "select c.store_name,o.store_id,sum(o.quantity) as qty from it_codes c,it_orders o where c.is_autorefill=1 and c.id=".$this->store_id." and c.inactive = 0  and c.is_closed = 0 and c.id=o.store_id and o.ck_order_id is null and o.quantity > 0 and o.bill_datetime >= case when ISNULL(c.autorefil_dttm) then o.bill_datetime else c.autorefil_dttm end group by o.store_id order by null";
                                }
                                else
                                {
                                    $query = "select c.store_name,o.store_id,sum(oi.quantity) as qty from it_codes c,it_orders o,it_order_items oi where c.is_autorefill=1 and c.inactive = 0  and is_closed = 0 and c.id=o.store_id and o.ck_order_id is null and oi.order_id = o.id and oi.quantity > 0 group by c.id";
                                }
                                $objs = $db->fetchObjectArray($query);
                                $selected = "";
                                foreach ($objs as $obj) {
                                    ?>  
                                    <option value="<?php echo $obj->store_id ?>"<?php echo $selected; ?>><?php echo $obj->store_name . " [" . $obj->qty . "]"; ?></option>
        <?php }} ?>
                            </select>
                            <div id="loader" style="display:none" >
                                <img src="images/loading.gif" width="25" height="25"> Loading....
                            </div>
                        </div>
                        <br/>
                        <div id="dis" name="dis"></div>
                        <br /><br />

                        <?php
                        if ($formResult) {
                            ?>
                            <div id="resform" style="clear:both;">
                                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" 
                                      style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?>
                                </span>
                            </div>
        <?php } $_SESSION['form_post'] = array(); ?>
                        <input type="submit" id="uploadBtn" name="uploadBtn" value="Place Orders" style="visibility:hidden "/>
                        <input type="hidden" name="form_id" value="loadarstock"/>


                    </form>
                </fieldset>
            </div>

        </div>
        <?php
    }

//pageContent
}

//class