<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_cp_calculations extends cls_renderer{
        var $currUser;
        var $userid;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
        }

	function extraHeaders() {
        ?>
        <link rel="stylesheet" href='js/chosen/chosen.css' />
<script type="text/javascript" src= 'js/ajax.js'></script>
<script type="text/javascript" src= 'js/ajax-dynamic-list.js' >
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
<link rel="stylesheet" href='css/bigbox.css' type="text/css" />

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
    window.location.href="formpost/cpCalculationsExcel.php";
}

function uploadFile(){
    value = $("#filename").val();
    var formname=eval("storeseq");
    var params = $(formname).serialize();
     //   alert(params);
    window.location.href = "formpost/checkStoreCreditPointCalculations.php?"+params;
}

function toggleDateInputs() {
    const schemeInput = document.getElementById('scheme').value;
    const monthInput = document.getElementById('monthyear');
    const monthDiv = document.getElementById('monthInputDiv');
    const dateRangeDiv = document.getElementById('dateRangeDiv');

    if (schemeInput === "2") {
        monthInput.disabled = true;
        monthDiv.style.display = "none";
        dateRangeDiv.style.display = "block";
    } else {
        monthInput.disabled = false;
        monthDiv.style.display = "block";
        dateRangeDiv.style.display = "none";
    }
}

function fetchCreditPointExcel() {
    const schemeInput = document.getElementById('scheme').value;
    const monthInput = document.getElementById('monthyear').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    if (schemeInput === "0") {
        alert("Please select a scheme before downloading.");
        return false;
    }

    if (schemeInput === "2") {
        if (!fromDate || !toDate) {
            alert("Please select both From and To dates.");
            return false;
        }
        //disbaled EOSS Scheme temporary (to enable remove below two lines)
//        alert("Right Now EOSS is Disabled");
//        return false;
    } else {
        if (!monthInput) {
            alert("Please select a month before downloading.");
            return false;
        }
    }

    document.getElementById('cpForm').submit();
}

function handleStoreSchemeChange() {
    const scheme = document.getElementById('scheme2').value;
    const monthDiv = document.getElementById('storeMonthDiv');
    const monthInput = document.getElementById('monthyear_store');
    const dateRangeDiv = document.getElementById('storeDateRangeDiv');

    if (scheme === "2") {
        // Hide month input
        monthDiv.style.display = "none";
        monthInput.disabled = true;
        dateRangeDiv.style.display = "block";
    } else {
        // Show month input
        monthDiv.style.display = "block";
        monthInput.disabled = false;
        dateRangeDiv.style.display = "none";
    }
}

function validateAndSubmitStoreForm() {
    const form = document.getElementById('storeseq');
    const scheme = document.getElementById('scheme2').value;
    const monthInput = document.getElementById('monthyear_store').value;
    const fileInput = document.getElementById('file').value;
    const fromDate = document.getElementById('fromDateTime').value;
    const toDate = document.getElementById('toDateTime').value;

    if (!fileInput) {
        alert("Please upload an Excel file.");
        return false;
    }

    if (scheme === "0") {
        alert("Please select a scheme.");
        return false;
    }

    if (scheme === "2") {
        if (!fromDate || !toDate) {
            alert("Please select both From and To dates.");
            return false;
        }
        //disbaled EOSS Scheme temporary (to enable remove below two lines)
//        alert("Right Now EOSS is Disabled");
//        return false;
    } else {
        if (!monthInput) {
            alert("Please select a month before submit.");
            return false;
        }
    }

    form.submit();
}

</script>
<script type='text/javascript'>

</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "cpCalculations";    // pagecode
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>

<div class="grid_10" id="tvo">
    <div class="grid_3">&nbsp;</div>
  
        <div class="clear"></div><br>
     <div class="grid_3">&nbsp;</div>
     <div class="grid_5">
     <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Credit Point Calculations</legend>	
        <br><center><button name="dwnFile" id="dwnFile" onclick="fetchSampleExcel();">Download Blank Excel for Credit Point Calculations</button></center><br><br>
         <form id="cpForm" method="post" action="formpost/discountSchemeUploadExcel.php">
            <center>
                <label for="scheme">Select Scheme *</label><br>
                <select name="scheme" id="scheme" onchange="toggleDateInputs()">
                    <option value="0">Select Scheme</option>
                    <?php foreach (Discount_scheme::getALL() as $id => $name) { ?>
                        <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php } ?>
                </select>
                <br>

                <div id="monthInputDiv">
                    <label for="monthyear">Select Month *</label><br>
                    <input type="month" name="monthyear" id="monthyear"><br><br>
                </div>

                <div id="dateRangeDiv" style="display:none;">
                    <label for="fromDate">From Date *</label><br>
                    <input type="date" name="fromDate" id="fromDate"><br>
                    <label for="toDate">To Date *</label><br>
                    <input type="date" name="toDate" id="toDate"><br><br>
                </div>

                <button type="button" name="dwnFile" id="dwnFile" onclick="fetchCreditPointExcel();">
                    Download Excel for Credit Point Calculations
                </button>
            </center>
        </form>
        <hr style="border: 3px solid white; margin: 40px 0;">
        <form  id="storeseq" name="storeseq" enctype="multipart/form-data" method="post" action="formpost/checkStoreCreditPointCalculations.php">      
                    
            <div>
            <div class="clsDid" >Add File (Excel)</div>
            <div class="clsText"><input type="file" id="file" name="file" ></div>
            <br/>
             <!-- Scheme Selection -->
            <label for="scheme">Select Scheme *</label><br>
            <select name="scheme" id="scheme2" onchange="handleStoreSchemeChange()" required>
                <option value="0">Select Scheme</option>
                <?php foreach (Discount_scheme::getALL() as $id => $name) { ?>
                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php } ?>
            </select>
            <br/>
            
            <div id="storeMonthDiv">
                    <label for="monthyear">Select Month *</label><br>
                    <input type="month" name="monthyear" id="monthyear_store" size="60"><br><br>
                </div>

                <div id="storeDateRangeDiv" style="display:none;">
                    <label for="fromDate">From Date *</label><br>
                    <input type="date" name="fromDate" id="fromDateTime"><br>
                    <label for="toDate">To Date *</label><br>
                    <input type="date" name="toDate" id="toDateTime"><br><br>
                </div>

            <button type="button" onclick="validateAndSubmitStoreForm()">Submit File</button>
            </div>
            <label>
            <?php
            $filename_arr= explode(".", $formResult->status);
             $fname=explode("(", $filename_arr[0]);
             $temp=$fname[0];
             if($temp="")
             {
                 $temp=$fname[1].'.xls';
             }
             
             
             echo $temp;
            ?>
            </label>
            
            <input type="hidden" name="form_id" value="1"/>
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
            <br/>Do not hit the browser <b>refresh</b> or any other buttons           
            </form>
	</fieldset>
    </div>
    </div>
</div>




<script src="<?php echo CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<?php
	}
}
?>