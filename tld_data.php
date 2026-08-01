<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//$_GET['tld'] = 'nl';
//$_GET['tld'] = 'vermögensberater';

if (!empty($_GET['tld']))	{
	if (strlen($_GET['tld']))	{
		$tld = mb_strtolower($_GET['tld']);
		$tld = str_replace('http://','', $tld);
		$tld = str_replace('https://','', $tld);
		if (substr_count($tld, '.') > 1)	{
			$tld = str_replace('www.','', $tld);
		}
		$strpos = mb_strpos($tld, '/');
		if ($strpos)	{
			$tld = mb_substr($tld, 0, $strpos);
		}
		$strpos = mb_strpos($tld, ':');
		if ($strpos)	{
			$tld = mb_substr($tld, 0, $strpos);
		}
		$tld = value_to_ascii($tld);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(write_file($tld), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		die();
	}
	else	{	
		die("No tld name is filled as input");	
	}
}
else	{	
	die("No tld name variable as input");
}

function detect_country_code($inputdefault, $inputCC, $inputcc)	{	
	$outputcc = $inputdefault;
	if (strlen($inputCC))	$outputcc = $inputCC.' "CC"=>"cc"';
	if (strlen($inputcc))	$outputcc = $inputcc;
	return $outputcc;
}

function value_to_ascii(string $inputvalue): string	{
    $ascii = idn_to_ascii($inputvalue, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    return $ascii !== false ? strtolower($ascii) : strtolower($inputtld);
}

function value_to_unicode(string $inputvalue): string	{
    $unicode = idn_to_utf8($inputvalue, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    return $unicode !== false ? $unicode : $inputtld;
}

function write_file($inputtld)	{
	
$tld_root_data_uri = 'https://rdap.iana.org/domain/'.$inputtld;
$obj = json_decode(file_get_contents($tld_root_data_uri), true);
$governance_notices = '';	
$tld_links = '';	
$tld_ascii_name = $obj['ldhName'];
$tld_unicode_name = $obj['unicodeName'];		
$tld_statuses = '';	
$tld_storage_model = '';
$tld_response_model = '';
$nameservers_handles = '';
$nameservers_ascii = '';
$nameservers_unicode = '';
$nameservers_ipv4 = '';
$nameservers_ipv6 = '';
$nameservers_statuses = '';	
$nameservers_rdap_dnssec_signed = '';
$nameservers_rdap_ds_key_tags = '';	
$nameservers_rdap_ds_algorithm_numbers = '';
$nameservers_rdap_ds_digest_types = '';
$nameservers_rdap_ds_digests = '';
$entity_registrant = -1;
$tld_registrant_full_name = '';
foreach ($obj['entities'] ?? [] as $entity_index => $entity) {
	foreach ($entity['roles'] ?? [] as $role) {
    	if ($role === 'registrant') {
        	$entity_registrant = $entity_index;
        	break 2;
        }
    }
}
if ($entity_registrant >= 0 && isset($obj['entities'][$entity_registrant]['vcardArray'][1])) {
	foreach ($obj['entities'][$entity_registrant]['vcardArray'][1] as $vcardField) {
    	if (($vcardField[0] ?? '') === 'fn') {
        	$tld_registrant_full_name = $vcardField[3] ?? '';
    	    break;
	    }
    }
}		
foreach($obj as $key1 => $value1) {
	if ($key1 == 'status')	{	
		$tld_statuses .= (is_array($value1)) ? implode(",<br />", $value1) : $value1;
	}
	foreach($value1 as $key2 => $value2) {
		if ($key1 == 'secureDNS')	{
			if ($key2 == 'delegationSigned') {
				if ($value2 === true)	{
					$nameservers_rdap_dnssec_signed .= 'Yes'."<br />";
				}	
				elseif ($value2 === false)	{
					$nameservers_rdap_dnssec_signed .= 'No'."<br />";
				}
				else	{
					$nameservers_rdap_dnssec_signed .= 'Not Applicable'."<br />";					
				}	
			}
		}	
		foreach($value2 as $key3 => $value3) {
			if ($key1 == 'notices')	{
				if (!is_array($value3))	{
					$governance_notices .= $key2.': '.$key3.': '.$value3."<br />";		
				}
			}				
			if ($key1 == 'links')	{
				$tld_links .= $key2.': '.$key3.': '.$value3."<br />";					
			}
			if ($key1 == 'nameservers')	{
				if ($key3 == 'handle') {
					$nameservers_handles .= $key2.': '.$value3."<br />";
				}
				elseif ($key3 == 'ldhName') {
					$nameservers_ascii .= $key2.': '.$value3."<br />";
				}
				elseif ($key3 == 'unicodeName')	{
					$nameservers_unicode .= $key2.': '.$value3."<br />";
				}
				elseif ($key3 == 'status')	{
					$nameservers_statuses .= $key2.': '.$value3[0]."<br />";	
				}
			}
			if ($key1 == 'secureDNS')	{
				if ($key2 == 'dsData') {
					$nameservers_rdap_ds_key_tags .= $key3.': '.$value3['keyTag']."<br />";	
					$nameservers_rdap_ds_algorithm_numbers .= $key3.': '.$value3['algorithm']."<br />";	
					$nameservers_rdap_ds_digest_types .= $key3.': '.$value3['digestType']."<br />";	
					$nameservers_rdap_ds_digests .= $key3.': '.$value3['digest']."<br />";
				}				
			}
			foreach($value3 as $key4 => $value4) {
				if ($key1 == 'notices')	{
					if (!is_array($value4))	{
						$governance_notices .= $key2.': '.$key3.': '.$key4.': '.$value4."<br />";				
					}
				}
				foreach($value4 as $key5 => $value5) {
					if ($key1 == 'notices')	{
						if (!is_array($value5))	{
							$governance_notices .= $key2.': '.$key3.': '.$key4.': '.$key5.': '.$value5."<br />";
						}	
					}
					if ($key1 == 'nameservers')	{							
						if ($key3 == 'ipAddresses') {
							if ($key4 == 'v4') {
								$nameservers_ipv4 .= $key2.': '.$value5."<br />";
							}
							elseif ($key4 == 'v6') {
								$nameservers_ipv6 .= $key2.': '.$value5."<br />";
							}
						}														
					}
					foreach($value5 as $key6 => $value6) {
						if ($key1 == 'notices')	{
							if (!is_array($value6))	{
								$governance_notices .= $key2.': '.$key3.': '.$key4.': '.$key5.': '.$key6.': '.$value6."<br />";
							}	
						}	
					}
				}
			}
		}
	}
}
$governance_services_uri = 'https://www.icann.org';
$governance_policies_uri = 'https://www.icann.org/en/data-protection/terms-of-service';
$governance_privacy_policy_uri = 'https://www.icann.org/privacy/policy';	
$governance_registrar_accreditation_uri = 'https://www.icann.org/en/contracted-parties/accredited-registrars';
$root_services_uri = 'https://www.iana.org';	
$root_tlds_uri = 'https://www.iana.org/domains/root/db';
$root_registrar_ids_uri = 'https://www.iana.org/assignments/registrar-ids/registrar-ids.xhtml';	
$root_lookup_endpoints_uri = 'https://data.iana.org/rdap/dns.json';
$tld_data_active_from = null;	
$tld_category = '';
$tld_type = '';
$tld_services_uri = '';	
$tld_data_usage_policy_uri = '';
$tld_privacy_policy_uri = '';	
$tld_search_engine_deletion_phase_ready = 'n/a';	
$function_json = '[]';
$function_json = '[
{"function_sequence": 10,"function_identifier": "contracting_authority"},
{"function_sequence": 20,"function_identifier": "contract_holder"},
{"function_sequence": 30,"function_identifier": "sponsoring_organization"},
{"function_sequence": 40,"function_identifier": "country_code_designated_manager"},
{"function_sequence": 50,"function_identifier": "registry_operator"},
{"function_sequence": 60,"function_identifier": "backend_operator"}]';
$decoded = json_decode($function_json, true);
usort($decoded, function ($a, $b) {
    return $a['function_sequence'] <=> $b['function_sequence'];
});
$function_identifiers = '<b>function_sequence, function_identifier</b><br />';    
foreach ($decoded as $function) {
	$function_identifiers .= $function['function_sequence'] . ', ' . $function['function_identifier'] . '<br />';
}
$ambiguous_rdap_statuses = implode('<br />', [
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
]);
$lifecycle_period_ranges_json = '[
	{"period_identifier": "subscription_years", "min": 1, "max": 10, "optimal": 1},
	{"period_identifier": "add_grace_days", "min": 5, "max": 5, "optimal": 5},
	{"period_identifier": "transfer_grace_days", "min": 5, "max": 5, "optimal": 5},
	{"period_identifier": "renew_grace_days", "min": 5, "max": 45, "optimal": 30},
	{"period_identifier": "post_transfer_lock_days", "min": 60, "max": 60, "optimal": 60},
	{"period_identifier": "pending_redemption_days", "min": 30, "max": 30, "optimal": 30},
	{"period_identifier": "pending_delete_days", "min": 5, "max": 5, "optimal": 5}
]';
$decoded = json_decode($lifecycle_period_ranges_json, true);	
$lifecycle_period_ranges = '';    
foreach ($decoded as $period) {
	$lifecycle_period_ranges .= $period['period_identifier'] . ': min ' . $period['min'] . ', max ' . $period['max'] .  ', optimal ' . $period['optimal'] . '<br />';
}	
$root_accepted_workload_json = '[{
	"public_status_requests": {
		"maximum_per_utc_day": null,
		"maximum_per_minute": null,
		"maximum_per_second": null,
		"caching_in_seconds": null
	},
 		"public_object_requests": {
   		"maximum_per_utc_day": null,
   		"maximum_per_minute": null,
   		"maximum_per_second": null,
   		"caching_in_seconds": null
	}
}]';
$decoded = json_decode($root_accepted_workload_json, true);
$root_accepted_workload = '';    
foreach ($decoded as $workload_entry) {
    foreach ($workload_entry as $request_type => $limits) {
        $root_accepted_workload .= '<b>&bull; </b><em>' . htmlspecialchars($request_type) . ':</em><br />';
        foreach ($limits as $limit_type => $limit_value) {
            $display_value = $limit_value !== null ? htmlspecialchars($limit_value) : 'n/a';
            $root_accepted_workload .= htmlspecialchars($limit_type) . ': ' . $display_value . '<br />';
        }	
    }	
}	
$lifecycle_data_active_from = null;	
$upon_termination = 'TLD-specific regulation';
$operational_periods_json = '[
	{"period_identifier": "subscription_years", "default": null, "allowed": null},
	{"period_identifier": "add_grace_days", "default": null, "allowed": null},
	{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
	{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
	{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
	{"period_identifier": "pending_redemption_days", "default": null, "allowed": null},
	{"period_identifier": "pending_delete_days", "default": null, "allowed": null}
]';
$tld_accepted_workload_json = '[{
	"public_status_requests": {
		"maximum_per_utc_day": null,
		"maximum_per_minute": null,
		"maximum_per_second": null,
		"caching_in_seconds": null
	},
 		"public_object_requests": {
   		"maximum_per_utc_day": null,
   		"maximum_per_minute": null,
   		"maximum_per_second": null,
   		"caching_in_seconds": null
	}
}]';	
if ($inputtld == 'nl')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$upon_termination = "40-day quarantine for .nl";
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": 1, "allowed": [1]},
		{"period_identifier": "add_grace_days", "default": null, "allowed": null},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
		{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
		{"period_identifier": "pending_redemption_days", "default": 40, "allowed": [40]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "Stichting Internet Domeinregistratie Nederland", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "Stichting Internet Domeinregistratie Nederland", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "SIDN B.V.", "function_presented_name": "SIDN"}
    ]';
	$tld_accepted_workload_json = '[{
		"public_status_requests": {
    		"maximum_per_utc_day": 50000,
    		"maximum_per_minute": null,
    		"maximum_per_second": 10,
    		"caching_in_seconds": 420
  		},
  		"public_object_requests": {
    		"maximum_per_utc_day": 2000,
    		"maximum_per_minute": null,
    		"maximum_per_second": 1,
    		"caching_in_seconds": 60
  		}
	}]';
	$tld_data_usage_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/general-terms-and-conditions-for-nl-registrants';
	$tld_privacy_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/sidn-and-privacy';
	$tld_services_uri = 'https://www.sidn.nl/en/theme/domain-names';
}
elseif ($inputtld == 'frl')	{
	$tld_category = 'gTLD';
	$tld_type = 'geoTLD';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": "FRLregistry B.V.", "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "FRLregistry B.V.", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "Team Internet Group PLC", "function_presented_name": "CTO CentralNic"}
    ]';
	$tld_data_usage_policy_uri = 'https://nic.frl/';
	$tld_services_uri = 'https://nic.frl/';
}
elseif ($inputtld == 'amsterdam')	{
	$tld_category = 'gTLD';
	$tld_type = 'geoTLD';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": "Gemeente Amsterdam", "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "Stichting Internet Domeinregistratie Nederland", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "SIDN B.V.", "function_presented_name": "SIDN"}
    ]';
	$tld_data_usage_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/general-terms-and-conditions-for-nl-registrants';
	$tld_privacy_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/sidn-and-privacy';
	$tld_services_uri = 'https://www.sidn.nl/en/theme/domain-names';
	$registrant_web_id = 'NL88COMM01234567890123456789012345';
}
elseif ($inputtld == 'politie')	{
	$tld_category = 'gTLD';
	$tld_type = 'Brand gTLD';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": "Politie Nederland", "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "Stichting Internet Domeinregistratie Nederland", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "SIDN B.V.", "function_presented_name": "SIDN"}
    ]';
	$tld_data_usage_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/general-terms-and-conditions-for-nl-registrants';
	$tld_privacy_policy_uri = 'https://www.sidn.nl/en/nl-domain-name/sidn-and-privacy';
	$tld_services_uri = 'https://www.sidn.nl/en/theme/domain-names';
	$registrant_web_id = 'NL88COMM01234567890123456789012345';
}
elseif ($inputtld == 'eu')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$upon_termination = "40-day quarantine for .eu";
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": 1, "allowed": [1]},
		{"period_identifier": "add_grace_days", "default": 5, "allowed": [5]},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
		{"period_identifier": "post_transfer_lock_days", "default": 60, "allowed": [60]},
		{"period_identifier": "pending_redemption_days", "default": 40, "allowed": [40]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "EURid vzw/asbl", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "EURid vzw", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "EURid vzw", "function_presented_name": "Technical Department"}
    ]';
	$tld_data_usage_policy_uri = 'https://help.eurid.eu/hc/en-gb/';
	$tld_services_uri = 'https://help.eurid.eu/hc/en-gb/';
}
elseif ($inputtld == 'de')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": null, "allowed": null},
		{"period_identifier": "add_grace_days", "default": null, "allowed": null},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
		{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
		{"period_identifier": "pending_redemption_days", "default": 28, "allowed": [28]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "DENIC eG", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "DENIC eG", "function_presented_name": "Vorstand"},
		{"function_identifier": "backend_operator", "function_legal_name": "DENIC eG", "function_presented_name": "Business Services"}
    ]';
	$tld_data_usage_policy_uri = 'https://www.denic.de/';
	$tld_services_uri = 'https://www.denic.de/';
}
elseif ($inputtld == 'fr')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": null, "allowed": null},
		{"period_identifier": "add_grace_days", "default": null, "allowed": null},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
		{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
		{"period_identifier": "pending_redemption_days", "default": 30, "allowed": [30]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "Association Française pour le Nommage Internet en Coopération", "function_presented_name": "A.F.N.I.C."},
        {"function_identifier": "registry_operator", "function_legal_name": "Association Française pour le Nommage Internet en Coopération", "function_presented_name": "A.F.N.I.C."},
		{"function_identifier": "backend_operator", "function_legal_name": "Association Française pour le Nommage Internet en Coopération", "function_presented_name": "A.F.N.I.C."}
    ]';	
	$tld_data_usage_policy_uri = 'https://www.afnic.fr/';
	$tld_services_uri = 'https://www.afnic.fr/';
}
elseif ($inputtld == 'ch')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": null, "allowed": null},
		{"period_identifier": "add_grace_days", "default": null, "allowed": null},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": null, "allowed": null},
		{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
		{"period_identifier": "pending_redemption_days", "default": 40, "allowed": [40]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "SWITCH Foundation", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "SWITCH Foundation", "function_presented_name": "The Swiss Education & Research Network"},
		{"function_identifier": "backend_operator", "function_legal_name": "SWITCH Foundation", "function_presented_name": "The Swiss Education & Research Network"}
    ]';
	$tld_data_usage_policy_uri = 'https://www.nic.ch/';
	$tld_services_uri = 'https://www.nic.ch/';
}	
elseif ($inputtld == 'li')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "SWITCH Foundation", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "SWITCH Foundation", "function_presented_name": "The Swiss Education & Research Network"},
		{"function_identifier": "backend_operator", "function_legal_name": "SWITCH Foundation", "function_presented_name": "The Swiss Education & Research Network"}
    ]';
	$tld_data_usage_policy_uri = 'https://www.nic.li/';
	$tld_services_uri = 'https://www.nic.li/';
}
elseif ($inputtld == 'be')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "DNS Belgium vzw/asbl", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "DNS Belgium vzw/asbl", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "DNS Belgium vzw/asbl", "function_presented_name": null}
    ]';
	$tld_data_usage_policy_uri = 'https://www.dnsbelgium.be/';
	$tld_services_uri = 'https://www.dnsbelgium.be/';
}
elseif ($inputtld == 'lu')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "RESTENA", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "Fondation RESTENA", "function_presented_name": null},
		{"function_identifier": "backend_operator", "function_legal_name": "Fondation RESTENA", "function_presented_name": "NOC"}
    ]';
	$tld_data_usage_policy_uri = 'https://restena.lu/';
	$tld_services_uri = 'https://restena.lu/';
}
elseif ($inputtld == 'uk')	{
	$tld_category = 'ccTLD';
	$tld_type = 'ccTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": null, "allowed": null},
		{"period_identifier": "add_grace_days", "default": null, "allowed": null},
		{"period_identifier": "transfer_grace_days", "default": null, "allowed": null},
		{"period_identifier": "renew_grace_days", "default": 30, "allowed": [30]},
		{"period_identifier": "post_transfer_lock_days", "default": null, "allowed": null},
		{"period_identifier": "pending_redemption_days", "default": 60, "allowed": [60]},
		{"period_identifier": "pending_delete_days", "default": 0, "allowed": [0]}
	]';
	$function_json = '[
        {"function_identifier": "contracting_authority", "function_legal_name": null, "function_presented_name": null},
		{"function_identifier": "contract_holder", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": "Nominet UK", "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "Nominet UK", "function_presented_name": "TLD Registry Services Management"},
		{"function_identifier": "backend_operator", "function_legal_name": "Nominet UK", "function_presented_name": "TLD Registry Services Technical"}
    ]';
	$tld_data_usage_policy_uri = 'https://nominet.uk/';
	$tld_services_uri = 'https://nominet.uk/';
}
elseif ($inputtld == 'com')	{
	$tld_category = 'gTLD';
	$tld_type = 'gTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": 1, "allowed": [1,10]},
		{"period_identifier": "add_grace_days", "default": 5, "allowed": [5]},
		{"period_identifier": "transfer_grace_days", "default": 5, "allowed": [5]},
		{"period_identifier": "renew_grace_days", "default": 45, "allowed": [45]},
		{"period_identifier": "post_transfer_lock_days", "default": 60, "allowed": [60]},
		{"period_identifier": "pending_redemption_days", "default": 30, "allowed": [30]},
		{"period_identifier": "pending_delete_days", "default": 5, "allowed": [5]}
	]';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": "Internet Corporation for Assigned Names and Numbers", "function_presented_name": "ICANN"},
        {"function_identifier": "contract_holder", "function_legal_name": "VeriSign Global Registry Services", "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "registry_operator", "function_legal_name": "VeriSign Global Registry Services", "function_presented_name": "Registry Customer Service"},
		{"function_identifier": "backend_operator", "function_legal_name": "VeriSign Global Registry Services", "function_presented_name": "Registry Customer Service"}
    ]';	
	$tld_data_usage_policy_uri = 'https://www.icann.org/privacy/tos';
	$tld_privacy_policy_uri = 'https://www.icann.org/privacy/policy';
	$tld_services_uri = 'https://www.verisigninc.com/';
}
elseif ($inputtld == 'org')	{
	$tld_category = 'gTLD';
	$tld_type = 'gTLD';
	$operational_periods_json = '[
		{"period_identifier": "subscription_years", "default": 1, "allowed": [1,10]},
		{"period_identifier": "add_grace_days", "default": 5, "allowed": [5]},
		{"period_identifier": "transfer_grace_days", "default": 5, "allowed": [5]},
		{"period_identifier": "renew_grace_days", "default": 45, "allowed": [45]},
		{"period_identifier": "post_transfer_lock_days", "default": 60, "allowed": [60]},
		{"period_identifier": "pending_redemption_days", "default": 30, "allowed": [30]},
		{"period_identifier": "pending_delete_days", "default": 5, "allowed": [5]}
	]';
	$function_json = '[
		{"function_identifier": "contracting_authority", "function_legal_name": "Internet Corporation for Assigned Names and Numbers", "function_presented_name": "ICANN"},
        {"function_identifier": "contract_holder", "function_legal_name": "Public Interest Registry (PIR)", "function_presented_name": null},
        {"function_identifier": "sponsoring_organization", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "country_code_designated_manager", "function_legal_name": null, "function_presented_name": null},
        {"function_identifier": "registry_operator", "\legal_name": "Public Interest Registry (PIR)", "function_presented_name": "Director of Operations, Compliance and Customer Support"},
		{"function_identifier": "backend_operator", "function_legal_name": "Public Interest Registry (PIR)", "function_presented_name": "Senior Director, DNS Infrastructure Group"}
    ]';	
	$tld_data_usage_policy_uri = 'https://www.icann.org/privacy/tos';
	$tld_privacy_policy_uri = 'https://www.icann.org/privacy/policy';
	$tld_services_uri = '';
}
$governance_delegation_uri = 'https://www.iana.org/domains/root/db/'.$inputtld.'.html';		
$decoded = json_decode($function_json, true);
$tld_functions = '';   
foreach ($decoded as $function) {
	if (strlen($function['function_legal_name']) or strlen($function['function_presented_name']))	{	
		$tld_functions .= '<b>&bull; </b><em>'.$function['function_identifier'] . ':</em><br />';
		if (strlen($function['function_legal_name']))	{
			$tld_functions .= 'legal_name: ' . $function['function_legal_name'] . '<br />';
		}
		if (strlen($function['function_presented_name']))	{
			$tld_functions .= 'presented_name: ' . $function['function_presented_name'] . '<br />';
		}
	}	
}
$decoded = json_decode($operational_periods_json, true);
$operational_periods = '';
foreach ($decoded as $period) {
	$operational_periods .= $period['period_identifier'] . ':';
	if ($period['default'] !== null)	{
		$operational_periods .= ' default ';
		$operational_periods .= (is_array($period['default'])) ? implode(',', $period['default']) : $period['default'];
	}
	if ($period['allowed'] !== null)	{
		$operational_periods .= ', allowed ';
		$operational_periods .= (is_array($period['allowed'])) ? implode(',', $period['allowed']): $period['allowed'];
	}
	if ($period['default'] == null and $period['allowed'] == null)	{
		$operational_periods .= ' n/a';
	}	
	$operational_periods .= '<br />';
}	
$resource_upload_at = null;
$status_meanings_json = '[
    {
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
    }
]';
		//"transfer prohibited": {
        //   "description": "server_transfer_prohibited",
        //    "phase": "active"
        //},
		//"locked": {
        //    "description": "server_protected_state",
        //    "phase": "active"
        //},		
		//"inactive": {
        //    "description": "dns_glue_tld_nameservers",
        //    "phase": "active"
        //},		
		//"excluded": {
        //    "description": "server_registration_restricted",
		//	"phase": "inactive"
        //}	
	
	
