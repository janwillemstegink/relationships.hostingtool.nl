/* ============================================================================
   02_tld_objects.sql
   Purpose: Registry / TLD-level schema (tld catalog + policy + functions).

   Tables:
     - tld_categories, tld_types
     - root             	 : global registry settings (functions, statuses, periods)
     - tlds                  : DNS root-zone TLD entries (with functions & workload)
	 - function_subjects     : subject functions
     - lifecycles            : TLD lifecycle policy snapshots

   Functions & Triggers:
     - update_tld_latest_update_at() + trigger
     - get_matching_tld_ascii_name(domain_name TEXT)  ← used by domain script

   Run order: 3 of 4  (bootstrap → logging → TLD → domain)
   Depends on: 00_bootstrap_extensions.sql
   Provides to: 03_domain_objects.sql (tlds + get_matching_tld_ascii_name)
   Safe to re-run: yes (IF NOT EXISTS + CREATE OR REPLACE on functions)
   Notes:
     - Unique index (tld_ascii_name, tld_data_active_from) supports versioning.
     - Potential future FKs to tld_categories/tld_types are commented for now.
============================================================================ */

-- ========================================
-- Table: tld_categories
-- ========================================
CREATE TABLE IF NOT EXISTS tld_categories (
    tld_category_id SERIAL PRIMARY KEY,
    tld_category_identifier VARCHAR(20) UNIQUE
);

-- ========================================
-- Table: tld_types
-- ========================================
CREATE TABLE IF NOT EXISTS tld_types (
    tld_type_id SERIAL PRIMARY KEY,
    tld_type_identifier VARCHAR(20) UNIQUE
);

-- ========================================
-- Table: root
-- ========================================
CREATE TABLE IF NOT EXISTS root (
    root_id SERIAL PRIMARY KEY,
    root_services_uri JSONB DEFAULT '[]'::jsonb,
    root_tlds_uri JSONB DEFAULT '[]'::jsonb,
	root_policies_uri JSONB DEFAULT '[]'::jsonb,
	root_privacy_policy_uri DEFAULT '[]'::jsonb,
	root_lookup_endpoints_uri JSONB DEFAULT '[]'::jsonb,
    root_registrar_ids_uri DEFAULT '[]'::jsonb,
    root_function_identifiers JSONB DEFAULT 
        '[
            {"function_sequence": 10,"function_identifier": "contracting_authority"},
            {"function_sequence": 20,"function_identifier": "contract_holder"},
            {"function_sequence": 30,"function_identifier": "sponsoring_organization"},
            {"function_sequence": 40,"function_identifier": "country_code_designated_manager"},
            {"function_sequence": 50,"function_identifier": "registry_operator"},
            {"function_sequence": 60,"function_identifier": "backend_operator"}
        ]'::jsonb,
	root_tld_statuses JSONB DEFAULT 
	'{
	"ok":{"rdap_v1":"active","rdap_v2":"dns_delegated","snake_case":"dns_delegated"},
	"inactive":{"rdap_v1":"inactive","rdap_v2":"no_dns","snake_case":"no_dns"},
	"pendingCreate":{"rdap_v1":"pending create","rdap_v2":"pending_create","snake_case":"pending_create"},
	"pendingDelete":{"rdap_v1":"pending delete","rdap_v2":"pending_delete","snake_case":"pending_delete"},
	"pendingRenew":{"rdap_v1":"pending renew","rdap_v2":"pending_renew","snake_case":"pending_renew"},
	"pendingTransfer":{"rdap_v1":"pending transfer","rdap_v2":"pending_transfer","snake_case":"pending_transfer"},
	"pendingUpdate":{"rdap_v1":"pending update","rdap_v2":"pending_update","snake_case":"pending_update"},
	"clientDeleteProhibited":{"rdap_v1":"client delete prohibited","rdap_v2":"client_delete_prohibited","snake_case":"client_delete_prohibited"},
	"serverDeleteProhibited":{"rdap_v1":"server delete prohibited","rdap_v2":"server_delete_prohibited","snake_case":"server_delete_prohibited"},
	"clientHold":{"rdap_v1":"client hold","rdap_v2":"client_hold","snake_case":"client_hold"},
	"serverHold":{"rdap_v1":"server hold","rdap_v2":"server_hold","snake_case":"server_hold"},
	"clientRenewProhibited":{"rdap_v1":"client renew prohibited","rdap_v2":"client_renew_prohibited","snake_case":"client_renew_prohibited"},
	"serverRenewProhibited":{"rdap_v1":"server renew prohibited","rdap_v2":"server_renew_prohibited","snake_case":"server_renew_prohibited"},
	"clientTransferProhibited":{"rdap_v1":"client transfer prohibited","rdap_v2":"client_transfer_prohibited","snake_case":"client_transfer_prohibited"},
	"serverTransferProhibited":{"rdap_v1":"server transfer prohibited","rdap_v2":"server_transfer_prohibited","snake_case":"server_transfer_prohibited"},
	"clientUpdateProhibited":{"rdap_v1":"client update prohibited","rdap_v2":"client_update_prohibited","snake_case":"client_update_prohibited"},
	"serverUpdateProhibited":{"rdap_v1":"server update prohibited","rdap_v2":"server_update_prohibited","snake_case":"server_update_prohibited"},
	"redemptionPeriod":{"rdap_v1":"redemption period","rdap_v2":"pending_redemption","snake_case":"pending_redemption"},
	"pendingRestore":{"rdap_v1":"pending restore","rdap_v2":"pending_restore","snake_case":"pending_restore"},
	"linked":{"rdap_v1":"linked","rdap_v2":"linked","snake_case":"linked"},
	"pendingValidation":{"rdap_v1":"pending validation","rdap_v2":"pending_validation","snake_case":"pending_validation"}
	}'::jsonb,		
    root_ambiguous_rdap_statuses JSONB DEFAULT '[
		"locked",
        "renew prohibited",
        "transfer prohibited",
        "update prohibited",
        "delete prohibited",
        "removed",
        "obscured",
        "private",
        "proxy",
        "associated"
    ]'::jsonb,	
    root_lifecycle_period_ranges JSONB DEFAULT '[
        {"period_identifier": "subscription_years", "min": 1, "max": 10, "optimal": 1},
        {"period_identifier": "add_grace_days", "min": 5, "max": 5, "optimal": 5},
        {"period_identifier": "transfer_grace_days", "min": 5, "max": 5, "optimal": 5},
        {"period_identifier": "renew_grace_days", "min": 5, "max": 45, "optimal": 30},
        {"period_identifier": "post_transfer_lock_days", "min": 60, "max": 60, "optimal": 60},
        {"period_identifier": "pending_redemption_days", "min": 30, "max": 30, "optimal": 30},
        {"period_identifier": "pending_delete_days", "min": 5, "max": 5, "optimal": 5}
    ]'::jsonb,
    root_accepted_workload JSONB DEFAULT 
        '[
            {
                "public_status_requests": {
                    "max_per_utc_day": null,
                    "max_per_minute": null,
                    "max_per_second": null,
                    "caching_in_seconds": null
                },
                "public_object_requests": {
                    "max_per_utc_day": null,
                    "max_per_minute": null,
                    "max_per_second": null,
                    "caching_in_seconds": null
                }
            }
        ]'::jsonb
);

