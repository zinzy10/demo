<?php
include '../models/summary.class.php';
include '../models/summaryEdit.class.php';

class SPDF {

    function ordersTable($opc, $client, $d1, $d2, $check) {

        if ($check){
            $tableData = new summaryE();
            $tableData->orders($opc, $client, $d1, $d2, $check);
        } else {
            $tableData = new summary();
            $tableData->orders($opc, $client, $d1, $d2);
        }
        $storeData = $tableData->storeData;

        $tableHtml = '<table>
                        <thead>
                            <tr>
                                <th>Client / Store</th>
                                <th class="center">Total POs</th>
                                <th class="center">POs FP</th>
                                <th class="center">POs Contract</th>
                                <th class="center">Total Items</th>
                                <th class="center">Items FP</th>
                                <th class="center">Items Contract</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($storeData['table'] AS $row) {
            $stores = $row['stores'];
            $po = ($row['po'] != 0) ? number_format($row['po'],0) : '';
            $poFull = ($row['poFull'] != 0) ? number_format($row['poFull'],0) : '';
            $items = ($row['items'] != 0) ? number_format($row['items'],0) : '';
            $itemFull = ($row['itemsFull'] != 0) ? number_format($row['itemsFull'],0) : '';
            $PoC = ($row['poContract'] != 0) ? number_format($row['poContract'],0) : '';
            $itemC = ($row['itemsC'] != 0) ? number_format($row['itemsC'],0) : '';
                
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $stores . '</td>';
            $tableHtml .= '<td class="center">' . $po . '</td>';
            $tableHtml .= '<td class="center">' . $poFull . '</td>';
            $tableHtml .= '<td class="center">' . $PoC . '</td>';
            $tableHtml .= '<td class="center">' . $items . '</td>';
            $tableHtml .= '<td class="center">' . $itemFull . '</td>';
            $tableHtml .= '<td class="center">' . $itemC . '</td></tr>';
        }   
        $tp = number_format($storeData['totales']['tp'],0);
        $tpf = number_format($storeData['totales']['tpf'],0);
        $tpc = number_format($storeData['totales']['tpc'],0);
        $ti = number_format($storeData['totales']['ti'],0);
        $tif = number_format($storeData['totales']['tif'],0);
        $tic = number_format($storeData['totales']['tic'],0);

        $tableHtml .= '<tr class="footer"><td>Total</td><td class="center">'.$tp.'</td><td class="center">'.$tpf.'</td><td class="center">'.$tpc.'</td><td class="center">'.$ti.'</td><td class="center">'.$tif.'</td><td class="center">'.$tic.'</td></tr>';
        $tableHtml .= '</tbody></table>';

        $html = '<head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 0.875rem;
                            margin-top: -20pt; 
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            border: 1px solid #ddd;
                        }
                        th, td {
                            padding: 2px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }
                        th {
                            background-color: #f4f4f4;
                        }
                        .center {   
                            text-align: center;
                        }
                        .footer {
                            font-weight: bolder;
                            background-color: #f4f4f4;
                        }
                        .text-right {
                            text-align: right;
                        }
                        .text-left {
                            text-align: left;
                        }
                        h2 {
                            font-style: italic; 
                            text-align: center;
                        }
                    </style>
                </head>
                <body>
                    <h2>Summary '.$client.'</h2>
                    <h2>ORDERS</h2>
                        ' . $tableHtml . '
                    <div style="page-break-after: always;"></div>';
                
