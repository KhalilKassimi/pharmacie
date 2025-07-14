-- Création de la base de données
CREATE DATABASE IF NOT EXISTS opticien_db;
USE opticien_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    role ENUM('admin', 'optometriste', 'assistant') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des clients
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(255),
    numero_secu VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des prescriptions
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    date_prescription DATE NOT NULL,
    medecin VARCHAR(100),
    sphere_droite DECIMAL(4,2),
    cylindre_droite DECIMAL(4,2),
    axe_droite INT,
    sphere_gauche DECIMAL(4,2),
    cylindre_gauche DECIMAL(4,2),
    axe_gauche INT,
    addition_droite DECIMAL(4,2),
    addition_gauche DECIMAL(4,2),
    commentaires TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- Table des montures
CREATE TABLE IF NOT EXISTS montures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100),
    couleur VARCHAR(50),
    taille VARCHAR(20),
    prix_ht DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des verres
CREATE TABLE IF NOT EXISTS verres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    indice DECIMAL(3,2) NOT NULL,
    traitement VARCHAR(100),
    prix_ht DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    date_rdv DATETIME NOT NULL,
    motif VARCHAR(255),
    optometriste_id INT NOT NULL,
    statut ENUM('planifié', 'confirmé', 'annulé', 'terminé') NOT NULL DEFAULT 'planifié',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (optometriste_id) REFERENCES users(id)
);

-- Table des ventes
CREATE TABLE IF NOT EXISTS ventes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    prescription_id INT,
    monture_id INT,
    verre_droite_id INT,
    verre_gauche_id INT,
    prix_total_ht DECIMAL(10,2) NOT NULL,
    tva DECIMAL(10,2) NOT NULL,
    prix_total_ttc DECIMAL(10,2) NOT NULL,
    date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut_paiement ENUM('en_attente', 'partiel', 'payé') NOT NULL DEFAULT 'en_attente',
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
    FOREIGN KEY (monture_id) REFERENCES montures(id),
    FOREIGN KEY (verre_droite_id) REFERENCES verres(id),
    FOREIGN KEY (verre_gauche_id) REFERENCES verres(id)
);