-- ========================================
-- Table: tlds (DNS root-zone TLDs)
-- ========================================
CREATE TABLE IF NOT EXISTS tlds (
    tld_id SERIAL PRIMARY KEY,
    tld_data_active_from TIMESTAMPTZ,
    tld_category VARCHAR(20) NOT NULL,
    tld_type VARCHAR(20) NOT NULL,
	tld_ascii_name CITEXT NOT NULL,
	tld_unicode_name TEXT NOT NULL,
    tld_statuses TEXT[],
	tld_storage_model TEXT[],
	tld_response_model TEXT[],
	tld_search_engine_deletion_phase_ready BOOLEAN NOT NULL DEFAULT FALSE,
	tld_services_uri JSONB DEFAULT '[]'::jsonb,
	tld_standardized_price_list_uri JSONB DEFAULT '[]'::jsonb,
    tld_delegation_uri JSONB DEFAULT '[]'::jsonb,
    tld_data_uri JSONB DEFAULT '[]'::jsonb,
    tld_data_usage_policy_uri JSONB DEFAULT '[]'::jsonb,
	tld_registry_geo_location JSONB DEFAULT '[]'::jsonb,
    tld_privacy_policy_uri JSONB DEFAULT '[]'::jsonb,
    tld_accepted_workload JSONB DEFAULT 
        '[
            {
                "public_status_requests": {
                    "max_per_utc_day": null,
                    "max_per_minute": null,
                    "max_per_second": null,
                    "caching_in_seconds": null
                },
                "public_object_requests": {
                    "max_per_utc_day": null,
                    "max_per_minute": null,
                    "max_per_second": null,
                    "caching_in_seconds": null
                }
            }
        ]'::jsonb,
    tld_relationships JSONB DEFAULT 
        '[
            {"relationship_sequence": 10, "relationship_identifier": "sponsor"},
            {"relationship_sequence": 20, "relationship_identifier": "registrant"},
			{"relationship_sequence": 30, "relationship_identifier": "actor"},
            {"relationship_sequence": 40, "relationship_identifier": "request_handling"},
            {"relationship_sequence": 50, "relationship_identifier": "issue_reporting"},
            {"relationship_sequence": 60, "relationship_identifier": "billing"},
            {"relationship_sequence": 70, "relationship_identifier": "escalation"},
            {"relationship_sequence": 80, "relationship_identifier": "reseller"},
            {"relationship_sequence": 90, "relationship_identifier": "registrar"},
            {"relationship_sequence": 95, "relationship_identifier": "abuse"}
        ]'::jsonb,
    tld_name_servers JSONB,
    tld_latest_update_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- one version per (tld_ascii_name, tld_data_active_from)
