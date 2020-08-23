<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_admin_usertype_assignpages extends cls_renderer{

        var $currUser;
        var $userid;
        var $params;

       
        function __construct($params=null) {
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;                
        }

	function extraHeaders() {
        ?>
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
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
      var selectedCountry=listLeft.options.selectedIndex;

      move(listRight,listLeft.options[selectedCountry].value,listLeft.options[selectedCountry].text);
      listLeft.remove(selectedCountry);

      if(listLeft.options.length>0){
      listLeft.options[selectedCountry].selected=true;
      }
    }
  } else if(side==2){
    if(listRight.options.length==0){
      alert('You have already moved all fields to Left');
      return false;
    }else{
      var selectedCountry=listRight.options.selectedIndex;
      //alert(listRight.options[selectedCountry].value);      
         move(listLeft,listRight.options[selectedCountry].value,listRight.options[selectedCountry].text);    
     // if(is_numeric(listRight.options[selectedCountry].value)){   
        listRight.remove(selectedCountry);
      //}

      if(listRight.options.length>0){
        listRight.options[selectedCountry].selected=true;
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
</script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "usertypeAssignPg";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login" style="background-color:#b0b0b0;">
	<legend>Assign Pages to Usertype</legend>	        
        <form action="" method="" onsubmit="assignPg(); return false;">
		<div class="grid_12">
		<div class="grid_4">
                   <label>User Type: </label>
                    <select name="usertype" id="usertype" onchange="usertypeSelect(this.value);">
                        <option value="">Please Select</option>
                                <?php
                                $allUserTypes = UserType::getAll();
                                $display = "block";
                                foreach ($allUserTypes as $usertype => $typename) {
                                    if ($usertype == UserType::Admin) { continue; }
                                    if ($usertype == $this->getFieldValue('usertype')) {
                                        $selected = "selected";
                                        if ($usertype == UserType::NoLogin) { $display="none"; }
                                    }
                                    else { $selected = ""; }
                                    ?>
                        <option value="<?php echo $usertype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
                                <?php } ?>
                    </select>
        	
		</div>		                
		</div>
            
		<div class="grid_12" id="pageselection">
                    <div class="grid_7">
                            <table border="0" colspan="4">
                                <tr>
                                    <td colspan="5">Page Selection:</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Enabled</td>
                                    <td colspan="1">&nbsp;</td>
                                    <td colspan="2">Disabled</td>
                                </tr>
                                <tr>
                                <td rowspan="3" colspan="2" align="right"><label>
                                    <select name="selectLeft" multiple size="10" width="100%" style="width:200px;" id="selectLeft"> 
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                </td>
                                    <td rowspan="3" colspan="2" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">                                       
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
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
    $(function(){
        $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});                            
    });
        
    function assignPg() {
       $('#selectLeft option').attr('selected', 'selected');
       var usertype = $('#usertype').val();       
       if (usertype !="") {
                var multiplevalues = $('#selectLeft').val();
                //alert(multiplevalues);
                window.location.href="formpost/assignUTypePage.php?usertype="+usertype+"&pgs="+multiplevalues;                
           
       } else {
           alert("please select a usertype to assign pages");
       }
    }
    
    function usertypeSelect(usertype){
        var ajaxUrl = "ajax/getUtypeAssgnPgs.php?user_type="+usertype;
        //alert(ajaxUrl);        
        $.getJSON(ajaxUrl, function(data) {
            var options = $('#selectLeft').attr('options');
           //  $('#selectLeft option').attr('selected', 'selected');
            options.length = 1;
//                    options.length = 1;
            console.log(data);
            console.log(data.length);
            //alert(data);
            for (var i = 0; i < data.length; i++) {
                console.log(data[i]);
                var arr = data[i].split('::');
                options[options.length] = new Option(arr[2] , arr[0], false, false);
            }            
        });  
        var ajaxPUrl = "ajax/getUtypeNotAssgnPgs.php?user_type="+usertype;
       // alert(ajaxPUrl);        
        $.getJSON(ajaxPUrl, function(data) {
            var options = $('#selectRight').attr('options');
           //  $('#selectLeft option').attr('selected', 'selected');
            options.length = 1;
//                    options.length = 1;
            console.log(data);
            console.log(data.length);
            //alert(data);
            for (var i = 0; i < data.length; i++) {
                console.log(data[i]);
                var arr = data[i].split('::');
                options[options.length] = new Option(arr[2] , arr[0], false, false);
            }            
        });
    }
</script>
<?php
	}
}
?>
