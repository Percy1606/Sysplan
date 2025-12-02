<?php
require_once(__DIR__ . '/../../config.php');

class MasterConexion {
    protected $conn;
    protected $_ultimo_error;
    protected $_log_file;

    public function __construct($log_file = __DIR__ . '/../../sysplan_debug.log') { // Ruta absoluta para el log
        $this->_log_file = $log_file;
        try {
            $this->conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            $this->_ultimo_error = "Connection failed: " . $e->getMessage();
            $this->_log_error($this->_ultimo_error);
            // Lanzar una excepción en lugar de imprimir y morir, para que el manejador de errores global la capture.
            throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public function _log_error($message) {
        if ($this->_log_file) {
            file_put_contents($this->_log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
        } else {
            error_log($message); // Fallback to default error_log if no file is specified
        }
    }

    /**
     * Ejecuta una consulta y devuelve un solo arreglo asociativo (una fila).
     */
    public function consulta_arreglo($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_arreglo: " . $e->getMessage() . " SQL: " . $sql);
            return null;
        }
    }

    /**
     * Ejecuta una consulta y devuelve una matriz de arreglos asociativos (múltiples filas).
     */
    public function consulta_matriz($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_matriz: " . $e->getMessage() . " SQL: " . $sql);
            return null;
        }
    }

    /**
     * Ejecuta una consulta de inserción, actualización o eliminación.
     * Devuelve el número de filas afectadas.
     */
    public function ejecutar_sentencia($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en ejecutar_sentencia: " . $e->getMessage() . " SQL: " . $sql);
            return 0;
        }
    }

    /**
     * Ejecuta una consulta SELECT y devuelve una matriz de arreglos asociativos (múltiples filas).
     * Soporta sentencias preparadas.
     */
    public function consulta($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta: " . $e->getMessage() . " SQL: " . $sql);
            return null;
        }
    }

    /**
     * Ejecuta una consulta SELECT y devuelve un solo arreglo asociativo (una fila).
     * Soporta sentencias preparadas.
     */
    public function consulta_registro($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            // Siempre loguear la consulta y los parámetros para depuración
            $this->_log_error("DEBUG - consulta_registro SQL: " . $sql . " Params: " . print_r($params, true));
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->_log_error("DEBUG - consulta_registro Result: " . print_r($result, true));
            return $result;
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_registro: " . $e->getMessage() . " SQL: " . $sql . " Params: " . print_r($params, true));
            return null;
        }
    }

    /**
     * Ejecuta una sentencia INSERT.
     * Soporta sentencias preparadas.
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en insert: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Ejecuta una sentencia UPDATE.
     * Soporta sentencias preparadas.
     */
    public function update($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en update: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Ejecuta una sentencia DELETE.
     * Soporta sentencias preparadas.
     */
    public function delete($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en delete: " . $e->getMessage() . " SQL: " . $sql);
            return 0;
        }
    }

    public function consulta_id($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_id: " . $e->getMessage() . " SQL: " . $sql);
            return 0;
        }
    }

    public function consulta_simple($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_simple: " . $e->getMessage() . " SQL: " . $sql);
            // Devolver false o lanzar la excepción para indicar el fallo de manera más clara
            return false; 
        }
    }

    public function consulta_cantidad($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_cantidad: " . $e->getMessage() . " SQL: " . $sql);
            return 0;
        }
    }

    public function consulta_found_row() {
        try {
            $stmt = $this->conn->prepare("SELECT FOUND_ROWS() as total");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            $this->_ultimo_error = $e->getMessage();
            $this->_log_error("Error en consulta_found_row: " . $e->getMessage());
            return 0;
        }
    }

    public function consulta_arreglo_c($sql) {
        return $this->consulta_arreglo($sql);
    }

    public function __destruct() {
        $this->conn = null;
    }

    public function getUltimoError() {
        return $this->_ultimo_error;
    }

    public function begin_transaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollBack();
    }
}
?>