CREATE UNIQUE INDEX IF NOT EXISTS uniq_tld_effective
  ON tlds (tld_ascii_name, tld_data_active_from);

-- Trigger Function: Update tld_latest_update_at on UPDATE
CREATE OR REPLACE FUNCTION update_tld_latest_update_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.tld_latest_update_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_tld_latest_update_at ON tlds;
CREATE TRIGGER trg_update_tld_latest_update_at
BEFORE UPDATE ON tlds
FOR EACH ROW
EXECUTE FUNCTION update_tld_latest_update_at();

-- Function: get_matching_tld_ascii_name(domain_name)
-- Returns the active DNS root-zone TLD matching the final label of a domain name.
CREATE OR REPLACE FUNCTION get_matching_tld_ascii_name(domain_name TEXT)
RETURNS VARCHAR AS $$
DECLARE
    requested_tld TEXT;
    match_tld_ascii_name VARCHAR;
BEGIN
    IF domain_name IS NULL OR btrim(domain_name) = '' THEN
        RETURN NULL;
    END IF;

    requested_tld := lower(split_part(rtrim(btrim(domain_name), '.'), '.', -1));

    IF requested_tld = '' THEN
        RETURN NULL;
    END IF;

    SELECT tld_ascii_name INTO match_tld_ascii_name
    FROM tlds
    WHERE tld_ascii_name = requested_tld
      AND (tld_data_active_from IS NULL
           OR (tld_data_active_from AT TIME ZONE 'UTC') <= (CURRENT_TIMESTAMP AT TIME ZONE 'UTC'))
    ORDER BY tld_data_active_from DESC NULLS LAST
    LIMIT 1;

    RETURN match_tld_ascii_name;
END;
$$ LANGUAGE plpgsql;

-- ========================================
-- Table: lifecycles
-- ========================================
CREATE TABLE IF NOT EXISTS lifecycles (
    lifecycle_id SERIAL PRIMARY KEY,
    lifecycle_tld VARCHAR(63) NOT NULL,
    lifecycle_data_active_from TIMESTAMPTZ,
    lifecycle_upon_termination TEXT,
    lifecycle_status_meanings JSONB DEFAULT '[{
        "pending_redemption": {
            "description": "Recoverable",
            "phase": "post-expiration",
            "recoverable": true,
            "final": false
        },
        "pending_delete": {
            "description": "Final stage",
            "phase": "pre-deletion",
            "recoverable": false,
            "final": true
        }
    }]'::jsonb,
    lifecycle_operational_periods JSONB DEFAULT '[
        {"period_identifier": "subscription_years", "default": null, "allowed": null},
        {"period_identifier": "add_grace_days", "default": null, "allowed": null},
        {"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
        {"period_identifier": "renew_grace_days", "default": null, "allowed": null},
        {"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
        {"period_identifier": "pending_redemption_days", "default": null, "allowed": null},
        {"period_identifier": "pending_delete_days", "default": null, "allowed": null}
    ]'::jsonb,
    lifecycle_latest_update_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_lifecycle_data_active_from
ON lifecycles(lifecycle_tld, lifecycle_data_active_from);

-- ========================================
-- Table: function_subjects (registry/TLD function subjects)
-- ========================================
CREATE TABLE IF NOT EXISTS functions_subjects (
    function_subject_id SERIAL PRIMARY KEY,
    function_subject_handle TEXT NOT NULL UNIQUE,
    function_subject_web_id VARCHAR(34),
    function_subject_organization_name VARCHAR(511),
    function_subject_presented_name VARCHAR(511),
    function_subject_name TEXT,
    function_subject_email CITEXT,
    function_subject_phone VARCHAR(50),
    function_subject_fax VARCHAR(50),
    function_subject_country_code CHAR(2),
    function_subject_street_address TEXT,
    function_subject_city TEXT,
    function_subject_state_or_province TEXT,
    function_subject_postal_code VARCHAR(20),
    function_subject_country_name TEXT,
    function_subject_latest_update_at TIMESTAMPTZ
);

-- ========================================
-- Table: tld_functions (links tlds<->functions)
-- ========================================
CREATE TABLE IF NOT EXISTS tld_functions (
    tf_id SERIAL PRIMARY KEY,
    tf_tld INT NOT NULL REFERENCES tlds(tld_id) ON DELETE CASCADE,
    tf_function_identifier VARCHAR(50),
    tf_data_active_from TIMESTAMPTZ,
	tf_field_publication JSONB,
    tf_function INT NOT NULL REFERENCES function_subjects(function_subject_id) ON DELETE CASCADE,
    tf_latest_update_at TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_tf_tld ON tld_functions(tf_tld);
CREATE INDEX IF NOT EXISTS idx_tf_function ON tld_functions(tf_function);
CREATE UNIQUE INDEX IF NOT EXISTS uniq_tld_function_start
    ON tld_functions(tf_tld, tf_function_identifier, tf_data_active_from);