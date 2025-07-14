-- Création de la base de données
CREATE DATABASE IF NOT EXISTS opticien_db;
USE opticien_db;

-- Table client
CREATE TABLE IF NOT EXISTS client (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    numero_secu VARCHAR(20),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table prescription
CREATE TABLE IF NOT EXISTS prescription (
    id_prescription INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    date_prescription DATE NOT NULL,
    sphere_od DECIMAL(4,2),
    cylindre_od DECIMAL(4,2),
    axe_od INT,
    addition_od DECIMAL(4,2),
    sphere_og DECIMAL(4,2),
    cylindre_og DECIMAL(4,2),
    axe_og INT,
    addition_og DECIMAL(4,2),
    commentaire TEXT,
    FOREIGN KEY (id_client) REFERENCES client(id_client)
);

-- Table monture
CREATE TABLE IF NOT EXISTS monture (
    id_monture INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(50) NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    couleur VARCHAR(50),
    taille VARCHAR(20),
    prix_ht DECIMAL(10,2),
    stock INT DEFAULT 0
);

-- Table verre
CREATE TABLE IF NOT EXISTS verre (
    id_verre INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    indice DECIMAL(3,2),
    traitement VARCHAR(100),
    diametre INT,
    prix_ht DECIMAL(10,2)
);

-- Table facture
CREATE TABLE IF NOT EXISTS facture (
    id_facture INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    date_facture DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant_ht DECIMAL(10,2),
    tva DECIMAL(5,2),
    montant_ttc DECIMAL(10,2),
    statut VARCHAR(20) DEFAULT 'en_attente',
    FOREIGN KEY (id_client) REFERENCES client(id_client)
);

-- Table ligne_facture
CREATE TABLE IF NOT EXISTS ligne_facture (
    id_ligne INT PRIMARY KEY AUTO_INCREMENT,
    id_facture INT,
    id_monture INT NULL,
    id_verre_od INT NULL,
    id_verre_og INT NULL,
    quantite INT,
    prix_unitaire_ht DECIMAL(10,2),
    montant_ht DECIMAL(10,2),
    FOREIGN KEY (id_facture) REFERENCES facture(id_facture),
    FOREIGN KEY (id_monture) REFERENCES monture(id_monture),
    FOREIGN KEY (id_verre_od) REFERENCES verre(id_verre),
    FOREIGN KEY (id_verre_og) REFERENCES verre(id_verre)
);

-- Table rendez_vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id_rdv INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    date_rdv DATETIME,
    motif VARCHAR(200),
    statut VARCHAR(20) DEFAULT 'planifie',
    notes TEXT,
    FOREIGN KEY (id_client) REFERENCES client(id_client)
);

-- Table utilisateur
CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'employe',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);