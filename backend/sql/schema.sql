--
--
--
CREATE TABLE client (
    id SERIAL PRIMARY KEY,
    name_client VARCHAR(255) NOT NULL,
    numero_client VARCHAR(255) NOT NULL,
    UNIQUE(numero_client)
);

--
--
--
CREATE TABLE options (
    id SERIAL PRIMARY KEY,
    code VARCHAR(255) NOT NULL,
    label VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    UNIQUE(code)
);

--
--
--
CREATE TABLE espaces (
    id SERIAL PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    hour_price DECIMAL(10, 2) NOT NULL,
    UNIQUE(label)
);

--
--
--
CREATE TABLE reservations (
    id SERIAL PRIMARY KEY,
    id_client INT NOT NULL REFERENCES client(id) NOT NULL,    
    id_espace INT NOT NULL REFERENCES espaces (id) NO NULL,
    reference VARCHAR(255) NOT NULL,
    datetime_reservation DATETIME NOT NULL,
    hour_duration INT NOT NULL,
    UNIQUE(reference)
);

--
--
--
CREATE TABLE reservation_options (
    id SERIAL PRIMARY KEY,
    id_reservation INT NOT NULL REFERENCES reservations(id),
    id_option INT NOT NULL REFERENCES options(id)
);

--
--
--
CREATE TABLE paiements (
    id SERIAL PRIMARY KEY,
    id_reservation INT NOT NULL REFERENCES reservations(id),
    reference VARCHAR(255) NOT NULL,
    date_paiement DATE NOT NULL
);

--
--
--
INSERT INTO client (id, name_client, numero_client) 
VALUES (1, 'Client 1', '0349049881');

INSERT INTO client (id, name_client, numero_client) 
VALUES (2, 'Client 2', '0381034567');

