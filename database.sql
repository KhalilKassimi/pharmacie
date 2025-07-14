-- Création de la base de données
CREATE DATABASE IF NOT EXISTS pharmacie_db;
USE pharmacie_db;

-- Table fournisseur
CREATE TABLE IF NOT EXISTS fournisseur (
    code_fournisseur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    adresse1 TEXT,
    fix VARCHAR(20),
    fax VARCHAR(20),
    portable VARCHAR(20)
);

-- Table medicament
CREATE TABLE IF NOT EXISTS medicament (
    code_medi INT PRIMARY KEY AUTO_INCREMENT,
    description TEXT,
    stockmin INT,
    prixunitaire DECIMAL(10,2),
    stock_dispo INT
);

-- Table commande
CREATE TABLE IF NOT EXISTS commande (
    code_commande INT PRIMARY KEY AUTO_INCREMENT,
    date DATE,
    montant DECIMAL(10,2),
    validated BOOLEAN DEFAULT FALSE,
    code_fournisseur INT,
    FOREIGN KEY (code_fournisseur) REFERENCES fournisseur(code_fournisseur)
);

-- Table vente
CREATE TABLE IF NOT EXISTS vente (
    num_vente INT PRIMARY KEY AUTO_INCREMENT,
    montant_vente DECIMAL(10,2),
    date_vente DATE,
    code_pharmacien INT
);

-- Table stock
CREATE TABLE IF NOT EXISTS stock (
    code_stock INT PRIMARY KEY AUTO_INCREMENT,
    quantite_stock INT,
    code_medi INT,
    FOREIGN KEY (code_medi) REFERENCES medicament(code_medi)
);

-- Table client
CREATE TABLE IF NOT EXISTS client (
    code INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL
);

-- Table pharmacien
CREATE TABLE IF NOT EXISTS pharmacien (
    code_pharmacien INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    tel VARCHAR(20)
);

-- Table est_dans (relation entre commande et medicament)
CREATE TABLE IF NOT EXISTS est_dans (
    code_commande INT,
    code_medi INT,
    quantite_medicament INT,
    PRIMARY KEY (code_commande, code_medi),
    FOREIGN KEY (code_commande) REFERENCES commande(code_commande),
    FOREIGN KEY (code_medi) REFERENCES medicament(code_medi)
);

-- Table concerne (relation entre vente et medicament)
CREATE TABLE IF NOT EXISTS concerne (
    num_vente INT,
    code_medi INT,
    quantite INT,
    PRIMARY KEY (num_vente, code_medi),
    FOREIGN KEY (num_vente) REFERENCES vente(num_vente),
    FOREIGN KEY (code_medi) REFERENCES medicament(code_medi)
);