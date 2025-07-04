<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';

class reportes {

	private $lastYear = '2024';
	private $currentYear = '2025';
	private $obj;

	public function __construct() {
		$this->obj = new ControlDB();
	}

	function prints() {

		$sql = "SELECT
					YEAR(finish_print) AS years,
					MONTH(finish_print) AS months, 
    				SUM(CASE WHEN comments LIKE '%hits%' AND YEAR(finish_print) = '2024' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN YEAR(finish_print) = '2025' THEN 0 ELSE total_qty END ) AS lastYear,
    				SUM(CASE WHEN comments LIKE '%hits%' AND YEAR(finish_print) = '2025' THEN substring(comments, locate('hits',comments)-2,1) * total_qty WHEN YEAR(finish_print) = '2024' THEN 0 ELSE total_qty END ) AS thisYear
    			FROM t_wip
    			WHERE comments NOT LIKE '%only%' 
    			AND ppk_bulk NOT LIKE '%tops%'
    			AND order_status != 'CANCELED'
    			AND wip LIKE '%F1%'
    			AND YEAR(finish_print) BETWEEN '2024' AND '2025'
				GROUP BY years, months";

		$F1= $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$F1qty = array_fill(1, 12, 0);
		$F1qty2 = array_fill(1, 6, 0);

		foreach ($F1 AS $row) {
			if ($row['years'] == $this->lastYear) {
				$F1qty[$row['months']] = $row['lastYear'];
			} else if ($row['years'] == $this->currentYear) {
				$F1qty2[$row['months']] = $row['thisYear'];
			}
		}

		$F1qty = array_values($F1qty);
		$F1qty2 = array_values($F1qty2);

		$datos = ['meses' => $mes, 'F1qty' => $F1qty, 'F1qty2' => $F1qty2,];

		return json_encode($datos);
	}

	function labels() {

		$data = parse_ini_file("../../../env/.env");
        $BD = $data['prefixC'];

		$sql = "SELECT
					YEAR(date_in) AS years,
    				MONTH(date_in) AS months,
    				SUM(CASE WHEN machine LIKE '%MP1%' THEN total_qty_p ELSE 0 END) AS F1,
    				SUM(CASE WHEN machine LIKE '%MP3%' OR machine LIKE '%MP8%' THEN total_qty_p ELSE 0 END) AS F3,
    				SUM(CASE WHEN machine LIKE '%MP7%' THEN total_qty_p ELSE 0 END) AS F7
    			FROM {$BD}production.label
    			WHERE YEAR(date_in) BETWEEN '2024' AND '2025'
    			AND estado != 'DELETE'
    			GROUP BY years, months";

		$label = $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$F1qty = array_fill(1, 12, 0);
		$F3qty = array_fill(1, 12, 0);
		$F7qty = array_fill(1, 12, 0);
		$F1qty2 = array_fill(1, 6, 0);
		$F3qty2 = array_fill(1, 6, 0);
		$F7qty2 = array_fill(1, 6, 0);

		foreach ($label as $row) {
			if ($row['years'] == $this->lastYear) {
				$F1qty[$row['months']] = $row['F1'];
				$F3qty[$row['months']] = $row['F3'];
				$F7qty[$row['months']] = $row['F7'];
			} else if ($row['years'] == $this->currentYear) {
				$F1qty2[$row['months']] = $row['F1'];
				$F3qty2[$row['months']] = $row['F3'];
				$F7qty2[$row['months']] = $row['F7'];
			}
		}

		$F1qty = array_values($F1qty);
		$F3qty = array_values($F3qty);
		$F7qty = array_values($F7qty);
		$F1qty2 = array_values($F1qty2);
		$F3qty2 = array_values($F3qty2);
		$F7qty2 = array_values($F7qty2);

		$datos = ['meses' => $mes, 'F1qty' => $F1qty, 'F3qty' => $F3qty, 'F7qty' => $F7qty, 'F1qty2' => $F1qty2, 'F3qty2' => $F3qty2, 'F7qty2' => $F7qty2];

		return json_encode($datos);
	}

