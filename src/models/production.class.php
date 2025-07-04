<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';

class production {

	private $data;
    private $BD;
	private $obj;
	private $lastYear = '2024';
	private $currentYear = '2025';

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
		$this->obj = new ControlDB();
    }

	function charts() {
	
		function obtenerDatos($query, $obj) {
			$res = $obj->consultar($query);
			$datos = [];

			foreach ($res AS $row) {
				$datos[] = $row['total'] ?? $row['totalHits'] ??  $row['extrahits'];
			}

			return $datos;
		}
	
		$queries = [
			'print' => "SELECT 
							MONTH(finish_print) AS months, 
							SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END) AS totalHits 
						FROM t_wip 
						WHERE comments NOT LIKE '%only%'
						AND ppk_bulk NOT LIKE '%tops%'
						AND order_status != 'CANCELED' 
						AND (wip LIKE '%F1%' OR wip LIKE '%F7%') 
						AND YEAR(finish_print) = :years 
						GROUP BY months",
						
			'treat' => "SELECT
							MONTH(actual_ship_date) AS months,
							SUM(total_qty) AS total 
						FROM t_wip 
						WHERE treatment_finish_date != 'NA'
						AND order_status = 'SHIPPED' 
						AND YEAR(actual_ship_date) = :years
						GROUP BY months",
						
			'embro' => "SELECT
							MONTH(actual_ship_date) AS months,
							SUM(total_qty) AS total 
					  	FROM t_wip 
					  	WHERE technique LIKE '%embroidery%'
						AND order_status = 'SHIPPED' 
					  	AND YEAR(actual_ship_date) = :years
					  	GROUP BY months",
					  
			'label' => "SELECT
							MONTH(date_in) AS months,
							SUM(total_qty_p) AS total 
						FROM {$this->BD}production.label 
						WHERE YEAR(date_in) = :years
						AND estado != 'DELETE' 
						GROUP BY months",
						
			'hits' => "SELECT
							MONTH(date_received) AS months, 
					   		SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END) AS extrahits 
					   	FROM t_wip 
					   	WHERE order_status != 'CANCELED'
						AND YEAR(date_received) = :years
					   	GROUP BY months",
					   
			'blank' => "SELECT
							MONTH(date_received) AS months,
							SUM(total_qty) AS total 
						FROM t_wip 
						WHERE order_status != 'CANCELED' 
						AND YEAR(date_received) = :years 
						GROUP BY months"
		];
	
		$datos = ['lastYear'=>$this->lastYear, 'currentYear'=>$this->currentYear];
		foreach ($queries AS $key => $sql) {
			$datos["{$key}1"] = obtenerDatos(str_replace(':years', $this->lastYear, $sql), $this->obj);
			$datos["{$key}2"] = obtenerDatos(str_replace(':years', $this->currentYear, $sql), $this->obj);
		}
	
		return json_encode($datos);
	}
	
	function prints() {

		$opc = is_numeric($_GET['factory']) ? $_GET['factory'] : 0;

		switch ($opc) {
			case 1:
				$condition = '';
			break;

			case 2:
				$condition = "AND wip LIKE '%F1%'";
			break;

			case 3:
				$condition = "AND wip LIKE '%F7%' AND wip NOT LIKE '%F1%'" ;
			break;

			default: return 0;
		}

		$sql = "SELECT
					YEAR(finish_print) AS years,
					MONTH(finish_print) AS months,
					SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS totalHits
				FROM t_wip
				WHERE comments NOT LIKE '%only%'
				AND ppk_bulk NOT LIKE '%tops%'
				AND order_status != 'CANCELED'
				AND YEAR(finish_print) BETWEEN '2024' AND '2025'
				$condition
				GROUP BY years, months";

		$print1 = array_fill(1, 12, 0);
		$print2 = array_fill(1, 6, 0);

		$res = $this->obj->consultar($sql);

		foreach ($res AS $row) {
			if ($row['years'] == $this->lastYear) {
				$print1[$row['months']] = $row['totalHits'];
			} else if ($row['years'] == $this->currentYear) {
				$print2[$row['months']] = $row['totalHits'];
			}
		}

		$print1 = array_values($print1);
		$print2 = array_values($print2);

		$datos = ['lastYear'=>$this->lastYear, 'currentYear'=>$this->currentYear, 'print1'=>$print1, 'print2'=>$print2];

		return json_encode($datos);
	}

	function labels() {

		$wip = $_GET['factory2'];
		$params = [];

		if ($wip == 'F8'){
			$condition = "AND (machine LIKE '%MP3%' OR machine LIKE '%MP8%')";
		} else if ($wip != 'ALL') {
			$condition = "AND machine LIKE :wip";
			$params = [':wip'=>"%$wip%"];
		} else {
			$condition = '';
		}

		$sql = "SELECT
					YEAR(date_in) AS years,
					MONTH(date_in) AS months,
					SUM(total_qty_p) AS total
				FROM {$this->BD}production.label
				WHERE YEAR(date_in) BETWEEN '2024' AND '2025'
				AND estado != 'DELETE'
				$condition
				GROUP BY years, months";

		$label1 = array_fill(1, 12, 0);
		$label2 = array_fill(1, 6, 0);

		$res = $this->obj->consultar($sql, $params);

		foreach ($res AS $row) {
			if ($row['years'] == $this->lastYear) {
				$label1[$row['months']] = $row['total'];
			} else if ($row['years'] == $this->currentYear) {
				$label2[$row['months']] = $row['total'];
			}
		}

		$label1 = array_values($label1);
		$label2 = array_values($label2);

		$datos = ['lastYear'=>$this->lastYear, 'currentYear'=>$this->currentYear, 'label1'=>$label1, 'label2'=>$label2];

		return json_encode($datos);
	}
}
?>