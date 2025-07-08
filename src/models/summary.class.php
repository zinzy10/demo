<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';

class summary {

    public $storeData;
    private $obj;

    public function __construct() {
        $this->obj = new ControlDB();
    }

    function orders($opc, $client, $d1, $d2) {

        $dataGlobal = [];
    
        if ($opc == '1') {
            $store = "store AS stores";
            $where = "client LIKE :client";
            $clientFilter = "%$client%";
        } else {
            $store = "TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores";
            $where = "store = :client AND client NOT IN ('NA', 'PEND', 'PENDING')";
            $clientFilter = $client;
        }
    
        $sql = "SELECT
                    $store,
                    COUNT(no_orden) AS items,
                    COUNT(DISTINCT po_lot) AS Pos,
                    SUM(CASE WHEN client LIKE '%(%' THEN 1 ELSE 0 END) AS itemsFull,
                    SUM(CASE WHEN client NOT LIKE '%(%' THEN 1 ELSE 0 END) AS itemsContract,
                    COUNT(DISTINCT CASE WHEN client LIKE '%(%' THEN po_lot ELSE NULL END) AS poFull,
                    COUNT(DISTINCT CASE WHEN client NOT LIKE '%(%' THEN po_lot ELSE NULL END) AS poContract
                FROM t_wip
                WHERE order_status != 'canceled'
                AND date_received BETWEEN :d1 AND :d2
                AND $where
                GROUP BY stores ORDER BY Pos DESC";
    
        $sqlTotal = "SELECT
                        COUNT(DISTINCT po_lot) AS TP,
                        COUNT(DISTINCT CASE WHEN client LIKE '%(%' THEN po_lot ELSE NULL END) AS TPF,
                        COUNT(DISTINCT CASE WHEN client NOT LIKE '%(%' THEN po_lot ELSE NULL END) AS TPC,
                        COUNT(no_orden) AS TI,
                        SUM(CASE WHEN client LIKE '%(%' THEN 1 ELSE 0 END) AS TIF,
                        SUM(CASE WHEN client NOT LIKE '%(%' THEN 1 ELSE 0 END) AS TIC
                    FROM t_wip
                    WHERE order_status != 'canceled'
                    AND date_received BETWEEN :d1 AND :d2
                    AND $where";
    
        $params = [':client'=>$clientFilter, ':d1'=>$d1, ':d2'=>$d2];
        $res = $this->obj->consultar($sql, $params);
        $totales = $this->obj->consultar($sqlTotal, $params)[0];
    
        foreach ($res as $row) {
            $dataGlobal['table'][] = [
                'stores' => $row['stores'],
                'items' => $row['items'],
                'po' => $row['Pos'],
                'itemsFull' => $row['itemsFull'],
                'poFull' => $row['poFull'],
                'poContract' => $row['poContract'],
                'itemsC' => $row['itemsContract']
            ];
        }
    
        $dataGlobal['totales'] = [
            'tp' => $totales['TP'],
            'tpf' => $totales['TPF'],
            'tpc' => $totales['TPC'],
            'ti' => $totales['TI'],
            'tif' => $totales['TIF'],
            'tic' => $totales['TIC']
        ];
    
        $this->storeData = $dataGlobal;
    }
    
