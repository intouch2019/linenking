<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "Classes/html2pdf/html2pdf.class.php";

		$html = "";
		$html2pdf = new HTML2PDF('P', array(45,50), 'en', true, 'UTF-8', array(1,0,1,0));
$html .=
"<page>
<br />
<div style=\"width:155px;\">
<table width=\"100%\" style=\"font-size:9px;margin-top:6px;\">
<tr><th> </th><th> </th><th> </th><th> </th></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\"></td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\"></td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\">Product: Category</td></tr>
<tr><td colspan=\"4\">Design no: C1989&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Qty in Nos: 1</td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\">REGALIA</td></tr>
<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:11px;padding-top:-4px;\">42 CM - HS</td></tr>
<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"8900000077441\" label=\"label\" style=\"width:36mm; height:6mm; font-size: 10px;\"></barcode></td></tr>
<tr style=\"font-weight:bold;\"><td colspan=\"2\" style=\"font-size:14px;\">Rs.599.00</td><td colspan=\"2\" style=\"font-size:8px;\">Maximum Retail Price<br />(Incl. All Taxes)</td></tr>
<tr><td colspan=\"4\" style=\"font-size:6px;\">Pack Dt :04/14</td></tr>
</table>
</div>
</page>";
		$html2pdf->writeHTML($html);
		$fname = "../tmp-barcodes.pdf";
		$html2pdf->Output("$fname", "F");
