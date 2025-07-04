<?php
include '../../config/controlDB_PDO.php';

class user {

    private $data;
    private $BD;
    private $obj;

    public function __construct() {
        $this->data = parse_ini_file("../../../env/.env");
        $this->BD = $this->data['prefixC'];
        $this->obj = new ControlDB("prod","../");
    }

    function users() {

        $session = $_SESSION['username'];
        $data = array();

        if ($session != 'carlos.casillas') {
            $sql = "SELECT id_user, username, name, last_name, email, puesto, acces, us_admin, edit, us_view, production FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username != 'demo'";
        } else {
            $sql = "SELECT id_user, username, name, last_name, email, puesto, acces, us_admin, edit, us_view, production FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user";
        }

        $tableUsers = $this->obj->consultar($sql);
        
        if (empty($tableUsers)) {
            return 0;
        } else {

            foreach ($tableUsers AS $dataUser) {
                $id = $dataUser['id_user'];
                $user = $dataUser['username'];
                $name = ucfirst($dataUser['name']);
                $lastName = ucfirst($dataUser['last_name']);
                $email = $dataUser['email'];
                $position = ucwords($dataUser['puesto']);
                $acces = $dataUser['acces'];
                $admin = $dataUser['us_admin'];
                $edit = $dataUser['edit'];
                $view = $dataUser['us_view'];
                $production = $dataUser['production'];
                $nameComplete = $name." ".$lastName;

                $data[] = [$id,
                           $user,
                           $nameComplete,
                           $email,
                           $position,
                           $acces,
                           $admin,
                           $edit,
                           $view,
                           $production];
            }

            $new_array = array("data"=>$data);

            return json_encode($new_array);
        }
    }

    function update_permission() {

        $user = $_SESSION['username'];

        $sql = "SELECT us_admin FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
        $permiso = $this->obj->consultar($sql, [':user'=>$user]);
        $admin = $permiso[0]['us_admin'];

        if ($admin === 1) {
            $id = $_POST['user'];
            $permit = $_POST['permit'];
		    $newValue = $_POST['value'];

            $allowed_columns = ['us_admin', 'us_view', 'production', 'acces', 'edit'];

            if (!in_array($permit, $allowed_columns)) {
                return '0';
            }

		    $query = "UPDATE {$this->BD}usuarios.permission SET $permit = :value WHERE id_user = :id";

            $params = [':value'=>$newValue, ':id'=>$id];
		    $newPermit = $this->obj->actualizar($query, $params);

            return '1';
        } else {
            return '0';
        }
    }

    function add_user() {

        $sessionUser = $_SESSION['username'];
        
        $query = "SELECT us_admin FROM {$this->BD}usuarios.usuarios INNER JOIN {$this->BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
        $permit = $this->obj->consultar($query, [':user'=>$sessionUser]);
        $admin = $permit[0]['us_admin'];

        if ($admin == '1') {
            $user = trim($_POST['username']);
            $pass = trim($_POST['password']);
            $name = trim($_POST['name']);
            $lastName = trim($_POST['lastname']);
            $puesto = trim($_POST['puesto']);
            $email = trim($_POST['email']);

            $passCryp = hash('sha512', $pass);

            $sql = "INSERT INTO {$this->BD}usuarios.usuarios (username, password, name, last_name, puesto, email) VALUES (:user, :pass, :name, :lastName, :puesto, :email)";

            $params = [':user'=>$user, ':pass'=>$passCryp, ':name'=>$name, ':lastName'=>$lastName, ':puesto'=>$puesto, ':email'=>$email];
            $this->obj->actualizar($sql, $params);

            $lastID = $this->obj->lastID();

            $sql_permission = "INSERT INTO {$this->BD}usuarios.permission (id_user, us_admin, us_view, production, acces, edit) VALUES (:id, 0, 1, 0, 1, 0)";

            $this->obj->actualizar($sql_permission, [':id'=>$lastID]);

            return '1';
        } else {
            return '0';
        }
    }

    function registers() {

        $sql = "SELECT * FROM {$this->BD}usuarios.session_register WHERE session_date >= CURRENT_DATE() AND session_date < CURRENT_DATE() + INTERVAL 1 DAY ORDER BY session_date DESC";

        $registers = $this->obj->consultar($sql);

        $data = array();
        foreach ($registers AS $wip) {
            $user = $wip['user'];
            $name = ucwords($wip['full_name']);
            $position = ucwords($wip['position']);
            $date = $wip['session_date'];

            $data[] = array($user,
                            $name,
                            $position,
                            $date);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }

    function check_user() {


        if (isset($_POST['username'])) {
            $username = trim($_POST['username']);
            $sql = "SELECT COUNT(*) AS total FROM {$this->BD}usuarios.usuarios WHERE username = :user";
            $res = $this->obj->consultar($sql, [':user'=>$username]);

            echo ($res[0]['total'] > 0) ? 1 : 0;
        }
    }
}
?>