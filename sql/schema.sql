-- CANACO Event Management System Database Schema
-- MySQL 5.7 Compatible

-- Create database
CREATE DATABASE IF NOT EXISTS canaco_eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE canaco_eventos;

-- Users table (SuperAdmin and Gestor roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'gestor') NOT NULL DEFAULT 'gestor',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    descripcion TEXT,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME,
    ubicacion TEXT,
    cupo INT DEFAULT 0,
    imagen VARCHAR(255),
    estado ENUM('borrador', 'publicado', 'cerrado') DEFAULT 'borrador',
    costo DECIMAL(10,2) DEFAULT 0.00,
    tipo_publico ENUM('todos', 'solo_empresas') DEFAULT 'todos',
    gestor_id INT,
    config_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gestor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_fecha (fecha_inicio),
    INDEX idx_estado (estado)
);

-- Event registrations table
CREATE TABLE registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    tipo ENUM('empresa', 'invitado') NOT NULL,
    
    -- Personal data
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    
    -- Company data (for empresas)
    rfc VARCHAR(13),
    razon_social VARCHAR(255),
    nombre_comercial VARCHAR(255),
    direccion_comercial TEXT,
    direccion_fiscal TEXT,
    puesto VARCHAR(255),
    vende ENUM('productos', 'servicios', 'ambos'),
    telefono_oficina VARCHAR(20),
    fecha_aniversario DATE,
    numero_afiliacion VARCHAR(50),
    es_consejero TINYINT(1) DEFAULT 0,
    
    -- Guest data (for invitados)
    fecha_nacimiento DATE,
    cargo_gubernamental VARCHAR(255),
    whatsapp VARCHAR(20),
    
    -- Registration control
    codigo_unico VARCHAR(20) UNIQUE NOT NULL,
    qr_hash VARCHAR(255) NOT NULL,
    estatus ENUM('registrado', 'asistio', 'no_asistio', 'cancelado') DEFAULT 'registrado',
    fecha_asistencia TIMESTAMP NULL,
    validado_por INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (validado_por) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_evento (evento_id),
    INDEX idx_codigo (codigo_unico),
    INDEX idx_rfc (rfc),
    INDEX idx_telefono (telefono),
    INDEX idx_email (email),
    INDEX idx_estatus (estatus)
);

-- Activity logs table
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    accion VARCHAR(255) NOT NULL,
    tabla VARCHAR(100),
    referencia_id INT,
    descripcion TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_tabla (tabla),
    INDEX idx_fecha (created_at)
);

-- System settings table
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion VARCHAR(255),
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (nombre, email, password_hash, role) VALUES 
('Administrador', 'admin@canaco.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');
-- Default password is 'password' - should be changed on first login

-- Insert default configuration
INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('smtp_host', 'localhost', 'Servidor SMTP', 'string'),
('smtp_port', '587', 'Puerto SMTP', 'number'),
('smtp_user', '', 'Usuario SMTP', 'string'),
('smtp_password', '', 'Contraseña SMTP', 'string'),
('site_name', 'CANACO Eventos', 'Nombre del sitio', 'string'),
('site_logo', '', 'Logo del sitio', 'string'),
('default_cupo', '100', 'Cupo predeterminado para eventos', 'number'),
('qr_secret_key', 'canaco_secret_2024', 'Clave secreta para QR', 'string');