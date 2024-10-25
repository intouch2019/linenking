<?php
//ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_admin_sendgrnmail extends cls_renderer {

    var $currStore;
    var $storeid;
    var $storeIds;
   
    function __construct() {
        if (isset($_SESSION['selectedStoreId'])) {
            $this->selectedStoreId = $_SESSION['selectedStoreId'];
        }
        $this->currStore = getCurrUser();
        $this->storeid = $this->currStore->id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeIds = $_POST['storeIds'];
        }
    }
 
    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>

        
        <style>
            .checkbox {
                font-size: 20px;
            }
            .form-row {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .form-roww {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .form-row label {
                min-width: 150px;
            }
            .escalation-row {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .escalation-row label {
                min-width: 150px;
                margin: 0;
            }
            .escalation-row input {
                margin: 0;
            }
            .escalation-row .date-label {
                margin-left: 20px;
            }
            .button-container {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
        </style>
        <script type="text/javascript">
           function confirmUpdate(id) {
    if (confirm("Are you sure you want to Send Email?" )) {
        updatestatus(id);
//        window.alert(id);
    }
}
    
 function updatestatus(value){
     $.ajax({
        url: 'ajax/resendgrnemail.php',
        type: 'GET',
        data: {id: value},
        success: function(response) {
//            window.alert( response);
            if (response == "1") {
                window.alert("Email send successfully....");
                // Refresh the list of invoices after successful update
            } else {
                                window.alert(response);

                window.alert("Not send....");
            }
        },
        error: function () {
            alert('An error occurred while clearing the invoice. Please try again.');
        }
    });
}
            
            
            
            
        </script>

        <?php 
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "sendgrnmail";
        include "sidemenu." . $this->currStore->usertype . ".php";

        ?>

        <div class="grid_10">
            <fieldset>
                <legend>Resend Mail</legend>
                <div class="grid_12">
                    <div><?php
$date = date('Y-m-d');
$query = "select * from it_grn_pdfs order by id desc limit 5 ";
    $invoices = $db->fetchObjectArray($query);
    if ($invoices) {
        echo "<h3>Mail Not send</h3>";
        echo "<table border='1'>
                <tr>
                    <th>File Name</th>
                    <th>Createtime</th>
                    <th>Update</th>

                </tr>";
        foreach ($invoices as $invoice) {
            $is_mailed = $invoice->is_mailed;
                        $name = basename($invoice->pdf_file_path);

//            print_r($after_replace);echo '<br>';
//            print_r($invoice->pdf_file_path);
            echo "<tr>
                    <td><a href='lib/grnPDFClass/pdf_files/$name'>$name</a></td>
                    <td>{$invoice->createtime}</td>";
//                        if ($is_mailed == "0") {
//        echo "<td><button class='process-btn' onclick='confirmUpdate()'>Send Mail</button>";
       echo" <td><button class='process-btn' data-invoice-id='{$invoice->id}' onclick = 'confirmUpdate($invoice->id)'>Send Mail</button></td>";

//    }
      echo "</td>
          </tr>";
                 
        }
        echo "</table>";
    }
             ?>
                    </div>
                </div>
            </fieldset>
        </div>

        <?php
    }
}

?>