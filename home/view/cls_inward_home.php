<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_inward_home extends cls_renderer {

        var $currUser;
        var $userid;
	function __construct($params=null) {
            // set page permissions
            parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::GoodsInward));
            $this->currUser = getCurrUser();
            $this->params = $params;
            if (!$this->currUser) { return; }
            $this->userid = $this->currUser->id;
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

</script>

<?php
    }

	public function pageContent() {
		$currUser = getCurrUser();
		$menuitem = "inwardhome";
		include "sidemenu.".$currUser->usertype.".php";
                $formResult = $this->getFormResult();
                $db = new DBConn();
                
?>
<div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $obj = $db->fetchObjectArray("select g.id as gateentry, s.name as supplier, t.name as transporter, g.qty_received as qty, g.dt_received as date, u.name as user from it_gateentry g, it_suppliers s, it_transporters t, it_users u where g.supplier_id = s.id and g.transport_id = t.id and g.received_by = u.id and g.inactive=0 order by g.id");
            ?>
    <div style="float:right;margin-right:10px;">
    <a href="inward/gateentry"><button>New Gate Entry</button></a><br />
    </div>
    <div class="grid_12">
        <table align="center">
            <tr>
                <th>List of Open Gate Entry</th> 
                <th></th><th></th><th></th><th></th><th></th><th></th> 
            </tr>                     
            <tr>
                <th>Gate Entry No</th>
                <th>Supplier</th>
                <th>Transporter</th>
                <th>Quantity</th>
                <th>Received Date</th>
                <th>Received By</th>                    
                <th></th>
            </tr>
                    <?php foreach ($obj as $ob) { ?>
            <tr>
                <td><?php echo $ob->gateentry; ?></td>
                <td><?php echo $ob->supplier; ?></td>
                <td><?php echo $ob->transporter; ?></td>                
                <td><?php echo $ob->qty; ?></td>
                <td><?php echo $ob->date; ?></td>
                <td><?php echo $ob->user; ?></td>
                <td><button onclick="deleteGateEntry(<?php echo $obj->id; ?>);">Pick</button></td>                
            </tr>
                    <?php } ?>
        </table>
    </div>
</div>
<?php
	}
}
?>
