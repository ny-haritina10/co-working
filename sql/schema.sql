CREATE TABLE client (
	id bigserial NOT NULL,
	name_client varchar(255) NOT NULL,
	numero_client varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT client_numero_client_unique UNIQUE (numero_client),
	CONSTRAINT client_pkey PRIMARY KEY (id)
);

CREATE TABLE espaces (
	id bigserial NOT NULL,
	"label" varchar(255) NOT NULL,
	hour_price numeric(10, 2) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT espaces_label_unique UNIQUE (label),
	CONSTRAINT espaces_pkey PRIMARY KEY (id)
);

CREATE TABLE "options" (
	id bigserial NOT NULL,
	code varchar(255) NOT NULL,
	"label" varchar(255) NOT NULL,
	price numeric(10, 2) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT options_code_unique UNIQUE (code),
	CONSTRAINT options_pkey PRIMARY KEY (id)
);

CREATE TABLE reservations (
	id bigserial NOT NULL,
	id_client int8 NOT NULL,
	id_espace int8 NOT NULL,
	reference varchar(255) NOT NULL,
	datetime_reservation timestamp(0) NOT NULL,
	hour_duration int4 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT reservations_pkey PRIMARY KEY (id),
	CONSTRAINT reservations_reference_unique UNIQUE (reference),
	CONSTRAINT reservations_id_client_foreign FOREIGN KEY (id_client) REFERENCES client(id) ON DELETE CASCADE,
	CONSTRAINT reservations_id_espace_foreign FOREIGN KEY (id_espace) REFERENCES espaces(id) ON DELETE CASCADE
);

CREATE TABLE paiements (
	id bigserial NOT NULL,
	id_reservation int8 NOT NULL,
	reference varchar(255) NOT NULL,
	date_paiement date NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT paiements_pkey PRIMARY KEY (id),
	CONSTRAINT paiements_id_reservation_foreign FOREIGN KEY (id_reservation) REFERENCES reservations(id) ON DELETE CASCADE
);

CREATE TABLE reservation_options (
	id bigserial NOT NULL,
	id_reservation int8 NOT NULL,
	id_option int8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT reservation_options_pkey PRIMARY KEY (id),
	CONSTRAINT reservation_options_id_option_foreign FOREIGN KEY (id_option) REFERENCES "options"(id) ON DELETE CASCADE,
	CONSTRAINT reservation_options_id_reservation_foreign FOREIGN KEY (id_reservation) REFERENCES reservations(id) ON DELETE CASCADE
);