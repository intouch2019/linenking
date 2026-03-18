<?php
ini_set('memory_limit', '1024M');

require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

// 🔹 Common function to fetch report data
function getSaleAnalysisData($store_ids, $from_this, $to_this, $from_last, $to_last) {
    $db = new DBConn();

    // fetch selected stores
    $allStores = $db->fetchObjectArray("
        SELECT id, store_name, old_id 
        FROM it_codes 
        WHERE id IN ($store_ids) 
          AND usertype = 4
        ORDER BY store_name
    ");

    // build a map of old_id → new_id
    $map_old_to_new = [];
    foreach ($allStores as $s) {
        if ($s->old_id > 0) {
            $map_old_to_new[$s->old_id] = $s->id;
        }
    }

    $total_qty_this = $total_val_this = $total_qty_last = $total_val_last = 0;
    $rows = [];

    foreach ($allStores as $store) {
        // skip if this store itself is an old_id of another
        if (array_key_exists($store->id, $map_old_to_new)) {
            continue;
        }

        $store_ids_to_use = [$store->id];
        if ($store->old_id > 0) {
            $store_ids_to_use[] = $store->old_id;
        }
        $id_list = implode(",", $store_ids_to_use);

        // ---- This Year
        $row1 = $db->fetchObject("
            SELECT SUM(oi.quantity) as qty,
                   SUM(oi.price * oi.quantity) as val
            FROM it_orders o
            JOIN it_order_items oi ON oi.order_id=o.id
            WHERE o.store_id IN ($id_list)
              AND o.bill_datetime BETWEEN '$from_this' AND '$to_this'
        ");

        // ---- Last Year
        $row2 = $db->fetchObject("
            SELECT SUM(oi.quantity) as qty,
                   SUM(oi.price * oi.quantity) as val
            FROM it_orders o
            JOIN it_order_items oi ON oi.order_id=o.id
            WHERE o.store_id IN ($id_list)
              AND o.bill_datetime BETWEEN '$from_last' AND '$to_last'
        ");

        $qty_this = $row1 ? $row1->qty : 0;
        $val_this = $row1 ? $row1->val : 0;
        $qty_last = $row2 ? $row2->qty : 0;
        $val_last = $row2 ? $row2->val : 0;

        if($qty_this==0 && $val_this==0 && $qty_last==0 && $val_last==0){
            continue;
        }
        
        
       $ids = explode(',', $store->id);
$ids = array_map('trim', $ids);

// Optional: ensure only numbers (IMPORTANT for safety)
$ids = array_filter($ids, 'is_numeric');

if (empty($ids)) {
    continue;
}

$idList = implode(',', $ids);

$query = "SELECT * FROM it_codes WHERE old_id IN ($idList)";

$stmt = $db->fetchObjectArray($query);

if ($stmt) {
    continue;
}  
        
        $growth_qty = ($qty_last != 0) ? (($qty_this - $qty_last) / $qty_last) * 100 : 0;
        $growth_val = ($val_last != 0) ? (($val_this - $val_last) / $val_last) * 100 : 0;

        $rows[] = [
            "store" => $store->store_name, // always new store name
            "qty_this" => round($qty_this),
            "val_this" => round($val_this, 2),
            "qty_last" => round($qty_last),
            "val_last" => round($val_last, 2),
            "growth_qty" => round($growth_qty, 2),
            "growth_val" => round($growth_val, 2),
        ];

        $total_qty_this += $qty_this;
        $total_val_this += $val_this;
        $total_qty_last += $qty_last;
        $total_val_last += $val_last;
    }

    $total_growth_qty = ($total_qty_last != 0) ? (($total_qty_this - $total_qty_last) / $total_qty_last) * 100 : 0;
    $total_growth_val = ($total_val_last != 0) ? (($total_val_this - $total_val_last) / $total_val_last) * 100 : 0;

    return [$rows, $total_qty_this, $total_val_this, $total_qty_last, $total_val_last, $total_growth_qty, $total_growth_val];
}

// 🔹 Handle Excel Export
if (isset($_POST['export_excel'])) {
    $db = new DBConn();
    $store_ids = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : [];
    $currUser = getCurrUser();
$currentId = $currUser->id;

if (in_array("-1", $store_ids)) {
    $store_ids = array_map(function ($s) {
        return $s->id;
    },
        $db->fetchObjectArray("
            SELECT id FROM it_codes 
            WHERE usertype=4 
            AND id IN (
                SELECT store_id 
                FROM executive_assign 
                WHERE exe_id=$currentId
            )
        ")
    );
}
    $store_id_str = implode(",", array_map('intval', $store_ids));

    $from_this = date("Y-m-d", strtotime($_POST['this_start'])) . " 00:00:00";
    $to_this = date("Y-m-d", strtotime($_POST['this_end'])) . " 23:59:59";
    $from_last = date("Y-m-d", strtotime($_POST['last_start'])) . " 00:00:00";
    $to_last = date("Y-m-d", strtotime($_POST['last_end'])) . " 23:59:59";

    // 🔹 Labels for headings
    $this_range = date("d/m/Y", strtotime($_POST['this_start'])) . " - " . date("d/m/Y", strtotime($_POST['this_end']));
    $last_range = date("d/m/Y", strtotime($_POST['last_start'])) . " - " . date("d/m/Y", strtotime($_POST['last_end']));

    list($rows, $total_qty_this, $total_val_this, $total_qty_last, $total_val_last, $total_growth_qty, $total_growth_val) = getSaleAnalysisData($store_id_str, $from_this, $to_this, $from_last, $to_last);

//    header("Content-Type: application/vnd.ms-excel");
//    header("Content-Disposition: attachment; filename=storewise_sale_analysis.xls");
//    header("Pragma: no-cache");
//    header("Expires: 0");

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=storewise_sale_analysis.xls");
    header("Cache-Control: max-age=0");

//    header("Content-type: text/csv");
//header("Content-Disposition: attachment; filename=storewise_sale_analysis.csv");
//header("Pragma: no-cache");
//header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>Store Name</th>
            <th>Qty ($this_range)</th>
            <th>Value ($this_range)</th>
            <th>Qty ($last_range)</th>
            <th>Value ($last_range)</th>
            <th>Growth % Qty</th>
            <th>Growth % Value</th>
          </tr>";
    foreach ($rows as $r) {
        echo "<tr>
                <td>{$r['store']}</td>
                <td>{$r['qty_this']}</td>
                <td>{$r['val_this']}</td>
                <td>{$r['qty_last']}</td>
                <td>{$r['val_last']}</td>
                <td>{$r['growth_qty']}%</td>
                <td>{$r['growth_val']}%</td>
              </tr>";
    }
    echo "<tr style='font-weight:bold;background:#cceeff'>
            <td>TOTAL</td>
            <td>" . round($total_qty_this) . "</td>
            <td>" . round($total_val_this, 2) . "</td>
            <td>" . round($total_qty_last) . "</td>
            <td>" . round($total_val_last, 2) . "</td>
            <td>" . round($total_growth_qty, 2) . "%</td>
            <td>" . round($total_growth_val, 2) . "%</td>
          </tr>";
    echo "</table>";
    exit;
}

// 🔹 If not Excel, render HTML
require_once "view/cls_renderer.php";

class cls_report_totalsaleanalysis_yearwise extends cls_renderer {

    var $currUser;
    var $userid;

    function __construct($params = null) {
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() {
        ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>




        <script>
            $(function () {
                $("#storeidforvoucher").chosen({width: "100%"});
            });

            jQuery.browser = {};
            (function () {
                jQuery.browser.msie = false;
                jQuery.browser.version = 0;
                if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
                    jQuery.browser.msie = true;
                    jQuery.browser.version = RegExp.$1;
                }
            })();



            function validateForm() {
                let stores = document.getElementById("storeidforvoucher").value;
                let thisStart = document.querySelector("[name='this_start']").value;
                let thisEnd = document.querySelector("[name='this_end']").value;
                let lastStart = document.querySelector("[name='last_start']").value;
                let lastEnd = document.querySelector("[name='last_end']").value;

                if (!stores) {
                    alert("Please select at least one store.");
                    return false;
                }
                if (!thisStart || !thisEnd || !lastStart || !lastEnd) {
                    alert("Please select all date fields.");
                    return false;
                }

                if (thisStart > thisEnd || lastStart > lastEnd) {
                    alert("Start date cannot be later than the end date.\n Please select a valid date range.");
                    return false;
                }


                return true;
            }



        </script>
        <style>
            form {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
            label {
                font-weight: bold;
                margin-right: 10px;
                color: #333;
            }
            input[type="date"] {
                padding: 6px 10px;
                border: 1px solid #ccc;
                border-radius: 6px;
                margin: 5px;
                background: #fffbe6;
            }
            select {
                padding: 6px;
                border-radius: 6px;
                border: 1px solid #ccc;
                background: #e6f7ff;
            }
            button {
                padding: 8px 14px;
                margin: 8px 5px 0 0;
                border: none;
                border-radius: 6px;
                font-weight: bold;
                cursor: pointer;
            }
            button[name="generate"] {
                background: #4CAF50;
                color: white;
            }
            button[name="generate"]:hover {
                background: #45a049;
            }
            button[name="export_excel"] {
                background: #2196F3;
                color: white;
            }
            button[name="export_excel"]:hover {
                background: #1976D2;
            }
            table {
                border-collapse: collapse;
                margin-top: 20px;
                width: 100%;
            }
            th, td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: center;
            }
            th {
                background: #f2f2f2;
            }
            tr:nth-child(even) {
                background: #f9f9f9;
            }
            tr:hover {
                background: #f1f1f1;
            }
        </style>

        <?php
    }

    public function pageContent() {
        $store_id = $this->currUser->id;
        if (isset($store_id) && ctype_digit((string) $store_id)) {
            $currentId = (int) $store_id;
        } else {
            $currentId = 0;
        }
        $menuitem = "totalsaleanalysisyearwise";
        include "sidemenu." . $this->currUser->usertype . ".php";
        ?>
        <div class="grid_10">
            <div class="box">
                <h2>Store-wise Sale Analysis </h2>
                <?php
// Capture posted values safely
                $selectedStores = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : [];
                $this_start = isset($_POST['this_start']) ? $_POST['this_start'] : "";
                $this_end = isset($_POST['this_end']) ? $_POST['this_end'] : "";
                $last_start = isset($_POST['last_start']) ? $_POST['last_start'] : "";
                $last_end = isset($_POST['last_end']) ? $_POST['last_end'] : "";
                ?>
                <form method="post" onsubmit="return validateForm()">

                    <label>Select Store(s):</label>
                    <select name="storeidforvoucher[]" id="storeidforvoucher" multiple size="5">
                        <option value="-1" <?= in_array("-1", $selectedStores) ? "selected" : "" ?>>All Stores</option>

                        <?php
                        $db = new DBConn();
                        $stores = $db->fetchObjectArray("SELECT id, store_name FROM it_codes 
                WHERE usertype=4 
                AND id IN (SELECT store_id FROM executive_assign WHERE exe_id=$currentId)
                ORDER BY store_name");

                        foreach ($stores as $store) {
                            $sel = in_array($store->id, $selectedStores) ? "selected" : "";
                            echo "<option value='{$store->id}' $sel>{$store->store_name}</option>";
                        }
                        ?>
                    </select>

                    <br><br>

                    <label>This Year Start: 
                        <input type="date" name="this_start" max="<?= date('Y-m-d') ?>" value="<?= $this_start ?>">
                    </label>

                    <label>This Year End: 
                        <input type="date" name="this_end" max="<?= date('Y-m-d') ?>" value="<?= $this_end ?>">
                    </label>

                    <br><br>

                    <label>Last Year Start: 
                        <input type="date" name="last_start" max="<?= date('Y-m-d') ?>" value="<?= $last_start ?>">
                    </label>

                    <label>Last Year End: 
                        <input type="date" name="last_end" max="<?= date('Y-m-d') ?>" value="<?= $last_end ?>">
                    </label>

                    <br><br>

                    <button type="submit" name="generate">Generate Report</button>
                    <button type="submit" name="export_excel">Export to Excel</button>

                </form>


                <?php
                if (isset($_POST['generate'])) {
                    $store_ids = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : [];

//                    echo $store_ids;
                    if (in_array("-1", $store_ids)) {
                        $store_ids = array_map(function ($s) {
                            return $s->id;
                        },
                                $db->fetchObjectArray("SELECT id FROM it_codes WHERE usertype=4 and id in (select store_id from executive_assign where exe_id=$currentId)"));
                    }
                    $store_id_str = implode(",", array_map('intval', $store_ids));

                    $from_this = date("Y-m-d", strtotime($_POST['this_start'])) . " 00:00:00";
                    $to_this = date("Y-m-d", strtotime($_POST['this_end'])) . " 23:59:59";
                    $from_last = date("Y-m-d", strtotime($_POST['last_start'])) . " 00:00:00";
                    $to_last = date("Y-m-d", strtotime($_POST['last_end'])) . " 23:59:59";

                    // 🔹 Labels for headings
                    $this_range = date("d/m/Y", strtotime($_POST['this_start'])) . " - " . date("d/m/Y", strtotime($_POST['this_end']));
                    $last_range = date("d/m/Y", strtotime($_POST['last_start'])) . " - " . date("d/m/Y", strtotime($_POST['last_end']));

                    list($rows, $total_qty_this, $total_val_this, $total_qty_last, $total_val_last, $total_growth_qty, $total_growth_val) = getSaleAnalysisData($store_id_str, $from_this, $to_this, $from_last, $to_last);

                    echo "<br><table border='1' cellpadding='5' cellspacing='0'>";
                    echo "<tr style='background:#eee'>
                            <th>Store Name</th>
                            <th>Qty ($this_range)</th>
                            <th>Value ($this_range)</th>
                            <th>Qty ($last_range)</th>
                            <th>Value ($last_range)</th>
                            <th>Growth % Qty</th>
                            <th>Growth % Value</th>
                          </tr>";
                    foreach ($rows as $r) {
                        echo "<tr>
                                <td>{$r['store']}</td>
                                <td>{$r['qty_this']}</td>
                                <td>{$r['val_this']}</td>
                                <td>{$r['qty_last']}</td>
                                <td>{$r['val_last']}</td>
                                <td>{$r['growth_qty']}%</td>
                                <td>{$r['growth_val']}%</td>
                              </tr>";
                    }
                    echo "<tr style='font-weight:bold;background:#cceeff'>
                            <td>TOTAL</td>
                            <td>" . round($total_qty_this) . "</td>
                            <td>" . round($total_val_this, 2) . "</td>
                            <td>" . round($total_qty_last) . "</td>
                            <td>" . round($total_val_last, 2) . "</td>
                            <td>" . round($total_growth_qty, 2) . "%</td>
                            <td>" . round($total_growth_val, 2) . "%</td>
                          </tr>";
                    echo "</table>";
                }
                ?>
            </div>
        </div>
        <?php
    }

}
?>
