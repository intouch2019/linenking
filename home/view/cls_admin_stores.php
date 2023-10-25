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
        if($this->currStore->usertype == UserType::Admin){  ?>
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
                <th>Owner</th>
                <th>Phone</th>
                <th></th>
                <th></th>
                <th></th>
                <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100){?>
                 <th>License</th>
                 <?php } ?>
                <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100){ ?>
                <th>License Reset</th>
                <?php } ?>
            </tr>
                    <?php foreach ($objs as $obj) {
                       // print_r($obj);
if(trim($obj->tax_type) != ""){$taxtype= taxType::getName($obj->tax_type);}else{$taxtype="-";};
$serobj = $db->fetchObject("select max(id) as max_id from it_server_changes where   store_id = $obj->id or store_id is null");
if ($serobj) $max_server_id = $serobj->max_id; else $max_server_id = "";
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
                <td><?php echo $obj->owner; ?></td>
                <td><?php echo $obj->phone; ?></td>
		<!-- Disable delete store for now -->
                <td><a href="#" onclick='javascript:showDialog(<?php echo $dialogHtml; ?>);return false;'><button>Details</button></a>
                </td><td><a href="admin/stores/edit/id=<?php echo $obj->id; ?>"><button>Edit</button></a></td>
                <td>
            <?php if ($obj->inactive) {// print_r($obj);?>
                    
		<!--<a href="admin/stores/enable/id=<?php echo $obj->id; ?>"><button>Enable</button></a>-->
                <?php $storeqry="select store_name from it_codes where id=$obj->inactivated_by";
//error_log("\nobj  qry:\n".$obj,3,"ajax/tmp.txt");                   
//print_r($obj->inactivated_by);
                $inactivatedby=$db->fetchObject($storeqry);
                          if(isset($inactivatedby->store_name)){
                              $whom=$inactivatedby->store_name;
                          }else{
                              $whom="";
                          }
                    ?>
                    <?php $reason =str_replace("\r\n","",trim($obj->inactivating_reason));?>
		<!--<a href="admin/stores/enable/id=<?php// echo $obj->id; ?>"><button>Enable</button></a>--><!--javascript:enableLogin("<?php //echo $obj->id;?>","<?php //echo $obj->disablelogins_reason;?>","<?php //echo $obj->loginsdisable_by;?>");-->
                    <a href="#" onclick='javascript:enableLogin("<?php echo $obj->id;?>","<?php echo $reason;?>","<?php echo $whom;?>");return false;'><button>Enable</button></a>
                   <!--<a href="admin/stores/enable/id=<?php //echo $obj->id; ?>"><button>Enable</button></a>-->
                        <?php } else { ?>
		<a href="stores/disablelogin/reason/id=<?php echo $obj->id; ?>"><button>Disable</button></a>
<?php } ?>             
		</td>
           <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100){ ?>
                <td><?php echo $obj->license; ?></td>
                <?php } ?>     
 <?php if($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->id ==100){ ?>               
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