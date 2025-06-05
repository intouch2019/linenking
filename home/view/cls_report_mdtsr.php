<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_mdtsr extends cls_renderer {
    var $currUser;
    var $userid;
    var $storeidstr;
    var $show_report = false;
    var $from_dt;
    var $to_dt;

    function __construct($params=null) {
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->storeidstr = isset($_POST['storeidforvoucher']) && is_array($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : [];
        
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            $this->from_dt = date("Y-m-d", strtotime($_POST['start_date'])) . " 00:00:00";
            $this->to_dt = date("Y-m-d", strtotime($_POST['end_date'])) . " 23:59:59";
            $this->show_report = true;
        }
    }

    function extraHeaders() {
        ?>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/chosen/chosen.jquery.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js"></script>

        

        <style>
            .filter-section { margin-bottom: 20px; }
            .filter-section label { font-weight: bold; margin-bottom: 5px; display: block; }
            .filter-section select, .filter-section input[type="date"] {
                padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px; width: 100%;
            }
            .filter-section .form-group { margin-bottom: 20px; }
            .generate-btn {
                background-color: #007BFF; color: white; padding: 10px 20px;
                border: none; border-radius: 5px; font-size: 14px; cursor: pointer;
            }
            .generate-btn:hover { background-color: #0056b3; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
            th { background-color: #f2f2f2; }
        </style>

        <script>
            $(document).ready(function () {
                // Initialize Chosen.js
                $("#storeidforvoucher").chosen({
                    width: "100%",
                    placeholder_text_multiple: "Select one or more stores"
                });

                // Export to CSV
                $("#exportCSV").click(function () {
                    const table = document.querySelector("table");

                    if (!table || table.rows.length === 0) {
                        alert("No report available to export.");
                        return;
                    }

                    let csvContent = "";

                    for (let row of table.rows) {
                        let rowData = [];
                        for (let cell of row.cells) {
                            let text = cell.innerText.replace(/"/g, '""'); // Escape quotes
                            rowData.push(`"${text}"`);
                        }
                        csvContent += rowData.join(",") + "\r\n";
                    }

                    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = "SalesTaxReport.csv";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
                
                // Form validation on submit
                $("#filterForm").submit(function(event) {
                    if (!$("#storeidforvoucher").val() || !$("#start_date").val() || !$("#end_date").val()) {
                        alert("Please select store(s), start date, and end date.");
                        event.preventDefault(); // Prevent form submission
                    }
                });
            });
        </script>
        <?php
    }

    public function pageContent() {
        $menuitem = "mdtsr";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $db = new DBConn();
        $stores = $db->fetchObjectArray("SELECT id, store_name FROM it_codes WHERE usertype = 4");
?>
<div class="grid_10">
    <div class="box">
        <h2 style="margin-bottom:20px;">Sales Tax Slab Report</h2>
        <form method="post" id="filterForm">
            <div class="filter-section">
                <div class="form-group">
                    <div style="margin-bottom: 20px;">
                        <label for="store" style="font-size: 14px; margin-bottom: 5px; display: block;">*Select Store(s):</label>
                        <select name="storeidforvoucher[]" id="storeidforvoucher" multiple required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box;">
                            <option value="-1">All Stores</option>
                            <?php
                                $objs = $db->fetchObjectArray("SELECT id, store_name FROM it_codes WHERE usertype=4");
                                foreach ($objs as $obj) {
                                    ?>
                                    <option value="<?php echo $obj->id; ?>"> <?php echo $obj->store_name; ?></option>
                                <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" name="start_date" id="start_date" value="<?= isset($_POST['start_date']) ? $_POST['start_date'] : '' ?>" style="width: 180px; font-size: 14px; padding: 6px;" />
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" name="end_date" id="end_date" value="<?= isset($_POST['end_date']) ? $_POST['end_date'] : '' ?>" style="width: 180px; font-size: 14px; padding: 6px;" />
                    </div>
                    <div class="form-group">
                        <button type="submit" class="generate-btn">Generate Report</button>
                        <button type="button" id="exportCSV" class="generate-btn" style="background-color: green;">Export to CSV</button>
                    </div>
                </div>
            </div>
        </form>

        <?php if ($this->show_report): ?>
            <div class="block">
                <table>
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th>Bill No</th>
                            <th>Date</th>
                            <th>Ticket Type</th>
                            <th>Barcode</th>
                            <th>MRP</th>
                            <th>Item Disc Value</th>
                            <th>Qty</th>
                            <th>Tax Rate</th>
                            <th>MRP Tax per Item</th>
                            <th>Tax Rate (Sales Value)</th>
                            <th>Total Value</th>
                            <th>Tax per Item (Sales Value)</th>
                            <th>Tax Slab Changed?</th>
                            <th>Tax Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $store_ids = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : array();

                            if (in_array("-1", $store_ids)) {
                                // "All Stores" selected — get all store IDs from DB
                                $all_stores = $db->fetchObjectArray("SELECT id FROM it_codes WHERE usertype=4");
                                $store_ids = array_map(function($store) {
                                    return (int)$store->id;
                                }, $all_stores);
                            }

                            $store_id_str = implode(",", array_map('intval', $store_ids)); // final sanitized comma-separated string

                            $from_dt = $_POST['start_date']. " 00:00:00";
                            $to_dt = $_POST['end_date']." 23:59:59";

                         $query = "SELECT c.store_name, o.bill_no, o.bill_datetime AS date, o.tickettype, CASE WHEN o.tickettype = 3 THEN 'Cancelled' WHEN bt.has_negative_qty = 1 THEN CASE WHEN bt.has_discount = 1 THEN 'Discount' ELSE 'Credit Note' END WHEN bt.has_discount = 1 THEN 'Loyalty Discount' ELSE 'Sale' END AS transaction, oi.barcode, i.MRP, IFNULL(oi.discount_val, 0.0) AS itmdiscv, CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END AS quantity, CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END AS tax_rate, ((i.MRP / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END)) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END) AS mrptaxperitem, CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END AS taxrateperitemaspersalesvalue, IFNULL(( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ), 0.0) AS totalvalue, IFNULL(( ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ), 0.0) AS taxperitemaspersalevalue, CASE WHEN ( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) = ( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) THEN 'NO' ELSE 'YES' END AS taxslabchange,"
                    . " IFNULL(( ( ( i.MRP / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) - ( ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) ) ), 0.0) AS taxdifference FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_codes c ON o.store_id = c.id JOIN states s ON s.id = c.state_id JOIN region r ON c.region_id = r.id LEFT JOIN it_category_taxes ict ON ict.category_id = i.ctg_id JOIN ( SELECT o.id AS order_id, o.tickettype, MAX(CASE WHEN oi.quantity < 0 THEN 1 ELSE 0 END) AS has_negative_qty, MAX(CASE WHEN IFNULL(oi.discount_val, 0.0) > 0 THEN 1 ELSE 0 END) AS has_discount FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_order_payments p ON p.order_id = o.id AND TRIM(p.payment_name) = 'Loyalty'"
                    . " WHERE o.tickettype = 0 AND o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' GROUP BY o.id, o.tickettype ) AS bt ON bt.order_id = o.id "
                    . "WHERE o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' ORDER BY o.bill_datetime,o.bill_no, oi.barcode;"; 
                            
                            $results = $db->fetchObjectArray($query);

                            if($results){
                                foreach ($results as $row) {
                                    echo "<tr>";
                                    echo "<td>{$row->store_name}</td>";
                                    echo "<td>{$row->bill_no}</td>";
                                    echo "<td>{$row->date}</td>";
                                    echo "<td>{$row->transaction}</td>";
                                    echo "<td>{$row->barcode}</td>";
                                    echo "<td>" . formatSmart($row->MRP) . "</td>";
                                    echo "<td>" . formatSmart($row->itmdiscv) . "</td>";
                                    echo "<td>" . formatSmart($row->quantity) . "</td>";
                                    echo "<td>" . formatSmart($row->tax_rate) . "%</td>";
                                    echo "<td>" . formatSmart($row->mrptaxperitem) . "</td>";
                                    echo "<td>" . formatSmart($row->taxrateperitemaspersalesvalue) . "%</td>";
                                    echo "<td>" . formatSmart($row->totalvalue) . "</td>";
                                    echo "<td>" . formatSmart($row->taxperitemaspersalevalue) . "</td>";
                                    echo "<td>{$row->taxslabchange}</td>";
                                    echo "<td>" . formatSmart($row->taxdifference) . "</td>";
                                    echo "</tr>";
                                }
                            }else{
                                ?> <div id="noDataMessage" style="color: red; margin-top: 20px; font-weight: bold;">
                                    No data found.
                                </div><?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
    }
}

function formatSmart($value, $precision = 2) {
    if (is_numeric($value)) {
        return floor($value) == $value ? number_format($value, 0) : number_format($value, $precision);
    }
    return $value;
}
?>