    function impression($opc, $client, $d1, $d2) {

        $dataGlobal = [];

        if ($opc == '1') {
            $store = "store AS stores";
            $where = "client LIKE :client";
            $clientFilter = "%$client%";
        } else {
            $store = "TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores";
            $where = "store = :client AND client NOT IN ('NA', 'PEND', 'PENDING')";
            $clientFilter = $client;
        }

        $sql = "SELECT $store,
		            SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE 1 * total_qty END ) AS hits,
                    SUM(CASE WHEN comments LIKE '%hits%' AND client LIKE '%FULL%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN client NOT LIKE '%FULL%' THEN 0 ELSE 1 * total_qty END ) AS hitsFull,
                    SUM(CASE WHEN comments LIKE '%hits%' AND client NOT LIKE '%FULL%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN client LIKE '%FULL%' THEN 0 ELSE 1 * total_qty END ) AS hitsCont,
                    SUM(CASE WHEN comments NOT LIKE '%hits%' THEN total_qty ELSE 0 END ) AS singleHit,
                    SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits', comments)-2,1) * total_qty ELSE 0 END ) AS multiHit
                FROM t_wip
                WHERE $where
                AND date_received BETWEEN :d1 AND :d2
                AND order_status != 'CANCELED'
                GROUP BY stores ORDER BY hits DESC";

        $sqlTotal = "SELECT
		                SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE 1 * total_qty END ) AS TH,
                        SUM(CASE WHEN comments LIKE '%hits%' AND client LIKE '%FULL%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN client NOT LIKE '%FULL%' THEN 0 ELSE 1 * total_qty END ) AS FPH,
                        SUM(CASE WHEN comments LIKE '%hits%' AND client NOT LIKE '%FULL%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN client LIKE '%FULL%' THEN 0 ELSE 1 * total_qty END ) AS CH,
                        SUM(CASE WHEN comments NOT LIKE '%hits%' THEN total_qty ELSE 0 END ) AS SH,
                        SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits', comments)-2,1) * total_qty ELSE 0 END ) AS MH
                    FROM t_wip
                    WHERE $where
                    AND date_received BETWEEN :d1 AND :d2
                    AND order_status != 'CANCELED'";
        
        $params = [':client'=>$clientFilter, ':d1'=>$d1, ':d2'=>$d2];
        $res = $this->obj->consultar($sql, $params);
        $totales = $this->obj->consultar($sqlTotal, $params)[0];
        
        foreach ($res AS $row) {
            $dataGlobal['table'][] = [
                'stores' => $row['stores'],
                'totalH' => $row['hits'],
                'hitsFP' => $row['hitsFull'],
                'hitsC' => $row['hitsCont'],
                'single' => $row['singleHit'],
                'multiH' => $row['multiHit']
            ];
        }

        $dataGlobal['totales'] = [
            'th' => $totales['TH'],
            'fph' => $totales['FPH'],
            'ch' => $totales['CH'],
            'sh' => $totales['SH'],
            'mh' => $totales['MH']
        ];

        $this->storeData = $dataGlobal;
    }

    function blanks($opc, $client, $d1, $d2) {

        $dataGlobal = [];

        if($opc == '1') {
            $store = "store AS stores";
            $where = "client LIKE :client";
            $clientFilter = "%$client%";
        } else {
            $store = "TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores";
            $where = "store = :client AND client NOT IN ('NA', 'PEND', 'PENDING')";
            $clientFilter = $client;
        }

        $sql = "SELECT $store, 
		            SUM(total_qty) AS totalBlanks,
                    SUM(CASE WHEN client LIKE '%FULL%' THEN total_qty ELSE 0 END ) AS FPblanks,
                    SUM(CASE WHEN client NOT LIKE '%FULL%' THEN total_qty ELSE 0 END ) AS ContBlanks,
                    SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END ) AS dye,
                    SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END ) AS no_dye,
                    SUM(CASE WHEN client LIKE '%FULL%' AND treatment != 'NA' THEN total_qty ELSE 0 END) AS FP_dye,
                    SUM(CASE WHEN client NOT LIKE '%FULL%' AND treatment != 'NA' THEN total_qty ELSE 0 END) AS Cont_dye,
                    SUM(CASE WHEN client LIKE '%FULL%' AND treatment = 'NA' THEN total_qty ELSE 0 END) AS FP_noDye,
                    SUM(CASE WHEN client NOT LIKE '%FULL%' AND treatment = 'NA' THEN total_qty ELSE 0 END) AS Cont_noDye,
                    SUM(CASE WHEN technique LIKE '%EMBROIDERY%' THEN total_qty ELSE 0 END) AS embroidery
                FROM t_wip
                WHERE $where 
                AND date_received BETWEEN :d1 AND :d2
                AND order_status != 'CANCELED'
                GROUP BY stores ORDER BY totalBlanks DESC";

        $sqlTotal = "SELECT
		                SUM(total_qty) AS BT,
                        SUM(CASE WHEN client LIKE '%FULL%' THEN total_qty ELSE 0 END ) AS BF,
                        SUM(CASE WHEN client NOT LIKE '%FULL%' THEN total_qty ELSE 0 END ) AS BC,
                        SUM(CASE WHEN treatment != 'NA' THEN total_qty ELSE 0 END ) AS DT,
                        SUM(CASE WHEN treatment = 'NA' THEN total_qty ELSE 0 END ) AS NDT,
                        SUM(CASE WHEN client LIKE '%FULL%' AND treatment != 'NA' THEN total_qty ELSE 0 END) AS DF,
                        SUM(CASE WHEN client NOT LIKE '%FULL%' AND treatment != 'NA' THEN total_qty ELSE 0 END) AS DC,
                        SUM(CASE WHEN client LIKE '%FULL%' AND treatment = 'NA' THEN total_qty ELSE 0 END) AS NDF,
                        SUM(CASE WHEN client NOT LIKE '%FULL%' AND treatment = 'NA' THEN total_qty ELSE 0 END) AS NDC,
                        SUM(CASE WHEN technique LIKE '%EMBROIDERY%' THEN total_qty ELSE 0 END) AS ET
                    FROM t_wip
                    WHERE $where
                    AND date_received BETWEEN :d1 AND :d2
                    AND order_status != 'CANCELED'";

        $params = [':client'=>$clientFilter, ':d1'=>$d1, ':d2'=>$d2];
        $res = $this->obj->consultar($sql, $params);
        $totales = $this->obj->consultar($sqlTotal, $params)[0];
        
        foreach ($res AS $row) {
            $dataGlobal['table'][] = [
                'stores' => $row['stores'],
                'blanks' => $row['totalBlanks'],
                'dye' => $row['dye'],
                'Wdye' => $row['no_dye'],
                'emb' => $row['embroidery'],
                'blanksF' => $row['FPblanks'],
                'dyeF' => $row['FP_dye'],
                'wdyeF' => $row['FP_noDye'],
                'blanksC' => $row['ContBlanks'],
                'dyeC' => $row['Cont_dye'],
                'wdyeC' => $row['Cont_noDye']
            ];
        }

        $dataGlobal['totales'] = [
            'bt' => $totales['BT'],
            'dt' => $totales['DT'],
            'ndt' => $totales['NDT'],
            'et' => $totales['ET'],
            'bf' => $totales['BF'],
            'df' => $totales['DF'],
            'ndf' => $totales['NDF'],
            'bc' => $totales['BC'],
            'dc' => $totales['DC'],
            'ndc' => $totales['NDC']
        ];

        $this->storeData = $dataGlobal;
    }

