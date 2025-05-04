-- Creazione del database
CREATE DATABASE IF NOT EXISTS erp_system;
USE erp_system;

-- Tabella clienti
CREATE TABLE IF NOT EXISTS clienti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    indirizzo TEXT,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella fornitori
CREATE TABLE IF NOT EXISTS fornitori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ragione_sociale VARCHAR(200) NOT NULL,
    partita_iva VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    indirizzo TEXT,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella prodotti
CREATE TABLE IF NOT EXISTS prodotti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descrizione TEXT,
    prezzo DECIMAL(10,2) NOT NULL,
    quantita_disponibile INT NOT NULL DEFAULT 0,
    fornitore_id INT,
    FOREIGN KEY (fornitore_id) REFERENCES fornitori(id)
);

-- Tabella ordini
CREATE TABLE IF NOT EXISTS ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    data_ordine TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('in attesa', 'confermato', 'spedito', 'consegnato') DEFAULT 'in attesa',
    totale DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clienti(id)
);

-- Tabella dettagli ordine
CREATE TABLE IF NOT EXISTS dettagli_ordine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordine_id INT,
    prodotto_id INT,
    quantita INT NOT NULL,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ordine_id) REFERENCES ordini(id),
    FOREIGN KEY (prodotto_id) REFERENCES prodotti(id)
);

-- Tabella movimenti contabili
CREATE TABLE IF NOT EXISTS movimenti_contabili (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('entrata', 'uscita') NOT NULL,
    importo DECIMAL(10,2) NOT NULL,
    descrizione TEXT,
    riferimento_ordine INT,
    FOREIGN KEY (riferimento_ordine) REFERENCES ordini(id)
);