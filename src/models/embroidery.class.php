<?php
include '../../config/controlDB_PDO.php';
include 'clock.php';

class dashData {

    private $data;
    private $BD;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
    }

    function embF3() {

        $obj = new controlDB();
        $currentDate = Clock::today();
        $data = array();
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];
    
        if ($opc >= "3") {

            if($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $currentDate;
            } else if($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = '2025-06-01';
				$date2 = $currentDate;
            } else if($opc === "6") {
				$date1 = date('Y-m-01', Clock::strtotime('first day of last month'));
				$date2 = date('Y-m-t', Clock::strtotime('last day of last month'));
			} else {
				$date1 = $_GET['date1'];
                $date2 = $_GET['date2'];
			}

            $time = "AND  date_in BETWEEN :d1 AND :d2";
            $params = [':d1'=>$date1, ':d2'=>$date2];

        } else {

            if($opc === "1") {
                $date = $currentDate;
                $time = "AND date_in = :date";
                $params = [':date'=>$date];
            } else if($opc === "2") {
                $date = date('Y-m-d', Clock::strtotime('-1 day'));
                $time = "AND date_in = :date";
                $params = [':date'=>$date];
            } else {
                $time = "AND YEAR(date_in) = '2025'";
            }
        }

        if (empty($_GET['shift']) || $_GET['shift'] === 'undefined') {
            $shift = '';
        } else {
            $shift = "AND shift = :shift";
            $params[':shift'] = $_GET['shift'];
        }

        $query = "SELECT
                        machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS totals
                    FROM {$this->BD}production.embroidery
                    WHERE estado != 'DELETE'
                    $time
                    $shift
                    GROUP BY machine";

        $bf3 = $obj->consultar($query, $params);

        if (empty($bf3)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($bf3 AS $emb) {
                $press = $emb['machine'];
                $firstQuality = number_format($emb['total_quality']);
                $totalMill = number_format($emb['total_mill']);
                $totalDamage = number_format($emb['total_damage']);
                $totals = number_format($emb['totals']);

                $data[] = array($press,
                                $firstQuality,
                                $totalMill,
                                $totalDamage,
                                $totals);
            }

            $new_array = array("data"=>$data);

            return json_encode($new_array);
        }
    }
}
?>