	function treatment() {

		$sql = "SELECT
					YEAR(actual_ship_date) AS years,
					MONTH(actual_ship_date) AS months,
					SUM(total_qty) AS total
				FROM t_wip
				WHERE treatment !='NA'
				AND order_status = 'SHIPPED'
				AND YEAR(actual_ship_date) BETWEEN '2024' AND '2025'
				GROUP BY years , months";
		
		$treatment = $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$qty = array_fill(1, 12, 0);
		$qty2 = array_fill(1, 6, 0);

		foreach ($treatment AS $row) {
			if ($row['years'] == $this->lastYear) {
				$qty[$row['months']] = $row['total'];
			} else if ($row['years'] == $this->currentYear) {
				$qty2[$row['months']] = $row['total'];
			}
		}

		$qty = array_values($qty);
		$qty2 = array_values($qty2);

		$datos = ['meses' => $mes, 'qty' => $qty, 'qty2' => $qty2];

		return json_encode($datos);
	}

	function embroidery() {

		$sql = "SELECT
					YEAR(actual_ship_date) AS years,
					MONTH(actual_ship_date) AS months,
					SUM(total_qty) AS total
				FROM t_wip
				WHERE technique LIKE '%embroidery%'
				AND order_status = 'SHIPPED'
				AND YEAR(actual_ship_date) BETWEEN '2024' AND '2025'
				GROUP BY years, months";

		$embroidery = $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$qty = array_fill(1, 12, 0);
		$qty2 = array_fill(1, 6, 0);

		foreach ($embroidery AS $row) {
			if ($row['years'] == $this->lastYear) {
				$qty[$row['months']] = $row['total'];
			} else if ($row['years'] == $this->currentYear) {
				$qty2[$row['months']] = $row['total'];
			}
		}

		$qty = array_values($qty);
		$qty2 = array_values($qty2);

		$datos = ['meses' => $mes, 'qty' => $qty, 'qty2' => $qty2];

		return json_encode($datos);
	}

	function shipped() {

		$sql = "SELECT
					YEAR(actual_ship_date) AS years,
					MONTH(actual_ship_date) AS months,
					SUM(total_qty) AS total
				FROM t_wip
				WHERE order_status = 'SHIPPED'
				AND YEAR(actual_ship_date) BETWEEN '2024' AND '2025'
				GROUP BY years, months";

		$shipped = $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$qty = array_fill(1, 12, 0);
		$qty2 = array_fill(1, 6, 0);

		foreach ($shipped AS $row) {
			if ($row['years'] == $this->lastYear) {
				$qty[$row['months']] = $row['total'];
			} else if ($row['years'] == $this->currentYear) {
				$qty2[$row['months']] = $row['total'];
			}
		}

		$qty = array_values($qty);
		$qty2 = array_values($qty2);		

		$datos = ['meses' => $mes, 'qty' => $qty, 'qty2' => $qty2];

		return json_encode($datos);
	}

	function received() {

		$sql = "SELECT
					YEAR(date_received) AS years,
					MONTH(date_received) AS months,
					SUM(total_qty) AS total
				FROM t_wip
				WHERE order_status NOT IN ('CANCELED', 'NO CT') 
				AND YEAR(date_received) BETWEEN '2024' AND '2025'
				GROUP BY years, months";

		$orders = $this->obj->consultar($sql);

		$mes = ['january', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$qty = array_fill(1, 12, 0);
		$qty2 = array_fill(1, 6, 0);

		foreach ($orders AS $row) {
			if ($row['years'] == $this->lastYear) {
				$qty[$row['months']] = $row['total'];
			} else if ($row['years'] == $this->currentYear) {
				$qty2[$row['months']] = $row['total'];
			}
		}

		$qty = array_values($qty);
		$qty2 = array_values($qty2);

		$datos = ['meses' => $mes, 'qty' => $qty, 'qty2' => $qty2];

		return json_encode($datos);
	}
}
?>