<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_reprintinvoices extends cls_renderer{

        var $currUser;
        var $userid;
        var $storeidreport = null;
        var $params;
        var $fields;
        
        var $storeloggedin = -1;
        
       
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin,  UserType::Manager));                
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
                if (isset($params['store'])) { $this->fields['store']=$params['store']; $this->store = $params['store']; } else $this->fields['store']="0";               
                if (isset($params['invno'])) { 
                    $this->fields['invno']=$params['invno']; 
                   
                    
                }
                if (isset($params['from'])) { 
                    $this->fields['from']=$params['from']; 
                   
                    
                }
                if (isset($params['to'])) { 
                    $this->fields['to']=$params['to']; 
                   
                    
                }
                if($this->currUser->usertype == UserType::Dealer){ 
                    $this->storeidreport = $this->currUser->id;
                    $this->storeloggedin = 1;                    
                }
                
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
<script type="text/javascript">    
var storeid = '<?php echo $this->storeidreport; ?>';  
var storeloggedin = '<?php echo $this->storeloggedin; ?>';
//alert("STORE ID: "+storeid);
//alert("STORE LOGGED IN: "+storeloggedin);

// $(function(){
//     
//     
//     $("#from").datepicker({
//    minDate:'-1Y-6M',
//    maxDate: '0',
//    dateFormat: 'dd-mm-yy',
//    onSelect: function () {
//        var min = $(this).datepicker('getDate'); // Get selected date
//        //alert(min);
//        $("#to").datepicker('option', 'minDate',min); // Set other min, default to today
//    }
//});
//
//$("#to").datepicker({
//     minDate: '0',
//     maxDate: '0',
//    dateFormat: 'dd-mm-yy',
//    onSelect: function () {
//        var start = $("#from").datepicker("getDate");
//        var end = $("#to").datepicker("getDate");
//        var startmonth=start.getMonth();
//        var selected_qt=Math.floor(startmonth/ 3);
//        var qt_arr=new Array("Q1", "Q2", "Q3", "Q4");
//        var qt=qt_arr[selected_qt];
//        var ck_qt=Math.floor(end.getMonth()/ 3);
//        var qtck=qt_arr[ck_qt];
//        if(start.getFullYear()==end.getFullYear())
//        {
//            if(qt!=qtck)
//            {
//                alert("Please select the date from same quarter(upto three months)");
//            }
//        }
//        else
//        {
//            alert("Please select the date from same year and same quarter");
//        }
//
//    }
//});
 //       $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
        
//          var dates = $( "#from, #to" ).datepicker({
//    		changeMonth: true,
//                changeYear: true,
//    		numberOfMonths: 1,
//    		dateFormat: 'dd-mm-yy',
//                //dateFormat: 'yy-mm-dd',
//    		onSelect: function( selectedDate ) {
//                       //alert("Yes");
//    			var option = this.id == "from" ? "minDate" : "maxDate",
//    			instance = $( this ).data( "datepicker" ),
//    			date = $.datepicker.parseDate(
//    				instance.settings.dateFormat ||
//    				$.datepicker._defaults.dateFormat,
//    				selectedDate, instance.settings );
//    			dates.not( this ).datepicker( "option", option, date );
//                        //alert(option);
//                        if(option=="minDate")
//                        {
//                            //alert(to);
//                            $('#to').datepicker('option', 'maxDate', max || '+3M');
//                            //var days = (to- from);
//                           // alert(to);
//                        }
//    		}
//    	});
   // });
    
