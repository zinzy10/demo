<?php
include '../../config/controlDB_PDO.php';
include 'clock.php';

class dashData {

    private $data;
    private $BD;
    private $obj;
    private $currentDate;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
        $this->obj = new ControlDB();
        $this->currentDate = Clock::today();
    }

    function padf1() {

        $data = array();
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if ($opc >= "3") {

            if($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = '2025-06-01';
				$date2 = $this->currentDate;
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
                $date = $this->currentDate;
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
                        IFNULL(machine, 'TOTAL') AS machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS totals
                    FROM
                        {$this->BD}production.label
                    WHERE machine LIKE '%MP1%'
                    $time
                    AND estado != 'DELETE'
                    $shift
                    GROUP BY machine WITH ROLLUP";

        $LF1 = $this->obj->consultar($query, $params);

        if (empty($LF1)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($LF1 AS $label) {
                $press = $label['machine'];
                $firstQuality = number_format($label['total_quality']);
                $totalMill = number_format($label['total_mill']);
                $totalDamage = number_format($label['total_damage']);
                $totals = number_format($label['totals']);

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

    function padf3() {

        $data = array();
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if ($opc >= "3") {

            if($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = '2025-06-01';
				$date2 = $this->currentDate;
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
                $date = $this->currentDate;
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
                        IFNULL(machine, 'TOTAL') AS machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS totals
                    FROM
                        {$this->BD}production.label
                    WHERE (machine LIKE '%MP3%' OR machine LIKE '%MP8%')
                    $time
                    AND estado != 'DELETE'
                    $shift
                    GROUP BY machine WITH ROLLUP";

        $LF3 = $this->obj->consultar($query, $params);

        if (empty($LF3)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($LF3 AS $label) {
                $press = $label['machine'];
                $firstQuality = number_format($label['total_quality']);
                $totalMill = number_format($label['total_mill']);
                $totalDamage = number_format($label['total_damage']);
                $totals = number_format($label['totals']);

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

    function padf7() {

        $data = array();
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if ($opc >= "3") {

            if($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = '2025-06-01';
				$date2 = $this->currentDate;
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
                $date = $this->currentDate;
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
                        IFNULL(machine, 'TOTAL') AS machine,
                        SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                        SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                        SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                        SUM(total_qty_p) AS totals
                    FROM
                        {$this->BD}production.label
                    WHERE machine LIKE '%MP7%'
                    $time
                    AND estado != 'DELETE'
                    $shift
                    GROUP BY machine WITH ROLLUP";

        $LF7 = $this->obj->consultar($query, $params);

        if (empty($LF7)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($LF7 AS $label) {
                $press = $label['machine'];
                $firstQuality = number_format($label['total_quality']);
                $totalMill = number_format($label['total_mill']);
                $totalDamage = number_format($label['total_damage']);
                $totals = number_format($label['totals']);

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

    function totalPad() {

        $data = array();
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if ($opc >= "3") {

            if($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = '2025-06-01';
				$date2 = $this->currentDate;
            } else if($opc === "6") {
				$date1 = date('Y-m-01', Clock::strtotime('first day of last month'));
				$date2 = date('Y-m-t', Clock::strtotime('last day of last month'));
			} else {
				$date1 = $_GET['date1'];
                $date2 = $_GET['date2'];
			}

            $time = "date_in BETWEEN :d1 AND :d2";
            $params = [':d1'=>$date1, ':d2'=>$date2];

        } else {

            if($opc === "1") {
                $date = $this->currentDate;
                $time = "date_in = :date";
                $params = [':date'=>$date];
            } else if($opc === "2") {
                $date = date('Y-m-d', Clock::strtotime('-1 day'));
                $time = "date_in = :date";
                $params = [':date'=>$date];
            } else {
                $time = "YEAR(date_in) = '2025'";
            }
        }

        if (empty($_GET['shift']) || $_GET['shift'] === 'undefined') {
            $shift = '';
        } else {
            $shift = "AND shift = :shift";
            $params[':shift'] = $_GET['shift'];
        }

        $query = "SELECT
                    SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                    SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                    SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                    SUM(total_qty_p) AS grand_total
                FROM
                    {$this->BD}production.label
                WHERE $time
                AND estado != 'DELETE'
                $shift";

        $PFT = $this->obj->consultar($query, $params);

        if (empty($PFT)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($PFT AS $label) {
                $firstQuality = number_format($label['total_quality']);
                $totalMill = number_format($label['total_mill']);
                $totalDamage = number_format($label['total_damage']);
                $totals = number_format($label['grand_total']);

                $data[] = array($firstQuality,
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