<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_discounts extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
       // parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher));
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<script type="text/javascript">
function fileSelect(fname) {
arr = fname.split("\\");
fname = arr[arr.length-1];
arr = fname.split(".");
$("#design_no").val(arr[0]);
return; // disable automatic file upload
document.getElementById("addform").submit();
}

</script>
<?php

    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="discount";
        include "sidemenu.".$this->currStore->usertype.".php";
        global $g_categories;
        $db = new DBConn();
        $discobjs = $db->fetchObjectArray("select s.*,c.store_name from it_ck_storediscount s, it_codes c where s.store_id=c.id and c.inactive = 0 order by s.store_id ");
        ?>
<div class="grid_10" style="overflow:auto;">
    <?php if ($this->currStore->usertype !=UserType::Dispatcher) { ?>
    <!--<a href="admin/discounts/add"><button>Add New Store Discount Details</button></a>-->
    <?php } ?>
    <table>
        <tr>
          <!--<th>Store Name</th>-->
            <th>Portal Store Name</th>
           <!-- <th>Location Name</th>-->
            <!--<th>Polaris Code</th>-->
            <th>Dealer Discount</th>
            <th>Additional Discount</th>
<!--            <th>VAT</th>
            <th>CST</th>-->
            <th>Transport</th>
            <th>Octroi</th>
            <th>Cash</th>
            <th>Non Claim</th>
            <?php if ($this->currStore->usertype !=UserType::Dispatcher) { ?>
            <!--<th></th>-->
            <?php } ?>
        </tr>
        <?php foreach ($discobjs as $disc) { ?>
        <tr>
           <!-- <td> //echo $disc->storename;</td>-->
            <td><?php if (strtolower($disc->store_name)!='administrator') echo $disc->store_name; ?></td>
           <!-- <td> echo //$disc->location; </td>-->
            <!--<td><?php echo $disc->polariscode; ?></td>-->
            <td><?php echo $disc->dealer_discount."%"; ?></td>
            <td><?php echo $disc->additional_discount."%"; ?></td>
<!--            <td><?php //if ($disc->vat) echo $disc->vat."%"; ?></td>
            <td><?php //if ($disc->cst) echo $disc->cst."%"; ?></td>-->
            <td><?php if ($disc->transport) echo $disc->transport."%"; ?></td>
            <td><?php if ($disc->octroi) echo $disc->octroi."%"; ?></td>
            <td><?php if ($disc->cash) echo $disc->cash."%"; ?></td>
            <td><?php if ($disc->nonclaim) echo $disc->nonclaim."%"; ?></td>   
            <?php if ($this->currStore->usertype !=UserType::Dispatcher) { ?>
            <!--<td><a href="admin/discounts/update/sid=<?php echo $disc->store_id; ?>">Update</td>-->
            <?php } ?>
        </tr>
        <?php } ?>
    </table>
    
</div>
    <?php
    } //pageContent
}//class
?>
