<?php
// Encodage utf-8 : çàéèç

require_once('config.php');

include 'Classes/PHPExcel.php';
include 'Classes/PHPExcel/Writer/Excel2007.php';

if(!isset($_REQUEST['sessionId']) || (isset($_REQUEST['sessionId']) && !ctype_digit($_REQUEST['sessionId']))) exit;

$mysqli = new mysqli(DBSERVER, DBUSER, DBPASSWORD, DBNAME);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->set_charset("utf8");
$sid = $mysqli->real_escape_string($_REQUEST['sessionId']);

if ($result = $mysqli->query("SELECT * FROM session_sms WHERE id = $sid LIMIT 1")) {
	$session = $result->fetch_all(MYSQLI_ASSOC);
	$result->free();
}

$workbook = new PHPExcel;
$myWorkSheet = new PHPExcel_Worksheet($workbook, 'Questions pour réponses écrites');
$workbook->addSheet($myWorkSheet, 0);
$myWorkSheet1 = new PHPExcel_Worksheet($workbook, 'Toutes les questions');
$workbook->addSheet($myWorkSheet1, 1);
$workbook->removeSheetByIndex(2);

$styleArray = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	)
);

$styleArray1 = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	)
);

$workbook->setActiveSheetIndex(1);
$workbook->getActiveSheet()->setCellValue('A1', 'Numéro');
$workbook->getActiveSheet()->setCellValue('B1', 'Question');
$workbook->getActiveSheet()->setCellValue('C1', 'Status');
$workbook->getActiveSheet()->setCellValue('D1', 'Reçue à');
$workbook->getActiveSheet()->setCellValue('F1', $session[0]['code_session_sms']);
$workbook->getActiveSheet()->setCellValue('G1', $session[0]['titre_session_sms1']);
$workbook->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray);
$workbook->getActiveSheet()->getStyle('F1:G1')->applyFromArray($styleArray1);
if ($result = $mysqli->query("SELECT id,message_a_editer,priorite,ts_email FROM messages WHERE id_session_sms = $sid ORDER BY id")) {
  $questions = $result->fetch_all(MYSQLI_ASSOC);
  $i = 2;
  $flags = array('Supprimée', 'Pour réponse écrite', 'Traitement en salle', 'Non traitée');
  foreach($questions as $q) {
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $q['id']);
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(1, $i, trim($q['message_a_editer']));
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $flags[$q['priorite']]);
    $saveTimeZone = date_default_timezone_get();
    date_default_timezone_set('UTC');
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(3, $i++, PHPExcel_Shared_Date::PHPToExcel(strtotime($q['ts_email'])));
    date_default_timezone_set($saveTimeZone);
  }
  $result->free();
}
$workbook->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$workbook->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$workbook->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$workbook->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$workbook->getActiveSheet()->getStyle('A2:A1000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$workbook->getActiveSheet()->getStyle('B1:B1000')->getAlignment()->setWrapText(true);
$workbook->getActiveSheet()->getStyle('D2:D1000')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
$workbook->getActiveSheet()->getStyle('D2:D1000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$workbook->setActiveSheetIndex(0);
$workbook->getActiveSheet()->setCellValue('A1', 'Numéro');
$workbook->getActiveSheet()->setCellValue('B1', 'Question pour réponse écrite');
$workbook->getActiveSheet()->setCellValue('C1', 'Réponse');
$workbook->getActiveSheet()->setCellValue('E1', $session[0]['code_session_sms']);
$workbook->getActiveSheet()->setCellValue('F1', $session[0]['titre_session_sms1']);
$workbook->getActiveSheet()->getColumnDimension('C')->setWidth(100);
$workbook->getActiveSheet()->getStyle('A2:A1000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$workbook->getActiveSheet()->getStyle('A1:C1')->applyFromArray($styleArray);
$workbook->getActiveSheet()->getStyle('E1:F1')->applyFromArray($styleArray1);
if ($result = $mysqli->query("SELECT id,message_a_editer FROM messages WHERE id_session_sms = $sid AND priorite = 1 ORDER BY id")) {
  $questions = $result->fetch_all(MYSQLI_ASSOC);
  $i = 2;
  foreach($questions as $q) {
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $q['id']);
  	$workbook->getActiveSheet()->setCellValueByColumnAndRow(1, $i++, trim($q['message_a_editer']));
  }
  $result->free();
}
$mysqli->close();
$workbook->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$workbook->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$workbook->getActiveSheet()->getStyle('B1:B1000')->getAlignment()->setWrapText(true);

$writer = new PHPExcel_Writer_Excel2007($workbook);
header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition:inline;filename=151124_ADF_'.trim($session[0]['code_session_sms']).'_Questions.xlsx');
$writer->save('php://output');
?>