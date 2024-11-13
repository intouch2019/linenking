<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";
    extract($_GET);
//    print_r($_GET);
        $db = new DBConn();
    ?>
<script> 
    function confirmUpdate(id) {
    if (confirm("Are you sure you want to Clear this item?" )) {
        updatestatus(id);
    }
}
    
 function updatestatus(value){
    console.log(value);
     $.ajax({
        url: 'ajax/ajax_process_invoice.php',
        type: 'GET',
        data: {id: value},
        success: function(response) {
            if (response == "1") {
                window.alert("Invoice cleared....");
                // Refresh the list of invoices after successful update
                $('#searchinvoice').click();
            } else {
                window.alert("Invoice not cleared....");
            }
        },
        error: function () {
            alert('An error occurred while clearing the invoice. Please try again.');
        }
    });
}
</script>
        <?php
if (isset($_GET['storeIds']) && !empty($_GET['storeIds'])) {
$storeId =$_GET['storeIds'];

    $query = "SELECT * FROM it_sp_invoices WHERE store_id = $storeId AND is_procsdForRetail = 0";
    $invoices = $db->fetchObjectArray($query);
    if ($invoices) {
        echo "<h3>Invoices for Store ID: $storeId</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Invoice ID</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>";
        foreach ($invoices as $invoice) {
            echo "<tr>
                    <td>{$invoice->id}</td>
                    <td>{$invoice->invoice_no}</td>
                    <td>{$invoice->invoice_dt}</td>
                    <td>{$invoice->invoice_amt}</td>
                        <td>
<button class='process-btn' data-invoice-id='{$invoice->id}' data-store-id='{$storeId}' onclick = 'confirmUpdate($invoice->id)'>Update Status</button>
                </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No invoices found for this store.";
    }
} else {
    echo "Invalid Store ID.";
}
?>