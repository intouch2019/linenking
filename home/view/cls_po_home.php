<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_po_home extends cls_renderer {

        var $currUser;
        var $userid;
        var $postatus;
        
	function __construct($params=null) {
            // set page permissions
            parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::GoodsInward));
            $this->currUser = getCurrUser();
            $this->params = $params;
            if (!$this->currUser) { return; }
            $this->userid = $this->currUser->id;
            if(isset($this->params['postatus'])){
                $this->postatus = $this->params['postatus'];
            }else{
                $this->postatus = "0";
            }
            
        }

	function extraHeaders() {
        if (!$this->currUser) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
?>

<script type="text/javascript">

    function viewPO(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if(value == "0"){
            window.location = "po/home/postatus="+value;
        }else if(value == "1"){
            window.location = "po/home/postatus="+value;
        }else{
            return;
        }
    }

</script>

<?php
    }

	public function pageContent() {
		$currUser = getCurrUser();
		$menuitem = "purchaseorder";
		include "sidemenu.".$currUser->usertype.".php";
                $formResult = $this->getFormResult();
                $db = new DBConn();
                
?>
<div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $dt = "";
            if($this->postatus == "0"){
                $obj = $db->fetchObjectArray("select p.id as pid, s.name as supplier, p.supplier_id as supplier_id,p.potype as potype, p.pono as pono, p.consignee as consignee, u.name as preparedby, p.createtime as createdate, p.po_status as status from it_suppliers s, it_purchaseorder p, it_users u where s.id = p.supplier_id and u.id = p.preparedby_id and p.po_status = 0 order by p.createtime desc");
                $potype = "List of Open Purchase Order";                
                $dt = "Create Date";
            }else if($this->postatus == "1"){
                $obj = $db->fetchObjectArray("select p.id as pid, s.name as supplier, p.supplier_id as supplier_id,p.potype as potype, p.pono as pono, p.consignee as consignee, u.name as preparedby, p.submittedtime as submitdate, p.po_status as status from it_suppliers s, it_purchaseorder p, it_users u where s.id = p.supplier_id and u.id = p.preparedby_id and p.po_status = 1 order by p.submittedtime desc");                
                $potype = "List of Published Purchase Order";                
                $dt = "Submitted Date";
            }
            
            ?>
    <div style="float:left;margin-left:10px;">
    <select id="selectPOType" name="POType" onchange="viewPO(this);">
        <!--<option>Select PO Type to get List</option>-->
        <option value="0"
                <?php if($this->postatus == "0"){echo "selected";}?>>
            Open Purchase Orders</option>
        <option value="1" <?php if($this->postatus == "1"){echo "selected";}?>>Published Purchase Orders</option>
    </select>    
    </div>
    <div style="float:right;margin-right:10px;">
    <a href="po/create"><button>New Purchase Order</button></a><br />
    </div>
    <div class="grid_12">
        <table align="center">
            <tr>
                <th><?php echo $potype?></th> 
                <th></th><th></th><th></th><th></th><th></th><th></th> 
            </tr>                     
            <tr>
                <th>PO No</th>
                <th>PO Type</th>
                <th>Supplier</th>
                <th>Consignee</th>
                <th>Prepared By</th>
                <th><?php echo $dt?></th>
                <th></th>
            </tr>
                    <?php foreach ($obj as $ob) { ?>
            <tr>
                <td><?php echo $ob->pono; ?></td>
                <td><?php echo PoType::getName($ob->potype); ?></td>
                <td><?php echo $ob->supplier; ?></td>
                <td><?php echo $ob->consignee; ?></td>                
                <td><?php echo $ob->preparedby; ?></td>
                <td><?php 
                    if($this->postatus == "0"){
                        echo $ob->createdate;
                    }elseif($this->postatus == "1"){
                        echo $ob->submitdate;
                    }
                ?></td>
                <?php if($ob->status == "0"){ ?>
                <td><a href="po/additems/id=<?php echo $ob->pid; ?>"><button>Edit</button></a><button onclick="deleteUser(<?php echo $ob->pid; ?>);">Delete</button></td>                
                <?php }else{?>
                <td><a href="po/view/id=<?php echo $ob->pid; ?>"><button>View PO</button></a><a target="_blank" href="pofiles/<?php echo $ob->pono.".pdf"; ?>"><button>View PDF</button></a></td>                
            </tr>
                    <?php } }?>
        </table>
    </div>
</div>
<?php
	}
}
?>
