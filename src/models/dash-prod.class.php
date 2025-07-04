<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';
require 'clock.php';

class dashData {

    private $data;
    private $BD;
    private $obj;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
        $this->obj = new ControlDB();
    }

    function Charts() {

        $sqlPrint = "SELECT
                MONTH(date_in) AS months,
                SUM(total_qty_p) AS total
            FROM
                (
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.front
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.back
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.sleeve
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_4
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_5
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_6
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_7
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_8
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_9
                ) AS combined_tables
            WHERE
                (machine LIKE '%mp7%' OR machine LIKE '%mp1%')
                AND date_in = YEAR(CURRENT_DATE())
                AND estado != 'DELETE' GROUP BY months";
        
        $sqlTreatment = "SELECT MONTH(date_in) AS months, SUM(total_qty_p) AS total FROM {$this->BD}production.treatment WHERE estado != 'DELETE' AND YEAR(date_in) = '2025' GROUP BY months";

        $sqlLabel = "SELECT MONTH(date_in) AS months, SUM(total_qty_p) AS total FROM {$this->BD}production.label WHERE estado != 'DELETE' AND YEAR(date_in) = '2025' GROUP BY months";

        $print = $this->obj->consultar($sqlPrint);
        $treatment = $this->obj->consultar($sqlTreatment);
        $label = $this->obj->consultar($sqlLabel);

        $totalPrint = array();
        foreach ($print AS $printing) {
            $totalPrint[] = $printing['total'];
        }

        $totalTreat = array();
        foreach ($treatment AS $treat) {
            $totalTreat[] = $treat['total'];
        }

        $totalLabel = array();
        foreach ($label AS $padPrint) {
            $totalLabel[] = $padPrint['total'];
        }

        $graph = ['totalPrint'=>$totalPrint, 'totalTreat'=>$totalTreat,'totalLabel'=>$totalLabel];

        return json_encode($graph);
    }

    function table() {

        $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
        $date2 = Clock::today();

        $data = array();

        $sqlPrint = "SELECT
                'Printing' AS machine,
                SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                SUM(total_qty_p) AS grand_total
            FROM
                (
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.front
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.back
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.sleeve
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_4
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_5
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_6
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_7
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_8
                    UNION ALL
                    SELECT date_in, campo, machine, shift, total_qty_p, estado
                    FROM {$this->BD}production.extra_hit_9
                ) AS combined_tables
            WHERE date_in BETWEEN '$date1' AND '$date2'
            AND estado != 'DELETE'";

        $print = $this->obj->consultar($sqlPrint);

        foreach ($print AS $printing) {
            $process = $printing['machine'];
            $firstQuality = number_format($printing['total_quality']);
            $total_mill = number_format($printing['total_mill']);
            $total_damage = number_format($printing['total_damage']);
            $totals = number_format($printing['grand_total']);

            $data[] = array($process,
                            $firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);
        }

        $sqlLabel = "SELECT
            'Pad Print' AS machine,
            SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
            SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
            SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
            SUM(total_qty_p) AS grand_total
        FROM
           {$this->BD}production.label
        WHERE date_in BETWEEN '$date1' AND '$date2'
        AND estado != 'DELETE'";

        $label = $this->obj->consultar($sqlLabel);

        foreach ($label AS $padPrint) {
            $process = $padPrint['machine'];
            $firstQuality = number_format($padPrint['total_quality']);
            $total_mill = number_format($padPrint['total_mill']);
            $total_damage = number_format($padPrint['total_damage']);
            $totals = number_format($padPrint['grand_total']);

            $data[] = array($process,
                            $firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);
        }

        $sqlEmb = "SELECT
                        'Embroidery' AS machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS total
                    FROM {$this->BD}production.embroidery
                    WHERE estado != 'DELETE'
                    AND date_in BETWEEN '$date1' AND '$date2'";

        $embroidery = $this->obj->consultar($sqlEmb);

        foreach ($embroidery AS $emb) {
            $process = $emb['machine'];
            $firstQuality = number_format($emb['total_quality']);
            $total_mill = number_format($emb['total_mill']);
            $total_damage = number_format($emb['total_damage']);
            $totals = number_format($emb['total']);

            $data[] = array($process,
                            $firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);
        }

        $sqlTreat = "SELECT
                        'Treatment' AS machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS total
                    FROM {$this->BD}production.treatment
                    WHERE estado != 'DELETE'
                    AND date_in BETWEEN '$date1' AND '$date2'";

        $treat = $this->obj->consultar($sqlTreat);

        foreach ($treat AS $treatment) {
            $process = $treatment['machine'];
            $firstQuality = number_format($treatment['total_quality']);
            $total_mill = number_format($treatment['total_mill']);
            $total_damage = number_format($treatment['total_damage']);
            $totals = number_format($treatment['total']);

            $data[] = array($process,
                            $firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);
        }

        $sqlPacking = "SELECT
                    'Packing' AS machine,
                    SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                    SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                    SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                    SUM(total_qty_p) AS total
                FROM
                    {$this->BD}production.packing
                WHERE
                    date_in BETWEEN '$date1' AND '$date2'
                    AND estado != 'DELETE'";

        $pack = $this->obj->consultar($sqlPacking);

        foreach ($pack AS $packing) {
            $process = $packing['machine'];
            $firstQuality = number_format($packing['total_quality']);
            $total_mill = number_format($packing['total_mill']);
            $total_damage = number_format($packing['total_damage']);
            $totals = number_format($packing['total']);

            $data[] = array($process,
                            $firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }
}
?>