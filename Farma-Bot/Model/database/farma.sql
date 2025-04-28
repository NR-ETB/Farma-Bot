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

-- Crear la tabla de Sedes
CREATE TABLE Sedes (
    id_Sed INT AUTO_INCREMENT PRIMARY KEY,
    nom_Sed VARCHAR(100) NOT NULL
);

-- Tabla: Inventario
CREATE TABLE Inventario (
    id_Inv INT AUTO_INCREMENT PRIMARY KEY,
    id_Inf INT,
    id_Onc INT,
    id_End INT,
	id_Sed INT,
    FOREIGN KEY (id_Inf) REFERENCES Infecciones(id_Inf),
    FOREIGN KEY (id_Onc) REFERENCES Oncologicas(id_Onc),
    FOREIGN KEY (id_End) REFERENCES Endocrinologicas(id_End),
	FOREIGN KEY (id_Sed) REFERENCES Sedes(id_Sed)
);

-- Insertar medicamentos en Infecciones
INSERT INTO Infecciones (nom_Inf, gra_Inf, ing_Inf, cant_Inf) VALUES
('Amoxicilina', '500mg', 'Amoxicilina, Celulosa microcristalina, Estearato de magnesio', 0),
('Azitromicina', '500mg', 'Azitromicina, Fosfato de calcio dibásico, Laurilsulfato sódico', 80),
('Metronidazol', '400mg', 'Metronidazol, Almidón de maíz, Estearato de magnesio', 120),
('Ciprofloxacino', '500mg', 'Ciprofloxacino, Dióxido de silicio, Povidona', 100),
('Isoniazida', '300mg', 'Isoniazida, Lactosa monohidrato, Talco', 90);

-- Insertar en Oncológicas
INSERT INTO Oncologicas (nom_Onc, gra_Onc, ing_Onc, cant_Onc) VALUES
('Cisplatino', '50mg', 'Cisplatino, Cloruro de sodio, Agua para inyección', 30),
('Doxorrubicina', '20mg', 'Doxorrubicina, Lactosa, Ácido cítrico', 0),
('Paclitaxel', '100mg', 'Paclitaxel, Cremophor EL, Etanol', 25),
('Enzalutamida', '40mg', 'Enzalutamida, Lactosa monohidrato, Celulosa microcristalina', 15),
('Bevacizumab', '100mg', 'Bevacizumab, Polisorbato 20, Fosfato de sodio', 20);

-- Insertar en Endocrinológicas
INSERT INTO Endocrinologicas (nom_End, gra_End, ing_End, cant_End) VALUES
('Metformina', '850mg', 'Metformina clorhidrato, Povidona, Estearato de magnesio', 0),
('Insulina Glargina', '100UI/ml', 'Insulina Glargina, Glicerol, Polisorbato 20', 150),
('Levotiroxina', '100mcg', 'Levotiroxina sódica, Lactosa monohidrato, Almidón de maíz', 180),
('Repaglinida ', '4mg', 'Celulosa microcristalina, Povidona, Estearato de magnesio', 200),
('Canagliflozina', '100mg', 'Canagliflozina, Dióxido de titanio, Macrogol', 90);

-- Insertar tres sedes de ejemplo
INSERT INTO Sedes (nom_Sed) VALUES
('Sede Central'),
('Sede Norte'),
('Sede Sur');

-- Crear vistas de inventario para cada sede

-- Vista para Sede Central utilizando la tabla Infecciones
CREATE VIEW Inventario_Sede_Central AS
SELECT
    i.id_Inf AS id_Medicamento,
    i.nom_Inf AS nombre,
    i.gra_Inf AS gramaje,
    i.ing_Inf AS ingredientes,
    i.cant_Inf AS cantidad,
    'Infecciones' AS categoria,
    'Sede Central' AS sede
FROM Infecciones i;

-- Vista para Sede Norte utilizando la tabla Oncologicas
CREATE VIEW Inventario_Sede_Norte AS
SELECT
    o.id_Onc AS id_Medicamento,
    o.nom_Onc AS nombre,
    o.gra_Onc AS gramaje,
    o.ing_Onc AS ingredientes,
    o.cant_Onc AS cantidad,
    'Oncologicas' AS categoria,
    'Sede Norte' AS sede
FROM Oncologicas o;

-- Vista para Sede Sur utilizando la tabla Endocrinologicas
CREATE VIEW Inventario_Sede_Sur AS
SELECT
    e.id_End AS id_Medicamento,
    e.nom_End AS nombre,
    e.gra_End AS gramaje,
    e.ing_End AS ingredientes,
    e.cant_End AS cantidad,
    'Endocrinologicas' AS categoria,
    'Sede Sur' AS sede
FROM Endocrinologicas e;