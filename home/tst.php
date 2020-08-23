<?php
require_once "../it_config.php";
require_once "Classes/html2pdf/html2pdf.class.php";

$printhtml = '<table>
		<tr>
		<td>Html2PDF</td>
		</tr>
	      </table>';
$filename="abc";
$html2pdf = new HTML2PDF('P', array(45,3650), 'en', true, 'UTF-8', array(0,0,0,0));
$html2pdf->writeHTML($printhtml);
$html2pdf->Output("$filename.pdf", "F");
