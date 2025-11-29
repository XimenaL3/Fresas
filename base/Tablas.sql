
CREATE DATABASE PinkberryDelights;
USE PinkberryDelights;

CREATE TABLE Rol (
    idRol INT AUTO_INCREMENT PRIMARY KEY,
    NombreRol VARCHAR(50) NOT NULL UNIQUE,
    Descripcion VARCHAR(255)
);

CREATE TABLE Persona (
    idPersona INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    ApellidoPaterno VARCHAR(100) NOT NULL,
    ApellidoMaterno VARCHAR(100),
    Telefono VARCHAR(15) UNIQUE,
    Email VARCHAR(100) UNIQUE,
    Edad INT CHECK (Edad >= 0),
    Sexo ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    Estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    Usuario VARCHAR(50) NOT NULL UNIQUE,
    Contrasena VARCHAR(255) NOT NULL,
    Imagen VARCHAR(255),
    idRol INT NOT NULL,
    FOREIGN KEY (idRol) REFERENCES Rol(idRol)
        ON UPDATE CASCADE ON DELETE RESTRICT
);


CREATE TABLE Categoria (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Descripcion VARCHAR(255),
    Imagen VARCHAR(255)
);

CREATE TABLE Ingrediente (
    idIngrediente INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    UnidadMedida VARCHAR(50) NOT NULL,
    CantidadDisponible DECIMAL(10,2) DEFAULT 0,
    CostoUnitario DECIMAL(10,2) NOT NULL CHECK (CostoUnitario >= 0),
    Estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    Imagen VARCHAR(255) NULL   -- Ruta o nombre del archivo
);

CREATE TABLE Platillo (
    idPlatillo INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Descripcion TEXT,
    PrecioVenta DECIMAL(10,2) NOT NULL CHECK (PrecioVenta >= 0),
    Imagen VARCHAR(255),
    idCategoria INT NOT NULL,
    idReceta INT NOT NULL UNIQUE,
    FOREIGN KEY (idCategoria) REFERENCES Categoria(idCategoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (idReceta) REFERENCES Receta(idReceta)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE DetallePlatillo (
    idDetallePlatillo INT AUTO_INCREMENT PRIMARY KEY,
    idPlatillo INT NOT NULL,
    Cantidad DECIMAL(10,2) NOT NULL CHECK (Cantidad > 0),
    CantidadDisponible DECIMAL(10,2) NOT NULL CHECK (CantidadDisponible >= 0),
    FechaPreparacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE Receta (
    idReceta INT AUTO_INCREMENT PRIMARY KEY,
    Instrucciones TEXT NOT NULL,
    FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    idPersona INT NOT NULL,
    FOREIGN KEY (idPersona) REFERENCES Persona(idPersona)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE DetalleReceta (
    idDetalleReceta INT AUTO_INCREMENT PRIMARY KEY,
    idReceta INT NOT NULL,
    idIngrediente INT NOT NULL,
    CantidadRequerida DECIMAL(10,2) NOT NULL CHECK (CantidadRequerida > 0),
    FOREIGN KEY (idReceta) REFERENCES Receta(idReceta)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (idIngrediente) REFERENCES Ingrediente(idIngrediente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE (idReceta, idIngrediente)
);

CREATE TABLE Venta (
    idVenta INT AUTO_INCREMENT PRIMARY KEY,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    TipoPago ENUM('Efectivo', 'Tarjeta', 'En línea') NOT NULL,
    Estatus ENUM('Activa', 'Cancelada') DEFAULT 'Activa',
    idPersona INT NOT NULL,
    FOREIGN KEY (idPersona) REFERENCES Persona(idPersona)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE DetalleVenta (
    idDetalleVenta INT AUTO_INCREMENT PRIMARY KEY,
    idVenta INT NOT NULL,
    idPlatillo INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    PrecioUnitario DECIMAL(10,2) NOT NULL CHECK (PrecioUnitario >= 0),
    Total DECIMAL(10,2) GENERATED ALWAYS AS (Cantidad * PrecioUnitario) STORED,
    FOREIGN KEY (idVenta) REFERENCES Venta(idVenta)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE Carrito (
    idCarrito INT AUTO_INCREMENT PRIMARY KEY,
    idPersona INT NOT NULL,
    FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idPersona) REFERENCES Persona(idPersona)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE DetalleCarrito (
    idDetalleCarrito INT AUTO_INCREMENT PRIMARY KEY,
    idCarrito INT NOT NULL,
    idPlatillo INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    PrecioUnitario DECIMAL(10,2) NOT NULL,
    Total DECIMAL(10,2) GENERATED ALWAYS AS (Cantidad * PrecioUnitario) STORED,
    FOREIGN KEY (idCarrito) REFERENCES Carrito(idCarrito)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE Pedido (
    idPedido INT AUTO_INCREMENT PRIMARY KEY,
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FechaEntrega DATE,
    Estatus ENUM('Pendiente', 'Entregado', 'Cancelado') DEFAULT 'Pendiente',
    idPersona INT NOT NULL,
    FOREIGN KEY (idPersona) REFERENCES Persona(idPersona)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE DetallePedido (
    idDetallePedido INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT NOT NULL,
    idPlatillo INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    PrecioUnitario DECIMAL(10,2) NOT NULL,
    Total DECIMAL(10,2) GENERATED ALWAYS AS (Cantidad * PrecioUnitario) STORED,
    FOREIGN KEY (idPedido) REFERENCES Pedido(idPedido)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE Finanzas (
    idFinanzas INT AUTO_INCREMENT PRIMARY KEY,
    idVenta INT NOT NULL UNIQUE,
    TotalVenta DECIMAL(10,2) NOT NULL,
    TotalInvertido DECIMAL(10,2) NOT NULL,
    Ganancia DECIMAL(10,2) GENERATED ALWAYS AS (TotalVenta - TotalInvertido) STORED,
    FOREIGN KEY (idVenta) REFERENCES Venta(idVenta)
        ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO Rol (NombreRol, Descripcion)
VALUES
('Administrador', 'Usuario con acceso total al sistema, encargado de gestionar usuarios, ventas, inventarios y configuraciones.'),
('Cliente', 'Usuario que realiza pedidos, sugerencias o compras dentro del sistema.');