    function units($opc, $client, $d1, $d2) {

        $dataGlobal = [];

        if($opc == '1') {
            $store = "store AS stores";
            $where = "client LIKE :client";
            $clientFilter = "%$client%";
        } else {
            $store = "TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores";
            $where = "store = :client AND client NOT IN ('NA', 'PEND', 'PENDING')";
            $clientFilter = $client;
        }

        $sql = "SELECT
                    $store,
                    SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) AS less_300,
                    (SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300,
                    SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) AS _300_to_600,
                    (SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300_to_600,
                    SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) AS _600_to_1200,
                    (SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_600_to_1200,
                    SUM(CASE WHEN total_qty >= 1200 AND total_qty < 10000 THEN total_qty ELSE 0 END) AS _1200_to_10000,
                    (SUM(CASE WHEN total_qty >= 1200 AND total_qty < 10000 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200_to_10000,
                    SUM(CASE WHEN total_qty >= 10000 THEN total_qty ELSE 0 END) AS over_10000,
                    (SUM(CASE WHEN total_qty >= 10000 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_10000
                FROM t_wip
                WHERE $where
                AND date_received BETWEEN :d1 AND :d2
                AND order_status != 'CANCELED'
                GROUP BY stores ORDER BY less_300 DESC";

        $sqlTotal = "SELECT
                        SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) AS less_300,
                        (SUM(CASE WHEN total_qty < 300 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300,
                        SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) AS _300_to_600,
                        (SUM(CASE WHEN total_qty >= 300 AND total_qty < 600 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_300_to_600,
                        SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) AS _600_to_1200,
                        (SUM(CASE WHEN total_qty >= 600 AND total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_600_to_1200,
                        SUM(CASE WHEN total_qty >= 1200 AND total_qty < 10000 THEN total_qty ELSE 0 END) AS _1200_to_10000,
                        (SUM(CASE WHEN total_qty >= 1200 AND total_qty < 10000 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200_to_10000,
                        SUM(CASE WHEN total_qty >= 10000 THEN total_qty ELSE 0 END) AS over_10000,
                        (SUM(CASE WHEN total_qty >= 10000 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_10000
                    FROM t_wip
                    WHERE $where 
                    AND date_received BETWEEN :d1 AND :d2 
                    AND order_status != 'CANCELED'";

        $params = [':client'=>$clientFilter, ':d1'=>$d1, ':d2'=>$d2];
        $res = $this->obj->consultar($sql, $params);
        $totales = $this->obj->consultar($sqlTotal, $params)[0];

        foreach ($res as $row) {
            $dataGlobal['table'][] = [
                'stores' => $row['stores'],
                'less_300' => $row['less_300'],
                '300P' => $row['por_300'],
                '300_600' => $row['_300_to_600'],
                '300_600P' => $row['por_300_to_600'],
                '600_1200' => $row['_600_to_1200'],
                '600_1200P' => $row['por_600_to_1200'],
                '1200_10K' => $row['_1200_to_10000'],
                '1200_10KP' => $row['por_1200_to_10000'],
                '10K' => $row['over_10000'],
                '10KP' => $row['por_10000']
            ];
        }

        $dataGlobal['totales'] = [
            'less' => $totales['less_300'],
            '300P' => $totales['por_300'],
            '300_600' => $totales['_300_to_600'],
            '300_600P' => $totales['por_300_to_600'],
            '600_1200' => $totales['_600_to_1200'],
            '600_1200P' => $totales['por_600_to_1200'],
            '1200_10K' => $totales['_1200_to_10000'],
            '1200_10KP' => $totales['por_1200_to_10000'],
            '10K' => $totales['over_10000'],
            '10KP' => $totales['por_10000']
        ];

        $this->storeData = $dataGlobal;
    }

    function units2($opc, $client, $d1, $d2) {

        $dataGlobal = [];

        if($opc == '1') {
            $store = "store AS stores";
            $where = "client LIKE :client";
            $clientFilter = "%$client%";
        } else {
            $store = "TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores";
            $where = "store = :client AND client NOT IN ('NA', 'PEND', 'PENDING')";
            $clientFilter = $client;
        }

        $sql = "SELECT
                    $store,
                    SUM(CASE WHEN total_qty < 1200 THEN total_qty ELSE 0 END) AS less_1200,
                    (SUM(CASE WHEN total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200,
                    COUNT(CASE WHEN total_qty < 1200 THEN 1 ELSE NULL END) AS items_less_1200,
                    (COUNT(CASE WHEN total_qty < 1200 THEN 1 ELSE NULL END) / COUNT(*)) * 100 AS por_items_1200,
                    SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) AS over_1200,
                    (SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_over_1200,
                    COUNT(CASE WHEN total_qty >= 1200 THEN 1 ELSE NULL END) AS items_over_1200,
                    (COUNT(CASE WHEN total_qty >= 1200 THEN 1 ELSE NULL END) / COUNT(*)) * 100 AS por_itemsO_1200
                FROM t_wip
                WHERE $where
                AND date_received BETWEEN :d1 AND :d2
                AND order_status != 'CANCELED'
                GROUP BY stores ORDER BY less_1200 DESC";

        $sqlTotal = "SELECT
                        SUM(CASE WHEN total_qty < 1200 THEN total_qty ELSE 0 END) AS less_1200,
                        (SUM(CASE WHEN total_qty < 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_1200,
                        COUNT(CASE WHEN total_qty < 1200 THEN 1 ELSE NULL END) AS items_less_1200,
                        (COUNT(CASE WHEN total_qty < 1200 THEN 1 ELSE NULL END) / COUNT(*)) * 100 AS por_items_1200,
                        SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) AS over_1200,
                        (SUM(CASE WHEN total_qty >= 1200 THEN total_qty ELSE 0 END) / SUM(total_qty)) * 100 AS por_over_1200,
                        COUNT(CASE WHEN total_qty >= 1200 THEN 1 ELSE NULL END) AS items_over_1200,
                        (COUNT(CASE WHEN total_qty >= 1200 THEN 1 ELSE NULL END) / COUNT(*)) * 100 AS por_itemsO_1200
                    FROM t_wip
                    WHERE $where
                    AND date_received BETWEEN :d1 AND :d2
                    AND order_status != 'CANCELED'";

        $params = [':client'=>$clientFilter, ':d1'=>$d1, ':d2'=>$d2];
        $res = $this->obj->consultar($sql, $params);
        $totales = $this->obj->consultar($sqlTotal, $params)[0];

        foreach ($res as $row) {
            $dataGlobal['table'][] = [
                'stores' => $row['stores'],
                'less_1200' => $row['less_1200'],
                '1200P' => $row['por_1200'],
                'items1200' => $row['items_less_1200'],
                'items1200P' => $row['por_items_1200'],
                'over_1200' => $row['over_1200'],
                'over_1200P' => $row['por_over_1200'],
                'itemsO1200' => $row['items_over_1200'],
                'itemsO1200P' => $row['por_itemsO_1200']
            ];
        }

        $dataGlobal['totales'] = [
            'less' => $totales['less_1200'],
            'less_1200P' => $totales['por_1200'],
            'items1200' => $totales['items_less_1200'],
            'items1200P' => $totales['por_items_1200'],
            'over_1200' => $totales['over_1200'],
            'over_1200P' => $totales['por_over_1200'],
            'itemsO1200' => $totales['items_over_1200'],
            'itemsO1200P' => $totales['por_itemsO_1200']
        ];

        $this->storeData = $dataGlobal;
    }
}
?>