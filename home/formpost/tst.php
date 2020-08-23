<?php
require_once("../../it_config.php");
require_once "Classes/html2pdf/html2pdf.class.php";
			$length = 53;
			$html = "";
			$html2pdf = new HTML2PDF('P', array(45,200), 'en', true, 'UTF-8', array(1,0,1,0));
$codes = array(
"8900000050475",
"8900000050482",
"8900000050499",
"8900000050543",
"8900000050550",
"8900000050567"
);
$html.="<page>";
			foreach ($codes as $code) {
$html .=
"<div style=\"width:155px;margin-left:70px;\">
<table width=\"100%\" style=\"font-size:9px;\">
<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"$code\" label=\"label\" style=\"width:36mm; height:8mm; font-size: 2mm\"></barcode></td></tr>
</table>
</div>";
				}
$html.="</page>";
			$html2pdf->writeHTML($html);
			$fname = "barcodes/tst-barcodes.pdf";
			$html2pdf->Output("../$fname", "F");
