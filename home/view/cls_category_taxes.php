
<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
//print "Yes";
//cls_category_taxes
class cls_category_taxes extends cls_renderer{

        var $currUser;
        var $userid;
        var $dtrange;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                $this->dtrange = date("d-m-Y");
        }

	function extraHeaders() {
        ?>
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
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<script type="text/javascript">
$(function(){
     var atype = $("#atype").val();
     if(atype == "categories"){
         document.getElementById('vatDiv').style.visibility = 'visible';
         document.getElementById('cstDiv').style.visibility = 'visible';
         document.getElementById('hsnDiv').style.visibility = 'visible';
     }else{
        document.getElementById('vatDiv').style.visibility = 'hidden';
        document.getElementById('cstDiv').style.visibility = 'hidden';  
        document.getElementById('hsnDiv').style.visibility = 'hidden';
     }
});
    
    
function ajaxValues(inputObj,e) {
	var selectVal = $("#atype").val(); 
	return ajax_showOptions(inputObj,'type='+selectVal,e);
}

function enterPressed() {
    var key = window.event.keyCode;
    // If the user has pressed enter
    if (key == 13) {
        alert("Enter detected");
        return false;
    } else {
        return true;
    }
}

function cateadd(){
   //alert("Yes");
   var tax_typ=document.getElementById("vat_type").value;
   var tax_rate=tax_typ/100;
   //alert("tax_typ"+tax_rate);
   //if(tax_typ=="")
   document.getElementById("lb2").style.visibility="visible";
   document.getElementById("lb1").innerHTML=""+tax_rate;
}

</script>


        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "CategoryTax";
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
	<fieldset class="login"> 
	<legend>Update Category Taxes</legend>
	
        <form action="formpost/addcategory_tax.php" method="post">
		<div class="grid_12">
		  
                 <div class="grid_2">
                     <lable style="font-size:15px;">Select Categories</lable>
		</div>   
                    
		<div class="grid_4">
                    <select name="avalue" data-placeholder="Choose categories" class="chzn-select" single style="width:100%;">
                        
                        <option value="1" selected>Choose Categories</option>
                       
                        
                        <?php
                         $objs=$db->fetchObjectArray("select * from it_categories where margin=0"); //
                         if($objs)
                         {
                             foreach ($objs as $obj) {
                                 ?>
                                 <option value="<?php echo $obj->name;?>"><?php echo $obj->name;?></option>
                                 <?php
                             }
                         }
                        ?>
                                  
                        <?php  ?>
                    </select>
                    		</br>

		</div>
                    
		</div> <!-- grid_12 -->
		</br>
                        <div class="grid_2">
                            </br>
                        <lable style="font-size:15px;">Select Tax</lable>
                        </div> 
                <div class="grid_4">
                    </br>
                    <select id= "vat_type" name="vat_type" data-placeholder="Choose GST Type..." class="chzn-select" single style="width:100%;" onchange="cateadd()">
                        
                        <option value="1" selected>Choose GST Type</option>
                        <option value="5">GST 5%</option>
                        <option value="12">GST 12%</option>
                        <option value="18">GST 18%</option>
                        <option value="28">GST 28%</option>
                        
                        <?php
//                
                        ?>
                                  
                        <?php  ?>
                    </select>
		</div>
                
                <div class="grid_4">
                    </br>
                   <lable style="font-size:16px;visibility: hidden" id="lb2">Tax rate:</lable>
                   
                   <lable style="font-size:16px;" id="lb1"></lable>

                </div>
                </br>
                <div class="grid_12" id="hsnDiv" name="vatDiv" style="visibilty:hidden">
                    </br>
                    <b><lable style="font-size:16px;">Valid From:</lable>
                    <input type="date" id="from" name="fromdate" value="<?php echo $fromdate ?>" required>&nbsp;&nbsp;&nbsp;&nbsp;
        
                                 
               
                </div>   
                <div class="grid_12" style="padding:10px;" id="resp">
                <input type="submit" name="addattr" id="addattr" value="Save" style="background-color:#34de63;"/>
                
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

<div class="grid_10">
<div class="grid_2">&nbsp;</div>
<div class="grid_8">
<table>
<tr>
<th>ID</th>
<th> Category Name</th>
<th>Tax Name</th>
<th>Tax Rate</th>

</tr>
<?php
$count=1;
$rules = $db->fetchObjectArray("select * from it_category_taxes order by id");
foreach ($rules as $rule) { ?>
<tr>
<td><?php echo $rule->id; ?></td>
<td><?php echo $rule->category_name; ?></td>
<td><?php echo $rule->tax_name; ?></td>
<td><?php echo $rule->tax_rate; ?></td>

</tr>
<? }
if (count($rules) == 0) { ?>
<tr></tr>
<?php }
?>
</table>
</div>
</div>

<?php
	}
}
?>
