<?php
include '../../config/controlDB_PDO.php';
include 'clock.php';

class dashData {

    private $data;
    private $BD;
    protected $allowedTable = ['front', 'back', 'sleeve', 'extra_hit_4', 'extra_hit_5', 'extra_hit_6', 'extra_hit_7', 'extra_hit_8', 'extra_hit_9'];
    private $currentDate;
    private $obj;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
        $this->currentDate = Clock::today();
        $this->obj = new ControlDB();
    }

    function allPrintF1() {

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

            if (isset($_GET['radios']) && $_GET['radios'] === "all") {

                if($opc === "1") {
                    $date = $this->currentDate;
                    $time = "AND date_in = :date";
                    $params = [':date'=>$date];
                } else {
                    $date = date('Y-m-d', Clock::strtotime('-1 day'));
                    $time = "AND date_in = :date";
                    $params = [':date'=>$date];
                }
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
                    FROM
                        (
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.front
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.back
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.sleeve
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_4
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_5
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_6
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_7
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_8
                            UNION ALL
                            SELECT date_in, campo, machine, shift, total_qty_p, estado FROM {$this->BD}production.extra_hit_9
                        ) AS combined_tables
                    WHERE machine LIKE '%MP1%'
                    AND estado != 'DELETE'
                    $time
                    $shift
                    GROUP BY machine";
    
        $mf1 = $this->obj->consultar($query, $params);

        if(empty($mf1)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($mf1 AS $quality) {
                $press = $quality['machine'];
                $firstQuality = number_format($quality['total_quality']);
                $total_mill = number_format($quality['total_mill']);
                $total_damage = number_format($quality['total_damage']);
                $totals = number_format($quality['totals']);
            
                $data[] = array($press,
                                $firstQuality,
                                $total_mill,
                                $total_damage,
                                $totals);
            }

            $new_array = array("data"=>$data);

            return json_encode($new_array);
        }
    }

    function allTotals() {

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

            if (isset($_GET['radios']) && $_GET['radios'] === "all") {

                if($opc === "1") {
                    $date = $this->currentDate;
                    $time = "AND date_in = :date";
                    $params = [':date'=>$date];
                } else {
                    $date = date('Y-m-d', Clock::strtotime('-1 day'));
                    $time = "AND date_in = :date";
                    $params = [':date'=>$date];
                }
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

        $query ="SELECT
                    'TOTAL' AS machine,
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
                WHERE estado != 'DELETE'
                $time
                $shift";
    
        $mf1_7 = $this->obj->consultar($query, $params);

        foreach ($mf1_7 AS $gtotal) {
            $firstQuality = number_format($gtotal['total_quality']);
            $total_mill = number_format($gtotal['total_mill']);
            $total_damage = number_format($gtotal['total_damage']);
            $totals = number_format($gtotal['grand_total']);

            $data[] = array($firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);

        }
        
        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }

    function printF1() {

        $data = array();
        $table = $_GET['radios'];
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if (!in_array($table, $this->allowedTable)) {
            return json_encode(["data"=>[]]);
        }

        if ($opc >= "3") {

            if ($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if ($opc === "4") {
                $date1 = date('Y-m-d', Clock::strtotime('monday last week'));
                $date2 = date('Y-m-d', Clock::strtotime('sunday last week'));
            } else if($opc === "5") {
                $date1 = date('Y-m-01');
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

            if ($opc === "1") {
                $date = $this->currentDate;
            } else {
                $date = date('Y-m-d', Clock::strtotime('-1 day'));    
            }

            $time = "AND date_in = :date";
            $params = [':date'=>$date];
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
                    FROM
                        {$this->BD}production.$table
                    WHERE machine LIKE '%MP1%'
                    AND estado != 'DELETE'
                    $time
                    $shift
                    GROUP BY machine";
    
        $f1 = $this->obj->consultar($query, $params);

        if (empty($f1)) {
            return json_encode(["data"=>[]]);
        } else {
            foreach ($f1 AS $printF1) {
                $press = $printF1['machine'];
                $total_quality = number_format($printF1['total_quality']);
                $total_mill = number_format($printF1['total_mill']);
                $total_damage = number_format($printF1['total_damage']);
                $totals = number_format($printF1['totals']);

                $data[] = [$press,
                            $total_quality,
                            $total_mill,
                            $total_damage,
                            $totals];
            }
        
            $new_array = array("data"=>$data);

            return json_encode($new_array);
        }
    }

    function totalPrint() {
        
        $data = array();
        $table = $_GET['radios'];
        $opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
        $params = [];

        if (!in_array($table, $this->allowedTable)) {
            return json_encode(["data"=>[]]);
        }

        if ($opc >= "3") {

            if ($opc === "3") {
                $date1 = date('Y-m-d', Clock::strtotime('monday this week'));
                $date2 = $this->currentDate;
            } else if ($opc === "4") {
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

            if ($opc === "1") {
                $date = $this->currentDate;
            } else {
                $date = date('Y-m-d', Clock::strtotime('-1 day'));
            }

            $time = "AND date_in = :date";
            $params = [':date'=>$date];    
        }

        if (empty($_GET['shift']) || $_GET['shift'] === 'undefined') {
            $shift = '';
        } else {
            $shift = "AND shift = :shift";
            $params[':shift'] = $_GET['shift'];
        }

        $query ="SELECT
                    'TOTAL' AS machine,
                    SUM(CASE WHEN campo LIKE '%quality%' THEN total_qty_p ELSE 0 END) AS total_quality,
                    SUM(CASE WHEN campo LIKE '%mill%' THEN total_qty_p ELSE 0 END) AS total_mill,
                    SUM(CASE WHEN campo LIKE '%production%' THEN total_qty_p ELSE 0 END) AS total_damage,
                    SUM(total_qty_p) AS grand_total
                FROM
                    {$this->BD}production.$table
                WHERE estado != 'DELETE'
                $time
                $shift";
    
        $mf1_7 = $this->obj->consultar($query, $params);

        foreach ($mf1_7 AS $gtotal) {
            $firstQuality = number_format($gtotal['total_quality']);
            $total_mill = number_format($gtotal['total_mill']);
            $total_damage = number_format($gtotal['total_damage']);
            $totals = number_format($gtotal['grand_total']);

            $data[] = array($firstQuality,
                            $total_mill,
                            $total_damage,
                            $totals);

        }
        
        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }
}
?>