$decoded = json_decode($status_meanings_json, true);
$status_meanings = '';
foreach ($decoded as $statuses) {
    foreach ($statuses as $key => $value) {	//ucwords()
        $status_meanings .= '"' . htmlspecialchars($key) . '": ';
        $status_meanings .= htmlspecialchars($value['description']);
        $status_meanings .= " (" . htmlspecialchars($value['phase']) . " phase)<br />";
    }
}	
	
$decoded = json_decode($tld_accepted_workload_json, true);
$tld_accepted_workload = '';    
foreach ($decoded as $workload_entry) {
    foreach ($workload_entry as $request_type => $limits) {
        $tld_accepted_workload .= '<b>&bull; </b><em>' . htmlspecialchars($request_type) . ':</em><br />';
        foreach ($limits as $limit_type => $limit_value) {
            $display_value = $limit_value !== null ? htmlspecialchars($limit_value) : 'n/a';
            $tld_accepted_workload .= htmlspecialchars($limit_type) . ': ' . $display_value . '<br />';
        }	
    }	
}
$tld_relationships_json = '[{"tld_relationship_sequence": 10,"tld_relationship_identifier": "sponsor"},
{"tld_relationship_sequence": 20,"tld_relationship_identifier": "registrant"},
{"tld_relationship_sequence": 30,"tld_relationship_identifier": "actor"},
{"tld_relationship_sequence": 40,"tld_relationship_identifier": "request_handling"},
{"tld_relationship_sequence": 50,"tld_relationship_identifier": "issue_reporting"},
{"tld_relationship_sequence": 60,"tld_relationship_identifier": "billing"},
{"tld_relationship_sequence": 70,"tld_relationship_identifier": "escalation"},
{"tld_relationship_sequence": 80,"tld_relationship_identifier": "reseller"},
{"tld_relationship_sequence": 90,"tld_relationship_identifier": "registrar"},
{"tld_relationship_sequence": 95,"tld_relationship_identifier": "abuse"}]';
$decoded = json_decode($tld_relationships_json, true);
usort($decoded, function ($a, $b) {
    return $a['tld_relationship_sequence'] <=> $b['tld_relationship_sequence'];
});
$tld_relationships = '<b>relationship_sequence, relationship_identifier</b><br />';    
foreach ($decoded as $relationship) {
	$tld_relationships .= $relationship['tld_relationship_sequence'] . ', ' . $relationship['tld_relationship_identifier'] . '<br />';
}
switch ($inputtld) {
	case 'com':
	case 'net':
		$tld_storage_model = 'thin';
		$tld_response_model = 'thin';
		break;
	case 'org':
		$tld_storage_model = 'thick';
		$tld_response_model = 'delegated';
		break;
	case 'tld':
		$tld_storage_model = '';
		$tld_response_model = '';
		break;		
	default:
		$tld_storage_model = 'thick';
		$tld_response_model = 'thick';
}
	
