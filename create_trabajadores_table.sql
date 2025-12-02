CREATE TABLE trabajadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombresApellidos VARCHAR(255) NOT NULL,
    tipoDocumento INT NOT NULL,
    documento VARCHAR(50) NOT NULL UNIQUE,
    sueldoBasico DECIMAL(10, 2) NOT NULL,
    ocupacion VARCHAR(255),
    contrato INT,
    condicion INT,
    situacion INT,
    fechaIngreso DATE,
    fechaCese DATE,
    asignacionFamiliar INT,
    regimenPensionario INT,
    idSocioRegimenPensionario VARCHAR(255)
);
