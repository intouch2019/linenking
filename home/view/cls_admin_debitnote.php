<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_admin_debitnote extends cls_renderer{
        var $currUser;
        var $userid;
        var $dtrange;
        var $errormsg;
        var $params;
        var $date; var $store; var $barcode; var $transaction;
        var $itemctg; var $designno; var $itemmrp; 
        var $linediscountper; var $linediscountval; var $ticketdiscountper;
        var $ticketdiscountval; var $totaldiscount; var $tax; var $brand;
        var $category; var $style; var $size; var $fabric; var $material;
        var $prodtype; var $manuf; var $gen; var $itemqty; var $itemvalue; var $totalvalue;
        var $storeidreport = null;var $a=0;
        var $storeloggedin = -1; 
        var $month=0;
        var $fields = array();
       
        function __construct($params=null) {
//		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
                ini_set('max_execution_time', 300);
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
                
                else { $this->dtrange = date("d-m-Y"); }
                
                if (isset($_SESSION['barcode'])) { 
                    
                    $this->barcode = $_SESSION['barcode'];
                    
                    }
                //}
                    $this->errormsg='';
                //date,store,billno,transaction,itemctg,designno,itemmrp,barcode,linediscountper,linediscountval,ticketdiscountper,
                //ticketdiscountval, totaldiscount, tax, brand, category, style, size, fabric, material, prodtgype, manuf.

                if (isset($params['str'])) 
                {
                    $arstrarr=  explode(":", $params['str']);
                    $this->storeidreport = $arstrarr[0]; 
                    $this->barcode=$arstrarr[1];
                    
                }else 
                    $this->storeidreport=null;
                  
                
                if (isset($params['month'])){ $this->fields['month'] =$params['month']; $this->month = $params['month']; }else{ $this->fields['month'] = "0"; }
                if(isset($params['a'])){ $this->a=$params['a'];}
                if($this->currUser->usertype==UserType::Dealer)
                { 
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
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        
        <?php
        }

        public function pageContent() {
            //$currUser = getCurrUser();
//            $menuitem = "bnewbatch";
            $menuitem = "debitnote";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn(); $write_htm = true;
            $categories = array(); $sizes = array(); $styles = array();
            $mfg_by = array(); $brands = array(); $prod_typs = array();
            $fabric_types = array();$materials = array();
            $sdate="";$edate="";
?>
<div class="grid_10">
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Generate Debit Note</legend>
	
        <form method="post">
		<div>
                <?php if($this->currUser->usertype != UserType::Dealer ){ ?>    
		
                    <div class="grid_4">
                    <b>Select Store*:</b><br/>
                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" style="width:100%;" onchange="reloadreport(-1)">
                <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }
                             else{ 
                                 $defaultSel = ""; 
                                 
                             } ?>
                <option value="-1" <?php echo $defaultSel;?>></option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");

if($this->storeidreport == "-1"){
    $storeid = array(); 
    if($this->a==0){ //means 'all stores report is req only in excel'
     $write_htm = false;   
    }
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeidreport);  
}

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
		
                    
                
                <br/>   <br/> 
              
                
                <?php  //22 fields ?>
               
                <b>Select Sale Back Invoice:</b>
                <select name="storeinvoice" id="storeinvoice" data-placeholder="Choose Saleback invoice" class="chzn-select" style="width:100%;" onchange="setfocus();">
        <?php //} 
        
        $datetmparr=explode("*", $this->dtrange);
        $dtarr = explode(" - ", $datetmparr[0]);
        $_SESSION['storeid'] = $this->storeidreport;
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";		
                $dQuery = " and invoice_dt >= '$sdate 00:00:00' and invoice_dt <= '$sdate 23:59:59' ";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";		
                $dQuery = " and invoice_dt >= '$sdate 00:00:00' and invoice_dt <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
        
        //print "$dQuery";
        if(isset($this->storeidreport))
        {
           $invoice_query="select invoice_no,invoice_dt from it_saleback_invoices where store_id=$this->storeidreport $dQuery";
        //print "$invoice_query";
        $invobjs=$db->fetchObjectArray($invoice_query);
        if($invobjs==null)
        {
            ?>
            <option value="1" <?php echo $selected; ?>>No Sale back invoices available in the selected date range</option>
        <?php
        
        }
        else{
        foreach ($invobjs as $obj) {        
	$selected="";

?>
            
          <option value="<?php echo $obj->invoice_no; ?>" <?php echo $selected; ?>><?php echo $obj->invoice_no; ?></option> 
        <?php }?>
        
          
          
               
   
                 <?php 
        
        echo '<input type="hidden" name="ref_date" value="'. ($obj->invoice_dt). '"/>';
        
        }//else end
        
        } 
        else
        {
          ?>
            <option value="1" <?php echo $selected; ?>>No Sale back invoices available</option>
        <?php  
        }
            //if store?>
            </select> 
             </div>
                 <div class="grid_4">   
                
                <b>Date Filter : </b><input size="25" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>"/> (Click to see date options) 
                <br/> 
                
                <br/><input type="text" id="prod_barcode" name="prod_barcode"  size="25" placeholder="Enter/Scan Barcode"  onchange="AddProduct();"/>(Click Here To Scan Or Enter Barcode)<br/><br/>
       
        </div>
          
		
            </div>
		<br/><br/>


       <br/><br/>
    <?php if (isset($this->storeidreport)) { //22 fields 
   
        
        
        $queryfields = "";
        $tableheaders = "";
        $total_td = "";
        $datetmparr=explode("*", $this->dtrange);
        $dtarr = explode(" - ", $datetmparr[0]); 
        $barcodearr=array();
        $barstring="";
        //print "$this->barcode";
        $pageRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) &&($_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0' ||  $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache'); 
        if($pageRefreshed == 1){
                $this->barcode='-1';
         }
                
         if($this->barcode!='2' && $this->barcode!='-1')
        {
            //$barcodearr=explode(",", $this->barcode);
             $barquery="select * from it_temp_barcodes";
             $barstrobj=$db->fetchObject($barquery);
             //print_r($barstrobj);
             if(isset($barstrobj))
             {
                $barstring=$barstrobj->barcode_string;
                $barstring =$this->barcode;
                $updatebar="update it_temp_barcodes set processed=0,barcode_string='$barstring'";
                $i=$db->execUpdate($updatebar);
             }
                     
        }
        else if($this->barcode=='-1')
        {
            //print "Yes:$this->barcode";
                $barstring ="";
                $updatebar="update it_temp_barcodes set processed=0,barcode_string='$barstring'";
                $i=$db->execUpdate($updatebar);
        }
        //$this->barcode="";
        //unset($_SESSION['barcode']);
        $barcodearr=explode(",", $barstring);
        
        
        ?>
       <br/><br/>
       <div class="grid_10" style="overflow-y: scroll;height: 250px">
        <table style="width:100%">
            <tr>
                <th colspan="6" align="center">Debit Note Details</th>
            </tr>
                        <tr>
                            
                            <th>Sr.No.:</th>
                            <th>Category</th>
                            <th>BARCODE</th>
                            <th>HSN</th>
                            <th>Total Quantity</th>
                            <th>Total Price</th>
                            
                            </tr>
                            <tr>
                            <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">
        <?php
        $qty=0;
        
        $barcodedata=array();
        for($i=1;$i<count($barcodearr);$i++)
        {
                    $barcodedata[$barcodearr[$i]]=$qty;
                    
        }
        //print_r($barcodearr);
        //print_r($barcodedata);
        
        foreach ($barcodedata as $key => $value) {
            if($key!=null)
            {
                for($i=1;$i<count($barcodearr);$i++)
                {
                    if($key==$barcodearr[$i])
                    {
                        $qty++;
                    }
                    $barcodedata[$key]=$qty;
                    
                }
            }
            $qty=0;
        }
        $cnt=1;
        $tvalue=0.0;
        $tqty=0.0;
        $this->fields=$barcodedata;
        //print_r($barcodedata);
        $flag=1;
        $rbarstring ="-1,";
        $checkbar="";
        foreach ($barcodedata as $key => $value) {
            
            if($key!=null)
            {
              $barcd=$key;
            $query="select barcode,mrp,ctg_id from it_items where barcode='$barcd'";
            $obj=$db->fetchObject($query);

            if(isset($obj))
            {
                $rbarstring .=$barcd;
                $hsnqry="select name,it_hsncode from it_categories where id=$obj->ctg_id";
                $hsnobj=$db->fetchObject($hsnqry);
                echo "<td>$cnt</td>";
                echo "<td>".($hsnobj->name)."</td>";
                echo "<td>$barcd</td>";
                echo "<td>".($hsnobj->it_hsncode)."</td>";
                echo "<td>$barcodedata[$barcd]</td>";
                echo "<td>".($obj->mrp*$barcodedata[$barcd])."</td>";
                
                $tvalue +=($obj->mrp*$barcodedata[$barcd]);
                $tqty +=$barcodedata[$barcd];
         
                echo '<input type="hidden" name="tpricearr[]" value="'. ($obj->mrp*$barcodedata[$barcd]). '">';
          
               
                
                //print "<br/>";
                
                $cnt++;
               $flag=1;   
            }
            else {
               
                $flag=0;
                $this->errormsg="Please enter valid barcode";
               
                //echo '<button type="button" id="okButton" onclick="funk()" value="okButton">Wrong barcode</button>';
                unset($this->fields[$barcd]);
                unset($barcodedata[$barcd]);
                
                
                
                
                $updatebar="update it_temp_barcodes set processed=0,barcode_string='$rbarstring'";
                $i=$db->execUpdate($updatebar);
            } 
            
            
            ?>
                    
             <?php
            
            } ?>
            
            </tr>
            </tbody>
            <?php
        }  
        if($flag==0)
        {
            echo '<span style="color:red;">Please Enter Valid Barcode</span>';
        }
  
        echo "<tfoot></tr><td></td><td></td><td></td><td><b>Total</b></td><td><b>$tqty</b></td><td><b>$tvalue</b></td></tr></tfoot>";
        ?>
        
       
                    
                    </table>
       </div>
<br/><br/><br/><br/>
    <?php }
        
    
           foreach($this->fields as $key=>$value)
            {
                echo '<input type="hidden" name="bararr[]" value="'. $key. '">';
                echo '<input type="hidden" name="qtyarr[]" value="'. $value. '">';
            }
        $_SESSION['storeid'] = $this->storeidreport;

        

          
 } ?>

       <br/><br/>

       <div class="grid_10">
           <br/>
       <lable>Add Remark Here:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</lable>
       <input type="text" name="remark" size="55"/><br/>
       <lable>Enter Freight/Insurance:</lable>
       <input type="text" name="frt_ins" size="15"/>
       <br/><br/>
       <input type="submit" id= "gen" value="Generate Debit Note" style="background-color:white;" formaction="formpost/gendebitnote.php"/>
   
    </div>
     </form>
	</fieldset>
    </div> <!-- class=box -->
  
    <?php //} 
     
    ?>
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
var storeid = '<?php echo $this->storeidreport; ?>'; 
var refinv='<?php echo $this->storeidreport; ?>'; 
var storeloggedin = '<?php echo $this->storeloggedin; ?>';
var cnt=1;
var barcodestring='<?php echo $this->barcode;?>';



