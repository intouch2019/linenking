<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_addcreditpoint extends cls_renderer{
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
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
    window.location.href="formpost/addCreditpointExcel.php";
}

function toggleDateInputs() {
    const schemeInput = document.getElementById('scheme').value;
    const monthInput = document.getElementById('monthyear');
    const monthDiv = document.getElementById('monthInputDiv');
    const dateRangeDiv = document.getElementById('dateRangeDiv');

    if (schemeInput === "2" || schemeInput === "3") {
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
    alert("If scheme periods match for EOSS and Loyalty+EOSS, choose either EOSS or Loyalty+EOSS.");
    const schemeInput = document.getElementById('scheme').value;
    const monthInput = document.getElementById('monthyear').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    if (schemeInput === "0") {
        alert("Please select a scheme before downloading.");
        return false;
    }

    if (schemeInput === "2" || schemeInput === "3") {
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

function uploadfile(event){
    event.preventDefault(); // Prevent default form submission
    var fileInput = document.getElementById('file');
    var file = fileInput.files[0];
    var reader = new FileReader();

    reader.onload = function(e) {
        var data = new Uint8Array(e.target.result);
        var workbook = XLSX.read(data, { type: 'array' });
        var sheet = workbook.Sheets[workbook.SheetNames[0]];
        var jsonData = XLSX.utils.sheet_to_json(sheet);

        // Calculate sum of credit points
        var sum = 0;
        var storecnt=0;
        jsonData.forEach(function(row) {
            if (row["Credit Point"]) {
                sum += parseFloat(row["Credit Point"]);
                storecnt++;
            }
        });

        // Display sum in an alert
var confirmation = confirm('Sum of stores: ' + storecnt + '\nSum of credit points: ' + sum + '\n\nDo you want to proceed?');

        // Submit the form after file processing
        if (confirmation) {
     document.getElementById("storeseq").submit();
    } else {

    }

    };

    reader.readAsArrayBuffer(file);
}


</script>
<script type='text/javascript'>

</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "addcreditnote";    // pagecode
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
	<legend>Add Credit Point</legend>	
        <br><center><button name="dwnFile" id="dwnFile" onclick="fetchSampleExcel();">Download Excel to add Credit Point</button></center><br><br>
        <form id="cpForm" method="post" action="formpost/getCreditpointExcel.php">
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
                    Download to Get Credit Point Excel
                </button>
            </center>
        </form>
        <br><br>
        <form  id="storeseq" name="storeseq" enctype="multipart/form-data" method="post" action="formpost/checkStoreCreditPoint.php">      
                    
            <div>
            <div class="clsDid" >Add Credit Point File (Excel)</div>
            <div class="clsText"><input type="file" id="file" name="file" ></div>
            <br/>
            <input type="submit" onclick="uploadfile(event)" value="Submit File"/>
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
    </div> <!-- class=box -->
    </div>
</div>




<script src="<?php echo CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<?php
	}
}
?>