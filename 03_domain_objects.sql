/* ============================================================================
   03_domain_objects.sql
   Purpose: Domain-level and other RDAP object schemas (operational data).

   Tables:
     - domains               : domain core
     - subjects              : identified parties/contacts/organizations
     - nameservers           : host objects with IPv4/IPv6 arrays
     - ip_network_versions   : registry for IPv4/IPv6 labels
     - ip_networks           : IP ranges with metadata
     - autnums               : Autonomous System Numbers
     - domain_relationships	 : domains ↔ subjects (relationship-based)
     - domain_nameservers    : domains ↔ nameservers (unique pairs)
     - subject_relationships : subject ↔ subject relations (e.g., registrar ↔ abuse)
     - domain_secure_dns     : DNSSEC/DANE settings per domain
     - domain_tlsa_records   : TLSA records tied to domain_secure_dns

   Functions & Triggers:
     - update_*_latest_data_mutation_at() for several tables
     - set_domain_tld() BEFORE INSERT/UPDATE → calls
       get_matching_tld_ascii_name() from 02_tld_objects.sql (root-zone TLD)

   Run order: 4 of 4  (bootstrap → logging → TLD → domain)
   Depends on: 00_bootstrap_extensions.sql, 02_tld_objects.sql
   Safe to re-run: yes (IF NOT EXISTS + CREATE OR REPLACE on functions)
   Notes:
     - Shared tables (events, links, remarks, notices) are defined in 01_logging.sql.
     - Consider FK from domains.domain_tld → tlds.tld_ascii_name once policies
       around historical snapshots are finalized.
============================================================================ */

-- ========================================
-- Table: metadata_registrar
-- ========================================

CREATE TABLE IF NOT EXISTS metadata_registrar (
    mr_id BIGSERIAL PRIMARY KEY,
	mr_registrar_server_handle TEXT NOT NULL UNIQUE,
	mr_registrar_identifiers JSONB DEFAULT '[]'::jsonb,
    mr_registrar_data_uri JSONB DEFAULT '[]'::jsonb,
    mr_registrar_complaint_uri JSONB DEFAULT '[]'::jsonb
);

-- ========================================
-- Table: domains
-- ========================================
CREATE TABLE IF NOT EXISTS domains (
    domain_id BIGSERIAL PRIMARY KEY,
    domain_tld CITEXT NOT NULL,
    domain_tld_global_handle TEXT NOT NULL UNIQUE,
    domain_source_handle TEXT,
    domain_ascii_name VARCHAR(511) NOT NULL,
    domain_unicode_name VARCHAR(511) NOT NULL,
	domain_subdomains BOOLEAN,
    domain_statuses TEXT[],
	domain_policy_statuses TEXT[],
	domain_dns_state TEXT[],
    domain_created_at TIMESTAMPTZ,
    domain_latest_registrar_transfer_at TIMESTAMPTZ,
    domain_latest_data_mutation_at TIMESTAMPTZ,
    domain_expiration_at TIMESTAMPTZ,
	domain_lifecycle_phase TEXT[],
	domain_lifecycle_phase_until TIMESTAMPTZ,
	domain_applicable_grace TEXT[],
	domain_applicable_grace_until TIMESTAMPTZ,
    domain_recoverable_until TIMESTAMPTZ,
    domain_deletion_at TIMESTAMPTZ,
	domain_metadata_registrar BIGINT REFERENCES metadata_registrar(mr_id),
    domain_extensions JSONB DEFAULT '[]',
    domain_remarks JSONB DEFAULT '[]'
);

-- Trigger Function: Update domain_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_domains_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.domain_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_domains_latest_data_mutation_at ON domains;
CREATE TRIGGER trg_update_domains_latest_data_mutation_at
BEFORE UPDATE ON domains
FOR EACH ROW
EXECUTE FUNCTION update_domains_latest_data_mutation_at();

-- Trigger Function: Set domain_tld using get_matching_tld_ascii_name (from 02)
CREATE OR REPLACE FUNCTION set_domain_tld()
RETURNS TRIGGER AS $$
BEGIN
    NEW.domain_tld := get_matching_tld_ascii_name(NEW.domain_ascii_name);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_set_domain_tld ON domains;
