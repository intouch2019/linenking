<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once "lib/db/DBConn.php";

class cls_scheme_stores extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    var $scheme_id;
    function __construct($params=null) {
	$this->currStore = getCurrUser();
	$this->params = $params;
	if (!$this->currStore) { return; }
	$this->storeid = $this->currStore->id;
	if (!isset($params['id'])) {
	  header("Location: ".DEF_SITEURL."pagenotfound");
	  exit;
	}
	$this->scheme_id = $params['id'];
    }

	function extraHeaders() {
        ?>
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">
	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, April 2006
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/	

</script>
<script type="text/javaScript">
function moveToRightOrLeft(side){
  var listLeft=document.getElementById('selectLeft');
  var listRight=document.getElementById('selectRight');

  if(side==1){
    if(listLeft.options.length==0){
    alert('You have already moved all fields to Right');
    return false;
    }else{
      var selectedItem=listLeft.options.selectedIndex;

      move(listRight,listLeft.options[selectedItem].value,listLeft.options[selectedItem].text);
      listLeft.remove(selectedItem);

      if(listLeft.options.length>0){
      listLeft.options[selectedItem].selected=true;
      }
    }
  } else if(side==2){
    if(listRight.options.length==0){
      alert('You have already moved all fields to Left');
      return false;
    }else{
      var selectedItem=listRight.options.selectedIndex;
      //alert(listRight.options[selectedItem].value);      
         move(listLeft,listRight.options[selectedItem].value,listRight.options[selectedItem].text);    
     // if(is_numeric(listRight.options[selectedItem].value)){   
        listRight.remove(selectedItem);
      //}

      if(listRight.options.length>0){
        listRight.options[selectedItem].selected=true;
      }
    }
  }
}

function move(listBoxTo,optionValue,optionDisplayText){
  var newOption = document.createElement("option"); 
  newOption.value = optionValue; 
  newOption.text = optionDisplayText; 
  newOption.selected = true;
  listBoxTo.add(newOption, null); 
  return true; 
}
function selectAllOptions(selStr)
{
  var selObj = document.getElementById("selectLeft");
  var leftStores="";
  for (var i=0; i<selObj.options.length; i++) {
    leftStores = leftStores + selObj.options[i].value + ",";
  }
  var selObj = document.getElementById("selectRight");
  var rightStores="";
  for (var i=0; i<selObj.options.length; i++) {
    rightStores = rightStores + selObj.options[i].value + ",";
  }
  $('#left_stores').val(leftStores);
  $('#right_stores').val(rightStores);
}
</script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
	    $menuitem = "schemeList";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
            $scheme = $db->fetchObject("select * from it_rules where id=$this->scheme_id");
            if (!$scheme) { print "Scheme not found [$this->scheme_id]"; return; }
            $stores = $db->fetchObjectArray("select c.id, c.code, c.store_name, r.IS_ACTIVE
		from it_codes c left outer join it_rule_stores r on c.id = r.STORE_ID
		where c.usertype=4 order by c.store_name");
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login" style="background-color:#b0b0b0;">
	<legend>Specify stores for Scheme [<?php echo $scheme->RULE_TEXT; ?>]</legend>	        
        <form action="formpost/setSchemeStores.php" method="POST" onsubmit="selectAllOptions(); return true;" >
	     <input type="hidden" name="scheme_id" value="<?php echo $this->scheme_id; ?>" />
	     <input type="hidden" id="left_stores" name="left_stores" />
	     <input type="hidden" id="right_stores" name="right_stores" />
		<div class="grid_12" id="pageselection">
                    <div class="grid_8">
                            <table border="0" colspan="7">
                                <tr>
                                    <td colspan="3">Stores Enabled</td>
                                    <td colspan="1">&nbsp;</td>
                                    <td colspan="3">Stores Disabled</td>
                                </tr>
                                <tr>
                                <td rowspan="3" colspan="3" align="right"><label>
                                    <select name="selectLeft" multiple size="10" width="100%" style="width:200px;" id="selectLeft"> 
<?php foreach ($stores as $store) {
    if (!($store->IS_ACTIVE)) continue;
?>
				    <option value="<?php echo $store->id; ?>"><?php echo $store->store_name; ?></option>
<?php } ?>
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                </td>
                                    <td rowspan="3" colspan="3" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">                                       
<?php foreach ($stores as $store) {
    if ($store->IS_ACTIVE == "1") continue;
?>
				    <option value="<?php echo $store->id; ?>"><?php echo $store->store_name; ?></option>
<?php } ?>
                                        </select>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </div>
            
           
		<div class="grid_12" id="submitbutton" style="padding:10px;">
                <input type="submit" name="add" id="add" value="Save" style="background-color:white;"/>
                
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->

</div>
<?php
	}
}