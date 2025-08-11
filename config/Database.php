<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            if (DB_TYPE === 'sqlite') {
                $dsn = "sqlite:" . SQLITE_PATH;
                $this->connection = new PDO($dsn);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Create tables if they don't exist
                $this->createSQLiteTables();
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            // For development, we'll set connection to null and handle gracefully
            $this->connection = null;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if ($this->connection === null) {
            throw new Exception("Database not available");
        }
        return $this->connection;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function prepare($sql) {
        if ($this->connection === null) {
            throw new Exception("Database not available");
        }
        return $this->connection->prepare($sql);
    }
    
    public function execute($sql, $params = []) {
        if ($this->connection === null) {
            return false;
        }
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function fetch($sql, $params = []) {
        if ($this->connection === null) {
            return false;
        }
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        if ($this->connection === null) {
            return [];
        }
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    private function createSQLiteTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'gestor',
            activo INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS eventos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            descripcion TEXT,
            fecha_inicio DATETIME NOT NULL,
            fecha_fin DATETIME,
            ubicacion TEXT,
            cupo INTEGER DEFAULT 0,
            imagen VARCHAR(255),
            estado VARCHAR(20) DEFAULT 'borrador',
            costo DECIMAL(10,2) DEFAULT 0.00,
            tipo_publico VARCHAR(20) DEFAULT 'todos',
            gestor_id INTEGER,
            config_json TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (gestor_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS registros (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            evento_id INTEGER NOT NULL,
            tipo VARCHAR(20) NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefono VARCHAR(20),
            rfc VARCHAR(13),
            razon_social VARCHAR(255),
            nombre_comercial VARCHAR(255),
            direccion_comercial TEXT,
            direccion_fiscal TEXT,
            puesto VARCHAR(255),
            vende VARCHAR(20),
            telefono_oficina VARCHAR(20),
            fecha_aniversario DATE,
            numero_afiliacion VARCHAR(50),
            es_consejero INTEGER DEFAULT 0,
            fecha_nacimiento DATE,
            cargo_gubernamental VARCHAR(255),
            whatsapp VARCHAR(20),
            codigo_unico VARCHAR(20) UNIQUE NOT NULL,
            qr_hash VARCHAR(255) NOT NULL,
            estatus VARCHAR(20) DEFAULT 'registrado',
            fecha_asistencia DATETIME,
            validado_por INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (evento_id) REFERENCES eventos(id),
            FOREIGN KEY (validado_por) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            accion VARCHAR(255) NOT NULL,
            tabla VARCHAR(100),
            referencia_id INTEGER,
            descripcion TEXT,
            ip VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS configuracion (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            clave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT,
            descripcion VARCHAR(255),
            tipo VARCHAR(20) DEFAULT 'string',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $this->connection->exec($sql);
        
        // Insert default admin user if it doesn't exist
        $adminExists = $this->fetch("SELECT id FROM users WHERE email = 'admin@canaco.org'");
        if (!$adminExists) {
            $this->execute(
                "INSERT INTO users (nombre, email, password_hash, role) VALUES (?, ?, ?, ?)",
                ['Administrador', 'admin@canaco.org', password_hash('password', PASSWORD_DEFAULT), 'superadmin']
            );
        }
    }
}