CREATE TRIGGER trg_set_domain_tld
BEFORE INSERT OR UPDATE ON domains
FOR EACH ROW
EXECUTE FUNCTION set_domain_tld();

-- ========================================
-- Table: subjects
-- ========================================
CREATE TABLE IF NOT EXISTS subjects (
    subject_id BIGSERIAL PRIMARY KEY,
    subject_tld_global_handle TEXT NOT NULL UNIQUE,
    subject_source_handle TEXT,
    subject_identifier VARCHAR(34),
    subject_organization_type TEXT,
    subject_organization_name VARCHAR(511),
    subject_presented_name VARCHAR(511),
    subject_kind TEXT,
    subject_name TEXT,
    subject_email CITEXT,
    subject_contact_uri JSONB DEFAULT '[]'::jsonb,
    subject_phone VARCHAR(50),
    subject_fax VARCHAR(50),
    subject_country_code CHAR(2),
    subject_street_address TEXT,
    subject_city TEXT,
    subject_state_or_province TEXT,
    subject_postal_code VARCHAR(20),
    subject_country_name TEXT,
	subject_preferred_languages TEXT[],
	subject_statuses TEXT[],
    subject_created_at TIMESTAMPTZ,
    subject_latest_data_mutation_at TIMESTAMPTZ,
    subject_identifier_received_at TIMESTAMPTZ,
    subject_verification_completed_at TIMESTAMPTZ,
	subject_verification_revoked_at TIMESTAMPTZ,
    subject_properties JSONB DEFAULT '[]'::jsonb,
    subject_remarks JSONB DEFAULT '[]'::jsonb,
	subject_links JSONB NOT NULL DEFAULT '[]'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_subject_postal_code ON subjects(subject_postal_code);
CREATE INDEX IF NOT EXISTS idx_subject_email ON subjects(subject_email);
CREATE INDEX IF NOT EXISTS idx_subject_country_code ON subjects(subject_country_code);

-- Trigger Function: Update subject_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_subjects_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.subject_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_subjects_latest_data_mutation_at ON subjects;
CREATE TRIGGER trg_update_subjects_latest_data_mutation_at
BEFORE UPDATE ON subjects
FOR EACH ROW
EXECUTE FUNCTION update_subjects_latest_data_mutation_at();

-- ========================================
-- Table: nameservers
-- ========================================
CREATE TABLE IF NOT EXISTS nameservers (
    nameserver_id BIGSERIAL PRIMARY KEY,
    nameserver_tld_global_handle TEXT NOT NULL UNIQUE,
    nameserver_source_handle TEXT,
    nameserver_ascii_name TEXT NOT NULL,
    nameserver_unicode_name TEXT,
    nameserver_ipv4_addresses inet[],
    nameserver_ipv6_addresses inet[],
    nameserver_statuses TEXT[],
    nameserver_delegation_check TIMESTAMPTZ,
    nameserver_latest_correct_delegation_check TIMESTAMPTZ,
    nameserver_latest_data_mutation_at TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_nameserver_ascii_name ON nameservers(nameserver_ascii_name);

-- Trigger Function: Update nameserver_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_nameservers_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.nameserver_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_nameservers_latest_data_mutation_at ON nameservers;
CREATE TRIGGER trg_update_nameservers_latest_data_mutation_at
BEFORE UPDATE ON nameservers
FOR EACH ROW
EXECUTE FUNCTION update_nameservers_latest_data_mutation_at();

-- ========================================
-- Table: ip_network_versions
-- ========================================
CREATE TABLE IF NOT EXISTS ip_network_versions (
  ip_network_version_id SERIAL PRIMARY KEY,
  ip_network_version_name VARCHAR(20) UNIQUE
);

-- ========================================
-- Table: ip_networks
-- ========================================
CREATE TABLE IF NOT EXISTS ip_networks (
    ip_network_id BIGSERIAL PRIMARY KEY,
    ip_network_tld_global_handle TEXT NOT NULL UNIQUE,
    ip_network_source_handle TEXT,
    ip_network_start_address VARCHAR(50),
    ip_network_end_address VARCHAR(50),
    ip_network_version INT REFERENCES ip_network_versions(ip_network_version_id),
    ip_network_name TEXT,
    ip_network_type VARCHAR(100),
    ip_network_country_code CHAR(2),
    ip_network_statuses TEXT[],
    ip_network_latest_data_mutation_at TIMESTAMPTZ
);

-- Trigger Function: Update ip_network_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_ip_networks_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.ip_network_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_ip_networks_latest_data_mutation_at ON ip_networks;
CREATE TRIGGER trg_update_ip_networks_latest_data_mutation_at
BEFORE UPDATE ON ip_networks
FOR EACH ROW
EXECUTE FUNCTION update_ip_networks_latest_data_mutation_at();

-- ========================================
-- Table: autnums
-- ========================================
CREATE TABLE IF NOT EXISTS autnums (
    autnum_id BIGSERIAL PRIMARY KEY,
    autnum_tld_global_handle TEXT NOT NULL UNIQUE,
    autnum_source_handle TEXT,
    autnum_start BIGINT,
    autnum_end BIGINT,
    autnum_name TEXT,
    autnum_type VARCHAR(100),
    autnum_country_code CHAR(2),
    autnum_status TEXT[],
    autnum_created_at TIMESTAMPTZ,
    autnum_latest_data_mutation_at TIMESTAMPTZ
);

-- Trigger Function: Update autnum_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_autnums_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.autnum_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_autnums_latest_data_mutation_at ON autnums;
CREATE TRIGGER trg_update_autnums_latest_data_mutation_at
BEFORE UPDATE ON autnums
FOR EACH ROW
EXECUTE FUNCTION update_autnums_latest_data_mutation_at();

-- ========================================
-- Table: domain_relationships
-- ========================================
-- "not_stored" — value is not maintained by the domain service.
-- "shielded" — value is maintained but not disclosed.
-- "visible" — value is maintained and disclosed.
-- "tunable_shielded" — value is maintained and currently not disclosed.
-- "tunable_visible" — value is maintained and currently disclosed.
-- ========================================
CREATE TABLE IF NOT EXISTS domain_relationships (
    dr_id SERIAL PRIMARY KEY,
    dr_domain BIGINT NOT NULL REFERENCES domains(domain_id) ON DELETE CASCADE,
    dr_source_layer VARCHAR(12) NOT NULL CHECK (dr_source_layer IN ('registry','registrar')),
    dr_relationship VARCHAR(50),
    dr_publication_state JSONB NOT NULL DEFAULT '{
        "organization_name": "shielded",
        "presented_name": "shielded",
        "name": "shielded",
        "email": "shielded",
        "contact_uri": "shielded",
        "phone": "shielded",
        "country_code": "shielded",
        "address": "shielded"
    }'::jsonb,
    dr_subject BIGINT NOT NULL REFERENCES subjects(subject_id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_dr_unique ON domain_relationships (dr_domain, dr_source_layer, dr_relationship, dr_subject);
CREATE INDEX IF NOT EXISTS idx_dr_domain ON domain_relationships(dr_domain);
CREATE INDEX IF NOT EXISTS idx_dr_subject ON domain_relationships(dr_subject);

-- ========================================
-- Table: domain_nameservers (link domains<->nameservers)
-- ========================================
CREATE TABLE IF NOT EXISTS domain_nameservers (
    dn_id BIGSERIAL PRIMARY KEY,
    dn_domain BIGINT NOT NULL REFERENCES domains(domain_id) ON DELETE CASCADE,
    dn_nameserver BIGINT NOT NULL REFERENCES nameservers(nameserver_id) ON DELETE CASCADE,
    dn_latest_data_mutation_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (dn_domain, dn_nameserver)
);

CREATE INDEX IF NOT EXISTS idx_dn_domain ON domain_nameservers(dn_domain);
CREATE INDEX IF NOT EXISTS idx_dn_nameserver ON domain_nameservers(dn_nameserver);

-- Trigger Function: Update dn_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_domain_nameservers_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.dn_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_domain_nameservers_latest_data_mutation_at ON domain_nameservers;
CREATE TRIGGER trg_update_domain_nameservers_latest_data_mutation_at
BEFORE UPDATE ON domain_nameservers
FOR EACH ROW
EXECUTE FUNCTION update_domain_nameservers_latest_data_mutation_at();

-- ========================================
-- "not_stored" — value is not maintained by the domain service.
-- "shielded" — value is maintained but not disclosed.
-- "visible" — value is maintained and disclosed.
-- "tunable_shielded" — value is maintained and currently not disclosed.
-- "tunable_visible" — value is maintained and currently disclosed.
-- ========================================
CREATE TABLE IF NOT EXISTS subject_relationships (
    sr_id BIGSERIAL PRIMARY KEY,
	sr_source_layer VARCHAR(12) NOT NULL CHECK (sr_source_layer IN ('registry','registrar')),
    sr_parent_relationship VARCHAR(50),
    sr_parent BIGINT NOT NULL REFERENCES subjects(subject_id) ON DELETE CASCADE,
    sr_child_relationship VARCHAR(50),
    sr_publication_state JSONB NOT NULL DEFAULT '{
        "organization_name": "shielded",
        "presented_name": "shielded",
        "name": "shielded",
        "email": "shielded",
        "contact_uri": "shielded",
        "phone": "shielded",
        "country_code": "shielded",
        "address": "shielded"
    }'::jsonb,
    sr_child BIGINT NOT NULL REFERENCES subjects(subject_id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_sr_unique ON subject_relationships (sr_source_layer, sr_parent_relationship, sr_parent, sr_child_relationship, sr_child);
CREATE INDEX IF NOT EXISTS idx_sr_parent ON subject_relationships(sr_parent);
CREATE INDEX IF NOT EXISTS idx_sr_child ON subject_relationships(sr_child);

-- ========================================
-- Table: domain_secure_dns
-- ========================================
CREATE TABLE IF NOT EXISTS domain_secure_dns (
    ds_id BIGSERIAL PRIMARY KEY,
    ds_domain BIGINT REFERENCES domains(domain_id),
    ds_secure_dns_enabled BOOLEAN,
    ds_dns_provider VARCHAR(100),
    ds_created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    ds_latest_data_mutation_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ds_domain ON domain_secure_dns(ds_domain);

-- Trigger Function: Update ds_latest_data_mutation_at on UPDATE
CREATE OR REPLACE FUNCTION update_domain_secure_dns_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.ds_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_domain_secure_dns_latest_data_mutation_at ON domain_secure_dns;
CREATE TRIGGER trg_update_domain_secure_dns_latest_data_mutation_at
BEFORE UPDATE ON domain_secure_dns
FOR EACH ROW
EXECUTE FUNCTION update_domain_secure_dns_latest_data_mutation_at();

-- ========================================
-- Table: domain_tlsa_records (DANE/TLSA)
-- ========================================
CREATE TABLE IF NOT EXISTS domain_tlsa_records (
    dt_id BIGSERIAL PRIMARY KEY,
    dt_secure_dns INT REFERENCES domain_secure_dns(ds_id) ON DELETE CASCADE,
    dt_usage SMALLINT CHECK (dt_usage BETWEEN 0 AND 3),
    dt_selector SMALLINT CHECK (dt_selector BETWEEN 0 AND 1),
    dt_matching_type SMALLINT CHECK (dt_matching_type BETWEEN 0 AND 2),
    dt_certificate_association_data TEXT NOT NULL,
    dt_latest_data_mutation_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_dt_secure_dns ON domain_tlsa_records(dt_secure_dns);

-- Trigger: Update dt_latest_data_mutation_at
CREATE OR REPLACE FUNCTION update_domain_tlsa_latest_data_mutation_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.dt_latest_data_mutation_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_domain_tlsa_latest_data_mutation_at ON domain_tlsa_records;
CREATE TRIGGER trg_update_domain_tlsa_latest_data_mutation_at
BEFORE UPDATE ON domain_tlsa_records
FOR EACH ROW
EXECUTE FUNCTION update_domain_tlsa_latest_data_mutation_at();
