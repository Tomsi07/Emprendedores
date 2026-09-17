CREATE DATABASE IF NOT EXISTS emprendedores CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE emprendedores;

-- emprendedor
CREATE TABLE emprendedor(
    idEmprendedor INT AUTO_INCREMENT PRIMARY KEY,
    nombreEmprendedor VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    domicilio VARCHAR(50),
    contacto VARCHAR(100),
    email VARCHAR(100),
    dni VARCHAR(20) NOT NULL,
    formalizacion VARCHAR(30) NOT NULL,
);

-- emprendimiento
CREATE TABLE emprendimiento(
    idEmprendimiento INT AUTO_INCREMENT PRIMARY KEY,
    nombreEmprendimiento VARCHAR(50) NOT NULL,
    contacto VARCHAR(100),
    domicilio VARCHAR(50)
);

-- emprendedor_emprendimiento
CREATE TABLE emprendedor_emprendimiento(
    idEmprendedor INT NOT NULL,
    idEmprendimiento INT NOT NULL,
    PRIMARY KEY(idEmprendedor,idEmprendimiento),
    FOREIGN KEY(idEmprendedor) REFERENCES emprendedor(idEmprendedor),
    FOREIGN KEY(idEmprendimiento) REFERENCES emprendimiento(idEmprendimiento)
);

--actividad
CREATE TABLE actividad(
    idActividad INT AUTO_INCREMENT PRIMARY KEY,
    especializacion VARCHAR(100)
);

-- emprendimiento_actividad
CREATE TABLE emprendimiento_actividad(
    idEmprendimiento INT NOT NULL,
    idActividad INT NOT NULL,
    PRIMARY KEY(idEmprendimiento,idActividad),
    FOREIGN KEY(idEmprendimiento) REFERENCES emprendimiento(idEmprendimiento),
    FOREIGN KEY(idActividad) REFERENCES actividad(idActividad)
);

-- evento
CREATE TABLE evento(
    idEvento INT AUTO_INCREMENT PRIMARY KEY,
    nombreEvento VARCHAR(100) NOT NULL,
    ciudad VARCHAR(50),
    lugar VARCHAR(100),
    fecha DATE,
    tipoDeEvento VARCHAR(50)
);

-- necesidad
CREATE TABLE necesidad(
    idNecesidad INT AUTO_INCREMENT PRIMARY KEY,
    idEmprendimiento INT NOT NULL,
    idEvento INT,
    descripcion VARCHAR(255) NOT NULL,
    FOREIGN KEY(idEmprendimiento) REFERENCES emprendimiento(idEmprendimiento),
    FOREIGN KEY(idEvento) REFERENCES evento(idEvento)
);

-- asistencia
CREATE TABLE asistencia(
    idEmprendimiento INT NOT NULL,
    idEvento INT NOT NULL,
    PRIMARY KEY(idEmprendimiento,idEvento),
    FOREIGN KEY(idEmprendimiento) REFERENCES emprendimiento(idEmprendimiento),
    FOREIGN KEY(idEvento) REFERENCES evento(idEvento)
);