//    function reloadreport() {
//        //alert("Hello");
//     var append='';
//       $('#selectRight option').attr('selected', 'selected');
//       if(storeloggedin == '-1'){
//           storeid = $('#store').val();
//           invoicenum=$('#invno').val();
//           from=$('#from').val();
//           to=$('#to').val();
//          append='/invno='+invoicenum;
//          append+='/from='+from;
//          append+='/to='+to;
//          
//       }
//       
//       if(invoicenum=='' && from=='' && to=='')
//       {
//           alert("Please select the date range or invoice number");
//       }
//       else
//       {
//            window.location.href="reprintinvoices/str="+storeid+append;
//       }
//       
//       //window.location.href="reprintinvoices/str="+storeid+append;
//
//}
   function downloadpdf() {
        //alert("Hello");
     var append='';
       $('#selectRight option').attr('selected', 'selected');
       if(storeloggedin == '-1'){
           //storeid = $('#store').val();
           invoicenum=$('#invno').val();
           //from=$('#from').val();
           //to=$('#to').val();
          append='invno='+invoicenum;
         // append+='&from='+from;
         // append+='&to='+to;
          
       }
       
       if(invoicenum=='')
       {
           alert("Please select the invoice number");
       }
       else
       {
            window.location.href="formpost/generateAllPdfs.php?"+append;
       }
       
       //window.location.href="reprintinvoices/str="+storeid+append;

}
function AutoRefresh(  ) {
               alert("Please insert correct data range(upto three months from start date)");
            }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        
        <?php
        }

        public function pageContent() {
            //$currUser = getCurrUser();
            $menuitem = "storecurrStock";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $errors=array();
            $db = new DBConn();
            //print_r($this->field);
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Reprint Invoices</legend>	
        <form action="" method="">
		<div class="grid_12">
            <?php  ?>    
                    
		
                    <br/>
 
              
                 
                 <div class="grid_8" id="storeDiv1" name="storeDiv1">
                    <br/><b>Enter the</b>                                      
                    <b>Invoice Number</b>
                    <input id="invno" type="text" name="invno" style ="width:30%" value=""/>;                    
                      <br/>             
                </div> 
		</div>
             <?php  ?>    
            <div class="clear"></div>
            <br>
      
		<div class="grid_12" id="submitbutton" style="padding:10px;">
                    
                    <input type="button" name="add" id="add" value="Download" style="background-color:white;" onclick="downloadpdf();"/>
                
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->
    <?php if (isset($this->storeidreport))  { //12 fields ?>
    <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
        <?php 
            $queryfields = "";
            $tableheaders = "";
            $group_by = array();$gClause="";
            $storeClause="";
             $invoiceClause="";
             $dtClause = "" ;
            if($this->fields)
            {
                $invnumber=$this->fields['invno'];
                $from=$this->fields['from'];
                $startdate =yymmdd($from);
                $to=$this->fields['to'];
                $enddate=yymmdd($to);
                
                
                $effectiveDate = date('Y-m-d', strtotime("+3 months", strtotime($startdate)));
                
                if($startdate!=""){
    
                         if($enddate!="")
                            {
                                $date1=date_create($effectiveDate);
                                $date2=date_create($enddate);
                                $diff=date_diff($date2,$date1);
                                $dd=$diff->format("%R%a");
                                if($dd<0)
                                    {
                                        //echo "Please insert correct datarange(upto three months from start date) or Invoice number";
                                        $dtClause="";
                                    }
                                else {
                                $dtClause = "i.invoice_dt >= '$startdate 00:00:00' and i.invoice_dt <= '$enddate 14:15:43' ";
     
                                }  
        
                            }else {
                                    $dtClause = "i.invoice_dt >= '$startdate 00:00:00' and i.invoice_dt<='$startdate 23:59:59'";
                                  }
     

   		
            
  
}else{ $dtClause=""; }



if($invnumber!=""){
    if($dtClause=="")
    {
        $iClause="i.invoice_no=$invnumber";
    }
    else
    {
        $iClause="and i.invoice_no=$invnumber";
    }
   
   
}else{ $iClause="" ;}
                 
            }
            else {
                     
                     $dtClause="";
                     $iClause="";
                     
            }

            $tableheaders.="Invoice Number:";
            $tableheaders.="Invoice Date:";
            $tableheaders.=" ";
           
            
             if($dtClause=="" && $iClause=="")
             {

                 ;?>
        
                 <script>AutoRefresh();</script>
                 <?php
                 //exit;
             }
             else {
                 
                  $query = "select i.invoice_no,i.invoice_dt,i.id";
            //$query .= " from it_codes c,it_current_stock cs, it_items i, it_categories ctg, it_brands br, it_styles st, it_sizes si, it_fabric_types fb, it_materials mt, it_prod_types pr, it_mfg_by mfg  where c.id = cs.store_id and cs.id in ( $storeClause ) and cs.barcode = i.barcode and  ctg.id=i.ctg_id and br.id=i.brand_id and st.id=i.style_id and si.id=i.size_id and pr.id=i.prod_type_id and mt.id=i.material_id and fb.id=i.fabric_type_id and mfg.id=i.mfg_id and cs.store_id = c.id  $gClause ";
            $query .= " from it_invoices i where $dtClause $iClause and i.invoice_type=0";
            //echo $query;
            $objs=$db->fetchObjectArray($query);
            $result = $db->execQuery($query);
            
             }
           

?>
        <br />
        
<?php 
    $totqty =0 ; $totTotalVal=0;
    if($objs)
    {
        //print "No data";
        //$errors['nodata']="Please insert correct datarange(upto three months from start date) or Invoice number";
    
    if (isset($result)) {
        //print_r($result);
        $fp = fopen('tmp/StoreCurrStock.csv', 'w');
        $fp2 = fopen ('tmp/storecurrstock.htm', 'w');
        if ($fp) {
            $trow = array(); $tcell = array(); 
            //write header info
            fwrite($fp2,"<table width='100%' style='overflow:auto;'><thead><tr>");
            $headerarr = explode(":", $tableheaders); 
            foreach ($headerarr as $harr) {
                if ($harr != "") {
                    $tcell[] .= $harr;
                    fwrite($fp2,"<th>$harr</th>");
                }
            }
            fputcsv($fp, $tcell,',',chr(0));
            fwrite($fp2,"</tr></thead><tbody>");
            //write body
            while ($reportrows = $result->fetch_object()) {
                $tcell = null; 
                fwrite($fp2,"<tr>");
                $i=0;
                foreach ($reportrows as $field => $value) {
                   $i++;
                   $tcell[] .= $value;
                   //echo $field;
                   //var/www/ck_new_y/home/formpost/generateHTMLtoPDF.php
                   if($field=='id')
                   {
                       fwrite($fp2,"<td><a id='no' href='formpost/generateHTMLtoPDF.php?id=$value'>Download</a></td>");
                   }
                   else if($field=='invoice_dt')
                   {
                       fwrite($fp2,"<td>".yymmdd($value)."</td>");
                   }
                   else {
                       fwrite($fp2,"<td>$value</td>");
                   }
                   //fwrite($fp2,"<td>$value</td>");
                }
                fputcsv($fp, $tcell,',',chr(0));
                fwrite($fp2,"</tr>");
            } 
            
            fwrite ($fp2,"</tbody></table>");
            fclose ($fp); fclose ($fp2); 
            $table = file_get_contents("tmp/storecurrstock.htm");
            echo $table;
        } else {
            echo "<br/>Unable to create file. Contact Intouch.";
        }
    }
    }
 
?>
    </div>
    <?php } ?>
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">

   
</script>
<?php
	}
}
?>
