<?php
class ControlDB {
    
    private $con;

    function __construct($db = "prod", $link = "../") {
        try {
            $data = parse_ini_file($link."../../env/.env");
            $BD = $data[$db."_db"];

            // Conexión PDO
            $dsn = "mysql:host=".$data[$db."_host"].";dbname=".$BD.";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->con = new PDO($dsn, $data[$db."_user"], $data[$db."_pass"], $options);
            
        } catch (Exception $ex) {
            throw new Exception("Error de conexión: " . $ex->getMessage());
        }
    }

    function consultar($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            
            return $stmt->fetchAll();
            
        } catch (Exception $ex) {
            throw new Exception("Error en consulta: " . $ex->getMessage());
        }
    }

    function cantidad($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            return $stmt->rowCount();
            
        } catch (Exception $ex) {
            throw new Exception("Error en consulta de cantidad: " . $ex->getMessage());
        }
    }

    function actualizar($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            return $stmt->rowCount();
            
        } catch (Exception $ex) {
            throw new Exception("Error en actualización: " . $ex->getMessage());
        }
    }

    function lastID() {
        return $this->con->lastInsertId();
    }

    // Método adicional útil para PDO
    function ejecutar($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            return true;
            
        } catch (Exception $ex) {
            throw new Exception("Error al ejecutar: " . $ex->getMessage());
        }
    }

    // Método para comenzar una transacción
    function beginTransaction() {
        return $this->con->beginTransaction();
    }

    // Método para confirmar una transacción
    function commit() {
        return $this->con->commit();
    }

    // Método para revertir una transacción
    function rollBack() {
        return $this->con->rollBack();
    }
}
?>