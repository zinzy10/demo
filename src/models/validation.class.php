<?php
include '../../config/controlDB_PDO.php';

class profile {

	private $data;
    private $BD;
	private $url;
	private $user;
	private $pass;
	private $obj;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
		$this->url = $this->data['url1'];
		$this->user = $this->data['user'];
		$this->pass = $this->data['pass'];
		$this->obj = new ControlDB();
    }
	
	function validation() {

		if($_POST['opcion'] === 'login'){

			if (isset($_POST['user']) && $_POST['user'] === 'demo') {
				$username = $this->user;
				$password = $this->pass;
				$pass_hash = hash('sha512', $password);
			} else {
				$username = $_POST['username'];
				$password = $_POST['password'];
				$pass_hash = hash('sha512', $password);
			}

			$sql = "SELECT
						name,
						last_name,
						puesto,
						password,
						production,
						acces
					FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user
					WHERE username = :user";

			$session = $this->obj->consultar($sql, [':user'=>$username]);
		
			if($session){
				$_SESSION['username'] = $username;
				$date = date('Y-m-d H:i:s');
				$name = $session[0]['name'];
				$last = $session[0]['last_name'];
				$fullName = $name." ".$last;
				$position = $session[0]['puesto'];
				$pass = $session[0]['password'];
				$production = $session[0]['production'];
				$acces = $session[0]['acces'];

				if ($acces == '0'){
					echo "0";
					exit;
				}
				
				$params = [':user'=>$username, ':fullName'=>$fullName, ':position'=>$position, ':date'=>$date];

				if($pass_hash === $pass){
					if ($production == '1') {

						$sql = "INSERT INTO {$this->BD}usuarios.session_register (user, full_name, position, session_date) VALUES (:user, :fullName, :position, :date)";

						$this->obj->actualizar($sql, $params);
						
						echo "1";
					} else {

						$sql = "INSERT INTO {$this->BD}usuarios.session_register (user, full_name, position, session_date) VALUES (:user, :fullName, :position, :date)";

						$this->obj->actualizar($sql, $params);
						
						echo "2";
					}
				}else if($password === $pass){
					session_destroy();
					echo "3";
				}else {
					session_destroy();
					echo "0";
				}
			}else {
				echo "0";
			}
		} else if($_POST['opcion'] === 'logOut'){
			session_destroy();
			return $this->url;
		}
	}

	function changePass() {

		if (isset($_SESSION['username'])) {

			$user = $_SESSION['username'];

			if ($user === 'demo') return ;

			$pass = $_POST['pass'];
			$passCryp = hash('sha512', $pass);

			$sql = "UPDATE {$this->BD}usuarios.usuarios SET  password = :pass WHERE username = :user";

			$params = [':pass' =>$passCryp, ':user'=>$user];
			$update =$this->obj->actualizar($sql, $params);

		} else if (isset($_POST['user'])) {
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			$passCryp = hash('sha512', $pass);

			$sql = "UPDATE {$this->BD}usuarios.usuarios SET  password = :pass WHERE username = :user";

			$params = [':pass' =>$passCryp, ':user'=>$user];
			$update =$this->obj->actualizar($sql, $params);

			return $this->url;
		}
	}
}

?>