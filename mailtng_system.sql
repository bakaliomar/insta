--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.2
-- Dumped by pg_dump version 9.6.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: admin; Type: SCHEMA; Schema: -; Owner: mtng
--

CREATE SCHEMA admin;


ALTER SCHEMA admin OWNER TO mtng;

--
-- Name: production; Type: SCHEMA; Schema: -; Owner: mtng
--

CREATE SCHEMA production;


ALTER SCHEMA production OWNER TO mtng;

--
-- Name: stats; Type: SCHEMA; Schema: -; Owner: mtng
--

CREATE SCHEMA stats;


ALTER SCHEMA stats OWNER TO mtng;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = admin, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: application_roles; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE application_roles (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE application_roles OWNER TO mtng;

--
-- Name: data_lists; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE data_lists (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    isp_id integer NOT NULL,
    flag character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text
);


ALTER TABLE data_lists OWNER TO mtng;

--
-- Name: data_types; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE data_types (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE data_types OWNER TO mtng;

--
-- Name: domains; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE domains (
    id integer NOT NULL,
    status_id integer NOT NULL,
    ip_id integer NOT NULL,
    value text NOT NULL,
    domain_status character varying(20) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE domains OWNER TO mtng;

--
-- Name: headers; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE headers (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    type character varying(100) DEFAULT NULL::character varying,
    value text
);


ALTER TABLE headers OWNER TO mtng;

--
-- Name: ips; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE ips (
    id integer NOT NULL,
    status_id integer NOT NULL,
    server_id integer NOT NULL,
    value character varying(100) NOT NULL,
    rdns character varying(100) DEFAULT NULL::character varying,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE ips OWNER TO mtng;

--
-- Name: isps; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE isps (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text
);


ALTER TABLE isps OWNER TO mtng;

--
-- Name: offer_creatives; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE offer_creatives (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE offer_creatives OWNER TO mtng;

--
-- Name: offer_links; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE offer_links (
    id integer NOT NULL,
    status_id integer NOT NULL,
    creative_id integer NOT NULL,
    value text NOT NULL,
    type character varying(20) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE offer_links OWNER TO mtng;

--
-- Name: offer_names; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE offer_names (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE offer_names OWNER TO mtng;

--
-- Name: offer_subjects; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE offer_subjects (
    id integer NOT NULL,
    status_id integer NOT NULL,
    offer_id integer NOT NULL,
    value text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE offer_subjects OWNER TO mtng;

--
-- Name: offers; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE offers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    sponsor_id integer NOT NULL,
    production_id integer NOT NULL,
    campaign_id integer NOT NULL,
    vertical_id integer NOT NULL,
    name text NOT NULL,
    flag text NOT NULL,
    description text,
    rate character varying(20) DEFAULT NULL::character varying,
    launch_date date NOT NULL,
    expiring_date date NOT NULL,
    rules text,
    epc character varying(20) DEFAULT NULL::character varying,
    suppression_list text,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    authorized_users text,
    key character varying(10)
);


ALTER TABLE offers OWNER TO mtng;

--
-- Name: proccesses; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE proccesses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    type text NOT NULL,
    status character varying(20) NOT NULL,
    progress text NOT NULL,
    data text,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone
);


ALTER TABLE proccesses OWNER TO mtng;

--
-- Name: seq_id_application_roles; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_application_roles
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_application_roles OWNER TO mtng;

--
-- Name: seq_id_data_lists; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_data_lists
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_data_lists OWNER TO mtng;

--
-- Name: seq_id_data_types; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_data_types
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_data_types OWNER TO mtng;

--
-- Name: seq_id_domains; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_domains
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_domains OWNER TO mtng;

--
-- Name: seq_id_headers; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_headers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_headers OWNER TO mtng;

--
-- Name: seq_id_ips; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_ips
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_ips OWNER TO mtng;

--
-- Name: seq_id_isps; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_isps
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_isps OWNER TO mtng;

--
-- Name: seq_id_offer_creatives; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_offer_creatives
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_offer_creatives OWNER TO mtng;

--
-- Name: seq_id_offer_links; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_offer_links
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_offer_links OWNER TO mtng;

--
-- Name: seq_id_offer_names; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_offer_names
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_offer_names OWNER TO mtng;

--
-- Name: seq_id_offer_subjects; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_offer_subjects
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_offer_subjects OWNER TO mtng;

--
-- Name: seq_id_offers; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_offers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_offers OWNER TO mtng;

--
-- Name: seq_id_proccesses; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_proccesses
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_proccesses OWNER TO mtng;

--
-- Name: seq_id_server_providers; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_server_providers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_server_providers OWNER TO mtng;

--
-- Name: seq_id_server_types; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_server_types
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_server_types OWNER TO mtng;

--
-- Name: seq_id_servers; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_servers
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_servers OWNER TO mtng;

--
-- Name: seq_id_sponsors; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_sponsors
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_sponsors OWNER TO mtng;

--
-- Name: seq_id_status; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_status
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_status OWNER TO mtng;

--
-- Name: seq_id_users; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_users
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_users OWNER TO mtng;

--
-- Name: seq_id_verticals; Type: SEQUENCE; Schema: admin; Owner: mtng
--

CREATE SEQUENCE seq_id_verticals
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_verticals OWNER TO mtng;

--
-- Name: server_providers; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE server_providers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(100) NOT NULL,
    website character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE server_providers OWNER TO mtng;

--
-- Name: server_types; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE server_types (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE server_types OWNER TO mtng;

--
-- Name: servers; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE servers (
    id integer NOT NULL,
    status_id integer NOT NULL,
    provider_id integer NOT NULL,
    server_type_id integer NOT NULL,
    name character varying(100) NOT NULL,
    host_name character varying(100) NOT NULL,
    main_ip character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date,
    ssh_port integer,
    authorized_users text,
    expiration_date date
);


ALTER TABLE servers OWNER TO mtng;

--
-- Name: sponsors; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE sponsors (
    id integer NOT NULL,
    status_id integer NOT NULL,
    affiliate_id integer NOT NULL,
    name character varying(20) NOT NULL,
    website text NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    api_key text,
    api_url text,
    api_type text,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE sponsors OWNER TO mtng;

--
-- Name: status; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE status (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE status OWNER TO mtng;

--
-- Name: users; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE users (
    id integer NOT NULL,
    status_id integer NOT NULL,
    application_role_id integer NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    telephone character varying(20) NOT NULL,
    email character varying(100) NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE users OWNER TO mtng;

--
-- Name: verticals; Type: TABLE; Schema: admin; Owner: mtng
--

CREATE TABLE verticals (
    id integer NOT NULL,
    status_id integer NOT NULL,
    name text NOT NULL,
    created_by integer NOT NULL,
    last_updated_by integer,
    created_at date NOT NULL,
    last_updated_at date
);


ALTER TABLE verticals OWNER TO mtng;

SET search_path = production, pg_catalog;

--
-- Name: drop_ips; Type: TABLE; Schema: production; Owner: mtng
--

CREATE TABLE drop_ips (
    id integer NOT NULL,
    server_id integer NOT NULL,
    isp_id integer,
    drop_id integer NOT NULL,
    ip_id integer NOT NULL,
    drop_date timestamp without time zone NOT NULL,
    total_sent integer,
    delivered integer,
    bounced integer
);


ALTER TABLE drop_ips OWNER TO mtng;

--
-- Name: drops; Type: TABLE; Schema: production; Owner: mtng
--

CREATE TABLE drops (
    id integer NOT NULL,
    user_id integer NOT NULL,
    server_id integer NOT NULL,
    isp_id integer,
    status character varying(20) NOT NULL,
    start_time timestamp without time zone NOT NULL,
    finish_time timestamp without time zone,
    total_emails integer NOT NULL,
    sent_progress integer,
    offer_id integer NOT NULL,
    offer_from_name_id integer NOT NULL,
    offer_subject_id integer NOT NULL,
    recipients_emails text,
    pids text,
    header text,
    creative_id integer NOT NULL,
    lists text,
    post_data text NOT NULL
);


ALTER TABLE drops OWNER TO mtng;

--
-- Name: ip_status; Type: TABLE; Schema: production; Owner: mtng
--

CREATE TABLE ip_status (
    id integer NOT NULL,
    ip_id integer NOT NULL,
    status_date timestamp without time zone NOT NULL,
    x_store_info character varying(255) NOT NULL,
    x_message_delivery character varying(255) NOT NULL,
    x_message_info character varying(255) NOT NULL
);


ALTER TABLE ip_status OWNER TO mtng;

--
-- Name: seq_id_drop_ips; Type: SEQUENCE; Schema: production; Owner: mtng
--

CREATE SEQUENCE seq_id_drop_ips
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_drop_ips OWNER TO mtng;

--
-- Name: seq_id_drops; Type: SEQUENCE; Schema: production; Owner: mtng
--

CREATE SEQUENCE seq_id_drops
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_drops OWNER TO mtng;

--
-- Name: seq_id_ip_status; Type: SEQUENCE; Schema: production; Owner: mtng
--

CREATE SEQUENCE seq_id_ip_status
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_ip_status OWNER TO mtng;

SET search_path = stats, pg_catalog;

--
-- Name: clicks; Type: TABLE; Schema: stats; Owner: mtng
--

CREATE TABLE clicks (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE clicks OWNER TO mtng;

--
-- Name: leads; Type: TABLE; Schema: stats; Owner: mtng
--

CREATE TABLE leads (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    rate character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE leads OWNER TO mtng;

--
-- Name: opens; Type: TABLE; Schema: stats; Owner: mtng
--

CREATE TABLE opens (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE opens OWNER TO mtng;

--
-- Name: seq_id_clicks; Type: SEQUENCE; Schema: stats; Owner: mtng
--

CREATE SEQUENCE seq_id_clicks
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_clicks OWNER TO mtng;

--
-- Name: seq_id_leads; Type: SEQUENCE; Schema: stats; Owner: mtng
--

CREATE SEQUENCE seq_id_leads
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_leads OWNER TO mtng;

--
-- Name: seq_id_opens; Type: SEQUENCE; Schema: stats; Owner: mtng
--

CREATE SEQUENCE seq_id_opens
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_opens OWNER TO mtng;

--
-- Name: seq_id_unsubs; Type: SEQUENCE; Schema: stats; Owner: mtng
--

CREATE SEQUENCE seq_id_unsubs
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seq_id_unsubs OWNER TO mtng;

--
-- Name: unsubs; Type: TABLE; Schema: stats; Owner: mtng
--

CREATE TABLE unsubs (
    id integer NOT NULL,
    drop_id integer NOT NULL,
    email character varying(100) NOT NULL,
    type character varying(20) NOT NULL,
    action_date timestamp without time zone NOT NULL,
    list character varying(100) NOT NULL,
    message text,
    ip character varying(20) DEFAULT NULL::character varying,
    country text,
    region text,
    city text,
    language character varying(2) DEFAULT NULL::character varying,
    device_type text,
    device_name character varying(100) DEFAULT NULL::character varying,
    os text,
    browser_name text,
    browser_version character varying(100) DEFAULT NULL::character varying,
    action_occurences integer
);


ALTER TABLE unsubs OWNER TO mtng;

SET search_path = admin, pg_catalog;

--
-- Data for Name: application_roles; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY application_roles (id, status_id, name, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	1	Administrator	1	1	2017-03-02	2017-03-02
2	1	Mailer	1	1	2017-03-02	2017-03-02
\.


--
-- Data for Name: data_lists; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY data_lists (id, name, isp_id, flag, created_by, last_updated_by, created_at, last_updated_at, authorized_users) FROM stdin;
\.


--
-- Data for Name: data_types; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY data_types (id, status_id, name, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	1	Fresh	1	1	2017-03-02	2017-03-02
2	1	Clean	1	1	2017-03-02	2017-03-02
3	1	Openers	1	1	2017-03-02	2017-03-02
4	1	Clickers	1	1	2017-03-02	2017-03-02
5	1	Leads	1	1	2017-03-02	2017-03-02
6	1	Unsubscribers	1	1	2017-03-02	2017-03-02
7	1	Seeds	1	1	2017-03-02	2017-03-02
\.


--
-- Data for Name: domains; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY domains (id, status_id, ip_id, value, domain_status, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: headers; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY headers (id, user_id, name, type, value) FROM stdin;
\.


--
-- Data for Name: ips; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY ips (id, status_id, server_id, value, rdns, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: isps; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY isps (id, status_id, name, created_by, last_updated_by, created_at, last_updated_at, authorized_users) FROM stdin;
\.


--
-- Data for Name: offer_creatives; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY offer_creatives (id, status_id, offer_id, value, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: offer_links; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY offer_links (id, status_id, creative_id, value, type, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: offer_names; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY offer_names (id, status_id, offer_id, value, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;

\.


--
-- Data for Name: offer_subjects; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY offer_subjects (id, status_id, offer_id, value, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: offers; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY offers (id, status_id, sponsor_id, production_id, campaign_id, vertical_id, name, flag, description, rate, launch_date, expiring_date, rules, epc, suppression_list, created_by, last_updated_by, created_at, last_updated_at, authorized_users, key) FROM stdin;
\.


--
-- Data for Name: proccesses; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY proccesses (id, user_id, name, type, status, progress, data, start_time, finish_time) FROM stdin;
\.


--
-- Name: seq_id_application_roles; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_application_roles', 2, true);


--
-- Name: seq_id_data_lists; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_data_lists', 35, true);


--
-- Name: seq_id_data_types; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_data_types', 7, true);


--
-- Name: seq_id_domains; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_domains', 34, true);


--
-- Name: seq_id_headers; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_headers', 1, true);


--
-- Name: seq_id_ips; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_ips', 22, true);


--
-- Name: seq_id_isps; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_isps', 1, true);


--
-- Name: seq_id_offer_creatives; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_offer_creatives', 15, true);


--
-- Name: seq_id_offer_links; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_offer_links', 38, true);


--
-- Name: seq_id_offer_names; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_offer_names', 39, true);


--
-- Name: seq_id_offer_subjects; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_offer_subjects', 45, true);


--
-- Name: seq_id_offers; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_offers', 3, true);


--
-- Name: seq_id_proccesses; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_proccesses', 1, false);


--
-- Name: seq_id_server_providers; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_server_providers', 4, true);


--
-- Name: seq_id_server_types; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_server_types', 2, true);


--
-- Name: seq_id_servers; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_servers', 10, true);


--
-- Name: seq_id_sponsors; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_sponsors', 2, true);


--
-- Name: seq_id_status; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_status', 2, true);


--
-- Name: seq_id_users; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_users', 5, true);


--
-- Name: seq_id_verticals; Type: SEQUENCE SET; Schema: admin; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_verticals', 2, true);


--
-- Data for Name: server_providers; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY server_providers (id, status_id, name, website, username, password, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


--
-- Data for Name: server_types; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY server_types (id, status_id, name, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	1	Master	1	1	2017-03-02	2017-03-02
2	1	Production Server	1	1	2017-03-02	2017-03-02
\.


--
-- Data for Name: servers; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY servers (id, status_id, provider_id, server_type_id, name, host_name, main_ip, username, password, created_by, last_updated_by, created_at, last_updated_at, ssh_port, authorized_users, expiration_date) FROM stdin;
\.


--
-- Data for Name: sponsors; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY sponsors (id, status_id, affiliate_id, name, website, username, password, api_key, api_url, api_type, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	1	700898	B2Direct	http://b2directpartners.com	contact@99leads.co	99leads600606	cgg1oq11hB2pBMqfw4FlKA	http://b2directpartners.com/affiliates/api/2/	cake	1	1	2017-03-02	2017-04-05
\.


--
-- Data for Name: status; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY status (id, name, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	Activated	1	1	2017-03-02	2017-03-02
2	Desactivated	1	1	2017-03-02	2017-03-02
\.



--
-- Data for Name: users; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY users (id, status_id, application_role_id, first_name, last_name, telephone, email, username, password, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
1	1	1	Master	master			master	7d9025957347f30ecf4a0313d39720a2	1	1	2017-02-27	2017-02-27
\.


--
-- Data for Name: verticals; Type: TABLE DATA; Schema: admin; Owner: mtng
--

COPY verticals (id, status_id, name, created_by, last_updated_by, created_at, last_updated_at) FROM stdin;
\.


SET search_path = production, pg_catalog;

--
-- Data for Name: drop_ips; Type: TABLE DATA; Schema: production; Owner: mtng
--

COPY drop_ips (id, server_id, isp_id, drop_id, ip_id, drop_date, total_sent, delivered, bounced) FROM stdin;
\.


--
-- Data for Name: drops; Type: TABLE DATA; Schema: production; Owner: mtng
--

COPY drops (id, user_id, server_id, isp_id, status, start_time, finish_time, total_emails, sent_progress, offer_id, offer_from_name_id, offer_subject_id, recipients_emails, pids, header, creative_id, lists, post_data) FROM stdin;
\.


--
-- Data for Name: ip_status; Type: TABLE DATA; Schema: production; Owner: mtng
--

COPY ip_status (id, ip_id, status_date, x_store_info, x_message_delivery, x_message_info) FROM stdin;
\.


--
-- Name: seq_id_drop_ips; Type: SEQUENCE SET; Schema: production; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_drop_ips', 42, true);


--
-- Name: seq_id_drops; Type: SEQUENCE SET; Schema: production; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_drops', 42, true);


--
-- Name: seq_id_ip_status; Type: SEQUENCE SET; Schema: production; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_ip_status', 1, false);


SET search_path = stats, pg_catalog;

--
-- Data for Name: clicks; Type: TABLE DATA; Schema: stats; Owner: mtng
--

COPY clicks (id, drop_id, email, action_date, list, ip, country, region, city, language, device_type, device_name, os, browser_name, browser_version, action_occurences) FROM stdin;
\.


--
-- Data for Name: leads; Type: TABLE DATA; Schema: stats; Owner: mtng
--

COPY leads (id, drop_id, email, rate, action_date, list, ip, country, region, city, language, device_type, device_name, os, browser_name, browser_version, action_occurences) FROM stdin;
\.


--
-- Data for Name: opens; Type: TABLE DATA; Schema: stats; Owner: mtng
--

COPY opens (id, drop_id, email, action_date, list, ip, country, region, city, language, device_type, device_name, os, browser_name, browser_version, action_occurences) FROM stdin;
\.


--
-- Name: seq_id_clicks; Type: SEQUENCE SET; Schema: stats; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_clicks', 12, true);


--
-- Name: seq_id_leads; Type: SEQUENCE SET; Schema: stats; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_leads', 1, false);


--
-- Name: seq_id_opens; Type: SEQUENCE SET; Schema: stats; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_opens', 26, true);


--
-- Name: seq_id_unsubs; Type: SEQUENCE SET; Schema: stats; Owner: mtng
--

SELECT pg_catalog.setval('seq_id_unsubs', 49, true);


--
-- Data for Name: unsubs; Type: TABLE DATA; Schema: stats; Owner: mtng
--

COPY unsubs (id, drop_id, email, type, action_date, list, message, ip, country, region, city, language, device_type, device_name, os, browser_name, browser_version, action_occurences) FROM stdin;
\.


SET search_path = admin, pg_catalog;

--
-- Name: application_roles c_pk_id_application_roles; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY application_roles
    ADD CONSTRAINT c_pk_id_application_roles PRIMARY KEY (id);


--
-- Name: data_lists c_pk_id_data_lists; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY data_lists
    ADD CONSTRAINT c_pk_id_data_lists PRIMARY KEY (id);


--
-- Name: data_types c_pk_id_data_types; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY data_types
    ADD CONSTRAINT c_pk_id_data_types PRIMARY KEY (id);


--
-- Name: domains c_pk_id_domains; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY domains
    ADD CONSTRAINT c_pk_id_domains PRIMARY KEY (id);


--
-- Name: headers c_pk_id_headers; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY headers
    ADD CONSTRAINT c_pk_id_headers PRIMARY KEY (id);


--
-- Name: ips c_pk_id_ips; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY ips
    ADD CONSTRAINT c_pk_id_ips PRIMARY KEY (id);


--
-- Name: isps c_pk_id_isps; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY isps
    ADD CONSTRAINT c_pk_id_isps PRIMARY KEY (id);


--
-- Name: offer_creatives c_pk_id_offer_creatives; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY offer_creatives
    ADD CONSTRAINT c_pk_id_offer_creatives PRIMARY KEY (id);


--
-- Name: offer_links c_pk_id_offer_links; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY offer_links
    ADD CONSTRAINT c_pk_id_offer_links PRIMARY KEY (id);


--
-- Name: offer_names c_pk_id_offer_names; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY offer_names
    ADD CONSTRAINT c_pk_id_offer_names PRIMARY KEY (id);


--
-- Name: offer_subjects c_pk_id_offer_subjects; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY offer_subjects
    ADD CONSTRAINT c_pk_id_offer_subjects PRIMARY KEY (id);


--
-- Name: offers c_pk_id_offers; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY offers
    ADD CONSTRAINT c_pk_id_offers PRIMARY KEY (id);


--
-- Name: proccesses c_pk_id_proccesses; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY proccesses
    ADD CONSTRAINT c_pk_id_proccesses PRIMARY KEY (id);


--
-- Name: server_providers c_pk_id_server_providers; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY server_providers
    ADD CONSTRAINT c_pk_id_server_providers PRIMARY KEY (id);


--
-- Name: server_types c_pk_id_server_types; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY server_types
    ADD CONSTRAINT c_pk_id_server_types PRIMARY KEY (id);


--
-- Name: servers c_pk_id_servers; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY servers
    ADD CONSTRAINT c_pk_id_servers PRIMARY KEY (id);


--
-- Name: sponsors c_pk_id_sponsors; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY sponsors
    ADD CONSTRAINT c_pk_id_sponsors PRIMARY KEY (id);


--
-- Name: status c_pk_id_status; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY status
    ADD CONSTRAINT c_pk_id_status PRIMARY KEY (id);


--
-- Name: users c_pk_id_users; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY users
    ADD CONSTRAINT c_pk_id_users PRIMARY KEY (id);


--
-- Name: verticals c_pk_id_verticals; Type: CONSTRAINT; Schema: admin; Owner: mtng
--

ALTER TABLE ONLY verticals
    ADD CONSTRAINT c_pk_id_verticals PRIMARY KEY (id);


SET search_path = production, pg_catalog;

--
-- Name: drop_ips c_pk_id_drop_ips; Type: CONSTRAINT; Schema: production; Owner: mtng
--

ALTER TABLE ONLY drop_ips
    ADD CONSTRAINT c_pk_id_drop_ips PRIMARY KEY (id);


--
-- Name: drops c_pk_id_drops; Type: CONSTRAINT; Schema: production; Owner: mtng
--

ALTER TABLE ONLY drops
    ADD CONSTRAINT c_pk_id_drops PRIMARY KEY (id);


--
-- Name: ip_status c_pk_id_ip_status; Type: CONSTRAINT; Schema: production; Owner: mtng
--

ALTER TABLE ONLY ip_status
    ADD CONSTRAINT c_pk_id_ip_status PRIMARY KEY (id);


SET search_path = stats, pg_catalog;

--
-- Name: clicks c_pk_id_clicks; Type: CONSTRAINT; Schema: stats; Owner: mtng
--

ALTER TABLE ONLY clicks
    ADD CONSTRAINT c_pk_id_clicks PRIMARY KEY (id);


--
-- Name: leads c_pk_id_leads; Type: CONSTRAINT; Schema: stats; Owner: mtng
--

ALTER TABLE ONLY leads
    ADD CONSTRAINT c_pk_id_leads PRIMARY KEY (id);


--
-- Name: opens c_pk_id_opens; Type: CONSTRAINT; Schema: stats; Owner: mtng
--

ALTER TABLE ONLY opens
    ADD CONSTRAINT c_pk_id_opens PRIMARY KEY (id);


--
-- Name: unsubs c_pk_id_unsubs; Type: CONSTRAINT; Schema: stats; Owner: mtng
--

ALTER TABLE ONLY unsubs
    ADD CONSTRAINT c_pk_id_unsubs PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

