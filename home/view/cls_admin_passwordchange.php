<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_admin_passwordchange extends cls_renderer {
    var $params;
    var $currUser;
    var $userid;
    var $currStore;
    var $storeid;
     var $storeidreport = null;
     var $storeloggedin = -1; 
    function __construct($params=null) {
	$this->currStore = getCurrUser();
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
	$this->params = $params;
	if (!$this->currStore) { return; }
	$this->storeid = $this->currStore->id;
        if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
         if($this->currUser->usertype==UserType::Dealer){ 
                    $this->storeidreport = $this->currUser->id;
                    $this->storeloggedin = 1;                    
                }
    }

    function extraHeaders() {
//	if (!$this->currStore) {
//	    return;
//	}
?>
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>   
<script language="JavaScript" src="js/tigra/validator.js"></script>

<!--<link rel="stylesheet" href="css/bigbox.css" type="text/css" />-->
<?php
    } // extraHeaders

    public function pageContent() {
	 $menuitem = "ssales";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn(); $write_htm = true;
            $categories = array(); $sizes = array(); $styles = array();
            $mfg_by = array(); $brands = array(); $prod_typs = array();
            $fabric_types = array();$materials = array();
            $sdate="";$edate="";
	?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_6">
	<fieldset>
	    <legend>Update Stores Password</legend>
            <p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values.</p>
    <form id="ruleform" name="ruleform" method="post" action="formpost/UpdateStorePasssword.php" ><!-- action path to change password-->
         <?php if($this->currUser->usertype != UserType::Dealer ){ ?>
        <div class="clsDiv"><b>Select Stores</b><br/>
            
<select id="store" data-placeholder="Choose Store"  name="store[]" class="chzn-select" multiple style="width:75%;" > 
                
                <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 and inactive=0 order by store_name");

if($this->storeidreport == "-1"){
    $storeid = array(); 
    if($this->a==0){ //means 'all stores report is req only in excel'
     $write_htm = false;   
    }
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4 and inactive=0");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeidreport);  
}
//print_r($allst);
//print_r($storeid);
foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storeidreport != -1){
            foreach($storeid as $sid){
                if($obj->id==$sid) 
                { $selected = "selected"; }
            }
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
		</select>
      
            </div>
         <?php } ?>
           
        <br/>
        
        <?php if($this->currUser->usertype != UserType::Dealer ){ ?>
        <div class="clsDiv"><b>Select Role</b><br/>
            
<select id="role" data-placeholder="Choose Role"  name="role[]" class="chzn-select" multiple style="width:75%;" > 
                
                <?php if( $this->storerole == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Roles</option> 
<?php
$objs = $db->fetchObjectArray("select * from  it_store_roles order by id");

if($this->storerole == "-1"){
    $storeid = array(); 
    if($this->a==0){ //means 'all stores report is req only in excel'
     $write_htm = false;   
    }
    $allstoreArrays=$db->fetchObjectArray("select * from  it_store_roles order by id");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storerole);  
}
//print_r($allst);
//print_r($storeid);
foreach ($objs as $obj) {        
    $selected="";
//    if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storerole != -1){
            foreach($storeid as $sid){
                if($obj->id==$sid) 
                { $selected = "selected"; }
            }
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->role_name; ?></option> 
<?php } ?>
        </select>
      
            </div>
         <?php } ?>
        <br/>
        
        
<div class="clsDiv"><b>Enter New Password</b></div>
<input type="password" name="Password" value=""/>

<br />
<br/>
<div class="clsDiv"><b>Re-Enter New Passworsd</b></div>
	<div class="clsText">
            <input type="password" name="RePassword" value=""/>
	</div>
<br />
<?php
if(!empty($_SESSION['form_errors'])){
$error=$_SESSION['form_errors'];
echo $error;
return;
}
?>
	<input type="submit" value="Update"/><br/>
	<input type="hidden" name="form_id" value="1"/>
	<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span> 
    </form>
            <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<div class="clear"></div>
	</fieldset>
    </div>
</div>
  <?php
    } 
}
?>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
var storeid = '<?php echo $this->storeidreport; ?>';  
var storeloggedin = '<?php echo $this->storeloggedin; ?>';
//alert("STORE ID: "+storeid);
//alert("STORE LOGGED IN: "+storeloggedin);
    $(function(){
        $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
        var isOpen=false;
        $('#dateselect').daterangepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				//window.location.reload();
			}
		});
		}
	});
        
        var radio = $('input[name=report]:checked').val()
        if (radio=='billwise') {
            showgeneral();
        } else {
            showitemwise();
        }
//        $('#dwnloadbtn').hide();
    });
    
    function showgeneral(){
        $('#itemselection').hide();
        $('#generalselection').show();
    }
    
    function showitemwise(){
        $('#generalselection').hide();
        $('#itemselection').show();
    }
    
    var fieldlist = new Array();
    
    function reloadreport() {        
       if(storeloggedin == '-1'){
           storeid = $('#store').val();
          //alert("SID:"+storeid);
       }
      //alert("1: "+storeid);
        var aclause='';
        if(storeid=='-1'){
          resp = confirm("Do you want all stores report visible on portal?"); 
          if(resp){
              aclause='/a=1';
          }
        }
        //alert("a:"+aclause);
//       // $('select.foo option:selected').val(); commented
       var reporttype=$('input[name=report]:radio:checked').val();
       //alert(reporttype);commented
       $('#selectRight option').attr('selected', 'selected');
      // var storeid = $('#store').val();      
       //alert("2: "+storeid);//commented
       if (storeid!="" && storeid != null) {
           if (reporttype=="itemwise") {
                var multiplevalues = $('#selectRight').val();
                //var values = $('#itemfields').attr('name'); commented
                //alert(values);commented
                //alert(multiplevalues);commented
                var append='';
                var sequence=1;
                for (var i=0;i<multiplevalues.length;i++) {
                        append += "/"+multiplevalues[i]+"="+sequence;
                        sequence++;
                }
                window.location.href="report/ssales/str="+storeid+append+aclause;
           } else {
                window.location.href="report/ssales/str="+storeid+"/gen=1"+aclause;
           }
           $('#dwnloadbtn').show();
       } else {
           alert("please select store(s) to genereate a report");
       }
    }
</script>
    <?php
   // } //pageContent
//}//class
?>