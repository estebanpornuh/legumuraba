-- ============================================================
-- schema.sql: base de datos MySQL de LegumUrabá (Legumbrería)
-- Importar en phpMyAdmin (pestaña Importar) o ejecutar con el cliente mysql.
-- ============================================================
CREATE DATABASE IF NOT EXISTS legumbreria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE legumbreria;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE            -- ADMIN, VENDEDOR, PROVEEDOR
) ENGINE=InnoDB;

CREATE TABLE proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL, telefono VARCHAR(20), municipio VARCHAR(60),
  tipo ENUM('PRODUCTOR','DISTRIBUIDOR') NOT NULL DEFAULT 'PRODUCTOR',
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rol_id INT NOT NULL, proveedor_id INT NULL,
  nombre VARCHAR(80) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,          -- password_hash() de PHP
  activo TINYINT(1) NOT NULL DEFAULT 1,
  intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0, bloqueado_hasta DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id),
  FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL, nombre VARCHAR(100) NOT NULL,
  unidad ENUM('kg','g','unidad','bulto','caja') NOT NULL,
  precio_compra DECIMAL(12,2) NOT NULL DEFAULT 0, precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_actual DECIMAL(12,3) NOT NULL DEFAULT 0, stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
  imagen_ruta VARCHAR(255) NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
  CHECK (precio_compra >= 0 AND precio_venta >= 0 AND stock_actual >= 0),
  KEY idx_prod_cat (categoria_id), KEY idx_prod_stock (stock_actual, stock_minimo),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE entradas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proveedor_id INT NOT NULL, usuario_id INT NOT NULL, fecha DATE NOT NULL,
  total DECIMAL(12,2) NOT NULL, observacion VARCHAR(200) NULL,
  KEY idx_ent_prov_fecha (proveedor_id, fecha),
  FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE detalle_entrada (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entrada_id INT NOT NULL, producto_id INT NOT NULL,
  cantidad DECIMAL(12,3) NOT NULL CHECK (cantidad > 0),
  precio_compra DECIMAL(12,2) NOT NULL, lote VARCHAR(30) NOT NULL, subtotal DECIMAL(12,2) NOT NULL,
  KEY idx_de_producto (producto_id),
  FOREIGN KEY (entrada_id) REFERENCES entradas(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL, fecha_hora DATETIME NOT NULL,
  metodo_pago ENUM('EFECTIVO','NEQUI','TRANSFERENCIA') NOT NULL,
  total DECIMAL(12,2) NOT NULL, estado ENUM('ACTIVA','ANULADA') NOT NULL DEFAULT 'ACTIVA',
  KEY idx_ven_fecha (fecha_hora), KEY idx_ven_pago (metodo_pago, fecha_hora),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT NOT NULL, producto_id INT NOT NULL,
  modalidad ENUM('CANTIDAD','PESO','VALOR') NOT NULL DEFAULT 'CANTIDAD',
  cantidad DECIMAL(12,3) NOT NULL CHECK (cantidad > 0),
  precio_unitario DECIMAL(12,2) NOT NULL, costo_unitario DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  KEY idx_dv_venta (venta_id), KEY idx_dv_producto (producto_id),
  FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

CREATE TABLE ajustes_inventario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL, usuario_id INT NOT NULL, fecha_hora DATETIME NOT NULL,
  cantidad DECIMAL(12,3) NOT NULL CHECK (cantidad > 0),
  motivo ENUM('PERDIDA','DANO','VENCIDO','CORRECCION') NOT NULL,
  costo_total DECIMAL(12,2) NOT NULL, nota VARCHAR(200) NULL,
  KEY idx_aj_fecha (fecha_hora),
  FOREIGN KEY (producto_id) REFERENCES productos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE movimientos_inventario (              -- kardex
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL, tipo ENUM('ENTRADA','VENTA','AJUSTE') NOT NULL,
  cantidad DECIMAL(12,3) NOT NULL,                 -- positivo suma, negativo resta
  referencia_id INT NOT NULL, fecha_hora DATETIME NOT NULL,
  KEY idx_mov_producto (producto_id, fecha_hora),
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL, accion VARCHAR(40) NOT NULL, entidad VARCHAR(40) NOT NULL,
  entidad_id INT NULL, fecha_hora DATETIME NOT NULL, ip VARCHAR(45) NOT NULL,
  KEY idx_aud_fecha (fecha_hora),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE configuracion_negocio (               -- una sola fila
  id TINYINT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, logo_ruta VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------- Datos iniciales ----------
INSERT INTO roles (nombre) VALUES ('ADMIN'), ('VENDEDOR'), ('PROVEEDOR');
INSERT INTO configuracion_negocio (id, nombre) VALUES (1, 'Mi Legumbrería');
-- El administrador se crea desde la pantalla de configuración inicial (index.php?r=setup)
