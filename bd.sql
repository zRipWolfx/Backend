CREATE DATABASE sistema_cotizaciones;
USE sistema_cotizaciones;

-- =========================
-- USUARIOS (LOGIN)
-- =========================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    estado TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- EMPRESA
-- =========================
CREATE TABLE empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    razon_social VARCHAR(200),
    ruc VARCHAR(20),
    direccion VARCHAR(200),
    telefono VARCHAR(20),
    email VARCHAR(100),
    logo VARCHAR(255), -- ruta de la imagen (RECOMENDADO)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CONTACTOS DE EMPRESA
-- =========================
CREATE TABLE empresa_contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    cargo VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion VARCHAR(200),
    estado TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (empresa_id) REFERENCES empresa(id)
);

-- =========================
-- CONFIGURACION (COLORES, IGV, MONEDA)
-- =========================
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    color_primario VARCHAR(20) DEFAULT '#1f2a7a',
    color_secundario VARCHAR(20) DEFAULT '#ffffff',
    moneda VARCHAR(10) DEFAULT 'S/',
    igv DECIMAL(5,2) DEFAULT 18.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CLIENTES
-- =========================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento ENUM('DNI','RUC') NOT NULL,
    numero_documento VARCHAR(20) NOT NULL,
    nombre VARCHAR(150),
    direccion VARCHAR(200),
    telefono VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- PRODUCTOS
-- =========================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) DEFAULT 0.00,
    unidad_medida VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- COTIZACIONES
-- =========================
CREATE TABLE cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE,
    cliente_id INT,
    contacto_id INT, -- contacto de empresa que atiende
    fecha_emision DATE,
    moneda VARCHAR(10),
    forma_pago VARCHAR(100),
    plazo_entrega VARCHAR(100),
    garantia VARCHAR(100),
    subtotal DECIMAL(10,2),
    igv DECIMAL(10,2),
    total DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (contacto_id) REFERENCES empresa_contactos(id)
);

-- =========================
-- DETALLE DE COTIZACION
-- =========================
CREATE TABLE detalle_cotizacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT,
    producto_id INT,
    cantidad DECIMAL(10,2),
    precio_unitario DECIMAL(10,2),
    total DECIMAL(10,2),
    descripcion TEXT,

    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);