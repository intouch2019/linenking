<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";

class cls_admin_stores_editlist extends cls_renderer {

    var $currStore;
    var $storeid;
    var $state_id;
    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($params['id'])) {
            $this->storeid = $params['id'];
        }
         if (isset($params['state_id'])) {
            $this->state_id = $params['state_id']; //   var $state_id;
        }
    }

    function extraHeaders() {
        ?>
        <?php
    }

    public function pageContent() {
        //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
        if (getCurrUser()) {
            $menuitem = "store_list_edit";
            include "sidemenu." . $this->currStore->usertype . ".php";
        }
        //print_r($this->currStore);
        $formResult = $this->getFormResult();
        $db = new DBConn();
        //$store = $db->fetchObject("select * from it_codes where id=$this->storeid and usertype=".UserType::Dealer);
        //$store = $db->fetchObject("select c.*,d.* from it_codes c left outer join it_ck_storediscount d on c.id=d.store_id where c.id=$this->storeid and usertype=" . UserType::Dealer);
        $store = $db->fetchObject("select c.id,c.store_name,c.owner,c.phone,c.address
	from it_codes c where c.id=$this->storeid and usertype=" . UserType::Dealer);
   //     print "select c.*,d.* from it_codes c left outer join it_ck_storediscount d on c.id=d.store_id where c.id=$this->storeid and usertype=".UserType::Dealer;
        if (!$store) {
            print "Store not found [$this->storeid]. Please report this error";
            return;
        }
        ?>
        <script>
 function backtoedit(){
                    
                   // alert('hiiiiiiiiiii');admin/stores
window.location.href ="report/storelist";
    
    }
    

        </script>
        <div class="grid_10">

 
    <div class="grid_8">
	<fieldset>
                <legend>Edit Store list</legend>
                <div class="grid_12">
                    <div class="addstore">
                         <input type="hidden"   value="< Back" id="back" onclick="backtoedit();"><br>
                      
                        <form action="formpost/editStoreList.php" method="post" >
                            <input type="hidden" name="storeid" value="<?php echo $this->storeid; ?>" />
                            <input type="hidden" name="usrname" value="<?php echo $this->currStore->code; ?>" />
                            <input type="hidden" name="usrid" value="<?php echo $this->currStore->id; ?>" />

                         
                            <p class="grid_12">
                                <label id="t_dealer_name">*Dealer Name: </label>
                                <input type="text" name="store_name" value="<?php echo $this->getFieldValue('store_name', $store->store_name); ?>" required>
                            </p>
                    
      
            
<!--                            <p class="grid_12">
                                <label id="t_city">*City: </label>
                                <input type="text" name="city"  value="<?php echo $this->getFieldValue('city', $store->city); ?>" required>
                            </p>-->

                            <p class="grid_12">
                                <label id="t_name">*Owner Name: </label>
                                <input type="text" name="owner" value="<?php echo $this->getFieldValue('owner', $store->owner); ?>" required>
                            </p>
                                    <p class="grid_12">
                                <label id="t_address">*Address: </label>
                                <input type="text" name="address" value="<?php echo $this->getFieldValue('address', $store->address); ?>" required>
                            </p>
                            <p class="grid_6">
                                <label id="t_phone">*Phone Number  </label>
                                <input type="text" name="phone" value="<?php echo $this->getFieldValue('phone', $store->phone); ?>" >
                            </p>
<!--                            <p class="grid_12">
                                <label id="t_phone2">Phone Number 2: </label>
                                <input type="text" name="phone2" value="<?php// echo $this->getFieldValue('phone2', $store->phone2); ?>">
                            </p>-->
                           
                            </div>
                            <p class="grid_12">
                                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                            </p>
                            <p class="grid_6" align="left">
                                <input type="submit" value="Update" style="width:35%">
                            </p>
                            </br>
                        </form>
                    </div>

            </fieldset>
        </div>
        </div>
        <?php
    }

}
?>