CREATE DATABASE IF NOT EXISTS raster_mecanica;
USE raster_mecanica;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS carros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) UNIQUE,
    modelo VARCHAR(100),
    ano INT,
    cliente VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS obd_dados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carro_id INT,
    rpm INT,
    velocidade INT,
    temperatura INT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carro_id) REFERENCES carros(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo VARCHAR(255) NULL
);

INSERT INTO configuracoes (id, logo) VALUES (1, 'img/logo.png')
ON DUPLICATE KEY UPDATE logo = VALUES(logo);
