<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/core/clsProperties.php";


class cls_admin_stores extends cls_renderer {
    var $currStore;
    var $storeid;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() { ?>
<link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="js/expand.js"></script>
<script language="JavaScript" src="js/tigra/validator.js"></script>
<script type="text/javascript">
	$(function(){
		modal = $("#storeinfo").dialog({
			autoOpen: false,
			title: 'Store Details'
		});
	});
	function showDialog(content) {
		$("#storeinfo").html(content);
		modal.dialog('open');
	}
        
        function resetMacAddr(id){
          var conf = confirm("Do you really want to enable the license?");  
          if(conf==true){
            var rurl = "ajax/resetMacaddr.php?id="+id;
            $.ajax({
                url:rurl,
                success: function(data){
                    alert(data);
                    window.location.reload();
                }
            });
         }
        }


       function DownloadExcel(){
            //alert("hello");
             window.location.href="formpost/disablereport.php";
       }
       
       function DownloadStoreExcel(){
            //alert("hello");
             window.location.href="util/storeExportNew.php";
       }
       
       function DownloadNonclaimExcel(){
            //alert("hello");
             window.location.href="util/NonclaimstoreExport.php";
       }
                
        function enableLogin(id,reason,whom){
         //alert(reason);
                  var conf1 = confirm("Do you want to override the decision?\n\nlogin disable reason :- "+reason+"\n\nlogin disabled by :- "+whom);   
                  if(conf1==true){
                      var conf = confirm("Do you really want to enable the Store Login");
                      if(conf==true){
                    window.location.href = "admin/stores/enable/id="+id;
                      }
                  }else{
                        return false;
                  }
          }
</script>
    <?php
    } //end of extra headers

    public function pageContent() {
        //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) {} else { print "Unauthorized Access"; return; }
        if (getCurrUser()) {
            $menuitem="stores";
            include "sidemenu.".$this->currStore->usertype.".php";
        }
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$dbProperties = new dbProperties();
	$disableStoreLogins = $dbProperties->getBoolean(Properties::DisableUserLogins);
	$obj = $db->fetchObject("select max(id) as max_id from it_server_changes");
	if ($obj) $max_server_id = $obj->max_id; else $max_server_id = "";
        ?>
<div id="storeinfo"></div>
    <div style="float:right;margin-right:15px;">
         <?php
        if(($this->currStore->usertype == UserType::Admin )||($this->currStore->roles == RollType::IT)){  ?>
        <a href="admin/stores/stocklimitupdate"><button>Update Stocklimit</button></a>
        
        <?php }  ?>
        <button name="dwnFile" id="dwnFile" onclick="DownloadNonclaimExcel();">Download Nonclaim Store Details</button>
        <button name="dwnFile" id="dwnFile" onclick="DownloadStoreExcel();">Download Store Details</button>
        <button name="dwnFile" id="dwnFile" onclick="DownloadExcel();">Download Disable Store Report</button>
        
<?php if ($disableStoreLogins) { ?>
    <a href="admin/stores/enablelogins"><button>Enable Store Logins</button></a>
<?php } else { ?>
    <a href="admin/stores/disablelogins"><button>Disable Store Logins</button></a>
<?php } ?>
    <a href="admin/stores/add"><button>Add New Store</button></a>
    </div>
<div class="grid_10">
                <?php
                if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Accounts || $this->currStore->id==100 || $this->currStore->id==103 || $this->currStore->usertype == UserType::Manager){
            $objs = $db->fetchObjectArray("select * from it_codes where usertype=4 and is_closed=0 order by createtime desc");
                }else{
                     echo '<script>alert("You are not allowed to edit the stores.");</script>';
                    exit;
                }
            //print "select * from it_codes where usertype=4 order by createtime desc";
            ?>
<fieldset>
        <legend>Stores</legend>
        <table border="3">
            <tr align="center">
                <th colspan="15"><h4>Shop Details</h4></th>
            </tr>
            <tr>
                <th>Dealer Number</th>
               
