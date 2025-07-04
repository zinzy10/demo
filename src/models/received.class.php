<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';
require_once 'clock.php';

class reportes {

	private $obj;

	public function __construct() {
		$this->obj = new ControlDB();
	}

    function allOrders() {

		$currentDate = Clock::today();
        $data = array();
		$opc = empty($_GET['opcion']) || $_GET['opcion'] === 'undefined' ? '0' : $_GET['opcion'];
		$params = [];
        $adicionConsulta = '';

		$arrayIds = isset($_GET['ids']) && $_GET['ids'] != null ? json_decode($_GET['ids']) : [];
		if (!empty($arrayIds) && count($arrayIds) > 0) {
			$placeholders = [];
  			foreach($arrayIds AS $index => $status){
				$paramName = ':status_' . $index;
				$placeholders[] = $paramName;
				$params[$paramName] = $status;
  			}
			$adicionConsulta = "AND order_status IN (" . implode(',', $placeholders). ")";
		}
        
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

			$time = "(date_received BETWEEN :date1 AND :date2)";
			$params[':date1'] = $date1;
			$params[':date2'] = $date2;

        } else {

            if($opc === "1") {
                $date = $currentDate;
				$time = "date_received = :date";
				$params[':date'] = $date;
            } else if($opc === "2") {
                $date = date('Y-m-d', Clock::strtotime('-1 day'));
				$time = "date_received = :date";
				$params[':date'] = $date;
            } else {
                $time = "YEAR(date_received) = '2025'";
            }
        }
		
