<?php
include '../models/summary.php';
include '../../libs/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$opc = $_POST['options'];
$clients = $_POST['store'];
$dateOption = $_POST['dateOpc'];
$check = isset($_POST['checkClient']) ? $_POST['checkClient'] : '';
$currentDate = date('Y-m-d');

switch ($dateOption) {
    case '1':
        $d1 = date('Y-m-01', strtotime($currentDate));
        $d2 = $currentDate;
    break;

    case '2':
        $d1 = date('Y-m-01', strtotime('-1 month'));
		$d2 = date('Y-m-t', strtotime('-1 month'));
    break;

    case '3':
        $d1 = date('Y-01-01');
        $d2 = $currentDate;
    break;

    case '4':
        $d1 = date('Y-01-01', strtotime('-1 year'));
        $d2 = date('Y-12-31', strtotime('-1 year'));
    break;

    case '5':
        $d1 = $_POST['dateIni'];
        $d2 = $_POST['dateFin'];
    break;
}

$table = new SPDF();
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('enable_local_files', true);
$dompdf = new Dompdf($options);
$dompdf->setPaper('letter', 'landscape');

$html = $table->ordersTable($opc, $clients, $d1, $d2, $check);
$html2 = $table->impressionTable($opc, $clients, $d1, $d2, $check);
$html3 = $table->blanksTable($opc, $clients, $d1, $d2, $check);
$html4 = $table->unitsTable($opc, $clients, $d1, $d2, $check);
$html5 = $table->unitsTable2($opc, $clients, $d1, $d2, $check);

$htmlF = "<html> " . $html . $html2 . $html3 . $html4 . $html5 . "</html>";
$dompdf->loadHtml($htmlF);
$dompdf->render();
$canvas = $dompdf->getCanvas();
$canvasWidth = $canvas->get_width();
$canvasHeight = $canvas->get_height();
$canvas->page_text($canvasWidth-415, $canvasHeight-30, "{PAGE_NUM} / {PAGE_COUNT}", "Courier", 12, array(0,0,0));

$dompdf->stream();
?>