<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';

class dashData {

	private $data;
	private $BD;
	private $obj;

	public function __construct() {
		$this->data = parse_ini_file("../../../env/.env");
		$this->BD = $this->data['prefixC'];
		$this->obj = new ControlDB();
	}

	function salesCharts() {

		$year = '2025';

		$chart = $this->obj->consultar("SELECT fecha, MONTH(fecha) AS months, SUM(sale) AS total FROM sales WHERE YEAR(fecha) = '2025' GROUP BY months");

		$totalYear = array();

		foreach ($chart as $defChart) {
			$totalYear[] = $defChart['total'];
		}

		$graph = ['totalYear' => $totalYear, 'year' => $year];

		return json_encode($graph);
	}

	function chartsByYear() {

		$years1 = is_numeric($_GET['years1']) ? (int)$_GET['years1'] : 0;
		$years2 = is_numeric($_GET['years2']) ? (int)$_GET['years2'] : 0;

		$sql1 = "SELECT
					YEAR(fecha) AS years,
					MONTH(fecha) AS months,
					SUM(sale) AS total
				FROM sales
				WHERE YEAR(fecha) IN (:years1, :years2)
				GROUP BY YEAR(fecha), MONTH(fecha)
				ORDER BY YEAR(fecha), MONTH(fecha)";
		
		$sales = $this->obj->consultar($sql1, [":years1"=>$years1, ":years2"=>$years2]);

		$totales1 = array_fill(1, 6, 0);
		$totales2 = array_fill(1, 6, 0);

		foreach ($sales AS $row) {
			if ($row['years'] == $years1) {
				$totales1[$row['months']] = $row['total'];
			} else if ($row['years'] == $years2) {
				$totales2[$row['months']] = $row['total'];
			}
		}

		$totales1 = array_values($totales1);
		$totales2 = array_values($totales2);

		$datos = ['totales1' => $totales1, 'totales2' => $totales2];

		return json_encode($datos);
	}

	function ClientChart() {
	
		$clients = $_GET['clients'];
		$years3 = is_numeric($_GET['years3']) ? (int)$_GET['years3'] : 0;
		$years4 = is_numeric($_GET['years4']) ? (int)$_GET['years4'] : 0;

		$sql = "SELECT 
					YEAR(fecha) AS years,
					MONTH(fecha) AS months,
					SUM(sale) AS total 
				FROM sales 
				INNER JOIN clients ON sales.id_clients = clients.id_client 
				WHERE client = :clients 
				AND YEAR(fecha) IN (:years3, :years4)
				GROUP BY YEAR(fecha), MONTH(fecha)
				ORDER BY YEAR(fecha), MONTH(fecha)";
	
		$params = [
			':clients' => $clients,
			':years3' => $years3,
			':years4' => $years4
		];
	
		$res = $this->obj->consultar($sql, $params);
	
		$totales1 = array_fill(1, 6, 0);
		$totales2 = array_fill(1, 6, 0);
	
		foreach ($res as $row) {
			if ($row['years'] == $years3) {
				$totales1[$row['months']] = $row['total'];
			} else if ($row['years'] == $years4) {
				$totales2[$row['months']] = $row['total'];
			}
		}
	
		$totales1 = array_values($totales1);
		$totales2 = array_values($totales2);
	
		$data = ['totales1' => $totales1, 'totales2' => $totales2];

		return json_encode($data);
	}

	function selectYears() {

		$obj2 = new ControlDB("prod","");
		
		$select = $obj2->consultar("SELECT fecha, YEAR(fecha) AS years, SUM(sale) AS total FROM sales GROUP BY years ORDER BY years DESC");

		$selectYears ="";

		foreach ($select AS $opc1) {
		$selectYears .="<option value='".$opc1["years"]."'>".$opc1["years"]."</option>";
		}

		$arrays['selectYears'] = $selectYears;

		return $arrays; 
	}

	function selectClient() {

		$obj2 = new ControlDB("prod","");

		$client = $obj2->consultar("SELECT client FROM clients GROUP BY client");

		$clients ="";

		foreach ($client AS $opc2) {
			$clients .="<option value='".$opc2["client"]."'>".$opc2["client"]."</option>";
		}

		$arrays['clients'] = $clients;

		return $arrays; 
	}

	function listYear() {

		$client = $_POST['Client'];

		$sql = "SELECT client, YEAR(fecha) AS years FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE client = :client GROUP BY years ORDER BY years DESC";

		$select = $this->obj->consultar($sql, [':client'=>$client]);

		$selectYears ="";

		foreach ($select AS $opc1) {
		$selectYears .="<option value='".$opc1["years"]."'>".$opc1["years"]."</option>";
		}

		echo $selectYears;
	}

