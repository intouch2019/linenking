<?php
ini_set('memory_limit', '1024M'); // or '1G' for 1 GB

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_phonewisebills extends cls_renderer {

    var $currUser;
    var $userid;
    var $storeidstr;
    var $show_report = false;
    var $from_dt;
    var $to_dt;

    function __construct($params = null) {
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
            .filter-section {
                margin-bottom: 20px;
            }
            .filter-section label {
                font-weight: bold;
                margin-bottom: 5px;
                display: block;
            }
            .filter-section select, .filter-section input[type="date"] {
                padding: 10px;
                font-size: 14px;
                border: 1px solid #ccc;
                border-radius: 5px;
                width: 100%;
            }
            .filter-section .form-group {
                margin-bottom: 20px;
            }
            .generate-btn {
                background-color: #007BFF;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
            }
            .generate-btn:hover {
                background-color: #0056b3;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #ccc;
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #f2f2f2;
            }
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
                    var stores = $("#storeidforvoucher").val();
                    var startDate = $("#start_date").val();
                    var endDate = $("#end_date").val();

                    if (!stores || stores.length === 0 || !startDate || !endDate) {
                        alert("Please select store(s), start date, and end date.");
                        return;
                    }

                    var start = new Date(startDate);
                    var end = new Date(endDate);

                    // Calculate difference in days
                    var timeDiff = end - start;
                    var diffDays = timeDiff / (1000 * 3600 * 24);

//                    if (diffDays > 31) {
//                        alert("Date range should not exceed 1 month.");
//                        event.preventDefault();
//                        return;
//                    }

                    // If table is empty, fetch CSV via GET
                    if (!table || table.rows.length === 0) {

                        var storeParams = stores.map(function (id) {
                            return "storeidforvoucher[]=" + encodeURIComponent(id);
                        }).join("&");

                        var url = "ajax/generate_phonewise_csv.php?" + storeParams + "&start_date=" + encodeURIComponent(startDate) + "&end_date=" + encodeURIComponent(endDate);

                        $.ajax({
                            url: url,
                            method: "GET",
                            xhrFields: {responseType: 'blob'}, // to receive blob
                            success: function (response, status, xhr) {

                                // Get filename from header if present, fallback to default
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                var filename = "phonewisebills.csv";
                                if (disposition && disposition.indexOf('filename=') !== -1) {
                                    var matches = disposition.match(/filename="?([^"]+)"?/);
                                    if (matches.length > 1)
                                        filename = matches[1];
                                }

                                var link = document.createElement('a');
                                link.href = window.URL.createObjectURL(response);
                                link.download = filename;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);




                            },
                            error: function (xhr, status, error) {
                                alert("Failed to download CSV. Please try again.");
                                console.error("AJAX failed: ", status, error);
                            }
                        });

                        return;
                    }

                    // Else: CSV export from existing table
                    let csvContent = "";
                    for (let row of table.rows) {
                        let rowData = [];
                        for (let cell of row.cells) {
                            let text = cell.innerText.replace(/"/g, '""');
                            rowData.push('"' + text + '"');
                        }
                        csvContent += rowData.join(",") + "\r\n";
                    }

                    const blob = new Blob([csvContent], {type: "text/csv;charset=utf-8;"});
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = "phonewiseReport.csv";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });


                // Form validation on submit
                $("#filterForm").submit(function (event) {
                    var stores = $("#storeidforvoucher").val();
                    var startDate = $("#start_date").val();
                    var endDate = $("#end_date").val();

                    if (!stores || stores.length === 0 || !startDate || !endDate) {
                        alert("Please select store(s), start date, and end date.");
                        event.preventDefault();
                        return;
                    }

                    var start = new Date(startDate);
                    var end = new Date(endDate);

                    // Calculate difference in days
                    var timeDiff = end - start;
                    var diffDays = timeDiff / (1000 * 3600 * 24);

//                    if (diffDays > 31) {
//                        alert("Date range should not exceed 1 month.");
//                        event.preventDefault();
//                        return;
//                    }
                });

            });
        </script>
        <?php
    }

    public function pageContent() {
        $menuitem = "phonewisebills ";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $db = new DBConn();
        $stores = $db->fetchObjectArray("SELECT id, store_name FROM it_codes WHERE usertype = 4");
        ?>
        <div class="grid_10">
            <div class="box">
                <h2 style="margin-bottom:20px;">Membership Bill Count Report</h2>
                <form method="post" id="filterForm">
                    <div class="filter-section">
                        <div class="form-group">
                            <div style="margin-bottom: 20px;">
                                <label for="store" style="font-size: 14px; margin-bottom: 5px; display: block;">*Select Store(s):</label>
                                <select name="storeidforvoucher[]" id="storeidforvoucher" multiple size="5" style="width: 300px;">
                                    <option value="-1" <?= (isset($_POST['storeidforvoucher']) && in_array("-1", $_POST['storeidforvoucher'])) ? "selected" : "" ?>>All Stores</option>
                                    <?php
                                    $stores = $db->fetchObjectArray("SELECT id, store_name FROM it_codes WHERE usertype = 4 ORDER BY store_name");
                                    $selectedStores = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : array();
                                    foreach ($stores as $store) {
                                        $selected = in_array($store->id, $selectedStores) ? "selected" : "";
                                        echo "<option value=\"$store->id\" $selected>$store->store_name</option>";
                                    }
                                    ?>
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
                                 <span id="csvExportHint" style="color: red; font-weight: bold; margin-bottom: 10px;">
                                    To directly generate CSV, please select store(s), start date, and end date, then click on Export to CSV.
                                </span>
                            </div>
                            <div class="form-group">
                               <span id="csvGeneratingMsg" style="color: blue; font-weight: bold; display: none; margin-bottom: 10px;">
                                    Please wait while CSV is being generated...
                                </span>

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
                                    <th>Mobile No.</th>
                                     <th>Count</th>
                                     <th>Bill No</th>
                                    <th>Bill Value</th>
                                   

                                </tr>
                            </thead>
                            <tbody id="reportData">
                                <?php
                                $store_ids = isset($_POST['storeidforvoucher']) ? $_POST['storeidforvoucher'] : array();

                                if (in_array("-1", $store_ids)) {
                                    // "All Stores" selected — get all store IDs from DB
                                    $all_stores = $db->fetchObjectArray("SELECT id FROM it_codes WHERE usertype=4");
                                    $store_ids = array_map(function ($store) {
                                        return (int) $store->id;
                                    }, $all_stores);
                                }

                                $store_id_str = implode(",", array_map('intval', $store_ids)); // final sanitized comma-separated string

                                $from_dt = $_POST['start_date'] . " 00:00:00";
                                ;
                                $to_dt = $_POST['end_date'] . " 23:59:59";

//                                $query = "SELECT c.store_name , o.store_id, o.cust_phone, o.bill_no, o.sub_total, sub.loyalty_count FROM it_orders o  join it_codes c on c.id = o.store_id  JOIN it_order_payments iop ON iop.order_id = o.id JOIN ( SELECT o2.cust_phone, COUNT(*) AS loyalty_count FROM it_orders o2 JOIN it_order_payments iop2 ON iop2.order_id = o2.id WHERE o2.cust_phone IS NOT NULL AND o2.cust_phone != '' AND iop2.payment_name = 'loyalty' GROUP BY o2.cust_phone HAVING COUNT(*) > 2 ) AS sub ON sub.cust_phone = o.cust_phone WHERE iop.payment_name = 'loyalty' and o.store_id in($store_id_str) and o.bill_datetime between '$from_dt' AND '$to_dt' ORDER BY loyalty_count desc";

                                
                                
                                
                              $query =  "SELECT c.store_name, o.store_id, o.cust_phone, o.bill_no, o.sub_total, sub.loyalty_count FROM it_orders o JOIN it_codes c ON c.id = o.store_id JOIN it_order_payments iop ON iop.order_id = o.id JOIN ( SELECT o2.cust_phone, COUNT(*) AS loyalty_count FROM it_orders o2 JOIN it_order_payments iop2 ON iop2.order_id = o2.id WHERE o2.cust_phone IS NOT NULL AND o2.cust_phone != '' AND iop2.payment_name = 'loyalty' AND o2.store_id in($store_id_str) AND o2.bill_datetime BETWEEN  '$from_dt' AND '$to_dt'  GROUP BY o2.cust_phone HAVING COUNT(*) > 2 ) AS sub ON sub.cust_phone = o.cust_phone WHERE iop.payment_name = 'loyalty' AND o.store_id in($store_id_str) AND o.bill_datetime BETWEEN  '$from_dt' AND '$to_dt'  ORDER BY sub.loyalty_count DESC, o.cust_phone, o.id DESC";
                                
                                
                                
                                
                                
                                
                                echo $query;
                                $results = $db->fetchObjectArray($query);
                                if ($results) {
                                    foreach ($results as $row) {
                                        echo "<tr>";
                                        echo "<td>{$row->store_name}</td>";
                                        echo "<td>{$row->cust_phone}</td>";
                                        echo "<td>{$row->loyalty_count}</td>";
                                        echo "<td>{$row->bill_no}</td>";
                                        echo "<td>{$row->sub_total}</td>";

                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="15" style="color:red; font-weight:bold;">No data found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination Controls -->
                        <div id="pagination" style="margin-top: 10px; text-align: center;"></div>

                        <script>
                            // Smart Pagination - Vanilla JS
                            (function () {
                                var rowsPerPage = 15;
                                var maxPageButtons = 8;
                                var table = document.getElementById("reportData");
                                var rows = table.getElementsByTagName("tr");
                                var totalRows = rows.length;
                                var totalPages = Math.ceil(totalRows / rowsPerPage);
                                var currentPage = 1;

                                function showPage(page) {
                                    currentPage = page;
                                    var start = (page - 1) * rowsPerPage;
                                    var end = start + rowsPerPage;

                                    for (var i = 0; i < totalRows; i++) {
                                        rows[i].style.display = (i >= start && i < end) ? "" : "none";
                                    }

                                    updatePagination();
                                }

                                function updatePagination() {
                                    var pagination = document.getElementById("pagination");
                                    pagination.innerHTML = "";

                                    if (totalPages <= 1)
                                        return;

                                    // Prev Button
                                    var prevBtn = document.createElement("button");
                                    prevBtn.innerHTML = "Prev";
                                    prevBtn.disabled = currentPage === 1;
                                    prevBtn.onclick = function () {
                                        if (currentPage > 1)
                                            showPage(currentPage - 1);
                                    };
                                    pagination.appendChild(prevBtn);

                                    var startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
                                    var endPage = startPage + maxPageButtons - 1;

                                    if (endPage > totalPages) {
                                        endPage = totalPages;
                                        startPage = Math.max(1, endPage - maxPageButtons + 1);
                                    }

                                    // Ellipsis before
                                    if (startPage > 1) {
                                        pagination.appendChild(createEllipsis());
                                    }

                                    for (var i = startPage; i <= endPage; i++) {
                                        var btn = document.createElement("button");
                                        btn.innerHTML = i;
                                        if (i === currentPage) {
                                            btn.style.fontWeight = "bold";
                                            btn.style.backgroundColor = "#ddd";
                                        }
                                        (function (page) {
                                            btn.onclick = function () {
                                                showPage(page);
                                            };
                                        })(i);
                                        pagination.appendChild(btn);
                                    }

                                    // Ellipsis after
                                    if (endPage < totalPages) {
                                        pagination.appendChild(createEllipsis());
                                    }

                                    // Next Button
                                    var nextBtn = document.createElement("button");
                                    nextBtn.innerHTML = "Next";
                                    nextBtn.disabled = currentPage === totalPages;
                                    nextBtn.onclick = function () {
                                        if (currentPage < totalPages)
                                            showPage(currentPage + 1);
                                    };
                                    pagination.appendChild(nextBtn);
                                }

                                function createEllipsis() {
                                    var span = document.createElement("span");
                                    span.innerHTML = "...";
                                    span.style.margin = "0 5px";
                                    return span;
                                }

                                // Initial load
                                showPage(1);
                            })();
                        </script>


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