        return $html;
    }

    function impressionTable($opc, $client, $d1, $d2, $check) {

        if ($check) {
            $tableData = new summaryE();
            $tableData->impression($opc, $client, $d1, $d2, $check);
        } else {
            $tableData = new summary();
            $tableData->impression($opc, $client, $d1, $d2);
        }
        $storeData = $tableData->storeData;

        $tableHtml = '<table>
                        <thead>
                            <tr>
                                <th>Client / Store</th>
                                <th class="center">Total Hits</th>
                                <th class="center">FP Hits</th>
                                <th class="center">Contract Hits</th>
                                <th class="center">Single Hits</th>
                                <th class="center">Multiple Hits</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($storeData['table'] AS $row) {
            $stores = $row['stores'];
            $totalH = ($row['totalH'] != 0) ? number_format($row['totalH'],0) : '';
            $hitsFP = ($row['hitsFP'] != 0) ? number_format($row['hitsFP'],0) : '';
            $hitsC = ($row['hitsC'] != 0) ? number_format($row['hitsC'],0) : '';
            $single = ($row['single'] != 0) ? number_format($row['single'],0) : '';
            $multiH = ($row['multiH'] != 0) ? number_format($row['multiH'],0) : '';
                
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $stores . '</td>';
            $tableHtml .= '<td class="center">' . $totalH . '</td>';
            $tableHtml .= '<td class="center">' . $hitsFP . '</td>';
            $tableHtml .= '<td class="center">' . $hitsC . '</td>';
            $tableHtml .= '<td class="center">' . $single . '</td>';
            $tableHtml .= '<td class="center">' . $multiH . '</td></tr>';
        }   
        $th = number_format($storeData['totales']['th'],0);
        $fph = number_format($storeData['totales']['fph'],0);
        $ch = number_format($storeData['totales']['ch'],0);
        $sh = number_format($storeData['totales']['sh'],0);
        $mh = number_format($storeData['totales']['mh'],0);

        $tableHtml .= '<tr class="footer"><td>Total</td><td class="center">'.$th.'</td><td class="center">'.$fph.'</td><td class="center">'.$ch.'</td><td class="center">'.$sh.'</td><td class="center">'.$mh.'</td></tr>';
        $tableHtml .= '</tbody></table>';

        $html = '<h2>Summary '.$client.'</h2>
                <h2>IMPRESSIONS</h2>
                    ' . $tableHtml . '
                <div style="page-break-after: always;"></div>';
                
        return $html;
    }

    function blanksTable($opc, $client, $d1, $d2, $check) {

        if ($check) {
            $tableData = new summaryE();
            $tableData->blanks($opc, $client, $d1, $d2, $check);
        } else {
            $tableData = new summary();
            $tableData->blanks($opc, $client, $d1, $d2);
        }
        $storeData = $tableData->storeData;

        $tableHtml = '<table>
                        <thead>
                            <tr>
                                <th>Client / Store</th>
                                <th class="center">Total Blanks</th>
                                <th class="center">FP Blanks</th>
                                <th class="center">Contract Blanks</th>
                                <th class="center">With Dye</th>
                                <th class="center">Without Dye</th>
                                <th class="center">FP With Dye</th>
                                <th class="center">Contract Dye</th>
                                <th class="center">FP Without Dye</th>
                                <th class="center">Contract Without Dye</th>.
                                <th class="center">Embroidery</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($storeData['table'] AS $row) {
            $stores = $row['stores'];
            $blanksT = ($row['blanks'] != 0) ? number_format($row['blanks'],0) : '';
            $blanksF = ($row['blanksF'] != 0) ? number_format($row['blanksF'],0) : '';
            $blanksC = ($row['blanksC'] != 0) ? number_format($row['blanksC'],0) : '';
            $dye = ($row['dye'] != 0) ? number_format($row['dye'],0) : '';
            $wdye = ($row['Wdye'] != 0) ? number_format($row['Wdye'],0) : '';
            $dyeF = ($row['dyeF'] != 0) ? number_format($row['dyeF'],0) : '';
            $dyeC = ($row['dyeC'] != 0) ? number_format($row['dyeC'],0) : '';
            $wdyeF = ($row['wdyeF'] != 0) ? number_format($row['wdyeF'],0) : '';
            $wdyeC = ($row['wdyeC'] != 0) ? number_format($row['wdyeC'],0) : '';
            $emb = ($row['emb'] != 0) ? number_format($row['emb'],0) : '';
                
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $stores . '</td>';
            $tableHtml .= '<td class="center">' . $blanksT . '</td>';
            $tableHtml .= '<td class="center">' . $blanksF . '</td>';
            $tableHtml .= '<td class="center">' . $blanksC . '</td>';
            $tableHtml .= '<td class="center">' . $dye . '</td>';
            $tableHtml .= '<td class="center">' . $wdye . '</td>';
            $tableHtml .= '<td class="center">' . $dyeF . '</td>';
            $tableHtml .= '<td class="center">' . $dyeC . '</td>';
            $tableHtml .= '<td class="center">' . $wdyeF . '</td>';
            $tableHtml .= '<td class="center">' . $wdyeC . '</td>';
            $tableHtml .= '<td class="center">' . $emb . '</td></tr>';
        }   
        $bt = number_format($storeData['totales']['bt'],0);
        $dt = number_format($storeData['totales']['dt'],0);
        $ndt = number_format($storeData['totales']['ndt'],0);
        $et = number_format($storeData['totales']['et'],0);
        $bf = number_format($storeData['totales']['bf'],0);
        $df = number_format($storeData['totales']['df'],0);
        $ndf = number_format($storeData['totales']['ndf'],0);
        $bc = number_format($storeData['totales']['bc'],0);
        $dc = number_format($storeData['totales']['dc'],0);
        $ndc = number_format($storeData['totales']['ndc'],0);

        $tableHtml .= '<tr class="footer"><td>Total</td><td class="center">'.$bt.'</td><td class="center">'.$bf.'</td><td class="center">'.$bc.'</td><td class="center">'.$dt.'</td><td class="center">'.$ndt.'</td><td class="center">'.$df.'</td><td class="center">'.$dc.'</td><td class="center">'.$ndf.'</td><td class="center">'.$ndc.'</td><td class="center">'.$et.'</td></tr>';
        $tableHtml .= '</tbody></table>';

        $html = '<h2>Summary '.$client.'</h2>
                <h2>BLANKS</h2>
                    ' . $tableHtml . '
                <div style="page-break-after: always;"></div>';
                
        return $html;

    }

    function unitsTable($opc, $client, $d1, $d2, $check) {

        if ($check) {
            $tableData = new summaryE();
            $tableData->units($opc, $client, $d1, $d2, $check);
        } else {
            $tableData = new summary();
            $tableData->units($opc, $client, $d1, $d2);
        }
        $storeData = $tableData->storeData;

        $tableHtml = '<table>
                        <thead>
                            <tr>
                                <th>Client / Store</th>
                                <th class="center">Under 300</th>
                                <th class="center">Items %</th>
                                <th class="center">300 to 599</th>
                                <th class="center">Items %</th>
                                <th class="center">600 to 1,199</th>
                                <th class="center">Items %</th>
                                <th class="center">1,200 to 9,999</th>
                                <th class="center">Items %</th>
                                <th class="center">Over 10,000</th>.
                                <th class="center">Items%</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($storeData['table'] AS $row) {
            $stores = $row['stores'];
            $less = ($row['less_300'] != 0) ? number_format($row['less_300'],0) : '';
            $por_300 = ($row['300P'] != 0) ? number_format($row['300P'],2).'%' : '';
            $_300_600 = ($row['300_600'] != 0) ? number_format($row['300_600'],0) : '';
            $por_300_600 = ($row['300_600P'] != 0) ? number_format($row['300_600P'],2).'%' : '';
            $_600_1200 = ($row['600_1200'] != 0) ? number_format($row['600_1200'],0) : '';
            $por_600_1200 = ($row['600_1200P'] != 0) ? number_format($row['600_1200P'],2).'%' : '';
            $_1200_10k = ($row['1200_10K'] != 0) ? number_format($row['1200_10K'],0) : '';
            $por_1200_10k = ($row['1200_10KP'] != 0) ? number_format($row['1200_10KP'],2).'%' : '';
            $_10k = ($row['10K'] != 0) ? number_format($row['10K'],0) : '';
            $por_10k = ($row['10KP'] != 0) ? number_format($row['10KP'],2).'%' : '';
                
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $stores . '</td>';
            $tableHtml .= '<td class="center">' . $less . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_300 . '</td>';
            $tableHtml .= '<td class="center">' . $_300_600 . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_300_600 . '</td>';
            $tableHtml .= '<td class="center">' . $_600_1200 . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_600_1200 . '</td>';
            $tableHtml .= '<td class="center">' . $_1200_10k . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_1200_10k . '</td>';
            $tableHtml .= '<td class="center">' . $_10k . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_10k . '</td></tr>';
        }   
        $bt = number_format($storeData['totales']['less'],0);
        $dt = number_format($storeData['totales']['300P'],2);
        $ndt = number_format($storeData['totales']['300_600'],0);
        $et = number_format($storeData['totales']['300_600P'],2);
        $bf = number_format($storeData['totales']['600_1200'],0);
        $df = number_format($storeData['totales']['600_1200P'],2);
        $ndf = number_format($storeData['totales']['1200_10K'],0);
        $bc = number_format($storeData['totales']['1200_10KP'],2);
        $dc = number_format($storeData['totales']['10K'],0);
        $ndc = number_format($storeData['totales']['10KP'],2);

        $tableHtml .= '<tr class="footer"><td>Total</td><td class="center">'.$bt.'</td><td class="center">'.$dt.'%</td><td class="center">'.$ndt.'</td><td class="center">'.$et.'%</td><td class="center">'.$bf.'</td><td class="center">'.$df.'%</td><td class="center">'.$ndf.'</td><td class="center">'.$bc.'%</td><td class="center">'.$dc.'</td><td class="center">'.$ndc.'%</td></tr>';
        $tableHtml .= '</tbody></table>';

        $html = '<h2>Summary '.$client.'</h2>
                <h2>UNITS</h2>
                    ' . $tableHtml . '
                <div style="page-break-after: always;"></div>';
                
        return $html;
    }

    function unitsTable2($opc, $client, $d1, $d2, $check) {

        if ($check){
            $tableData = new summaryE();
            $tableData->units2($opc, $client, $d1, $d2, $check);
        } else {
            $tableData = new summary();
            $tableData->units2($opc, $client, $d1, $d2);
        }
        $storeData = $tableData->storeData;

        $tableHtml = '<table>
                        <thead>
                            <tr>
                                <th>Client / Store</th>
                                <th class="center">Under 1,200</th>
                                <th class="center">Units %</th>
                                <th class="center">Items Under 1,200</th>
                                <th class="center">Items %</th>
                                <th class="center">Over 1,200</th>
                                <th class="center">Units %</th>
                                <th class="center">Items Over 1,200</th>
                                <th class="center">Items %</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($storeData['table'] AS $row) {
            $stores = $row['stores'];
            $less_1200 = ($row['less_1200'] != 0) ? number_format($row['less_1200'],0) : '';
            $por_1200 = ($row['1200P'] != 0) ? number_format($row['1200P'],2).'%' : '';
            $items_1200 = ($row['items1200'] != 0) ? number_format($row['items1200'],0) : '';
            $items_1200P = ($row['items1200P'] != 0) ? number_format($row['items1200P'],2).'%' : '';
            $over_1200 = ($row['over_1200'] != 0) ? number_format($row['over_1200'],0) : '';
            $over_1200P = ($row['over_1200P'] != 0) ? number_format($row['over_1200P'],2).'%' : '';
            $itemsO_1200 = ($row['itemsO1200'] != 0) ? number_format($row['itemsO1200'],0) : '';
            $itemsO_1200P = ($row['itemsO1200P'] != 0) ? number_format($row['itemsO1200P'],2).'%' : '';
                
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $stores . '</td>';
            $tableHtml .= '<td class="center">' . $less_1200 . '</td>';
            $tableHtml .= '<td class="text-left">' . $por_1200 . '</td>';
            $tableHtml .= '<td class="center">' . $items_1200 . '</td>';
            $tableHtml .= '<td class="text-left">' . $items_1200P . '</td>';
            $tableHtml .= '<td class="center">' . $over_1200 . '</td>';
            $tableHtml .= '<td class="text-left">' . $over_1200P . '</td>';
            $tableHtml .= '<td class="center">' . $itemsO_1200 . '</td>';
            $tableHtml .= '<td class="text-left">' . $itemsO_1200P . '</td></tr>';
        }   
        $bt = number_format($storeData['totales']['less'],0);
        $dt = number_format($storeData['totales']['less_1200P'],2);
        $ndt = number_format($storeData['totales']['items1200'],0);
        $et = number_format($storeData['totales']['items1200P'],2);
        $bf = number_format($storeData['totales']['over_1200'],0);
        $df = number_format($storeData['totales']['over_1200P'],2);
        $ndf = number_format($storeData['totales']['itemsO1200'],0);
        $bc = number_format($storeData['totales']['itemsO1200P'],2);

        $tableHtml .= '<tr class="footer"><td>Total</td><td class="center">'.$bt.'</td><td class="center">'.$dt.'%</td><td class="center">'.$ndt.'</td><td class="center">'.$et.'%</td><td class="center">'.$bf.'</td><td class="center">'.$df.'%</td><td class="center">'.$ndf.'</td><td class="center">'.$bc.'%</td></tr>';
        $tableHtml .= '</tbody></table>';

        $html = '<h2>Summary '.$client.'</h2>
                <h2>UNITS</h2>
                    ' . $tableHtml . '
                </body>';
                
        return $html;
    }
}
?>