	function actualizarGoals() {
		
		$mes = date('n');
		$meses = 12 - $mes;
		$user = $_SESSION['username'];

		$sql = "SELECT edit, us_admin FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
		$permit = $this->obj->consultar($sql, [':user'=>$user]);
		$canEdit = ($permit[0]['edit'] == '1' || $permit[0]['us_admin'] == '1');

		$sqlSales = "SELECT id_client, client, SUM(sale) AS total, goal FROM sales INNER JOIN clients ON sales.id_clients = clients.id_client WHERE YEAR(fecha) = '2025' GROUP BY client";
		$clientsData = $this->obj->consultar($sqlSales);

		$data_array = array();
		foreach ($clientsData AS $data) {
			$id = $data['id_client'];
			$client = $data['client'];
			$goals = $data['goal'];
			$sales = $data['total'];
			$difference = $sales - $goals;
			$monthlyGoal = $goals / 12;
			$currentMonth = $monthlyGoal * $mes;
			$currentMDiff = $sales - $currentMonth;

			if ($meses == 0) {
				$restMonthly = ($currentMDiff < 0)? 0 :$monthlyGoal;  
			} else {
				
				$restMonthly = ($currentMDiff < 0)? -($difference / $meses) :$monthlyGoal;

				if (is_nan($restMonthly) || is_infinite($restMonthly)) {
					$restMonthly = 0;
				}
			}
			
			$formatGoals = '$'.number_format($goals,2);
			if ($canEdit) {
				$click = "class='edit-goal' onclick='update(".$id.")'";
				$disabled = "";
			} else {
				$disabled = "disabled";
				$click = "";
			}

			$check = '<div class="form-check text-center"><input type="checkbox" class="form-check-input check-center cursor-pointer" name="checkBox" id='.$id.' '.$disabled.'></div>';

			$data_array[] = array($check,
								  $client, 
								  "<div ".$click.">".$formatGoals."</div>", 
								  '$'.number_format($sales,2), 
								  '$'.number_format($difference,2), 
								  '$'.number_format($monthlyGoal,2), 
								  '$'.number_format($currentMonth,2), 
								  '$'.number_format($currentMDiff,2), 
								  '$'.number_format($restMonthly,2));
		}

		$new_array = array("data"=>$data_array);

		return json_encode($new_array);
	}

	function updateSales() {

		if (isset($_FILES['file'])) {
    		$file = $_FILES['file'];
    		$cvsFile = fopen($file['tmp_name'], 'r');

    		if ($cvsFile) {
				$header = fgetcsv($cvsFile);

				$listHeader = ['id_sale', 'fecha', 'id_clients', 'sale'];

				if ($header !== $listHeader) {
					fclose($cvsFile);
					return '0';
				}

        		while (($line = fgetcsv($cvsFile)) !== false) {

					// if (count($line) === 4) continue;

            		$id_sale = trim($line[0]);
            		$fecha = trim($line[1]);
            		$id_clients = trim($line[2]);
            		$sale = trim($line[3]);

            		$query1 = "SELECT * FROM sales WHERE id_sale = :id_sale";

            		$upload = $this->obj->consultar($query1, [':id_sale'=>$id_sale]);

            		if (is_array($upload) && count($upload) > 0) {
                		$sql = "UPDATE sales SET fecha = :fecha, id_clients = :id_clients, sale = :sale WHERE id_sale = :id_sale";
            		} else {
                		$sql = "INSERT INTO sales (id_sale, fecha, id_clients, sale) VALUES (:id_sale, :fecha, :id_clients, :sale)";
            		}

					$params = [':fecha'=>$fecha, ':id_clients'=>$id_clients, ':sale'=>$sale, ':id_sale'=>$id_sale];

            		$this->obj->actualizar($sql, $params);
            
        		}

        		fclose($cvsFile);

        		echo "1";
    		}
		} else {
    		echo "0";
		}
	}

	function tableClient() {

		$datos = array();

		$sql = "SELECT
					id_client,
					client,
					SUM(CASE WHEN YEAR(fecha) = '2022' THEN sales.sale ELSE 0 END) AS year1,
					SUM(CASE WHEN YEAR(fecha) = '2023' THEN sales.sale ELSE 0 END) AS year2,
					SUM(CASE WHEN YEAR(fecha) = '2024' THEN sales.sale ELSE 0 END) AS year3,
					SUM(CASE WHEN YEAR(fecha) = '2025' THEN sales.sale ELSE 0 END) AS year4
				FROM sales
				INNER JOIN clients ON sales.id_clients = clients.id_client
				WHERE YEAR(fecha) IN (YEAR(CURRENT_DATE())-3,YEAR(CURRENT_DATE())-2,YEAR(CURRENT_DATE())-1,YEAR(CURRENT_DATE()))
				GROUP BY client";

		$client = $this->obj->consultar($sql);
		
		foreach ($client AS $data) {
			$clients = $data['client'];
			$year1 = $data['year1'];
			$year2 = $data['year2'];
			$year3 = $data['year3'];
			$year4 = $data['year4'];

			$datos[] = array($clients,
							 '$'.number_format($year1,2),
							 '$'.number_format($year2,2),
							 '$'.number_format($year3,2),
							 '$'.number_format($year4,2));
		}

		$new_array = array("data"=>$datos);

		return json_encode($new_array);
	}

	function multiUpdate() {

		$user = $_SESSION['username'];

		$query = "SELECT edit, us_admin FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
		$permit = $this->obj->consultar($query, [':user'=>$user]);
		$canEdit = ($permit[0]['edit'] == '1' || $permit[0]['us_admin'] == '1');

		if ($canEdit) {

			$ids = $_POST['ids'];
			$newValue = is_numeric($_POST['value']) ? (float)$_POST['value'] : 0;

			foreach ($ids AS $id) {
				if (!is_numeric($id)) {
					return '0';
				}
				$value = $newValue;

				$sql = "UPDATE clients SET goal = :nvalue WHERE id_client = :id";

				$this->obj->actualizar($sql ,[':nvalue'=>$value, ':id'=>$id]);
			}

			return '1';
		} else {
			return '0';
		}
	}
}
?>