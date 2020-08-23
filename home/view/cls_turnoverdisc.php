<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_turnoverdisc extends cls_renderer{
        var $currUser;
        var $userid;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                //print_r($this);
                if (!$this->currUser) { return; }
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
<script type="text/javascript">
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
function fetchSampleExcel(){
    //alert("hello");
    window.location.href="formpost/turnoverDiscExcel.php";
}

function setStoreincentives(){
    value = $("#filename").val();
    var formname=eval("storeseq");
    var params = $(formname).serialize();
//        alert(params);
    window.location.href = "formpost/updatestoreincentives.php?"+params;
}



function setTurnOverDisc(){
    //alert("hello");
    window.location.href="view/cls_turnoverdisc_upload.php";
}
</script>
<script type='text/javascript'>
// <![CDATA[
//jQuery(document).ready(function(){
//
//$('input:radio[id="td"]').change(function(){
//    if($(this).val() == 'turnover_disc'){
//       //alert("test");
//       //window.location.href ="creditnote"
//       document.getElementById("dsch").style.display="none";
//       document.getElementById("tvo").style.display="block";
//    }
////    else
////    {
////        alert("test");
////        document.getElementById("dsch").style.display="block";
////    }
//});
////});
//
////jQuery(document).ready(function(){
//$('input:radio[id="ds"]').change(function(){
//    if($(this).val() == 'disc_scheme'){
//       //alert("test");
//       //window.location.href ="creditnote"
//       document.getElementById("tvo").style.display="none";
//       document.getElementById("dsch").style.display="block";
//    }
//});
//
//});

// ]]>


$(function(){
    m1=0;
    m2=0;
    qtyear1="";
    qtyear2="";
    qt1=0;
    qt2=0;
     var dates = $( "#from, #to" ).datepicker({
            changeMonth: true,
                changeYear: true,
            numberOfMonths: 1,
            dateFormat: 'dd-mm-yy',
            onSelect: function( selectedDate ) {
                var option = this.id == "from" ? "minDate" : "maxDate",
                instance = $( this ).data( "datepicker" ),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
                        if(option=="minDate"){
                        
                            m1=date.getMonth();
                            
                                 
                            if(m1<=2)
                            {
                                qtyear1=""+(date.getFullYear()-1)+"-"+date.getFullYear();
                                qt1=4;
                                //alert("Yes"+m1);
                                //alert(qtyear);
                            }
                            else if(m1>=3 && m1<=5)
                            {
                                qtyear1=""+date.getFullYear()+"-"+(date.getFullYear()+1);
                                qt1=1;
                            }
                            else if(m1>=6 && m1<=8)
                            {
                                qtyear1=""+date.getFullYear()+"-"+(date.getFullYear()+1);
                                qt1=2;
                            }
                            else
                            {
                                qtyear1=""+date.getFullYear()+"-"+(date.getFullYear()+1);
                                qt1=3;
                            }
                            //alert(qt1+"for year"+qtyear1);
                        }
                         if(option=="maxDate"){
                        
                            m2=date.getMonth();
                           if(m2<=2)
                            {
                                qtyear2=""+(date.getFullYear()-2)+"-"+(date.getFullYear()-1);
                                qt2=4;
                                //alert(qtyear);
                            }
                            else if(m2>=3 && m2<=5)
                            {
                                qtyear2=""+(date.getFullYear()-1)+"-"+date.getFullYear();
                                qt2=1;
                            }
                            else if(m2>=6 && m2<=8)
                            {
                                qtyear2=""+(date.getFullYear()-1)+"-"+date.getFullYear();
                                qt2=2;
                            }
                            else
                            {
                                qtyear2=""+(date.getFullYear()-1)+"-"+date.getFullYear();
                                qt2=3;
                            }
                            

                                
                                //alert(qt1+"for year"+qtyear1+"*"+qt2+"-"+qtyear2);
                                var q1=qt1;
                                var q2=qt2;
                                if(q1==q2)
                                {
                                    //alert(Yes1);
                                    Qt(""+"For Q"+qt1+":F.Y."+qtyear1);
                                }
                                else
                                {
                                    Qt("Select Date for  a Quarter");
                                }
                        }   

                        }

        });
        
        
});    

function Qt(msg)
{
  //alert(msg);
  if(msg=="Select Date for  a Quarter")
  {
      document.getElementById("qtlabel").innerHTML=msg;
      document.getElementById("qtlabel").style.color = "#ff0000";
  }
  else
  {
      document.getElementById("qtlabel").innerHTML=msg;
      document.getElementById("qtlabel").style.color = "white";
  }
 
} 




function downloadpdf(downloadlink)
 {
     var c = confirm("Are U Sure Want To Genererate TD CreditNote ?");
     //var c = alert("r u sure ??");
     if(c){
       //alert(downloadlink);
         window.location.href = downloadlink;
     }
     
     
     
 }



</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "creditnote";
            //print 'ashish'; print_r($currUser->usertype);
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>

<div class="grid_10" id="tvo">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5"><button name="dwnFile" id="dwnFile" onclick="fetchSampleExcel();">Download Excel to Set Turnover Discount</button></div>
        <div class="clear"></div><br>
     <div class="grid_3">&nbsp;</div>
     <div class="grid_5">
     <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Set Turnover Discount</legend>	
        <form id="storeseq" name="storeseq" enctype="multipart/form-data" method="post" action="formpost/checkStoreIncentiveFile.php">      
            Select date range from below :<br/>                                         
                    From  :
                    <input id="from" type="text" name="from" style ="width:30%" value=""/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                    To:
                    <input id="to" type="text" name="to" style ="width:30%"  value=""/>
                    <br/><span id="qtlabel"></span><br/><br/>
            
            <div class="clsDid">Turnover Discount File (Excel)</div>
            <div class="clsText"><input type="file" id="file" name="file" ></div>
            <br/><br />
            <input type="submit" value="Validate File"/>
            <label>
            <?php
            $filename_arr= explode(".", $formResult->status);
             $fname=explode("(", $filename_arr[0]);
             $temp=$fname[0];
             if($temp!="")
             {
                 $temp=$fname[1].'.xls';
             }
             
             
             echo $temp;
            ?>
            </label>
            
            <input type="hidden" name="form_id" value="1"/>
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
            <br/>NOTE: Set/Update Turnover discount takes 2-3 minutes to complete.<br />Please wait for it to do so.<br />Do not hit the browser <b>refresh</b> or any other buttons

            </form>
	</fieldset>
    </div> <!-- class=box -->
    </div>
</div>




<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<?php
	}
}
?>
