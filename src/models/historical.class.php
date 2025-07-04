<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';
include '../../libs/pdf/fpdf.php';
include '../../libs/FPDI-2.6.0/src/autoload.php';
use \setasign\Fpdi\Fpdi;

class PDF extends Fpdi {

    private $client;
    protected $_toc=array();
    protected $_numbering=false;
    protected $_numberingFooter=false;
    protected $_numPageNum=1;

    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    protected $flowingBlockAttr;

    function setClient($client) {
        $this->client = $client;
    }

    function header() {
        $this->Image('../../assets/img/header.jpg', 0, 0);
        $this->Ln(15);
    }

    function footer() {
        
        $this->SetY(-12);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }

    function table1() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    client,
                    COUNT(DISTINCT po_lot) AS orders,
                    SUM(total_qty) AS blanks,
                    COUNT(no_orden) AS items,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS extrahits
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY years";

        $res = $obj->consultar($sql, [':client'=>$this->client]);

        $totalGlobal = array(
            'orders' => 0,
            'items' => 0,
            'blanks' => 0,
            'extrahits' => 0,
        );

        // Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 5, 'Year', 0, 0, '', 1);
        $this->Cell(30, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(30, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(30, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(30, 5, 'Hits', 0, 1, 'C', 1);

        //Cuerpo
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetFont('', '', 10);
        foreach ($res AS $sales) {
            $this->SetX(23);
            $this->Cell(35, 5, $sales['years'], 0, 0, '', 1);
            $this->Cell(30, 5, number_format($sales['orders']), 0, 0, 'C', 1);
            $this->Cell(30, 5, number_format($sales['items']), 0, 0, 'C', 1);
            $this->Cell(30, 5, number_format($sales['blanks']), 0, 0, 'R', 1);
            $this->Cell(30, 5, number_format($sales['extrahits']), 0, 1, 'R', 1);

            $totalGlobal['orders'] += $sales['orders'];
            $totalGlobal['items'] += $sales['items'];
            $totalGlobal['blanks'] += $sales['blanks'];
            $totalGlobal['extrahits'] += $sales['extrahits'];
        }

        //Totales
        $this->SetX(23);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 5, 'Total', 0, 0, '', 1);
        $this->Cell(30, 5, number_format($totalGlobal['orders']), 0, 0, 'C', 1);
        $this->Cell(30, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(30, 5, number_format($totalGlobal['blanks']), 0, 0, 'R', 1);
        $this->Cell(30, 5, number_format($totalGlobal['extrahits']), 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table2() {

        $obj = new controlDB();

        $sql = "SELECT 
                    store,
                    COUNT(DISTINCT po_lot) AS po,
                    COUNT(no_orden) AS items,
                    SUM(total_qty) AS blanks,
                    (SUM(total_qty) / @total_blanks) * 100 AS por_blanks,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) AS hits,
                    (SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) / @total_hit) * 100 AS por_hits
                FROM t_wip
                CROSS JOIN
                    (SELECT @total_blanks := SUM(total_qty) FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS total_blanks,
                    (SELECT @total_hit := SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) FROM t_wip WHERE client = :client2 AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS hits
                WHERE client = :client3
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY store ORDER BY blanks DESC";
        
        $params = [':client'=>$this->client, ':client2'=>$this->client, ':client3'=>$this->client];
        $res = $obj->consultar($sql, $params);

        $totalGlobal = array(
            'po' => 0,
            'items' => 0,
            'blanks' => 0,
            'por_blanks' => 0,
            'hits' => 0,
            'por_hits' => 0
        );

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
        $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 1, 'C', 1);

        foreach ($res AS $store) {
             //Cuerpo
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
    
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetFont('', '', 10);
            $this->SetTextColor(0);
            $this->Cell(51, 5, $store['store'], 0, 0, '', 1);
            $this->Cell(20, 5, number_format($store['po']), 0, 0, 'C', 1);
            $this->Cell(20, 5, number_format($store['items']), 0, 0, 'C', 1);
            $this->Cell(20, 5, number_format($store['blanks']), 0, 0, 'R', 1);
            $this->Cell(20, 5, number_format($store['por_blanks'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5, number_format($store['hits']), 0, 0, 'R', 1);
            $this->Cell(20, 5, number_format($store['por_hits'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['po'] += $store['po'];
            $totalGlobal['items'] += $store['items'];
            $totalGlobal['blanks'] += $store['blanks'];
            $totalGlobal['por_blanks'] += $store['por_blanks'];
            $totalGlobal['hits'] += $store['hits'];
            $totalGlobal['por_hits'] += $store['por_hits'];
        }

        //Totales
        $this->SetX(20);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(20, 5, number_format($totalGlobal['po']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blanks']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['hits']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_hits'], 2).'%', 0, 1, 'R', 1);
    }

    function table3() {

        $obj = new controlDB();

        $sql = "SELECT 
                    YEAR(date_received) AS years,
                    store,
                    COUNT(DISTINCT po_lot) AS po,
                    COUNT(no_orden) AS items,
                    SUM(total_qty) AS blanks,
                    (SUM(total_qty) / @total_blanks) * 100 AS por_blanks,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) AS hits,
                    (SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) / @total_hit) * 100 AS por_hits
                FROM t_wip
                CROSS JOIN
                    (SELECT @total_blanks := SUM(total_qty) FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS total_blanks,
                    (SELECT @total_hit := SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) FROM t_wip WHERE client = :client2 AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS hits
                WHERE client = :client3
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY years, store";

        $params = [':client'=>$this->client, ':client2'=>$this->client, ':client3'=>$this->client];
        $result = $obj->consultar($sql, $params);

        $lastYear = null;
        
        $totalGlobal = array(
            'po' => 0,
            'items' => 0,
            'blanks' => 0,
            'por_blanks' => 0,
            'hits' => 0,
            'por_hits' => 0
        );

        foreach ($result as $total) {
            if ($total['years'] != $lastYear) {
                $totalPorYear[$total['years']] = array(
                    'po' => 0,
                    'items' => 0,
                    'blanks' => 0,
                    'por_blanks' => 0,
                    'hits' => 0,
                    'por_hits' => 0
                );
            }
            $totalPorYear[$total['years']]['po'] += $total['po'];
            $totalPorYear[$total['years']]['items'] += $total['items'];
            $totalPorYear[$total['years']]['blanks'] += $total['blanks'];
            $totalPorYear[$total['years']]['por_blanks'] += $total['por_blanks'];
            $totalPorYear[$total['years']]['hits'] += $total['hits'];
            $totalPorYear[$total['years']]['por_hits'] += $total['por_hits'];
            $lastYear = $total['years'];
        }

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
        $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 1, 'C', 1);
        
        foreach ($result as $row) {
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }

            if ($row['years'] != $lastYear) {
                $this->SetX(20);
                $this->SetFillColor(255);
                $this->SetTextColor(0);
                $this->SetFont('', 'BI', 11);
                $this->Cell(51, 5, $row['years'], 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['po']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['items']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['blanks']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_blanks'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['hits']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_hits'], 2).'%', 0, 1, 'R', 1);
            }
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['store'], 0, 0, '', 1);
            $this->Cell(20, 5,  number_format($row['po']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['items']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['blanks']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanks'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['hits']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_hits'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['po'] += $row['po'];
            $totalGlobal['items'] += $row['items'];
            $totalGlobal['blanks'] += $row['blanks'];
            $totalGlobal['por_blanks'] += $row['por_blanks'];
            $totalGlobal['hits'] += $row['hits'];
            $totalGlobal['por_hits'] += $row['por_hits'];
            $lastYear = $row['years'];
        }

        $this->SetX(20);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(20, 5, number_format($totalGlobal['po']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blanks']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['hits']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_hits'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table4() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    store,
                    SUM(total_qty) AS blanks,
                    SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) AS blankT,
                    (SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanks,
                    SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) AS blankWT,
                    (SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanksWT
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY years, store";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $lastYear = null;

        $totalGlobal = array(
            'blanks' => 0,
            'blankT' => 0,
            'por_blanks' => 0,
            'blankWT' => 0,
            'por_blanksWT' => 0
        );

        foreach ($result as $total) {
            if ($total['years'] != $lastYear) {
                $totalPorYear[$total['years']] = array(
                    'blanks' => 0,
                    'blankT' => 0,
                    'por_blanks' => 0,
                    'blankWT' => 0,
                    'por_blanksWT' => 0
                );
            }
            $totalPorYear[$total['years']]['blanks'] += $total['blanks'];
            $totalPorYear[$total['years']]['blankT'] += $total['blankT'];
            $totalPorYear[$total['years']]['por_blanks'] += $total['por_blanks'];
            $totalPorYear[$total['years']]['blankWT'] += $total['blankWT'];
            $totalPorYear[$total['years']]['por_blanksWT'] += $total['por_blanksWT'];
            $lastYear = $total['years'];
        }
        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
        $this->Cell(35, 5, 'Total Blanks (FP)', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Treatment', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Mill DYE', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 1, 'C', 1);
        
        foreach ($result as $row) {
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(35, 5, 'Total Blanks (FP)', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Treatment', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Mill DYE', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            if ($row['years'] != $lastYear) {
                $totalPorYear[$row['years']]['por_blanks'] = ($totalPorYear[$row['years']]['blankT'] / $totalPorYear[$row['years']]['blanks']) * 100;
                $totalPorYear[$row['years']]['por_blanksWT'] = ($totalPorYear[$row['years']]['blankWT'] / $totalPorYear[$row['years']]['blanks']) * 100;

                $this->SetX(20);
                $this->SetFillColor(255);
                $this->SetTextColor(0);
                $this->SetFont('', 'BI', 11);
                $this->Cell(51, 5, $row['years'], 0, 0, 'C', 1);
                $this->Cell(35, 5, number_format($totalPorYear[$row['years']]['blanks']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['blankT']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_blanks'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['blankWT']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_blanksWT'], 2).'%', 0, 1, 'R', 1);
            }
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
    
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(35, 5, 'Total Blanks (FP)', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Treatment', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Mill DYE', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['store'], 0, 0, '', 1);
            $this->Cell(35, 5,  number_format($row['blanks']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['blankT']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanks'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['blankWT']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanksWT'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['blanks'] += $row['blanks'];
            $totalGlobal['blankT'] += $row['blankT'];
            $totalGlobal['por_blanks'] += $row['por_blanks'];
            $totalGlobal['blankWT'] += $row['blankWT'];
            $totalGlobal['por_blanksWT'] += $row['por_blanksWT'];
            $lastYear = $row['years'];
        }

        $totalGlobal['por_blanks'] = ($totalGlobal['blankT'] / $totalGlobal['blanks']) * 100;
        $totalGlobal['por_blanksWT'] = ($totalGlobal['blankWT'] / $totalGlobal['blanks']) * 100;

        $this->SetX(20);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(35, 5, number_format($totalGlobal['blanks']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blankT']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blankWT']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanksWT'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table5() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND treatment != 'NA' AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received) AS years, client, treatment, SUM(total_qty) AS total FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND treatment != 'NA' AND order_status != 'CANCELED' GROUP BY years, treatment ORDER BY treatment, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada tratamiento y año
        foreach ($result AS $row) {
            $data[$row['treatment']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetX(11);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(70, 5, 'Treatment', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(17, 5, $year, 0, 0, 'C', 1);
        }
        $this->Cell(20, 5, 'Total', 0 , 0, 'C', 1);
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $treatment => $years_data) {
            $this->SetTextColor(0);
            $this->SetFillColor(209);
            $this->Cell(1, 5, '');
            if ($this->GetY() <= 25.00125) {
                // Encabezado
                $this->SetFont('', 'B', 11);
                $this->Cell(70, 5, 'Treatment', 0, 0, '', 1);
                foreach ($years as $year) {
                    $this->Cell(17, 5, $year, 0, 0, 'C', 1);
                }
                $this->Cell(20, 5, 'Total', 0 , 0, 'C', 1);
                $this->Ln();
                $this->Cell(1, 5, '');
            }
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(70, 5, $treatment, 0, 0, '', 1);
            $total = 0;
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(17, 5, number_format($quantity), 0, 0, 'R', 1);
                $total += $quantity;
            }
            $this->Cell(20, 5, number_format($total), 0, 0, 'R', 1);
            $this->Ln();
        }

        // Totales globales por columna
        $this->SetX(11);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(70, 5, 'Total', 0, 0, '', 1);
        $Grand_totals = 0;
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $treatment => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(17, 5, number_format($total_column), 0, 0, 'R', 1);
            $Grand_totals += $total_column;
        }
        // Calcular el total general sumando todos los totales globales por columna
        $this->Cell(20, 5, number_format($Grand_totals), 0, 0, 'R', 1);
        $this->Ln();

        $this->SetTextColor(0);
    }

    function table6() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received) AS years, client, gender, SUM(total_qty) AS total FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' GROUP BY years, gender ORDER BY gender, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada genero y año
        foreach ($result AS $row) {
            $data[$row['gender']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(40, 5, 'Gender', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(22, 5, $year, 0, 0, 'C', 1);
        }
        $this->Cell(22, 5, 'Total', 0, 0, 'C', 1);
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $gender => $years_data) {
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(40, 5, $gender, 0, 0, '', 1);
            $total = 0;
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(22, 5, number_format($quantity), 0, 0, 'R', 1);
                $total += $quantity;
            }
            $this->Cell(22, 5, number_format($total), 0, 0, 'R', 1);
            $this->Ln();
        }

        // Totales globales por columna
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(40, 5, 'Total', 0, 0, '', 1);
        $Grand_totals = 0;
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $gender => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(22, 5, number_format($total_column), 0, 0, 'R', 1);
            $Grand_totals += $total_column;
        }
        // Calcular el total general sumando todos los totales globales por columna
        $this->Cell(22, 5, number_format($Grand_totals), 0, 0, 'R', 1);
        $this->Ln();

        $this->SetTextColor(0);
    }

    function blanks() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received) AS years, MONTHNAME(date_received) AS months, MONTH(date_received) AS mes, SUM(total_qty) AS total FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' GROUP BY years, months ORDER BY mes, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada mes y año
        foreach ($result AS $row) {
            $data[$row['months']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Blanks', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(20, 4, $year, 0, 0, 'C', 1);
        }
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $months => $years_data) {
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(35, 4, $months, 0, 0, '', 1);
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(20, 4, number_format($quantity), 0, 0, 'R', 1);
            }
            $this->Ln();
        }
        // Totales globales por columna
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Total', 0, 0, '', 1);
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $months => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(20, 4, number_format($total_column), 0, 0, 'R', 1);
        }
        $this->Ln();
    }

    function hits() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received)AS years, MONTHNAME(date_received) AS months, MONTH(date_received) AS mes, SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS hits FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' GROUP BY years, months ORDER BY mes, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada mes y año
        foreach ($result AS $row) {
            $data[$row['months']][$row['years']] = $row['hits'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Hits', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(20, 4, $year, 0, 0, 'C', 1);
        }
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $months => $years_data) {
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(35, 4, $months, 0, 0, '', 1);
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(20, 4, number_format($quantity), 0, 0, 'R', 1);
            }
            $this->Ln();
        }
        // Totales globales por columna
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Total', 0, 0, '', 1);
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $months => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(20, 4, number_format($total_column), 0, 0, 'R', 1);
        }
        $this->Ln();
    }

    function treatment() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received) AS years, MONTHNAME(date_received) AS months, MONTH(date_received) AS mes, SUM(total_qty) AS total FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND treatment != 'NA' AND order_status != 'CANCELED' GROUP BY years, months ORDER BY mes, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada mes y año
        foreach ($result AS $row) {
            $data[$row['months']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'With Treatment', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(20, 4, $year, 0, 0, 'C', 1);
        }
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $months => $years_data) {
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(35, 4, $months, 0, 0, '', 1);
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(20, 4, number_format($quantity), 0, 0, 'R', 1);
            }
            $this->Ln();
        }
        // Totales globales por columna
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Total', 0, 0, '', 1);
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $months => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(20, 4, number_format($total_column), 0, 0, 'R', 1);
        }
        $this->Ln();
    }

    function no_treatment() {

        $obj = new controlDB();

        $sql2 = "SELECT DISTINCT YEAR(date_received) AS years FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED' ORDER BY years";

        $result_years = $obj->consultar($sql2, [':client'=>$this->client]);

        $sql = "SELECT YEAR(date_received) AS years, MONTHNAME(date_received) AS months, MONTH(date_received) AS mes, SUM(total_qty) AS total FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND treatment = 'NA' AND order_status != 'CANCELED' GROUP BY years, months ORDER BY mes, years";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada mes y año
        foreach ($result AS $row) {
            $data[$row['months']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Without Treatment', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(20, 4, $year, 0, 0, 'C', 1);
        }
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $months => $years_data) {
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(35, 4, $months, 0, 0, '', 1);
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = intval($quantity);
                $this->Cell(20, 4, number_format($quantity), 0, 0, 'R', 1);
            }
            $this->Ln();
        }
        // Totales globales por columna
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 4, 'Total', 0, 0, '', 1);
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $months => $years_data) {
                $total_column += isset($years_data[$year]) ? intval($years_data[$year]) : 0;
            }
            $this->Cell(20, 4, number_format($total_column), 0, 0, 'R', 1);
        }
        $this->Ln();
    }

    function table7() {

        $obj = new controlDB();

        $sql = "SELECT 
                    YEAR(date_received) AS years,
                    store,
                    po_lot,
                    COUNT(DISTINCT po_lot) AS po,
                    COUNT(no_orden) AS items,
                    SUM(total_qty) AS blanks,
                    (SUM(total_qty) / @total_blanks) * 100 AS por_blanks,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) AS hits,
                    (SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) / @total_hit) * 100 AS por_hits
                FROM t_wip
                CROSS JOIN
                    (SELECT @total_blanks := SUM(total_qty) FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status = 'CANCELED') AS total_blanks,
                    (SELECT @total_hit := SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) FROM t_wip WHERE client = :client2 AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status = 'CANCELED') AS hits
                WHERE client = :client3
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status = 'CANCELED'
                GROUP BY store, po_lot";

        $params = [':client'=>$this->client, ':client2'=>$this->client, ':client3'=>$this->client];
        $result = $obj->consultar($sql, $params);

        $lastYear = null;

        $totalGlobal = array(
            'po' => 0,
            'items' => 0,
            'blanks' => 0,
            'por_blanks' => 0,
            'hits' => 0,
            'por_hits' => 0
        );

        foreach ($result as $total) {
            if ($total['store'] != $lastYear) {
                $totalPorYear[$total['store']] = array(
                    'po' => 0,
                    'items' => 0,
                    'blanks' => 0,
                    'por_blanks' => 0,
                    'hits' => 0,
                    'por_hits' => 0
                );
            }
            $totalPorYear[$total['store']]['po'] += $total['po'];
            $totalPorYear[$total['store']]['items'] += $total['items'];
            $totalPorYear[$total['store']]['blanks'] += $total['blanks'];
            $totalPorYear[$total['store']]['por_blanks'] += $total['por_blanks'];
            $totalPorYear[$total['store']]['hits'] += $total['hits'];
            $totalPorYear[$total['store']]['por_hits'] += $total['por_hits'];
            $lastYear = $total['store'];
        }

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
        $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 1, 'C', 1);

        foreach ($result as $row) {
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            if ($row['store'] != $lastYear) {
                $this->SetX(20);
                $this->SetFillColor(255);
                $this->SetTextColor(0);
                $this->SetFont('', 'BI', 11);
                $this->Cell(51, 5, $row['store'], 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['po']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['items']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['blanks']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['por_blanks'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['hits']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['store']]['por_hits'], 2).'%', 0, 1, 'R', 1);
            }
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
    
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Client / Store', 0, 0, '', 1);
                $this->Cell(20, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Hits', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['po_lot'], 0, 0, '', 1);
            $this->Cell(20, 5,  number_format($row['po']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['items']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['blanks']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanks'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['hits']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_hits'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['po'] += $row['po'];
            $totalGlobal['items'] += $row['items'];
            $totalGlobal['blanks'] += $row['blanks'];
            $totalGlobal['por_blanks'] += $row['por_blanks'];
            $totalGlobal['hits'] += $row['hits'];
            $totalGlobal['por_hits'] += $row['por_hits'];
            $lastYear = $row['store'];
        }

        $this->SetX(20);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(20, 5, number_format($totalGlobal['po']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blanks']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['hits']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_hits'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table8() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    store,
                    SUM(total_qty) AS total,
                    COUNT(no_orden) AS items,
                    SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) AS less_300,
                    (SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300,
                    SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) AS _300_to_600,
                    (SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300_to_600,
                    SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) AS _600_to_1200,
                    (SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_600_to_1200,
                    SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) AS over_1200,
                    (SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY years, store";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $lastYear = null;

        $totalGlobal = array(
            'total' => 0,
            'items' => 0,
            'less_300' => 0,
            'por_300' => 0,
            '_300_to_600' => 0,
            'por_300_to_600' => 0,
            '_600_to_1200' => 0,
            'por_600_to_1200' => 0,
            'over_1200' => 0,
            'por_1200' => 0
        );

        foreach ($result as $total) {
            if ($total['years'] != $lastYear) {
                $totalPorYear[$total['years']] = array(
                    'total' => 0,
                    'items' => 0,
                    'less_300' => 0,
                    'por_300' => 0,
                    '_300_to_600' => 0,
                    'por_300_to_600' => 0,
                    '_600_to_1200' => 0,
                    'por_600_to_1200' => 0,
                    'over_1200' => 0,
                    'por_1200' => 0
                );
            }
            $totalPorYear[$total['years']]['total'] += $total['total'];
            $totalPorYear[$total['years']]['items'] += $total['items'];
            $totalPorYear[$total['years']]['less_300'] += $total['less_300'];
            $totalPorYear[$total['years']]['por_300'] += $total['por_300'];
            $totalPorYear[$total['years']]['_300_to_600'] += $total['_300_to_600'];
            $totalPorYear[$total['years']]['por_300_to_600'] += $total['por_300_to_600'];
            $totalPorYear[$total['years']]['_600_to_1200'] += $total['_600_to_1200'];
            $totalPorYear[$total['years']]['por_600_to_1200'] += $total['por_600_to_1200'];
            $totalPorYear[$total['years']]['over_1200'] += $total['over_1200'];
            $totalPorYear[$total['years']]['por_1200'] += $total['por_1200'];
            $lastYear = $total['years'];
        }

        $x = $this->GetX();
        $y = $this->GetY();
        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(5, 5, '');
        $this->SetXY($x + 5, $y);
        $this->Cell(51, 11, 'Client / Store', 0, 0, '', 1);
        $this->SetXY($x + 51 + 5, $y);
        $this->Cell(20, 11, 'Blanks', 0, 0, 'C', 1);
        $this->SetXY($x + 51 + 25, $y);
        $this->Cell(20, 11, 'Items', 0, 0, 'C', 1);
        $this->SetXY($x + 51 + 45, $y);
        $this->MultiCell(20, 5, 'Under 300', 0, 'C', 1);
        $this->SetXY($x + 51 + 65, $y);
        $this->MultiCell(20, 5, 'Under 300%', 0, 'C', 1);
        $this->SetXY($x + 51 + 85, $y);
        $this->MultiCell(20, 5, '300 to 599', 0, 'C', 1);
        $this->SetXY($x + 51 + 105, $y);
        $this->MultiCell(20, 5, '300 to 599%', 0, 'C', 1);
        $this->SetXY($x + 51 + 125, $y);
        $this->MultiCell(20, 5, '600 to 1,199', 0, 'C', 1);
        $this->SetXY($x + 51 + 145, $y);
        $this->MultiCell(20, 5, '600 to 1,199%', 0, 'C', 1);
        $this->SetXY($x + 51 + 165, $y);
        $this->MultiCell(20, 5, 'Over 1,200', 0, 'C', 1);
        $this->SetXY($x + 51 + 185, $y);
        $this->MultiCell(20, 5, 'Over 1,200%', 0, 'C', 1);
        
        foreach ($result as $row) {
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 11, 'Client / Store', 0, 0, '', 1);
                $this->SetXY($x + 51 + 5, 25);
                $this->Cell(20, 11, 'Blanks', 0, 0, 'C', 1);
                $this->SetXY($x + 51 + 25, 25);
                $this->Cell(20, 11, 'Items', 0, 0, 'C', 1);
                $this->SetXY($x + 51 + 45, 25);
                $this->MultiCell(20, 5, 'Under 300', 0, 'C', 1);
                $this->SetXY($x + 51 + 65, 25);
                $this->MultiCell(20, 5, 'Under 300%', 0, 'C', 1);
                $this->SetXY($x + 51 + 85, 25);
                $this->MultiCell(20, 5, '300 to 599', 0, 'C', 1);
                $this->SetXY($x + 51 + 105, 25);
                $this->MultiCell(20, 5, '300 to 599%', 0, 'C', 1);
                $this->SetXY($x + 51 + 125, 25);
                $this->MultiCell(20, 5, '600 to 1,199', 0, 'C', 1);
                $this->SetXY($x + 51 + 145, 25);
                $this->MultiCell(20, 5, '600 to 1,199%', 0, 'C', 1);
                $this->SetXY($x + 51 + 165, 25);
                $this->MultiCell(20, 5, 'Over 1,200', 0, 'C', 1);
                $this->SetXY($x + 51 + 185, 25);
                $this->MultiCell(20, 5, 'Over 1,200%', 0, 'C', 1);
                $this->Cell(5, 5, '');
            }
            if ($row['years'] != $lastYear) {
                $totalPorYear[$row['years']]['por_300'] = ($totalPorYear[$row['years']]['less_300'] / $totalPorYear[$row['years']]['total']) * 100;
                $totalPorYear[$row['years']]['por_300_to_600'] = ($totalPorYear[$row['years']]['_300_to_600'] / $totalPorYear[$row['years']]['total']) * 100;
                $totalPorYear[$row['years']]['por_600_to_1200'] = ($totalPorYear[$row['years']]['_600_to_1200'] / $totalPorYear[$row['years']]['total']) * 100;
                $totalPorYear[$row['years']]['por_1200'] = ($totalPorYear[$row['years']]['over_1200'] / $totalPorYear[$row['years']]['total']) * 100;

                $this->SetFillColor(255);
                $this->SetTextColor(0);
                $this->SetFont('', 'BI', 11);
                $this->Cell(51, 5, $row['years'], 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['total']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['items']), 0, 0, 'C', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['less_300']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_300'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['_300_to_600']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_300_to_600'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['_600_to_1200']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_600_to_1200'], 2).'%', 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['over_1200']), 0, 0, 'R', 1);
                $this->Cell(20, 5, number_format($totalPorYear[$row['years']]['por_1200'], 2).'%', 0, 1, 'R', 1);
                $this->Cell(5, 5, '');
                if ($this->GetY() <= 25.00125 ) {
                    // Encabezado
                    $this->SetTextColor(0);
                    $this->SetFillColor(209);
                    $this->SetFont('', 'B', 11);
                    $this->Cell(51, 11, 'Client / Store', 0, 0, '', 1);
                    $this->SetXY($x + 51 + 5, 25);
                    $this->Cell(20, 11, 'Blanks', 0, 0, 'C', 1);
                    $this->SetXY($x + 51 + 25, 25);
                    $this->Cell(20, 11, 'Items', 0, 0, 'C', 1);
                    $this->SetXY($x + 51 + 45, 25);
                    $this->MultiCell(20, 5, 'Under 300', 0, 'C', 1);
                    $this->SetXY($x + 51 + 65, 25);
                    $this->MultiCell(20, 5, 'Under 300%', 0, 'C', 1);
                    $this->SetXY($x + 51 + 85, 25);
                    $this->MultiCell(20, 5, '300 to 599', 0, 'C', 1);
                    $this->SetXY($x + 51 + 105, 25);
                    $this->MultiCell(20, 5, '300 to 599%', 0, 'C', 1);
                    $this->SetXY($x + 51 + 125, 25);
                    $this->MultiCell(20, 5, '600 to 1,199', 0, 'C', 1);
                    $this->SetXY($x + 51 + 145, 25);
                    $this->MultiCell(20, 5, '600 to 1,199%', 0, 'C', 1);
                    $this->SetXY($x + 51 + 165, 25);
                    $this->MultiCell(20, 5, 'Over 1,200', 0, 'C', 1);
                    $this->SetXY($x + 51 + 185, 25);
                    $this->MultiCell(20, 5, 'Over 1,200%', 0, 'C', 1);
                    $this->Cell(5, 5, '');
                }
            }
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['store'], 0, 0, '', 1);
            $this->Cell(20, 5,  number_format($row['total']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['items']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['less_300']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_300'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['_300_to_600']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_300_to_600'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['_600_to_1200']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_600_to_1200'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['over_1200']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_1200'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['total'] += $row['total'];
            $totalGlobal['items'] += $row['items'];
            $totalGlobal['less_300'] += $row['less_300'];
            $totalGlobal['por_300'] += $row['por_300'];
            $totalGlobal['_300_to_600'] += $row['_300_to_600'];
            $totalGlobal['por_300_to_600'] += $row['por_300_to_600'];
            $totalGlobal['_600_to_1200'] += $row['_600_to_1200'];
            $totalGlobal['por_600_to_1200'] += $row['por_600_to_1200'];
            $totalGlobal['over_1200'] += $row['over_1200'];
            $totalGlobal['por_1200'] += $row['por_1200'];
            $lastYear = $row['years'];
        }

        $totalGlobal['por_300'] = ($totalGlobal['less_300'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_300_to_600'] = ($totalGlobal['_300_to_600'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_600_to_1200'] = ($totalGlobal['_600_to_1200'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_1200'] = ($totalGlobal['over_1200'] / $totalGlobal['total']) * 100;

        $this->SetFont('', 'B', 11);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->Cell(5, 5, '');
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(20, 5, number_format($totalGlobal['total']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['less_300']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_300'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['_300_to_600']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_300_to_600'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['_600_to_1200']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_600_to_1200'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['over_1200']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_1200'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table9() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    store,
                    SUM(total_qty) AS total,
                    COUNT(no_orden) AS items,
                    SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) AS less_300,
                    (SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300,
                    SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) AS _300_to_600,
                    (SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300_to_600,
                    SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) AS _600_to_1200,
                    (SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_600_to_1200,
                    SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) AS over_1200,
                    (SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY store ORDER BY total DESC";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $totalGlobal = array(
            'total' => 0,
            'items' => 0,
            'less_300' => 0,
            'por_300' => 0,
            '_300_to_600' => 0,
            'por_300_to_600' => 0,
            '_600_to_1200' => 0,
            'por_600_to_1200' => 0,
            'over_1200' => 0,
            'por_1200' => 0
        );
        $x = $this->GetX();
        $y = $this->GetY();

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(5, 5, '');
        $this->SetXY($x + 5, $y);
        $this->Cell(51, 11, 'Client / Store', 0, 0, '', 1);
        $this->SetXY($x + 51 + 5, $y);
        $this->Cell(20, 11, 'Blanks', 0, 0, 'C', 1);
        $this->SetXY($x + 51 + 25, $y);
        $this->Cell(20, 11, 'Items', 0, 0, 'C', 1);
        $this->SetXY($x + 51 + 45, $y);
        $this->MultiCell(20, 5, 'Under 300', 0, 'C', 1);
        $this->SetXY($x + 51 + 65, $y);
        $this->MultiCell(20, 5, 'Under 300%', 0, 'C', 1);
        $this->SetXY($x + 51 + 85, $y);
        $this->MultiCell(20, 5, '300 to 599', 0, 'C', 1);
        $this->SetXY($x + 51 + 105, $y);
        $this->MultiCell(20, 5, '300 to 599%', 0, 'C', 1);
        $this->SetXY($x + 51 + 125, $y);
        $this->MultiCell(20, 5, '600 to 1,199', 0, 'C', 1);
        $this->SetXY($x + 51 + 145, $y);
        $this->MultiCell(20, 5, '600 to 1,199%', 0, 'C', 1);
        $this->SetXY($x + 51 + 165, $y);
        $this->MultiCell(20, 5, 'Over 1,200', 0, 'C', 1);
        $this->SetXY($x + 51 + 185, $y);
        $this->MultiCell(20, 5, 'Over 1,200%', 0, 'C', 1);
        
        foreach ($result as $row) {
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125 ) {
                // Encabezado
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 11, 'Client / Store', 0, 0, '', 1);
                $this->SetXY($x + 51 + 5, 25);
                $this->Cell(20, 11, 'Blanks', 0, 0, 'C', 1);
                $this->SetXY($x + 51 + 25, 25);
                $this->Cell(20, 11, 'Items', 0, 0, 'C', 1);
                $this->SetXY($x + 51 + 45, 25);
                $this->MultiCell(20, 5, 'Under 300', 0, 'C', 1);
                $this->SetXY($x + 51 + 65, 25);
                $this->MultiCell(20, 5, 'Under 300%', 0, 'C', 1);
                $this->SetXY($x + 51 + 85, 25);
                $this->MultiCell(20, 5, '300 to 599', 0, 'C', 1);
                $this->SetXY($x + 51 + 105, 25);
                $this->MultiCell(20, 5, '300 to 599%', 0, 'C', 1);
                $this->SetXY($x + 51 + 125, 25);
                $this->MultiCell(20, 5, '600 to 1,199', 0, 'C', 1);
                $this->SetXY($x + 51 + 145, 25);
                $this->MultiCell(20, 5, '600 to 1,199%', 0, 'C', 1);
                $this->SetXY($x + 51 + 165, 25);
                $this->MultiCell(20, 5, 'Over 1,200', 0, 'C', 1);
                $this->SetXY($x + 51 + 185, 25);
                $this->MultiCell(20, 5, 'Over 1,200%', 0, 'C', 1);
                $this->Cell(5, 5, '');
            }
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['store'], 0, 0, '', 1);
            $this->Cell(20, 5,  number_format($row['total']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['items']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['less_300']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_300'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['_300_to_600']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_300_to_600'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['_600_to_1200']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_600_to_1200'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['over_1200']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_1200'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['total'] += $row['total'];
            $totalGlobal['items'] += $row['items'];
            $totalGlobal['less_300'] += $row['less_300'];
            $totalGlobal['por_300'] += $row['por_300'];
            $totalGlobal['_300_to_600'] += $row['_300_to_600'];
            $totalGlobal['por_300_to_600'] += $row['por_300_to_600'];
            $totalGlobal['_600_to_1200'] += $row['_600_to_1200'];
            $totalGlobal['por_600_to_1200'] += $row['por_600_to_1200'];
            $totalGlobal['over_1200'] += $row['over_1200'];
            $totalGlobal['por_1200'] += $row['por_1200'];
        }

        $totalGlobal['por_300'] = ($totalGlobal['less_300'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_300_to_600'] = ($totalGlobal['_300_to_600'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_600_to_1200'] = ($totalGlobal['_600_to_1200'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_1200'] = ($totalGlobal['over_1200'] / $totalGlobal['total']) * 100;

        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(5, 5, '');
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(20, 5, number_format($totalGlobal['total']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['less_300']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_300'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['_300_to_600']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_300_to_600'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['_600_to_1200']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_600_to_1200'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['over_1200']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_1200'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table10() {

        
        $obj = new controlDB();

        $sql = "SELECT
                    blank_color,
                    COUNT(DISTINCT po_lot) AS po,
                    COUNT(no_orden) AS items,
                    SUM(total_qty) AS blanks,
                    (SUM(total_qty) / @total_blanks) * 100 AS por_blanks
                FROM t_wip
                CROSS JOIN
                    (SELECT @total_blanks := SUM(total_qty) FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS total_blanks
                WHERE client = :client2
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE())
                AND order_status != 'CANCELED'
                GROUP BY blank_color ORDER BY blanks DESC";

        $params = [':client'=>$this->client, ':client2'=>$this->client];
        $res = $obj->consultar($sql, $params);

        $totalGlobal = array(
            'po' => 0,
            'items' => 0,
            'blanks' => 0,
            'por_blanks' => 0
        );

        //Encabezado
        $this->SetX(25);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Color', 0, 0, '', 1);
        $this->Cell(26, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Blanks %', 0, 1, 'C', 1);

        
        foreach ($res AS $color) {
            $this->SetX(20);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125) {
                // Encabezado
                $this->SetX(25);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Color', 0, 0, '', 1);
                $this->Cell(26, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Blanks %', 0, 1, 'C', 1);
            }
            $this->SetX(25);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5, $color['blank_color'], 0, 0, '', 1);
            $this->Cell(26, 5, number_format($color['po']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($color['items']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($color['blanks']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($color['por_blanks'],2).'%', 0, 1, 'C', 1);

            $totalGlobal['po'] += $color['po'];
            $totalGlobal['items'] += $color['items'];
            $totalGlobal['blanks'] += $color['blanks'];
            $totalGlobal['por_blanks'] += $color['por_blanks'];
        }

        //Totales
        $this->SetX(25);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(26, 5, number_format($totalGlobal['po']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['blanks']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 1, 'C', 1);
    }

    function table11() {

        
        $obj = new controlDB();

        $sql = "SELECT
                    blank_style,
                    COUNT(DISTINCT po_lot) AS po,
                    COUNT(no_orden) AS items,
                    SUM(total_qty) AS blanks,
                    (SUM(total_qty) / @total_blanks) * 100 AS por_blanks
                FROM t_wip
                CROSS JOIN
                    (SELECT @total_blanks := SUM(total_qty) FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED') AS total_blanks
                WHERE client = :client2 AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) AND order_status != 'CANCELED'
                GROUP BY blank_style ORDER BY blanks DESC";

        $params = [':client'=>$this->client, ':client2'=>$this->client];
        $res = $obj->consultar($sql, $params);

        $totalGlobal = array(
            'po' => 0,
            'items' => 0,
            'blanks' => 0,
            'por_blanks' => 0
        );

        //Encabezado
        $this->SetX(25);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(56, 5, 'Blank Style', 0, 0, '', 1);
        $this->Cell(26, 5, 'PO / LOT#', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Items', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Blanks', 0, 0, 'C', 1);
        $this->Cell(26, 5, 'Blanks %', 0, 1, 'C', 1);

        foreach ($res AS $style) {
            $this->SetX(20);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125) {
                // Encabezado
                $this->SetX(25);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(56, 5, 'Blank Style', 0, 0, '', 1);
                $this->Cell(26, 5, 'PO / LOT#', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Items', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Blanks', 0, 0, 'C', 1);
                $this->Cell(26, 5, 'Blanks %', 0, 1, 'C', 1);
            }
            $this->SetX(25);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(56, 5, $style['blank_style'], 0, 0, '', 1);
            $this->Cell(26, 5, number_format($style['po']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($style['items']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($style['blanks']), 0, 0, 'C', 1);
            $this->Cell(26, 5, number_format($style['por_blanks'],2).'%', 0, 1, 'C', 1);

            $totalGlobal['po'] += $style['po'];
            $totalGlobal['items'] += $style['items'];
            $totalGlobal['blanks'] += $style['blanks'];
            $totalGlobal['por_blanks'] += $style['por_blanks'];
        }

        //Totales
        $this->SetX(25);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(56, 5, 'Total', 0, 0, '', 1);
        $this->Cell(26, 5, number_format($totalGlobal['po']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['blanks']), 0, 0, 'C', 1);
        $this->Cell(26, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 1, 'C', 1);

        $this->SetTextColor(0);
    }

    function table12() {

        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }

        $param = [':client'=>$cliente];
        $sql = "SELECT YEAR(fecha) AS years, SUM(sale) AS total FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) GROUP BY years";

        $res = $obj->consultar($sql, $param);

        $totalGlobal = array(
            'total' => 0
        );

        // Encabezado
        $this->SetX(60);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 5, 'Year', 0, 0, '', 1);
        $this->Cell(40, 5, 'Amount', 0, 1, 'C', 1);

        //Cuerpo
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetFont('', '', 10);
        foreach ($res AS $sales) {
            $this->SetX(60);
            $this->Cell(35, 5, $sales['years'], 0, 0, '', 1);
            $this->Cell(40, 5, '$'.number_format($sales['total'],2), 0, 1, 'R', 1);

            $totalGlobal['total'] += $sales['total'];
        }

        //Totales
        $this->SetX(60);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(35, 5, 'Total', 0, 0, 'L', 1);
        $this->Cell(40, 5, '$'.number_format($totalGlobal['total'],2), 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table13() {

        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }
        
        $param = [':client'=>$cliente];
        $sql2 = "SELECT YEAR(fecha) AS years FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) GROUP BY years";

        $result_years = $obj->consultar($sql2, $param);

        $sql = "SELECT YEAR(fecha) AS years, MONTHNAME(fecha) AS months, MONTH(fecha) AS mes, SUM(sale) AS total FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE()) GROUP BY years, months ORDER BY mes, years";
        
        $result = $obj->consultar($sql, $param);

        $data = array();
        $years = array();

        // Llenar el array de datos con las cantidades correspondientes a cada mes y año
        foreach ($result AS $row) {
            $data[$row['months']][$row['years']] = $row['total'];
        }

        foreach ($result_years AS $row) {
            $years[] = $row['years'];
        }

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(25, 5, '', 0, 0, '', 1);
        foreach ($years as $year) {
            $this->Cell(28, 5, $year, 0, 0, 'C', 1);
        }
        $this->Ln();

        // Imprimir los datos de la tabla
        foreach ($data as $months => $years_data) {
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 11);
            $this->Cell(25, 5, $months, 0, 0, '', 1);
            foreach ($years as $year) {
                $quantity = isset($years_data[$year]) ? $years_data[$year] : '';
                $quantity = floatval($quantity);
                $this->Cell(28, 5, '$'.number_format($quantity,2), 0, 0, 'R', 1);
            }
            $this->Ln();
        }
        // Totales globales por columna
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(25, 5, 'Total', 0, 0, '', 1);
        foreach ($years as $year) {
            $total_column = 0;
            foreach ($data as $months => $years_data) {
                $total_column += isset($years_data[$year]) ? floatval($years_data[$year]) : 0;
            }
            $this->Cell(28, 5, '$'.number_format($total_column,2), 0, 0, 'R', 1);
        }
        $this->Ln();

        $this->SetTextColor(0);
    }

    function table14() {

        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);
		$mes = date('n');
		$meses = 12 - date('n');

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }

        $param = [':client'=>$cliente];
        $sql = "SELECT client, SUM(sale) AS total, goal FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE YEAR(fecha) = YEAR(CURRENT_DATE()) AND client = :client";

		$tabla = $obj->consultar($sql, $param);

        $x = $this->GetX();
        $y = $this->GetY();

        //Encabezado
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(25, 12, date('Y').' Goal', 0, 0, 'C', 1);
        $this->SetXY($x + 25, $y);
        $this->Cell(25, 12, 'Current Sales', 0, 0, 'C', 1);
        $this->SetXY($x + 50, $y);
        $this->Cell(30, 12, 'Difference', 0, 0, 'C', 1);
        $this->SetXY($x + 80, $y);
        $this->Cell(25, 12, 'Monthly Goal', 0, 0, 'C', 1);
        $this->SetXY($x + 105, $y);
        $this->Cell(30, 12, 'Current Month', 0, 0, 'C', 1);
        $this->SetXY($x + 135, $y);
        $this->MultiCell(30, 6, 'Current Month Difference', 0, 0, 'C', 1);
        $this->SetXY($x + 165, $y);
        $this->MultiCell(30, 6, 'Updated Monthly Goal', 0, 1, 'C', 1);

		foreach ($tabla AS $data) {
			$goals = $data['goal'];
			$sales = $data['total'];
			$difference = $sales - $goals;
			$monthlyGoal = $goals / 12;
			$currentMonth = $monthlyGoal * $mes;
			$currentMDiff = $sales - $currentMonth;

			if ($meses == 0) {
				$restMonthly = ($currentMDiff < 0)? 0 :$monthlyGoal;  
			} else {
				
				$restMonthly = ($currentMDiff < 0)? -($difference / $meses) :$monthlyGoal;

				if (is_nan($restMonthly) || is_infinite($restMonthly)) {
					$restMonthly = 0;
				}
			}
			
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(25, 6, '$'.number_format($goals,2), 0, 0, 'C', 1);
            $this->Cell(25, 6, '$'.number_format($sales,2), 0, 0, 'C', 1);
            $this->Cell(30, 6, '$'.number_format($difference,2), 0, 0, 'C', 1);
            $this->Cell(25, 6, '$'.number_format($monthlyGoal,2), 0, 0, 'C', 1);
            $this->Cell(30, 6, '$'.number_format($currentMonth,2), 0, 0, 'C', 1);
            $this->Cell(30, 6, '$'.number_format($currentMDiff,2), 0, 0, 'C', 1);
            $this->Cell(30, 6, '$'.number_format($restMonthly,2), 0, 1, 'C', 1);
			
		}

    }

    function table15() {

        $obj = new controlDB();

        $sql = "SELECT
                    order_status,
                    store,
                    SUM(amount_number) AS amount,
                    COUNT(no_orden) AS items,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE '1' * total_qty END) AS hits,
                    SUM(total_qty) AS blanks
                FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c
                WHERE client = :client
                AND order_status NOT IN ('CANCELED', 'SHIPPED')
                GROUP BY order_status, store";

        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $lastYear = null;

        $totalGlobal = array(
            'amount' => 0,
            'items' => 0,
            'blanks' => 0,
            'hits' => 0
        );

        foreach ($result as $total) {
            if ($total['order_status'] != $lastYear) {
                $totalPorYear[$total['order_status']] = array(
                    'amount' => 0,
                    'items' => 0,
                    'blanks' => 0,
                    'hits' => 0
                );
            }
            $totalPorYear[$total['order_status']]['amount'] += $total['amount'];
            $totalPorYear[$total['order_status']]['items'] += $total['items'];
            $totalPorYear[$total['order_status']]['blanks'] += $total['blanks'];
            $totalPorYear[$total['order_status']]['hits'] += $total['hits'];
            $lastYear = $total['order_status'];
        }

        if (isset($totalPorYear) && count($totalPorYear) === 1) {
            $lastYear = null;
        }

        //Encabezado
        $this->SetX(30);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 6, 'Status', 0, 0, '', 1);
        $this->Cell(25, 6, 'Amount', 0, 0, 'C', 1);
        $this->Cell(20, 6, 'Items', 0, 0, 'C', 1);
        $this->Cell(20, 6, 'Hits', 0, 0, 'C', 1);
        $this->Cell(20, 6, 'Units', 0, 1, 'C', 1);

        //Cuerpo
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        
        foreach ($result as $row) {
            if ($row['order_status'] != $lastYear) {
                $this->SetX(30);
                $this->SetFillColor(255);
                $this->SetTextColor(0);
                $this->SetFont('', 'BI', 11);
                $this->Cell(51, 6, $row['order_status'], 0, 0, 'C', 1);
                $this->Cell(25, 6, '$'.number_format($totalPorYear[$row['order_status']]['amount'],2), 0, 0, 'R', 1);
                $this->Cell(20, 6, number_format($totalPorYear[$row['order_status']]['items']), 0, 0, 'C', 1);
                $this->Cell(20, 6, number_format($totalPorYear[$row['order_status']]['hits']), 0, 0, 'R', 1);
                $this->Cell(20, 6, number_format($totalPorYear[$row['order_status']]['blanks']), 0, 1, 'R', 1);
            }
            $this->SetX(30);
            $this->SetFont('', '', 10);
            $this->Cell(51, 6,  $row['store'], 0, 0, '', 1);
            $this->Cell(25, 6,  '$'.number_format($row['amount'],2), 0, 0, 'R', 1);
            $this->Cell(20, 6,  number_format($row['items']), 0, 0, 'C', 1);
            $this->Cell(20, 6,  number_format($row['hits']), 0, 0, 'R', 1);
            $this->Cell(20, 6,  number_format($row['blanks']), 0, 1, 'R', 1);

            $totalGlobal['amount'] += $row['amount'];
            $totalGlobal['items'] += $row['items'];
            $totalGlobal['blanks'] += $row['blanks'];
            $totalGlobal['hits'] += $row['hits'];
            $lastYear = $row['order_status'];
        }

        $this->SetX(30);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 6, 'Total', 0, 0, '', 1);
        $this->Cell(25, 6, '$'.number_format($totalGlobal['amount'],2), 0, 0, 'R', 1);
        $this->Cell(20, 6, number_format($totalGlobal['items']), 0, 0, 'C', 1);
        $this->Cell(20, 6, number_format($totalGlobal['hits']), 0, 0, 'R', 1);
        $this->Cell(20, 6, number_format($totalGlobal['blanks']), 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function table16() {

        $obj = new controlDB();

        $sql = "SELECT
                    notas,
                    SUM(total_qty) AS blanks,
                    SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) AS blankT,
                    (SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanks,
                    SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) AS blankWT,
                    (SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanksWT
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE)
                AND order_status != 'CANCELED'
                AND store != 'sales'
                GROUP BY notas ORDER BY blanks DESC";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $totalGlobal = array(
            'blanks' => 0,
            'blankT' => 0,
            'por_blanks' => 0,
            'blankWT' => 0,
            'por_blanksWT' => 0
        );

        //Encabezado
        $this->SetX(20);
        $this->SetTextColor(0);
        $this->SetFillColor(209);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Artist', 0, 0, '', 1);
        $this->Cell(35, 5, 'Total Blanks (FP)', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Treatment', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 0, 'C', 1);
        $this->Cell(20, 5, 'Mill DYE', 0, 0, 'C', 1);
        $this->Cell(20, 5, '%', 0, 1, 'C', 1);
        
        foreach ($result as $row) {
            $this->SetX(15);
            $this->Cell(5, 5, '');
            if ($this->GetY() <= 25.00125) {
                // Encabezado
                $this->SetX(20);
                $this->SetTextColor(0);
                $this->SetFillColor(209);
                $this->SetFont('', 'B', 11);
                $this->Cell(51, 5, 'Artist', 0, 0, '', 1);
                $this->Cell(35, 5, 'Total Blanks (FP)', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Treatment', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 0, 'C', 1);
                $this->Cell(20, 5, 'Mill DYE', 0, 0, 'C', 1);
                $this->Cell(20, 5, '%', 0, 1, 'C', 1);
            }
            $this->SetX(20);
            $this->SetFillColor(255);
            $this->SetTextColor(0);
            $this->SetFont('', '', 10);
            $this->Cell(51, 5,  $row['notas'], 0, 0, '', 1);
            $this->Cell(35, 5,  number_format($row['blanks']), 0, 0, 'C', 1);
            $this->Cell(20, 5,  number_format($row['blankT']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanks'],2).'%', 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['blankWT']), 0, 0, 'R', 1);
            $this->Cell(20, 5,  number_format($row['por_blanksWT'],2).'%', 0, 1, 'R', 1);

            $totalGlobal['blanks'] += $row['blanks'];
            $totalGlobal['blankT'] += $row['blankT'];
            $totalGlobal['por_blanks'] += $row['por_blanks'];
            $totalGlobal['blankWT'] += $row['blankWT'];
            $totalGlobal['por_blanksWT'] += $row['por_blanksWT'];
        }

        $totalGlobal['por_blanks'] = ($totalGlobal['blankT'] / $totalGlobal['blanks']) * 100;
        $totalGlobal['por_blanksWT'] = ($totalGlobal['blankWT'] / $totalGlobal['blanks']) * 100;

        $this->SetX(20);
        $this->SetFillColor(209);
        $this->SetTextColor(0);
        $this->SetFont('', 'B', 11);
        $this->Cell(51, 5, 'Total', 0, 0, '', 1);
        $this->Cell(35, 5, number_format($totalGlobal['blanks']), 0, 0, 'C', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blankT']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanks'], 2).'%', 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['blankWT']), 0, 0, 'R', 1);
        $this->Cell(20, 5, number_format($totalGlobal['por_blanksWT'], 2).'%', 0, 1, 'R', 1);

        $this->SetTextColor(0);
    }

    function chart() {
        
        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }

        $param = [':client'=>$cliente];
        $sql = "SELECT YEAR(fecha) AS years, DATE_FORMAT(fecha, '%b') AS months, MONTH(fecha) AS mes, SUM(sale) AS total FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE) GROUP BY years, months ORDER BY years, mes";
        
        $res = $obj->consultar($sql, $param);

        $sales = array();

        foreach ($res as $total) {
            $year = $total['years'];
            if (!isset($sales[$year])) {
                $sales[$year] = array();
            }
            $month = $total['months'];
            $sales[$year][$month] = $total['total'];
        }

        foreach ($sales as $year => $data) {
            $chartData = array();
            foreach ($data as $month => $total) {
                $chartData[] = array($month, $total);
            }

            $months = array_column($chartData, 0);
            $totals = array_column($chartData, 1);

            $chartConfig = array(
                "type" => "line",
                "data" => array(
                    "labels" => $months,
                    "datasets" => array(
                        array(
                            "label" => "",
                            "data" => $totals,
                            "borderColor" => "rgba(75, 192, 192, 1)",
                            "borderWidth" => 2,
                            "fill" => false
                        )
                    )
                ),
                "options" => array(
                    "legend" => array(
                        "display" => false
                    ),
                    "scales" => array(
                        "y" => array(
                            "beginAtZero" => true,
                        )
                    )
                )
            );

            $encodedChartConfig = urlencode(json_encode($chartConfig));
            $chartUrl = "https://quickchart.io/chart?c=$encodedChartConfig";

            $this->SetFont('', 'I', 10);
            $this->Cell(45, 10, 'Sales ' . $year, 0, 1, 'R');
            $this->Ln(.5);
            $this->Image($chartUrl, 55, $this->GetY(), 90, 50, 'PNG');
            $this->Ln(65);
        }
    }

    function porcent() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    store,
                    SUM(total_qty) AS blanks,
                    SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) AS blankT,
                    (SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanks,
                    SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) AS blankWT,
                    (SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_blanksWT
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE)
                AND order_status != 'CANCELED'
                GROUP BY years, store";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $totalsGlobal = array(
            'blanks' => 0,
            'blankT' => 0,
            'por_blanks' => 0,
            'blankWT' => 0,
            'por_blanksWT' => 0
        );

        foreach ($result as $row) {
            $totalsGlobal['blanks'] += $row['blanks'];
            $totalsGlobal['blankT'] += $row['blankT'];
            $totalsGlobal['por_blanks'] += $row['por_blanks'];
            $totalsGlobal['blankWT'] += $row['blankWT'];
            $totalsGlobal['por_blanksWT'] += $row['por_blanksWT'];
        }

        $totalsGlobal['por_blanks'] = ($totalsGlobal['blankT'] / $totalsGlobal['blanks']) * 100;
        $totalsGlobal['por_blanksWT'] = ($totalsGlobal['blankWT'] / $totalsGlobal['blanks']) * 100;

        return $totalsGlobal;
    }

    function porcentBlank() {

        $obj = new controlDB();

        $sql = "SELECT
                    YEAR(date_received) AS years,
                    store,
                    SUM(total_qty) AS total,
                    COUNT(no_orden) AS items,
                    SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) AS less_300,
                    (SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300,
                    SUM(CASE WHEN total_qty > 300 AND total_qty < 600 THEN total_qty ELSE 0 END) AS _300_to_600,
                    (SUM(CASE WHEN total_qty > 300 AND total_qty < 600 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300_to_600,
                    SUM(CASE WHEN total_qty > 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) AS _600_to_1200,
                    (SUM(CASE WHEN total_qty > 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_600_to_1200,
                    SUM(CASE WHEN total_qty > 1200 THEN total_qty ELSE 0 END) AS over_1200,
                    (SUM(CASE WHEN total_qty > 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200
                FROM t_wip
                WHERE client = :client
                AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE)
                AND order_status != 'CANCELED'
                GROUP BY years, store";
        
        $result = $obj->consultar($sql, [':client'=>$this->client]);

        $totalGlobal = array(
            'total' => 0,
            'less_300' => 0,
            'por_300' => 0,
            '_300_to_600' => 0,
            'por_300_to_600' => 0,
            '_600_to_1200' => 0,
            'por_600_to_1200' => 0,
            'over_1200' => 0,
            'por_1200' => 0
        );

        foreach ($result as $row) {
            $totalGlobal['total'] += $row['total'];
            $totalGlobal['less_300'] += $row['less_300'];
            $totalGlobal['por_300'] += $row['por_300'];
            $totalGlobal['_300_to_600'] += $row['_300_to_600'];
            $totalGlobal['por_300_to_600'] += $row['por_300_to_600'];
            $totalGlobal['_600_to_1200'] += $row['_600_to_1200'];
            $totalGlobal['por_600_to_1200'] += $row['por_600_to_1200'];
            $totalGlobal['over_1200'] += $row['over_1200'];
            $totalGlobal['por_1200'] += $row['por_1200'];
        }

        $totalGlobal['por_300'] = ($totalGlobal['less_300'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_300_to_600'] = ($totalGlobal['_300_to_600'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_600_to_1200'] = ($totalGlobal['_600_to_1200'] / $totalGlobal['total']) * 100;
        $totalGlobal['por_1200'] = ($totalGlobal['over_1200'] / $totalGlobal['total']) * 100;

        return $totalGlobal;
    }

    function first_date() {

        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }

        $param = [':client'=>$cliente];
        $sql = "SELECT fecha, client, sale FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE)";

        $res = $obj->consultar($sql, $param);

        foreach ($res AS $data) {
            if ($data['sale'] > 0) {
                $dates = date("M 'y", strtotime($data['fecha']));
                return $dates;
            }
        }

    }

    function totals() {

        $obj = new controlDB();
        $cliente = $this->client;
        $clients = strstr($cliente, ' (', true);

        if ($clients == 'CIVIL REGIME') {
            $clients = 'CIVIL CLOTHING';
        }

        $param = [':client'=>$cliente];
        $sql = "SELECT SUM(sale) AS total FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client AND YEAR(fecha) BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE)";

        $res = $obj->consultar($sql, $param);

        foreach ($res AS $data) {
            $total = '$'.number_format($data['total'],2); 
        }
        return $total;
    }

    function top() {

        $obj = new controlDB();

        $sql = "SELECT notas, SUM(total_qty) AS blanks FROM t_wip WHERE client = :client AND date_received BETWEEN YEAR(CURRENT_DATE())-5 AND YEAR(CURRENT_DATE) AND order_status != 'CANCELED' AND store != 'sales' GROUP BY notas ORDER BY blanks DESC LIMIT 3";

        $res = $obj->consultar($sql, [':client'=>$this->client]);

        $top = array();
        foreach ($res AS $data) {
            $top[] = $data['notas']; 
        }
        return $top;
    }

    function startPageNums() {
        $this->_numbering=true;
        $this->_numberingFooter=true;
    }

    function stopPageNums() {
        $this->_numbering=false;
    }

    function TOC_Entry($txt, $level=0) {
        $this->_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->PageNo());
    }

    function insertTOC( $location=1,
                        $labelSize=16,
                        $entrySize=12,
                        $tocfont='Arial',
                        $label='Contents'
                        ) {
        //make toc at end
        $this->stopPageNums();
        $this->AddPage();
        $tocstart=$this->page;

        $this->SetFont($tocfont,'B',$labelSize);
        $this->Ln();
        $this->Cell(0,5,$label,0,1,'C');
        $this->Ln(5);

        foreach($this->_toc as $t) {

            //Offset
            $level=$t['l'];
            if($level>0)
                $this->Cell($level*8);
            $weight='';
            if($level==0)
                $weight='B';
            $str=$t['t'];
            $this->SetFont($tocfont,$weight,$entrySize);
            $strsize=$this->GetStringWidth($str);
            $this->Cell($strsize+2,$this->FontSize+2,$str);

            //Filling dots
            $this->SetFont($tocfont,'',$entrySize);
            $PageCellSize=$this->GetStringWidth($t['p'])+2;
            $w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*8)-($strsize+2);
            $nb=$w/$this->GetStringWidth('.');
            $dots=str_repeat('.',(int)$nb);
            $this->Cell($w,$this->FontSize+2,$dots,0,0,'R');

            //Page number
            $this->Cell($PageCellSize,$this->FontSize+2,$t['p'],0,1,'R');
        }

        //Grab it and move to selected location
        $n=$this->page; 
        $n_toc = $n - $tocstart + 1;
        $last = array();

        //store toc pages
        for($i = $tocstart;$i <= $n;$i++)
            $last[]=$this->pages[$i];

        //move pages
        // for($i=$tocstart-1;$i>=$location-1;$i--)
        //     $this->pages[$i+$n_toc]=$this->pages[$i];

        //Put toc pages at insert point
        for($i = 0;$i < $n_toc;$i++)
            $this->pages[$location + $i]=$last[$i];
    }
}  
?>