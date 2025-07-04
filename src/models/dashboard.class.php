<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';
require 'clock.php';

class dashData {

    public $cuerpo;
	public $Tamount;
	public $orders;
	public $blanks;
	public $hits;
	private $obj;
	private $fecha;

	public function __construct() {
		$this->obj = new ControlDB();
		$this->fecha = Clock::today();
	}

    function cardsData() {

		$objs = new controlDB("prod","");

		$sql = "SELECT SUM(amount_number) AS Tamount, COUNT(po_lot) AS orders, SUM(total_qty) AS blanks, sum(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS extrahits FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE date_received = :fecha AND order_status NOT IN ('CANCELED', 'NO CT')";
		$cards = $objs->consultar($sql, [':fecha'=>$this->fecha]);

  		$this->Tamount = '$'.number_format($cards['0']['Tamount'],2);
  		$this->orders = $cards['0']['orders'];
  		$this->blanks = number_format($cards['0']['blanks']);
  		$this->hits = number_format($cards['0']['extrahits']);
	}

    function updateData() {

		$sql = "SELECT SUM(amount_number) AS Tamount, COUNT(po_lot) AS orders, SUM(total_qty) AS blanks, sum(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS extrahits FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE date_received = :fecha AND order_status NOT IN ('CANCELED', 'NO CT')";
		$cards = $this->obj->consultar($sql, [':fecha'=>$this->fecha]);

		$Tamount = '$0.00';
		$orders = $blanks = $hits = 0;

		if (!empty($cards[0])) {
			$row = $cards[0];
			$Tamount = '$'. number_format($row['Tamount'],2);
			$orders = $row['orders'];
			$blanks = number_format($row['blanks']);
			$hits = number_format($row['extrahits']);
		}

		$mes = $this->obj->consultar("SELECT MONTH(date_received) AS months, SUM(amount_number) AS total_amount FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE YEAR(date_received) = '2025' AND order_status NOT IN ('CANCELED', 'NO CT') GROUP BY months");
		$week = $this->obj->consultar("SELECT WEEK(date_received, 1) AS semana, SUM(amount_number) AS total_amount FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE YEAR(date_received) = '2025' AND order_status NOT IN ('CANCELED', 'NO CT') GROUP BY semana");
		$quarter = $this->obj->consultar("SELECT QUARTER(date_received) AS trimestre, SUM(amount_number) AS total_amount FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE YEAR(date_received) = '2025' AND order_status NOT IN ('CANCELED', 'NO CT') GROUP BY trimestre");

		$totalAmount = array_column($mes, 'total_amount');
  		$totalesWeek = array_column($week, 'total_amount');
  		$semanas = array_column($week, 'semana');
  		$totalesQtr = array_column($quarter, 'total_amount');

		$graph = ['totalesMes' => $totalAmount, 'totalesWeek' => $totalesWeek, 'totalesQtr' => $totalesQtr, 'semana' => $semanas, 'tamount' => $Tamount, 'orders' => $orders, 'blanks' => $blanks, 'hits' => $hits];

		return json_encode($graph);
	}

    function received() {

        $query = "SELECT client, SUM(amount_number) AS amount_total, SUM(total_qty) AS blanks, COUNT(DISTINCT po_lot) AS POs, COUNT(no_orden) AS items, sum(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END ) AS total_hits FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c WHERE date_received = :fecha AND order_status NOT IN ('CANCELED', 'NO CT') GROUP BY client";

        $received = $this->obj->consultar($query, [':fecha'=>$this->fecha]);

        $data = array();
        foreach ($received AS $orders) {
			$client = $orders['client'];
            $dolars = '$'.number_format($orders['amount_total'],2);
            $pos = $orders['POs'];
            $items = $orders['items'];
            $blanks = number_format($orders['blanks']);
            $hits = number_format($orders['total_hits']);

            $data[] = array($client,
                            $dolars,
                            $pos,
                            $items,
                            $blanks,
                            $hits);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
	}
}
?>