		$queryT = "SELECT
        			SUM(amount_number) AS total_amount,
        			SUM(total_qty) AS total_blanks,
        			COUNT(no_orden) AS total_item,
        			SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) AS total_hit
    			FROM t_wip INNER JOIN complementos_wip ON id_wip = id_wip_c
    			WHERE $time $adicionConsulta";

		$total = $this->obj->consultar($queryT, $params);

		$total_amount = $total[0]['total_amount'] ?: 1;
    	$total_blanks = $total[0]['total_blanks'] ?: 1;
    	$total_items = $total[0]['total_item'] ?: 1;
    	$total_hits = $total[0]['total_hit'] ?: 1;

		$query = "SELECT
            		IFNULL(client, 'TOTAL') AS client,
            		SUM(amount_number) AS amount,
            		(SUM(amount_number) / :amount) * 100 AS por_amount,
            		SUM(total_qty) AS blanks,
            		(SUM(total_qty) / :blanks) * 100 AS por_blanks,
            		SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) AS hits,
            		(SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) / :hits) * 100 AS por_hits,
            		COUNT(no_orden) AS items,
            		(COUNT(no_orden) / :items) * 100 AS por_items
        		FROM t_wip INNER JOIN complementos_wip ON id_wip = id_wip_c
        		WHERE $time $adicionConsulta
        		GROUP BY client WITH ROLLUP";
		
		$params[':amount'] = $total_amount;
		$params[':blanks'] = $total_blanks;
		$params[':hits'] = $total_hits;
		$params[':items'] = $total_items;

        $received = $this->obj->consultar($query, $params);

		if (empty($received)) {
			return json_encode(['data' => []]);
		}

		foreach($received AS $wip) {
			$client = $wip['client'];
			$Tamount = '$'.number_format($wip['amount'],2);
			$Pamount = number_format($wip['por_amount'],2).'%';
			$blanks = number_format($wip['blanks']);
			$Pblanks = number_format($wip['por_blanks'],2).'%';
			$hits = number_format($wip['hits']);
			$Phits = number_format($wip['por_hits'],2).'%';
			$items = number_format($wip['items']);
			$Pitems = number_format($wip['por_items'],2).'%';

			$data[] = [$client,
						$Tamount,
						$Pamount,
						$blanks,
						$Pblanks,
						$hits,
						$Phits,
						$items,
						$Pitems];
		}

		$new_array = array("data"=>$data);

		return json_encode($new_array);
    }

	function received() {

        $data = array();
		$params = [];

		$queryT = "SELECT
        			SUM(amount_number) AS total_amount,
        			SUM(total_qty) AS total_blanks,
        			COUNT(no_orden) AS total_item,
        			SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) AS total_hit
    			FROM t_wip INNER JOIN complementos_wip ON id_wip = id_wip_c
    			WHERE YEAR(date_received) = '2025'
				AND order_status NOT IN ('CANCELED', 'NO CT')";

		$total = $this->obj->consultar($queryT);

		$total_amount = $total[0]['total_amount'] ?: 1;
    	$total_blanks = $total[0]['total_blanks'] ?: 1;
    	$total_items = $total[0]['total_item'] ?: 1;
    	$total_hits = $total[0]['total_hit'] ?: 1;

		$query = "SELECT
            		IFNULL(client, 'TOTAL') AS client,
            		SUM(amount_number) AS amount,
            		(SUM(amount_number) / :amount) * 100 AS por_amount,
            		SUM(total_qty) AS blanks,
            		(SUM(total_qty) / :blanks) * 100 AS por_blanks,
            		SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) AS hits,
            		(SUM(CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END) / :hits) * 100 AS por_hits,
            		COUNT(no_orden) AS items,
            		(COUNT(no_orden) / :items) * 100 AS por_items
        		FROM t_wip INNER JOIN complementos_wip ON id_wip = id_wip_c
        		WHERE YEAR(date_received) = '2025'
				AND order_status NOT IN ('CANCELED', 'NO CT')
        		GROUP BY client WITH ROLLUP";
		
		$params[':amount'] = $total_amount;
		$params[':blanks'] = $total_blanks;
		$params[':hits'] = $total_hits;
		$params[':items'] = $total_items;

		$received = $this->obj->consultar($query, $params);

		if (empty($received)) {
			return 0;
		}

		foreach($received AS $wip) {
			$client = $wip['client'];
			$Tamount = '$'.number_format($wip['amount'],2);
			$Pamount = number_format($wip['por_amount'],2).'%';
			$blanks = number_format($wip['blanks']);
			$Pblanks = number_format($wip['por_blanks'],2).'%';
			$hits = number_format($wip['hits']);
			$Phits = number_format($wip['por_hits'],2).'%';
			$items = number_format($wip['items']);
			$Pitems = number_format($wip['por_items'],2).'%';

			$data[] = [$client,
						$Tamount,
						$Pamount,
						$blanks,
						$Pblanks,
						$hits,
						$Phits,
						$items,
						$Pitems];
		}

		$new_array = array("data"=>$data);

		return json_encode($new_array);
	}

	function clients() {

		$sql = "SELECT client FROM t_wip GROUP BY client";

		$selectClient = $this->obj->consultar($sql);

		$clients = "";

		foreach ($selectClient AS $opc) {
			$clients .="<option value='".$opc["client"]."'>".$opc["client"]."</option>";
		}

		echo $clients;
	}

	function options() {

		$option = $_POST['option'];

		switch ($option) {

			case '1': 
				$sql = "SELECT TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS clients FROM t_wip WHERE client NOT IN ('NA', 'PEND', 'PENDING') GROUP BY clients";

				$client = $this->obj->consultar($sql);

				$listClient = "";

				foreach ($client AS $opc1) {
					$listClient .="<option value='".$opc1["clients"]."'>".$opc1["clients"]."</option>";
				}

				echo $listClient;
			break;

			case '2':
				$sql = "SELECT store FROM t_wip GROUP BY store";

				$store = $this->obj->consultar($sql);

				$listStore = "";

				foreach ($store AS $opc2) {
					$listStore .="<option value='".$opc2["store"]."'>".$opc2["store"]."</option>";
				}

				echo $listStore;
			break;
		}
	}

	function tableEdit() {

		$opc = $_POST['opc'];
		$client = $_POST['client'];
		$dateOption = $_POST['dateOpc'];
		$currentDate = Clock::today();
		$params = [];

		switch ($dateOption) {
			case '1':
				$d1 = date('Y-m-01', Clock::strtotime($currentDate));
				$d2 = $currentDate;
			break;
		
			case '2':
				$d1 = date('Y-m-01', Clock::strtotime('-1 month'));
				$d2 = date('Y-m-t', Clock::strtotime('-1 month'));
			break;
		
			case '3':
				$d1 = '2025-01-01';
				$d2 = $currentDate;
			break;
		
			case '4':
				$d1 = date('Y-01-01', Clock::strtotime('-1 year'));
				$d2 = date('Y-12-31', Clock::strtotime('-1 year'));
			break;
		
			case '5':
				$d1 = $_POST['d1'];
				$d2 = $_POST['d2'];
			break;
		}

		if ($opc == '1') {
			$sql = "SELECT store AS stores FROM t_wip WHERE order_status != 'canceled' AND date_received BETWEEN :d1 AND :d2 AND client = :client GROUP BY stores";
			$params[':client'] = $client;
		} else {
			$sql = "SELECT TRIM(CASE WHEN INSTR(client, '(') > 0 THEN SUBSTRING(client, 1, INSTR(client, '(')-1) ELSE client END) AS stores FROM t_wip WHERE client NOT IN('NA', 'PEND', 'PENDING') AND order_status != 'canceled' AND store = :client AND date_received BETWEEN :d1 AND :d2 GROUP BY stores";
			$params[':client'] = $client;
		}

		$params[':d1'] = $d1;
		$params[':d2'] = $d2;

		$list = $this->obj->consultar($sql, $params);

		$listStore = '';

		foreach ($list AS $store) {
			$listStore .= '<div class="form-check">';
			$listStore .= '<input class="form-check-input clientsBox" type="checkbox" id="'.$store['stores'].'" value="'.$store['stores'].'">';
			$listStore .= '<label class="form-check-label" for="'.$store['stores'].'">'.$store['stores'].'</label>';
			$listStore .= '</div>';
		}

		echo $listStore;
	}
}
?>