//alert("STORE LOGGED IN: "+storeloggedin);
   $(function(){
       //alert(Yes);
       
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
				reloadreport(-1);
			}
		});
		}
	});
        
        
//        $('#dwnloadbtn').hide();
    });

    
    var fieldlist = new Array();
    
    function reloadreport(bstr) { 
        barcodestring=bstr;
       if(storeloggedin == '-1'){
           storeid = $('#store').val();
          //alert("SID:"+storeid);
       }
      //alert("1: "+storeid);
        var aclause='';
        if(storeid=='-1'){
          resp = confirm("Please select the store properly"); 
          if(resp){
              aclause='/a=1';
          }
        }

       if (storeid!="" && storeid != null) {
           
           
            window.location.href="admin/debitnote/str="+storeid+":"+barcodestring;
            setfocus();

       } else {
           alert("please select store(s) to genereate Debit Note");
       }
    }
    
    ///add product
    
    function AddProduct(){
            
            if(storeid=='-1'){
          confirm("Please select the store properly"); 
          return;
        
        }
        var invref=document.getElementById ("storeinvoice").value;//storeinvoice
        if(invref==1)
        {
            confirm("Please Select Saleback Invoice Properly");
            return;
        }
                  var code =document.getElementById ("prod_barcode").value;
                  //document.getElementById ("bstr").value=code;
                   //checkbarcode(code);
                         barcodestring +=","+code; 
                        //alert(barcodestring); 
                 $.ajax({
			url: "savesession.php?name=barcode&value="+barcodestring,
			success: function(data) {
                            reloadreport(barcodestring);
			}
		});
                
                 
                //setfocus();

    }

    


</script>
<?php
	}
        
        
}
?>