                  <th>Store ID</th>
                <th>Username</th>
                <th>Level</th>
                <th>Is Store Closed</th>
                <th>Dealer Name</th>
                <th>City</th>
                <th>RBF Name</th>
                <th>Owner</th>
                <th>Phone</th>
                <th></th>
                <th></th>
                <th></th>
                <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100 || $this->currStore->roles == RollType::IT ){?>
                 <th>License</th>
                 <?php } ?>
                <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100 || $this->currStore->roles == RollType::IT){ ?>
                <th>License Reset</th>
                <?php } ?>
            </tr>
                    <?php
            /* ---------------------------------------------------------------
             * PERFORMANCE FIX: batch-load all related data up-front to avoid
             * the previous N+1 pattern (3 queries per store row). We now run a
             * fixed, small number of queries regardless of store count.
             * ------------------------------------------------------------- */
            $storeIds = array();
            $inactivatedByIds = array();
            foreach ($objs as $o) {
                $storeIds[] = (int)$o->id;
                if ($o->inactive && trim($o->inactivated_by) != "") {
                    $inactivatedByIds[] = (int)$o->inactivated_by;
                }
            }
            $idList = !empty($storeIds) ? implode(",", $storeIds) : "0";

            // (1) Global max server-change id for store-agnostic rows (store_id IS NULL).
            $gobj = $db->fetchObject("select max(id) as max_id from it_server_changes where store_id is null");
            $globalNullMax = ($gobj && $gobj->max_id !== null) ? (int)$gobj->max_id : null;

            // (2) Per-store max server-change id in ONE grouped query.
            $storeMaxMap = array();
            $smRows = $db->fetchObjectArray("select store_id, max(id) as max_id from it_server_changes where store_id in ($idList) group by store_id");
            if (!empty($smRows)) {
                foreach ($smRows as $r) { $storeMaxMap[(int)$r->store_id] = (int)$r->max_id; }
            }

            // (3) Executive names per store in ONE join, grouped in PHP.
            $execMap = array();
            $exRows = $db->fetchObjectArray("select e.store_id, i.store_name from executive_assign e join it_codes i on i.id = e.exe_id where i.usertype=3 and i.roles=6 and e.store_id in ($idList)");
            if (!empty($exRows)) {
                foreach ($exRows as $er) { $execMap[(int)$er->store_id][] = $er->store_name; }
            }

            // (4) "Inactivated by" store names in ONE IN() lookup.
            $inactByMap = array();
            if (!empty($inactivatedByIds)) {
                $inList = implode(",", array_unique($inactivatedByIds));
                $ibRows = $db->fetchObjectArray("select id, store_name from it_codes where id in ($inList)");
                if (!empty($ibRows)) {
                    foreach ($ibRows as $ir) { $inactByMap[(int)$ir->id] = $ir->store_name; }
                }
            }
            ?>
                    <?php foreach ($objs as $obj) {
                       // print_r($obj);
if(trim($obj->tax_type) != ""){$taxtype= taxType::getName($obj->tax_type);}else{$taxtype="-";};
// PERFORMANCE FIX: use pre-batched maps instead of a per-row query.
$perStoreMax = isset($storeMaxMap[(int)$obj->id]) ? $storeMaxMap[(int)$obj->id] : null;
$candidates = array();
if ($perStoreMax !== null)  $candidates[] = $perStoreMax;
if ($globalNullMax !== null) $candidates[] = $globalNullMax;
$max_server_id = !empty($candidates) ? max($candidates) : "";
$dialogHtml = '<table border="0">';
$dialogHtml .= "<tr>";
$dialogHtml .= "<th colspan=2>$obj->store_name</th>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= '<td width="70px;">Create Date:</td><td>'.mmddyy($obj->createtime)."</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Store Number:</td><td>$obj->store_number</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<th colspan=2>$obj->level</th>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Owner:</td><td>$obj->owner</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Address:</td><td>$obj->address</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>City:</td><td>$obj->city</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Postal Code:</td><td>$obj->zipcode</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Email1:</td><td>$obj->email</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Email2:</td><td>$obj->email2</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Phone1:</td><td>$obj->phone</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Phone2:</td><td>$obj->phone2</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>VAT:</td><td>$obj->vat</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Tax type:</td><td>$taxtype</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Tally Name:</td><td>$obj->tally_name</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= '</table>';
$dialogHtml = json_encode($dialogHtml);
	$server_change = "[ $obj->server_change_id / $max_server_id ]";
			    ?>
            <tr>
                <td><?php echo $obj->store_number; ?></td>
                 <td><?php echo $obj->id; ?></td>
                <td><?php echo "$obj->code<br>$server_change"; ?></td>
                <td><?php echo $obj->level; ?></td>
                <td><?php if(trim($obj->is_closed)==1){ echo "Yes";}else{ echo "No";}?></td>
                <td><?php echo $obj->store_name; ?></td>
                <td><?php echo $obj->city; ?></td>
                                            <td><?php
//                $executive=$db->fetchObject("select exe_id from executive_assign where store_id=$obj->id order by id desc limit 1");
//                echo "select exe_id from executive_assign where store_id=$obj->id order by id desc limit 1";
//                echo "SELECT s.id AS store_id, s.code AS store_code, s.store_name AS store_name, e.id AS executive_id, e.code AS executive_code, e.store_name AS executive_name FROM executive_assign ea JOIN it_codes s ON ea.store_id = $obj->id JOIN it_codes e ON ea.exe_id = $executive->exe_id WHERE e.usertype = 3 and e.roles=6 and  s.is_closed=0 ORDER BY s.id, e.id;";
                                // PERFORMANCE FIX: read from pre-batched executive map.
                                $names = isset($execMap[(int)$obj->id]) ? $execMap[(int)$obj->id] : array();
                                echo implode(", ", $names);
                                ?></td>
                <td><?php echo $obj->owner; ?></td>
                <td><?php echo $obj->phone; ?></td>
		<!-- Disable delete store for now -->
                <td><a href="#" onclick='javascript:showDialog(<?php echo $dialogHtml; ?>);return false;'><button>Details</button></a>
                </td><td><a href="admin/stores/edit/id=<?php echo $obj->id; ?>"><button>Edit</button></a></td>
                <td>
            <?php if ($obj->inactive) {// print_r($obj);?>
                    
		<!--<a href="admin/stores/enable/id=<?php echo $obj->id; ?>"><button>Enable</button></a>-->
                <?php
                // PERFORMANCE FIX: read from pre-batched "inactivated by" map.
                $whom = isset($inactByMap[(int)$obj->inactivated_by]) ? $inactByMap[(int)$obj->inactivated_by] : "";
                    ?>
                    <?php $reason =str_replace("\r\n","",trim($obj->inactivating_reason));?>
		<!--<a href="admin/stores/enable/id=<?php// echo $obj->id; ?>"><button>Enable</button></a>--><!--javascript:enableLogin("<?php //echo $obj->id;?>","<?php //echo $obj->disablelogins_reason;?>","<?php //echo $obj->loginsdisable_by;?>");-->
                    <a href="#" onclick='javascript:enableLogin("<?php echo $obj->id;?>","<?php echo $reason;?>","<?php echo $whom;?>");return false;'><button>Enable</button></a>
                   <!--<a href="admin/stores/enable/id=<?php //echo $obj->id; ?>"><button>Enable</button></a>-->
                        <?php } else { ?>
		<a href="stores/disablelogin/reason/id=<?php echo $obj->id; ?>"><button>Disable</button></a>
<?php } ?>             
		</td>
           <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100 || $this->currStore->roles == RollType::IT){ ?>
                <td><?php echo $obj->license; ?></td>
                <?php } ?>     
 <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100 || $this->currStore->roles == RollType::IT){ ?>               
                <td>
<?php if($obj->macaddress != null || trim($obj->macaddress) != ""){ ?>                    
                    <a href="#" onclick='javascript:resetMacAddr(<?php echo $obj->id; ?>);return false;'><button>Enable </button></a>
<?php }else { ?>  
                   <button disabled>Enabled</button>
<?php } ?>                    
                </td>
<?php } ?>     
            </tr>
                    <?php } ?>
        </table>
</fieldset>
</div>
    <?php
    }
}
?>