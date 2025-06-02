-- Crear base de datos
CREATE DATABASE IF NOT EXISTS clinica_odontologica;
USE clinica_odontologica;

-- Tabla de pacientes
CREATE TABLE pacientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    fecha_nacimiento DATE
);

-- Tabla de citas
CREATE TABLE citas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paciente_id INT,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'realizada', 'cancelada') DEFAULT 'pendiente',
    tratamiento VARCHAR(200),
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

-- Tabla de historias clínicas
CREATE TABLE historias_clinicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paciente_id INT,
    fecha_creacion DATE,
    diagnostico TEXT,
    tratamiento TEXT,
    observaciones TEXT,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);