$arr = array();
	
$arr[$inputtld]['governance']['notices'] = $governance_notices;
	
$arr[$inputtld]['governance']['services_uri'] = $governance_services_uri;	
$arr[$inputtld]['governance']['policies_uri'] = $governance_policies_uri;
$arr[$inputtld]['governance']['privacy_policy_uri'] = $governance_privacy_policy_uri;
$arr[$inputtld]['governance']['delegation_uri'] = $governance_delegation_uri;	
$arr[$inputtld]['governance']['registrar_accreditation_uri'] = $governance_registrar_accreditation_uri;
		
$arr[$inputtld]['root']['services_uri'] = $root_services_uri;
$arr[$inputtld]['root']['tlds_uri'] = $root_tlds_uri;
$arr[$inputtld]['root']['registrar_ids_uri'] = $root_registrar_ids_uri;	
$arr[$inputtld]['root']['lookup_endpoints_uri'] = $root_lookup_endpoints_uri;
	
$arr[$inputtld]['root']['function_identifiers'] = $function_identifiers;
$arr[$inputtld]['root']['ambiguous_rdap_statuses'] = $ambiguous_rdap_statuses;
$arr[$inputtld]['root']['lifecycle_period_ranges'] = $lifecycle_period_ranges;		
$arr[$inputtld]['root']['accepted_workload'] = $root_accepted_workload;	
	
