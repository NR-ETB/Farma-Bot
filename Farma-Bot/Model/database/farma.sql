-- Creación de la base de datos
DROP DATABASE farma;
CREATE DATABASE farma;
USE farma;

-- Tabla: Infecciones
CREATE TABLE Infecciones (
    id_Inf INT AUTO_INCREMENT PRIMARY KEY,
    nom_Inf VARCHAR(45),
    gra_Inf VARCHAR(10),
    ing_Inf VARCHAR(200),
    cant_Inf INT
);

-- Tabla: Oncologicas
CREATE TABLE Oncologicas (
    id_Onc INT AUTO_INCREMENT PRIMARY KEY,
    nom_Onc VARCHAR(45),
    gra_Onc VARCHAR(10),
    ing_Onc VARCHAR(200),
    cant_Onc INT
);

-- Tabla: Endocrinologicas
CREATE TABLE Endocrinologicas (
    id_End INT AUTO_INCREMENT PRIMARY KEY,
    nom_End VARCHAR(45),
    gra_End VARCHAR(10),
    ing_End VARCHAR(200),
    cant_End INT
);

-- Tabla: Inventario
CREATE TABLE Inventario (
    id_Inv INT AUTO_INCREMENT PRIMARY KEY,
    id_Inf INT,
    id_Onc INT,
    id_End INT,
    FOREIGN KEY (id_Inf) REFERENCES Infecciones(id_Inf),
    FOREIGN KEY (id_Onc) REFERENCES Oncologicas(id_Onc),
    FOREIGN KEY (id_End) REFERENCES Endocrinologicas(id_End)
);

-- Insertar medicamentos en Infecciones
INSERT INTO Infecciones (nom_Inf, gra_Inf, ing_Inf, cant_Inf) VALUES
('Amoxicilina', '500mg', 'Tratamiento de infecciones bacterianas respiratorias y urinarias', 100),
('Azitromicina', '500mg', 'Infecciones respiratorias, de la piel y transmisión sexual', 80),
('Metronidazol', '400mg', 'Infecciones anaerobias y parasitarias como giardiasis', 120),
('Ciprofloxacino', '500mg', 'Infecciones urinarias y gastrointestinales graves', 70),
('Isoniazida', '300mg', 'Tratamiento de tuberculosis activa y latente', 90);

-- Insertar medicamentos en Oncológicas
INSERT INTO Oncologicas (nom_Onc, gra_Onc, ing_Onc, cant_Onc) VALUES
('Cisplatino', '50mg', 'Quimioterapia para cáncer de pulmón, ovario y vejiga', 30),
('Doxorrubicina', '20mg', 'Cáncer de mama, leucemias y linfomas', 40),
('Paclitaxel', '100mg', 'Tratamiento de cáncer de ovario, mama y pulmón', 25),
('Enzalutamida', '40mg', 'Cáncer de próstata resistente a castración', 15),
('Bevacizumab', '100mg', 'Tratamiento de tumores sólidos como colon y pulmón', 20);

-- Insertar medicamentos en Endocrinológicas
INSERT INTO Endocrinologicas (nom_End, gra_End, ing_End, cant_End) VALUES
('Metformina', '850mg', 'Tratamiento de diabetes tipo 2, mejora sensibilidad a la insulina', 200),
('Insulina Glargina', '100UI/ml', 'Control glucémico prolongado en diabetes tipo 1 y 2', 150),
('Levotiroxina', '100mcg', 'Tratamiento del hipotiroidismo', 180),
('Empagliflozina', '10mg', 'Inhibidor SGLT2 para controlar la glucosa en diabetes tipo 2', 100),
('Canagliflozina', '100mg', 'Otro inhibidor SGLT2 para control glucémico y protección renal', 90);