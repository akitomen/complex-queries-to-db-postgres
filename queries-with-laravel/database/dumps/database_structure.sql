create table address_types
(
	id bigint CONSTRAINT atp_pk PRIMARY KEY,
	code VARCHAR(50) NOT NULL,
	name varchar(100) not null,
	country VARCHAR(30) NOT NUll
);

CREATE UNIQUE INDEX atp_code_uk ON address_types(UPPER(code));

CREATE SEQUENCE atp_seq;

--
create table address_object_types (
	id bigint CONSTRAINT aot_pk PRIMARY KEY,
	atp_id bigint NOT NULL CONSTRAINT aot_atp_fk REFERENCES address_types,
	code VARCHAR(50) NOT NULL,
	name varchar(100) not null,
	sequence_number integer not null,
	is_free_text boolean not null
);

CREATE UNIQUE INDEX aot_uk ON address_object_types(atp_id, UPPER(code));

CREATE SEQUENCE aot_seq;

create index aot_atp_fk_i on address_object_types (atp_id);
--
create table addresses (
	id bigint CONSTRAINT addr_pk PRIMARY KEY,
	atp_id bigint not null CONSTRAINT addr_atp_fk REFERENCES address_types,
	full_address varchar(1000)
);

CREATE SEQUENCE addr_seq;

create index addr_atp_fk_i ON addresses(atp_id);
--
create table address_object_type_values (
	id bigint CONSTRAINT aov_pk PRIMARY KEY,
	aot_id bigint not null CONSTRAINT aov_aot_fk REFERENCES address_object_types,
	value varchar(500)
);
create index aov_aot_fk_i on address_object_type_values (aot_id);

CREATE SEQUENCE aov_seq;
--
create table address_objects
(
	addr_id bigint not null
		constraint ado_addr_fk
			references addresses,
	aot_id bigint not null
		constraint ado_aot_fk
			references address_object_types,
	aov_id bigint
		constraint ado_aov_fk
			references address_object_type_values,
	value varchar(500),
	constraint ado_pk
		primary key (addr_id, aot_id)
);

CREATE SEQUENCE ado_seq;

create index ado_aov_fk_i on address_objects (aov_id);
create index ado_aot_fk on address_objects (aot_id);
--
create table delivery_types
(
	id bigint CONSTRAINT dty_pk PRIMARY KEY,
	code VARCHAR(50) NOT NULL,
	name varchar(100) not null,
	type varchar(50) not null,
	default_atp_id bigint not null constraint ado_default_atp_fk
		references address_types
);

create unique index dty_code_uk on delivery_types(UPPER(code));

create index dty_default_atp_fk_i on delivery_types(default_atp_id);

CREATE SEQUENCE dty_seq;

--
create table pickup_points (
	id bigint CONSTRAINT ppo_pk PRIMARY KEY,
	dty_id bigint not null CONSTRAINT ppo_dty_fk REFERENCES delivery_types,
	addr_id bigint not null CONSTRAINT ppo_addr_fk REFERENCES addresses
);

CREATE SEQUENCE ppo_seq;

create index ppo_dty_fk_i on pickup_points (dty_id);
create index ppo_addr_fk_i on pickup_points(addr_id);
--
create table delivery_fee_types (
	id bigint CONSTRAINT dft_pk PRIMARY KEY,
	code VARCHAR(50) NOT NULL,
	name varchar(100)
);
CREATE SEQUENCE dft_seq;
CREATE UNIQUE INDEX dft_code_uk ON delivery_fee_types(UPPER(code));

--
create table delivery_fee_configuration
(
	id bigint not null
		constraint dfc_pk
			primary key,
	dty_id bigint not null
		constraint dfc_dty_fk
			references delivery_types,
	dft_id bigint not null CONSTRAINT dfc_dft_fk REFERENCES delivery_fee_types,
	atp_id bigint 
		constraint dfc_atp_fk
			references address_types,
	aov_id bigint
		constraint dfc_aov_fk
			references address_object_type_values,
	total_product_weight_from numeric(7,2),
	total_product_weight_to numeric(7,2) ,
	order_total_amount_from numeric(14,2),
	order_total_amount_to numeric(14,2) ,
	delivery_fee numeric(14,2) not null
);

CREATE SEQUENCE dfc_seq;

create index dfc_aov_fk_i
	on delivery_fee_configuration (aov_id);

create index dfc_atp_fk_i
	on delivery_fee_configuration (atp_id);

create index dfc_dft_fk_i
	on delivery_fee_configuration (dft_id);
--
create table contacts (
	id bigint CONSTRAINT cpe_pk PRIMARY KEY,
	first_name varchar(100) not null,
	last_name varchar(100) not null,
	phone_number varchar(20) not null
);
CREATE SEQUENCE cpe_seq;

create table delivery_type_available_address_types
(
	id bigint not null
		constraint daa_pk
			primary key,
	dty_id bigint not null constraint daa_dty_fk references delivery_types,
	atp_id bigint not null constraint daa_atp_fk references address_types
);

create sequence daa_seq;


create unique index daa_uk
	on delivery_type_available_address_types (dty_id, atp_id);

create unique index daa_uk2
	on delivery_type_available_address_types (atp_id, dty_id);