$arr[$inputtld]['tld']['links'] = $tld_links;

$arr[$inputtld]['tld']['ascii_name'] = $inputtld;
$arr[$inputtld]['tld']['unicode_name'] = value_to_unicode($inputtld);	
$arr[$inputtld]['tld']['data_active_from'] = $tld_data_active_from;	
$arr[$inputtld]['tld']['category'] = $tld_category;
$arr[$inputtld]['tld']['type'] = $tld_type;
$arr[$inputtld]['tld']['ascii_name'] = $tld_ascii_name;
$arr[$inputtld]['tld']['unicode_name'] = $tld_unicode_name;	
$arr[$inputtld]['tld']['statuses'] = $tld_statuses;
$arr[$inputtld]['tld']['storage_model'] = $tld_storage_model;
$arr[$inputtld]['tld']['response_model'] = $tld_response_model;
$arr[$inputtld]['tld']['search_engine_deletion_phase_ready'] = $tld_search_engine_deletion_phase_ready;
	
$arr[$inputtld]['tld']['services_uri'] = $tld_services_uri;	
$arr[$inputtld]['tld']['root_data_uri'] = $tld_root_data_uri;
$arr[$inputtld]['tld']['registry_data_uri'] = '';
$arr[$inputtld]['tld']['data_usage_policy_uri'] = $tld_data_usage_policy_uri;
$arr[$inputtld]['tld']['privacy_policy_uri'] = $tld_privacy_policy_uri;

