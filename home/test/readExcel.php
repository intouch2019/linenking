<?php
require_once "../../it_config.php";

error_reporting(E_ALL);
require_once 'Classes/PHPExcel/IOFactory.php';
$objPHPExcel = PHPExcel_IOFactory::load("test.xls");
$objWorksheet = $objPHPExcel->getActiveSheet();
echo '<table>' . "\n";
foreach ($objWorksheet->getRowIterator() as $row) {
echo '<tr>' . "\n";
$cellIterator = $row->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);
foreach ($cellIterator as $cell) {
echo '<td>' . $cell->getValue() . '</td>' . "\n";
}
echo '</tr>' . "\n";
}
echo '</table>' . "\n";