$arr[$inputtld]['tld']['registrant_full_name'] = $tld_registrant_full_name;
$arr[$inputtld]['tld']['functions'] = $tld_functions;	
$arr[$inputtld]['tld']['accepted_workload'] = $tld_accepted_workload;
$arr[$inputtld]['tld']['relationships'] = $tld_relationships;

$arr[$inputtld]['lifecycle']['data_active_from'] = $lifecycle_data_active_from;
$arr[$inputtld]['lifecycle']['upon_termination'] = $upon_termination;
$arr[$inputtld]['lifecycle']['status_meanings'] = $status_meanings;	
$arr[$inputtld]['lifecycle']['operational_periods'] = $operational_periods;
	
$arr[$inputtld]['nameservers']['handles'] = $nameservers_handles;
$arr[$inputtld]['nameservers']['ascii_names'] = $nameservers_ascii;
$arr[$inputtld]['nameservers']['unicode_names'] = $nameservers_unicode;	
$arr[$inputtld]['nameservers']['ipv4_addresses'] = $nameservers_ipv4;	
$arr[$inputtld]['nameservers']['ipv6_addresses'] = $nameservers_ipv6;
$arr[$inputtld]['nameservers']['statuses'] = $nameservers_statuses;
$arr[$inputtld]['nameservers']['rdap_dnssec_signed'] = $nameservers_rdap_dnssec_signed;
$arr[$inputtld]['nameservers']['rdap_ds_key_tags'] = $nameservers_rdap_ds_key_tags;
$arr[$inputtld]['nameservers']['rdap_ds_algorithm_numbers'] = $nameservers_rdap_ds_algorithm_numbers;
$arr[$inputtld]['nameservers']['rdap_ds_digest_types'] = $nameservers_rdap_ds_digest_types;
$arr[$inputtld]['nameservers']['rdap_ds_digests'] = $nameservers_rdap_ds_digests;

return $arr;
}
}?>