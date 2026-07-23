<?php
session_start();  // A clean, stateless environment works without a sleep command.
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$datetime = new DateTime('now', new DateTimeZone('UTC'));
$utc = $datetime->format('Y-m-d H:i:s');
if (!empty($_GET["language"]))	{
	$_GET["language"] = intval($_GET["language"]);
	$viewlanguage = $_GET["language"];
}
if (empty($_GET["language"]))	{	
	$browserlanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	switch ($browserlanguage) {
		case 'nl':
	 		$viewlanguage = 1;
	 		break;
		case 'de':
	 		$viewlanguage = 3;
			break;
		case 'fr':
  			$viewlanguage = 4;
			break;
		default:
   			$viewlanguage = 2;
	}
}
if (!empty(trim($_GET['domain'])))	{
	$_GET["domain"] = trim($_GET['domain']);
	$_GET["domain"] = str_replace("'", "", $_GET["domain"]);
	$vd = mb_strtolower($_GET["domain"]);
	$vd = str_replace('http://','', $vd);
	$vd = str_replace('https://','', $vd);
	if (substr_count($vd, '.') > 1)	{
		$vd = str_replace('www.','', $vd);
	}
	$strpos = mb_strpos($vd, '/');
	if ($strpos)	{
		$vd = mb_substr($vd, 0, $strpos);
	}
	$strpos = mb_strpos($vd, ':');
	if ($strpos)	{
		$vd = mb_substr($vd, 0, $strpos);
	}
}
else	{
	$vd = 'domain';
}	
if (empty([ip]) or empty([block]))	{
	[ip] = getClientIP();
	[block] = get_block([ip]);
}

$internal = (str_contains([block],'Freedom')) ? '_internal_' : '';
$log_file = "/home/admin/logging/" . $internal . "domain_tool_" . $datetime->format('Ym') . ".txt";
$log_line = $datetime->format('Y-m-d H:i:s') . " UTC, lang" . $viewlanguage . ", " . $vd . ", " . [ip] . ", " . [block] . "\n";
file_put_contents($log_file, $log_line, FILE_APPEND);
echo '<!DOCTYPE html><html lang="en" style="font-size: 90%">
<head><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8"><meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="robots" content="index">
<title>Domain Information</title><style>.top-align td {vertical-align: top;}</style>';
?><script>	
	
function SwitchDisplay(type) {
	if (type == 11)	{ // notice
		var pre = '11';
		var max = 1
	}
	else if (type == 12)	{ // links
		var pre = '12';
		var max = 1
	}
	else if (type == 13)	{ // verification state
		var pre = '13';
		var max = 1
	}
	else if (type == 14)	{ // publication state
		var pre = '14';
		var max = 1
	}
	else if (type == 15)	{ // publication states
		var pre = '15';
		var max = 10
	}
	else if (type == 20)	{ // metadata
		var pre = '20';
		var max = 11
	}
	else if (type == 30)	{ // domain properties
		var pre = '30';
		var max = 24
	}
	else if (type == 39)	{ // sponsor
		var pre = '39';
		var max = 25
	}
	else if (type == 40)	{ // registrant
		var pre = '40';
		var max = 22
	}
	else if (type == 41)	{ // actor
		var pre = '41';
		var max = 9
	}
	else if (type == 42)	{ // request handling
		var pre = '42';
		var max = 19
	}
	else if (type == 43)	{ // issue_reporting
		var pre = '43';
		var max = 19
	}
	else if (type == 44)	{ // billing
		var pre = '44';
		var max = 21
	}
	else if (type == 45)	{ // escalation
		var pre = '45';
		var max = 10
	}
	else if (type == 50)	{ // reseller
		var pre = '50';
		var max = 25
	}	
	else if (type == 60)	{ // registrar
		var pre = '60';
		var max = 25
	}
	else if (type == 61)	{ // registrar abuse
		var pre = '61';
		var max = 10
	}
	else if (type == 63)	{ // nameservers
		var pre = '63';
		var max = 19
	}
	else if (type == 75)	{ // raw rdap data
		var pre = '75';
		var max = 1
	}
	else	{
		return;	
	}
	
	for (let i = 1; i <= max; i++) {
		var id = pre + i.toString();
		if (typeof(document.getElementById(id)) != 'undefined' && document.getElementById(id) != null )	{
			if (document.getElementById(id).style.display == "table-row")	{
				document.getElementById(id).style.display = "none";	
			}
			else	{
				document.getElementById(id).style.display = "table-row";
			}
		}
	}
		
	function echo( ...s )	{
   		for(var i = 0; i < s.length; i++ ) {
    		document.write(s[i] + ' ');
		}
	}
}
		
const publication_state = `Legacy Limitations
1. The current "name.description", "name.type", and "type" alternatives may result in differing visibility representation across registry and registrar RDAP.
2. Current RDAP "redacted" semantics can evolve into relationship-specific field names such as "registrant_contact_uri" and appropriate "publication_state" representation.
3. Relationship responsibility order is not inherent in RDAP JSON representation.
4. Contact endpoints may be represented in email fields even when the value is not an email address, resulting in ambiguous semantics and reduced interoperability.
5. Registry RDAP and registrar RDAP are distinct sources; "Server policy" does not indicate which applies.
6. Legacy subject-level hyperlinks may not consistently express relationship-specific data visibility.

Model Principles
Inclusion of publication details depends on an actual RDAP service's data structure and representation choices.
RDAP output MUST preserve relationship context and MUST NOT combine data from distinct relationships.

Definition (Proposed)
A subject is a natural person or organization with one or more relationship responsibilities. A subject identifier is a unique reference assigned to a registered subject and prefixed by the issuing jurisdiction.
The "publication_state" member MUST be included within relationship entries for which subject data is actually stored and MAY provide publication state for any subset of those fields, including none or all.
Each publication state MUST contain exactly one enumerated value.
When data is unavailable or not disclosed, placeholder values MUST NOT be used.
If relationship-specific data is present, that data MUST precede "publication_state".

Required Output Naming and Human-Readable Ordering
Member names defined by this specification MUST use snake_case.
Top-level members: "metadata", "domain", "relationships", "nameservers", "dns_security".
Relationship responsibilities: "sponsor", "registrant", "actor", "request_handling", "issue_reporting", "billing", "escalation", "reseller", "registrar", "registrar_abuse".

Operational Guidance
Field values SHOULD conform to the semantics of the field in which they are represented.
Registry communications SHOULD clearly identify the applicable relationship when addressing the recipient.

Example (Non-Normative)
A subject's email address may be disclosed for one relationship but not another.

Enumerated Values
"not_stored" — value not maintained by the domain service.
"shielded" — value maintained, not disclosed.
"visible" — value disclosed.
"tunable_shielded" — value maintained, currently not disclosed.
"tunable_visible" — value maintained, currently disclosed.

End of RFC modeling section.

`;
const verification_state = `Existing verification mechanisms primarily prove operational control of a domain or service endpoint rather than registry-level subject verification. The limitations of these mechanisms include:

1. Verification based on PKI using an SSL/TLS certificate requires a reachable website and does not inherently provide registry-level verification lifecycle information.
2. Two-way verification using a "/.well-known/" resource requires a reachable website and does not define registry verification lifecycle semantics.
3. Verification based on DNS records, including TXT and CNAME validation, requires control of the DNS zone and verifies domain control rather than registry-level subject verification.
4. Verification using administrative domain email addresses verifies control of domain-associated email addresses and does not provide registry verification lifecycle semantics.
5. Business E-Wallets may contain overlapping domain claims and are not authoritative sources for domain registration relationships.

Verification Lifecycle (Proposed)
A registry MAY publish only the verification data necessary to provide a globally unique anchor for subject verification in a globally accessible RDAP verification service.

Country-specific web domain services MAY perform periodic operational validation, including DNSSEC and other domain stability checks, and MAY retain historical results for statistical and reporting purposes. Such services provide operational information and do not alter the responsibility for investigating or resolving identified issues, which remains with the responsible organization.

Verification lifecycle members describe the status of subject identifier verification independently of publication state and relationship responsibilities.

When a registry performs subject identifier verification, the following members apply.

The "identifier_received_at" member indicates that a subject identifier has been received by the registry for verification.

The "verification_set_at" member indicates that the registry has successfully verified the subject identifier through a verification authority recognized by the registry.

The "verification_revoked_at" member indicates that a previously successful verification is no longer valid and that the verification status has been revoked. Revocation of verification MUST NOT imply that the associated subject relationship has been removed.

End of RFC modeling section.

`;

function SwitchTranslation(translation)	{
	document.getElementById("language").value = translation;
	if (translation == 99)	{
		var modified = '';
		var proposed = '';
		var accessible = '';
		var legacy = '';
		document.getElementById("title").textContent = "Domain Information — RDAP Data & Verification";
		document.getElementById("modeling").textContent = "";
		document.getElementById("instruction").textContent = "Enter here:";
		document.getElementById("field_name").textContent = "Modeled in snake_case";
		document.getElementById("explanation").textContent = "";
		document.getElementById("notices_part").textContent = legacy;
		document.getElementById("links_part").textContent = legacy;
		document.getElementById("verification_state_part").textContent = "";
		document.getElementById("verification_state").textContent = "";
		document.getElementById("publication_state_part").textContent = modified + "";
		document.getElementById("publication_state").textContent = "";
		document.getElementById("metadata_part").textContent = proposed;
		document.getElementById("metadata_object_type").textContent = modified;
		document.getElementById("metadata_rdap_version").textContent = modified;
		document.getElementById("metadata_rdap_data_layer").textContent = proposed;
		document.getElementById("metadata_rdap_issue_uri").textContent = proposed;
		document.getElementById("metadata_rdap_supplier_uri").textContent = proposed;
		document.getElementById("metadata_tld_information_uri").textContent = proposed;
		document.getElementById("metadata_global_data_uri").textContent = proposed;
		document.getElementById("metadata_registry_data_uri").textContent = proposed;
		document.getElementById("metadata_registrar_identifiers").textContent = modified;
		document.getElementById("metadata_registrar_data_uri").textContent = proposed;
		document.getElementById("metadata_registrar_complaint_uri").textContent = proposed;
		document.getElementById("metadata_registrar_publication_method").textContent = proposed;
		document.getElementById("metadata_status_explanation_uri").textContent = proposed;
		document.getElementById("metadata_resource_upload_at").textContent = modified;
		document.getElementById("domain_part").textContent = "";
		document.getElementById("domain_ascii_name").textContent = "";
		document.getElementById("domain_unicode_name").textContent = "";
		document.getElementById("domain_statuses").textContent = legacy;
		document.getElementById("domain_policy_statuses").textContent = modified;
		document.getElementById("domain_dns_state").textContent = proposed;
		document.getElementById("domain_created_at").textContent = "";
		document.getElementById("domain_latest_data_mutation_at").textContent = modified;
		document.getElementById("domain_expiration_at").textContent = "";
		document.getElementById("domain_lifecycle_phase").textContent = modified;
		document.getElementById("domain_lifecycle_phase_until").textContent = modified;
		document.getElementById("domain_applicable_grace").textContent = modified;
		document.getElementById("domain_applicable_grace_until").textContent = modified;		
		document.getElementById("domain_recoverable_until").textContent = proposed;
		document.getElementById("domain_deletion_at").textContent = "";
		document.getElementById("domain_extensions").textContent = "";		
		document.getElementById("sponsor_part").textContent = "";
		document.getElementById("registrant_part").textContent = "";
		document.getElementById("registrant_tld_global_handle").textContent = proposed;
		document.getElementById("registrant_source_handle").textContent = modified;
		document.getElementById("registrant_subject_identifier").textContent = proposed;
		document.getElementById("registrant_organization_type").textContent = "";
		document.getElementById("registrant_organization_name").textContent = "";
		document.getElementById("registrant_presented_name").textContent = "";
		document.getElementById("registrant_kind").textContent = "";
		document.getElementById("registrant_name").textContent = "";
		document.getElementById("registrant_contact_uri").textContent = "";
		document.getElementById("registrant_country_code").textContent = "";
		document.getElementById("registrant_street_address").textContent = "";
		document.getElementById("registrant_postal_code").textContent = "";
		document.getElementById("registrant_country_name").textContent = "";
		document.getElementById("registrant_identifier_received_at").textContent = proposed;
		document.getElementById("registrant_verification_set_at").textContent = proposed;
		document.getElementById("registrant_verification_revoked_at").textContent = proposed;
		document.getElementById("registrant_remarks").textContent = "";
		document.getElementById("registrant_data_uri").textContent = proposed;
		document.getElementById("actor_part").textContent = proposed;
		document.getElementById("request_handling_part").textContent = modified;
		document.getElementById("request_handling_subject_identifier").textContent = proposed;
		document.getElementById("issue_reporting_part").textContent = modified;
		document.getElementById("issue_reporting_subject_identifier").textContent = proposed;
		document.getElementById("billing_part").textContent = "";
		document.getElementById("escalation_part").textContent = proposed;
		document.getElementById("reseller_part").textContent = "";
		document.getElementById("reseller_subject_identifier").textContent = proposed;
		document.getElementById("reseller_identifier_received_at").textContent = proposed;
		document.getElementById("reseller_verification_set_at").textContent = proposed;
		document.getElementById("reseller_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_part").textContent = "";
		document.getElementById("registrar_subject_identifier").textContent = proposed;
		document.getElementById("registrar_email").textContent = "";
		document.getElementById("registrar_identifier_received_at").textContent = proposed;
		document.getElementById("registrar_verification_set_at").textContent = proposed;
		document.getElementById("registrar_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_abuse_part").textContent = "";
		document.getElementById("registrar_abuse_phone").textContent = "";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "";
		document.getElementById("nameservers_rdap_ds_digest_types").textContent = "";
		document.getElementById("measured_ds_algorithm_numbers").textContent = "";
		document.getElementById("measured_ds_algorithm_names").textContent = "";
		document.getElementById("nameservers_ipv4_addresses").textContent = "";
		document.getElementById("nameservers_ipv6_addresses").textContent = "";
		document.getElementById("br_tld").textContent = "";
		document.getElementById("raw_data_next").textContent = "";
	}
	else if (translation == 1)	{
		var modified = '(Gewijzigd) ';
		var proposed = '(Nieuw) ';
		var accessible = 'Voor het gebruiksgemak en de duidelijkheid kunnen nieuwe velden worden toegevoegd.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domeininformatie — RDAP-gegevens & Verificatie";
		document.getElementById("modeling").textContent = "Deze RDAP-modellering biedt een gestructureerde referentie voor het interpreteren van domeingegevens en openbaarmakingssemantiek.";
		document.getElementById("instruction").textContent = "Geef hier in:";
		document.getElementById("field_name").textContent = "Gemodelleerd in snake_case";
		document.getElementById("explanation").textContent = "Domein-RDAP-model dat RDAP-gegevens van registry en registrar combineert.";
		document.getElementById("notices_part").textContent = legacy + accessible;
		document.getElementById("links_part").textContent = legacy + accessible;
		document.getElementById("verification_state_part").textContent = "Het volgende model biedt wereldwijde verificatie van domeinsubjecten.";
		document.getElementById("verification_state").textContent = verification_state;
		document.getElementById("publication_state_part").textContent = modified + "Het volgende model biedt machineleesbare en mensleesbare zichtbaarheid.";
		document.getElementById("publication_state").textContent = publication_state;
		document.getElementById("metadata_part").textContent = proposed + "Metadata bieden context en details over data-elementen.";
		document.getElementById("metadata_object_type").textContent = modified;
		document.getElementById("metadata_rdap_version").textContent = modified;
		document.getElementById("metadata_rdap_data_layer").textContent = proposed + "Geeft aan uit welke RDAP-laag de gegevens afkomstig zijn.";
		document.getElementById("metadata_rdap_issue_uri").textContent=proposed+"URI voor het melden van RDAP-technische problemen of zorgen over gegevensintegriteit.";
		document.getElementById("metadata_rdap_supplier_uri").textContent = proposed+"URI die de leverancier van de RDAP-dienst identificeert, indien bekendgemaakt.";
		document.getElementById("metadata_tld_information_uri").textContent = proposed + "Naar informatieve TLD-context die vereenvoudigde domein-RDAP kan ondersteunen.";
		document.getElementById("metadata_global_data_uri").textContent=proposed+"URI naar een globale RDAP JSON-respons die kan verwijzen naar een andere gezaghebbende RDAP-bron.";
		document.getElementById("metadata_registry_data_uri").textContent=proposed+"URI van de registry-RDAP JSON-respons; relatie 'self'.";
		document.getElementById("metadata_registrar_identifiers").textContent = modified + "Registrar-identificaties. Voor ccTLD's: een geldige IANA Registrar ID of 0000.";
		document.getElementById("metadata_registrar_data_uri").textContent=proposed+"URI van de registrar-RDAP JSON-respons; relatie 'related'.";
		document.getElementById("metadata_registrar_complaint_uri").textContent=proposed+"URI voor klachtenafhandeling van ICANN gTLD-registrars.";
		document.getElementById("metadata_registrar_publication_method").textContent=proposed+"Voor gTLD's kan publicatie per registrantveld worden ingeschakeld.";
		document.getElementById("metadata_status_explanation_uri").textContent=proposed+"Vereist indien de registrar IANA-geaccrediteerd is; biedt uitleg over statuscodes.";
		document.getElementById("metadata_resource_upload_at").textContent = modified + "Tijdstempel van de RDAP-datasetupdate in UTC (Zulu-tijd).";
		document.getElementById("domain_part").textContent = "Een domein onder TLD-niveau is wereldwijd uniek en kan vrij worden gekozen onder bepaalde regels.";
		document.getElementById("domain_ascii_name").textContent = "Voor speciale tekens bevatten de ASCII-tekenreeksen Punycode-transcriptie.";
		document.getElementById("domain_unicode_name").textContent = "Optioneel veld dat, indien van toepassing, de Unicode-weergave van het domein biedt.";
		document.getElementById("domain_statuses").textContent = legacy + "RDAPv1 zelf garandeert niet of status van registry, registrar, of lifecycle is — elimineerbaar.";
		document.getElementById("domain_policy_statuses").textContent = modified;
		document.getElementById("domain_dns_state").textContent = proposed + "Gemodelleerde DNS-resolutiestatussen: dns_delegated, dns_undelegated, no_dns_records, unknown.";
		document.getElementById("domain_created_at").textContent = "De datumvelden staan hier in een logische volgorde. Dit is ook eenvoudig in de JSON-array.";
		document.getElementById("domain_latest_data_mutation_at").textContent = modified + "Verschillende mutatieniveaus: contactobject (registrar) versus domeinobject (registry).";
		document.getElementById("domain_expiration_at").textContent = "Vervaldatumgrens van het domein, waarna de rechten van de registrar afnemen.";
		document.getElementById("domain_lifecycle_phase").textContent = modified + "De EPP-status 'redemptionPeriod' kan in RDAPv2 worden gewijzigd naar 'pending_redemption'.";
		document.getElementById("domain_lifecycle_phase_until").textContent = modified;
		document.getElementById("domain_applicable_grace").textContent = modified;
		document.getElementById("domain_applicable_grace_until").textContent = modified;		
		document.getElementById("domain_recoverable_until").textContent = proposed + "Gemodelleerd als na stop van DNS-publicatie + TLD pending_redemption_days.";
		document.getElementById("domain_deletion_at").textContent = "Datum en tijdstip gepland voor volledige verwijdering. Er kan een laatste verwijderingsfase zijn.";
		document.getElementById("domain_extensions").textContent = "'Eligibility': Hoe het domein voldoet aan specifieke eisen van de TLD-rootzone.";
		document.getElementById("sponsor_part").textContent = "Gebruikt wanneer een partij toezicht houdt op het domeinabonnement.";
		document.getElementById("registrant_part").textContent = "De registrant is primair verantwoordelijk; andere partijen kunnen afhankelijk van de context worden benaderd.";
		document.getElementById("registrant_tld_global_handle").textContent = proposed + "Zonder deze identificatie kan een wereldwijde RDAP-server niet worden bijgewerkt.";
		document.getElementById("registrant_source_handle").textContent = modified;
		document.getElementById("registrant_subject_identifier").textContent = proposed + "Een jurisdictiegebonden identificatie van een rechtspersoon of natuurlijke persoon, geverifieerd via PKI.";
		document.getElementById("registrant_organization_type").textContent = 'De gebruikelijke waarde is "work", of mogelijk "work", "headquarters".';
		document.getElementById("registrant_organization_name").textContent = "De juridische naam van de organisatie die primair verantwoordelijk is voor het domeinabonnement.";
		document.getElementById("registrant_presented_name").textContent = "De naam van de primair verantwoordelijke persoon of een rol binnen de organisatie wordt verwacht.";
		document.getElementById("registrant_kind").textContent = "Leeg / 'org' / 'individual' (Voor continuïteit: levenstestament + testament + digitale executeur)";
		document.getElementById("registrant_name").textContent = "Een persoonlijke naam kan openbaar zichtbaar zijn in het veld 'presented_name'. Zie bijvoorbeeld cira.ca.";
		document.getElementById("registrant_contact_uri").textContent = "Biedt een contactendpoint, mogelijk professioneel beheerd, om de registrant te bereiken.";
		document.getElementById("registrant_country_code").textContent = "ISO-2 landcode van vestiging; standaard rechtsmacht tenzij overschreven door wet of beleid.";
		document.getElementById("registrant_street_address").textContent = "Het afschermen van adresgegevens resulteert in rommelige gegevens.";
		document.getElementById("registrant_postal_code").textContent = "Indexeren op postcode is in de database noodzakelijk. De vCard-array vormt een obstakel.";
		document.getElementById("registrant_country_name").textContent = "Een openbaar zichtbare landnaam is beperkt tot 'gTLD registrar RDAP' (ontwerpwijziging).";
		document.getElementById("registrant_identifier_received_at").textContent = proposed + "Na identificatie wordt de subjectidentificatie ter verificatie ontvangen.";
		document.getElementById("registrant_verification_set_at").textContent = proposed + "De registry verifieert de subjectidentificatie via de landspecifieke webdomeindienst.";
		document.getElementById("registrant_verification_revoked_at").textContent = proposed + "De verificatie van de subjectidentificatie kan worden ingetrokken.";
		document.getElementById("registrant_remarks").textContent = "Meer informatie. Zie bijvoorbeeld france.fr.";
		document.getElementById("registrant_data_uri").textContent = proposed;
		document.getElementById("actor_part").textContent = proposed + "Handelt namens de registrant en opereert volgens de vereisten van de registrant.";
		document.getElementById("request_handling_part").textContent = modified + "Behandelt binnenkomende verzoeken en stuurt deze indien nodig door naar de juiste partij.";
		document.getElementById("request_handling_subject_identifier").textContent = proposed;
		document.getElementById("issue_reporting_part").textContent = modified + "Ontvangt en behandelt meldingen van DNS-problemen.";
		document.getElementById("issue_reporting_subject_identifier").textContent = proposed;
		document.getElementById("billing_part").textContent = "Gebruikt wanneer het register het domeinabonnement rechtstreeks factureert.";
		document.getElementById("escalation_part").textContent = proposed + "Gebruikt wanneer escalatie voorbij het primaire contact vereist is.";
		document.getElementById("reseller_part").textContent = "Verantwoordelijkheden zijn afhankelijk van overeenkomsten en toepasselijke TLD-beleidsregels.";
		document.getElementById("reseller_subject_identifier").textContent = proposed;
		document.getElementById("reseller_identifier_received_at").textContent = proposed;
		document.getElementById("reseller_verification_set_at").textContent = proposed;
		document.getElementById("reseller_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_part").textContent = "Door het register erkende organisatie in verband met het domeinabonnement.";
		document.getElementById("registrar_subject_identifier").textContent = proposed
		document.getElementById("registrar_email").textContent = "Een registrar moet zonder verdere hyperlinks bereikbaar zijn.";
		document.getElementById("registrar_identifier_received_at").textContent = proposed;
		document.getElementById("registrar_verification_set_at").textContent = proposed;
		document.getElementById("registrar_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_abuse_part").textContent = "Biedt contactinformatie voor de registrar of een aangewezen abuse-afhandelaar.";
		document.getElementById("registrar_abuse_phone").textContent = "Een telefoonnummer moet beginnen met het type. Toegestaan zijn in ieder geval 'voice' en 'fax'.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC beveiligt DNS tegen spoofing en cachevergiftiging.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algoritmen 13–16 zijn actueel. IANA voert algoritme 8 als RECOMMENDED, geldt als uitlopend.";
		document.getElementById("nameservers_rdap_ds_digest_types").textContent = "De hexadecimale digest waarde is niet hoofdlettergevoelig.";
		document.getElementById("measured_ds_algorithm_numbers").textContent = "Deze tool meet DNSSEC-algoritmen direct, onafhankelijk van RDAP, ook bij tijdelijke duplicatie.";
		document.getElementById("measured_ds_algorithm_names").textContent = "IANA DNSSEC-algoritmenaamidentificaties op basis van de algoritmenummers.";
		document.getElementById("nameservers_ipv4_addresses").textContent = "Een glue-record is een DNS-record dat wordt meegegeven door de bovenliggende zone, ook al is die daar niet";
		document.getElementById("nameservers_ipv6_addresses").textContent = "autoritatief voor, om cirkelafhankelijke resoluties van nameservers binnen de onderliggende zone te voorkomen.";
		document.getElementById("br_tld").textContent = "De .br TLD RDAP-dienst is verrijkt met nameservervalidatie.";
		document.getElementById("raw_data_next").textContent = "Opmerkingen: De relaties zijn hier gerangschikt naar verantwoordelijkheid. Duidelijker met '(not provided)'. Een JSON-structuur kan net zo leesbaar zijn als XML.";
	}
	else if (translation == 2)	{
		var modified = '(Modified) ';
		var proposed = '(New) ';
		var accessible = 'For ease of use and clarity, new fields can be added.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domain Information — RDAP Data & Verification";
		document.getElementById("modeling").textContent = "This RDAP modeling provides a structured reference for interpreting domain data and disclosure semantics.";
		document.getElementById("instruction").textContent = "Enter here:";
		document.getElementById("field_name").textContent = "Modeled in snake_case";
		document.getElementById("explanation").textContent = "Domain RDAP model combining registry and registrar RDAP data.";
		document.getElementById("notices_part").textContent = legacy + accessible;
		document.getElementById("links_part").textContent = legacy + accessible;
		document.getElementById("verification_state_part").textContent = "The following model provides worldwide domain subject verification.";
		document.getElementById("verification_state").textContent = verification_state;		
		document.getElementById("publication_state_part").textContent = modified + "The following model provides machine-readable and human-readable visibility.";
		document.getElementById("publication_state").textContent = publication_state;		
		document.getElementById("metadata_part").textContent=proposed+"Metadata provides context and details about data elements.";
		document.getElementById("metadata_object_type").textContent=modified;
		document.getElementById("metadata_rdap_version").textContent=modified;
		document.getElementById("metadata_rdap_data_layer").textContent=proposed+"Indicates the RDAP layer from which the data originates.";
		document.getElementById("metadata_rdap_issue_uri").textContent=proposed+"URI for reporting RDAP technical issues or data integrity concerns.";
		document.getElementById("metadata_rdap_supplier_uri").textContent = proposed+"URI identifying the supplier of the RDAP service, if disclosed.";
		document.getElementById("metadata_tld_information_uri").textContent=proposed+"Towards informative TLD context that may support simplified domain RDAP.";
		document.getElementById("metadata_global_data_uri").textContent=proposed+"URI to a global RDAP JSON response that may reference another authoritative RDAP source.";
		document.getElementById("metadata_registry_data_uri").textContent=proposed+"Registry RDAP JSON response URI; relationship 'self'.";
		document.getElementById("metadata_registrar_identifiers").textContent=modified+"Registrar identifiers. For ccTLDs: a valid IANA Registrar ID or 0000.";
		document.getElementById("metadata_registrar_data_uri").textContent=proposed+"Registrar RDAP JSON response URI; relationship 'related'.";
		document.getElementById("metadata_registrar_complaint_uri").textContent=proposed+"URI for ICANN gTLD registrar complaint handling.";
		document.getElementById("metadata_registrar_publication_method").textContent=proposed+"For gTLDs, publication may be enabled on a per-registrant-field basis.";
		document.getElementById("metadata_status_explanation_uri").textContent=proposed+"Required if the registrar is IANA-accredited; provides status code explanations.";
		document.getElementById("metadata_resource_upload_at").textContent=modified+"Timestamp of RDAP dataset update in UTC (Zulu time).";		
		document.getElementById("domain_part").textContent = "A domain below TLD level is globally unique and can be freely chosen under certain rules.";
		document.getElementById("domain_ascii_name").textContent = "For special characters, the ASCII character strings contain Punycode transcription.";
		document.getElementById("domain_unicode_name").textContent = "Optional field that provides the Unicode representation of the domain, if applicable.";
		document.getElementById("domain_statuses").textContent = legacy + "RDAPv1 itself doesn’t guarantee showing if status is registry, registrar, or lifecycle — eliminable.";
		document.getElementById("domain_policy_statuses").textContent = modified;
		document.getElementById("domain_dns_state").textContent = proposed + "Modeled DNS resolution states: dns_delegated, dns_undelegated, no_dns_records, unknown.";
		document.getElementById("domain_created_at").textContent = "The date fields are here in a logical order. This is also easy in the JSON array.";
		document.getElementById("domain_latest_data_mutation_at").textContent = modified + "Distinct mutation layers: contact object (registrar) vs domain object (registry).";
		document.getElementById("domain_expiration_at").textContent = "Domain expiration boundary, after which registrar rights decline.";
		document.getElementById("domain_lifecycle_phase").textContent = modified + "The EPP 'redemptionPeriod' status can be changed to 'pending_redemption' in RDAPv2.";
		document.getElementById("domain_lifecycle_phase_until").textContent = modified;
		document.getElementById("domain_applicable_grace").textContent = modified;
		document.getElementById("domain_applicable_grace_until").textContent = modified;
		document.getElementById("domain_recoverable_until").textContent = proposed + "Modeled as after DNS publication stop + TLD pending_redemption_days.";
		document.getElementById("domain_deletion_at").textContent = "Date and time scheduled for complete deletion. A final deletion phase may exist.";
		document.getElementById("domain_extensions").textContent = "'Eligibility': How the domain meets specific TLD root zone requirements.";
		document.getElementById("sponsor_part").textContent = "Used when a party provides oversight related to the domain subscription.";
		document.getElementById("registrant_part").textContent = "The registrant is the primary accountable party; other parties may be addressed depending on context.";
		document.getElementById("registrant_tld_global_handle").textContent = proposed + "Without this identifier, a global RDAP server cannot be updated.";
		document.getElementById("registrant_source_handle").textContent = modified;
		document.getElementById("registrant_subject_identifier").textContent = proposed + "A jurisdiction-specific identifier for a business entity or natural person, verified through PKI.";
		document.getElementById("registrant_organization_type").textContent = 'The usual value is "work", or possibly "work", "headquarters".';
		document.getElementById("registrant_organization_name").textContent = "The legal name of the organization primarily responsible for the domain subscription.";
		document.getElementById("registrant_presented_name").textContent = "The name of the primarily responsible person or a role within the organization is expected.";
		document.getElementById("registrant_kind").textContent = "Empty / 'org' / 'individual' (For continuity: Living Will + Will + Digital Executor)";
		document.getElementById("registrant_name").textContent = "A personal name may be publicly visible in the 'presented_name' field. See for example cira.ca.";
		document.getElementById("registrant_contact_uri").textContent = "Provides a contact endpoint, potentially professionally managed, for reaching the registrant.";
		document.getElementById("registrant_country_code").textContent = "ISO-2 country code of establishment; default jurisdiction unless overridden by law or policy.";
		document.getElementById("registrant_street_address").textContent = "Shielding address data results in messy data.";
		document.getElementById("registrant_postal_code").textContent = "Indexing by postal code is necessary in the database. The Vcard array is an obstacle.";	
		document.getElementById("registrant_country_name").textContent = "A publicly visible country name is limited to 'gTLD registrar RDAP' (design change).";
		document.getElementById("registrant_identifier_received_at").textContent = proposed + "After identification, the subject identifier is received for verification.";
		document.getElementById("registrant_verification_set_at").textContent = proposed + "The registry verifies the subject identifier through the country-specific web domain service.";
		document.getElementById("registrant_verification_revoked_at").textContent = proposed + "The verification of the subject identifier can be revoked.";
		document.getElementById("registrant_remarks").textContent = "More information. See for example france.fr.";
		document.getElementById("registrant_data_uri").textContent = proposed;
		document.getElementById("actor_part").textContent = proposed + "Acts on behalf of the registrant and operates in accordance with the registrant’s requirements.";
		document.getElementById("request_handling_part").textContent = modified + "Handles incoming requests and forwards them to the appropriate party when necessary.";
		document.getElementById("request_handling_subject_identifier").textContent = proposed;
		document.getElementById("issue_reporting_part").textContent = modified + "Receives and handles DNS issue reports.";
		document.getElementById("issue_reporting_subject_identifier").textContent = proposed;
		document.getElementById("billing_part").textContent = "Used when the registry directly bills the domain subscription.";
		document.getElementById("escalation_part").textContent = proposed + "Used when escalation beyond the primary contact is required.";
		document.getElementById("reseller_part").textContent = "Responsibilities depend on agreements and applicable TLD policies.";
		document.getElementById("reseller_subject_identifier").textContent = proposed;
		document.getElementById("reseller_identifier_received_at").textContent = proposed;
		document.getElementById("reseller_verification_set_at").textContent = proposed;
		document.getElementById("reseller_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_part").textContent = "Organization recognized by the registry in relation to the domain subscription.";
		document.getElementById("registrar_subject_identifier").textContent = proposed;
		document.getElementById("registrar_email").textContent = "A registrar needs to be reachable without any further hyperlink.";
		document.getElementById("registrar_identifier_received_at").textContent = proposed;
		document.getElementById("registrar_verification_set_at").textContent = proposed;
		document.getElementById("registrar_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_abuse_part").textContent = "Provides contact information for the registrar or designated abuse handler.";
		document.getElementById("registrar_abuse_phone").textContent = "A phone number must begin with the type. Allowed are anyway 'voice' and 'fax'.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC secures DNS against spoofing and cache poisoning.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algorithms 13–16 are current. IANA lists algorithm 8 as RECOMMENDED, considered phasing out.";
		document.getElementById("nameservers_rdap_ds_digest_types").textContent = "The hexadecimal digest value is not case-sensitive.";
		document.getElementById("measured_ds_algorithm_numbers").textContent = "This tool measures DNSSEC algorithms directly, independent of RDAP, including during temporary duplication.";
		document.getElementById("measured_ds_algorithm_names").textContent = "IANA DNSSEC algorithm name identifiers based on the algorithm numbers.";
		document.getElementById("nameservers_ipv4_addresses").textContent = "A glue record is a DNS record provided by the parent zone, even though it is not authoritative for it,";
		document.getElementById("nameservers_ipv6_addresses").textContent = "to prevent circular dependencies when resolving nameservers within the child zone.";
		document.getElementById("br_tld").textContent = "The .br TLD RDAP service includes nameserver validation enrichment.";
		document.getElementById("raw_data_next").textContent = "Remarks: The relations here are arranged according to responsibility. Clearer with '(not provided)'. A JSON structure can be as readable as XML.";
	}
	else if (translation == 3)	{
		var modified = '(Geändert) ';
		var proposed = '(Neu) ';
		var accessible = 'Zur Vereinfachung und besseren Übersichtlichkeit können neue Felder hinzugefügt werden.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domaininformationen — RDAP-Daten & Verifikation";
		document.getElementById("modeling").textContent = "Diese RDAP-Modellierung bietet eine strukturierte Referenz zur Interpretation von Domain-Daten und Offenlegungssemantik.";
		document.getElementById("instruction").textContent = "Hier eingeben:";
		document.getElementById("field_name").textContent = "Modelliert in snake_case";
		document.getElementById("explanation").textContent = "Domain-RDAP-Modell, das RDAP-Daten von Registry und Registrar kombiniert.";
		document.getElementById("notices_part").textContent = legacy + accessible;
		document.getElementById("links_part").textContent = legacy + accessible;
		document.getElementById("verification_state_part").textContent = "Das folgende Modell bietet eine weltweite Verifizierung von Domänensubjekten.";
		document.getElementById("verification_state").textContent = verification_state;		
		document.getElementById("publication_state_part").textContent = modified + "Das folgende Modell bietet maschinenlesbare und menschenlesbare Sichtbarkeit.";
		document.getElementById("publication_state").textContent = publication_state;
		document.getElementById("metadata_part").textContent = proposed + "Metadaten liefern Kontext und Details zu Datenelementen.";
		document.getElementById("metadata_object_type").textContent = modified;
		document.getElementById("metadata_rdap_version").textContent = modified;
		document.getElementById("metadata_rdap_data_layer").textContent = proposed + "Gibt an, aus welcher RDAP-Ebene die Daten stammen.";
		document.getElementById("metadata_rdap_issue_uri").textContent=proposed+"URI zum Melden technischer RDAP-Probleme oder von Bedenken hinsichtlich der Datenintegrität.";
		document.getElementById("metadata_rdap_supplier_uri").textContent = proposed+"URI zur Identifizierung des Anbieters des RDAP-Dienstes, sofern veröffentlicht.";
		document.getElementById("metadata_tld_information_uri").textContent = proposed + "Hin zu einem informativen TLD-Kontext, der vereinfachtes Domain-RDAP unterstützen kann.";
		document.getElementById("metadata_global_data_uri").textContent=proposed+"URI zu einer globalen RDAP-JSON-Antwort, die auf eine andere maßgebliche RDAP-Quelle verweisen kann.";
		document.getElementById("metadata_registry_data_uri").textContent=proposed+"URI der Registry-RDAP-JSON-Antwort; Beziehung 'self'.";
		document.getElementById("metadata_registrar_identifiers").textContent = modified + "Registrar-Kennungen. Für ccTLDs: eine gültige IANA Registrar-ID oder 0000.";
		document.getElementById("metadata_registrar_data_uri").textContent=proposed+"URI der Registrar-RDAP-JSON-Antwort; Beziehung 'related'.";
		document.getElementById("metadata_registrar_complaint_uri").textContent=proposed+"URI für die Beschwerdebehandlung von ICANN-gTLD-Registraren.";
		document.getElementById("metadata_registrar_publication_method").textContent=proposed+"Für gTLDs kann die Veröffentlichung pro Registrantenfeld aktiviert werden.";
		document.getElementById("metadata_status_explanation_uri").textContent=proposed+"Erforderlich, wenn der Registrar IANA-akkreditiert ist; stellt Erläuterungen zu Statuscodes bereit.";
		document.getElementById("metadata_resource_upload_at").textContent = modified + "Zeitstempel der Aktualisierung des RDAP-Datensatzes in UTC (Zulu-Zeit).";
		document.getElementById("domain_part").textContent = "Eine Domain unterhalb der TLD-Ebene ist weltweit eindeutig und kann unter bestimmten Regeln frei gewählt werden.";
		document.getElementById("domain_ascii_name").textContent = "Für Sonderzeichen enthalten die ASCII-Zeichenfolgen eine Punycode-Transkription.";
		document.getElementById("domain_unicode_name").textContent = "Optionales Feld, das gegebenenfalls die Unicode-Darstellung der Domäne bereitstellt.";
		document.getElementById("domain_statuses").textContent = legacy + "RDAPv1 garantiert nicht, ob Status von Registry, Registrar, oder Lifecycle stammt — eliminierbar.";
		document.getElementById("domain_policy_statuses").textContent = modified;
		document.getElementById("domain_dns_state").textContent = proposed + "Modellierte DNS-Auflösungszustände: dns_delegated, dns_undelegated, no_dns_records, unknown.";
		document.getElementById("domain_created_at").textContent = "Die Datumsfelder stehen hier in einer logischen Reihenfolge. Auch dies ist im JSON-Array einfach.";
		document.getElementById("domain_latest_data_mutation_at").textContent = modified + "Unterschiedliche Mutationsebenen: Kontaktobjekt (Registrar) versus Domainobjekt (Registry).";
		document.getElementById("domain_expiration_at").textContent = "Ablaufschwelle der Domain, nach der die Befugnisse des Registrars abnehmen.";
		document.getElementById("domain_lifecycle_phase").textContent = modified + "Der EPP-Status 'redemptionPeriod' kann in RDAPv2 auf 'pending_redemption' geändert werden.";
		document.getElementById("domain_lifecycle_phase_until").textContent = modified;
		document.getElementById("domain_applicable_grace").textContent = modified;
		document.getElementById("domain_applicable_grace_until").textContent = modified;		
		document.getElementById("domain_recoverable_until").textContent = proposed + "Modelliert als nach Ende der DNS-Veröffentlichung + TLD pending_redemption_days.";
		document.getElementById("domain_deletion_at").textContent = "Datum und Uhrzeit für die vollständige Löschung geplant. Es kann eine abschließende Löschphase geben.";
		document.getElementById("domain_extensions").textContent = "'Eligibility': Wie die Domain die spezifischen Anforderungen der TLD-Rootzone erfüllt.";
		document.getElementById("sponsor_part").textContent = "Wird verwendet, wenn eine Partei die Aufsicht über das Domain-Abonnement wahrnimmt.";
		document.getElementById("registrant_part").textContent = "Der Registrant ist die primär verantwortliche Partei; andere Parteien können je nach Kontext angesprochen werden.";
		document.getElementById("registrant_tld_global_handle").textContent = proposed + "Ohne diese Kennung kann ein globaler RDAP-Server nicht aktualisiert werden.";
		document.getElementById("registrant_source_handle").textContent = modified;
		document.getElementById("registrant_subject_identifier").textContent = proposed + "Eine rechtsraumbezogene Identifikation einer juristischen oder natürlichen Person, verifiziert durch PKI.";
		document.getElementById("registrant_organization_type").textContent = 'Der übliche Wert ist "work" oder möglicherweise "work", "headquarters".';
		document.getElementById("registrant_organization_name").textContent = "Der offizielle Name der Organisation, die hauptsächlich für das Domänenabonnement verantwortlich ist.";
		document.getElementById("registrant_presented_name").textContent = "Erwartet wird der Name der primär verantwortlichen Person oder einer Rolle innerhalb der Organisation.";
		document.getElementById("registrant_kind").textContent = "Leer / 'org' / 'individual' (Für Kontinuität: Patientenverfügung + Testament + digitaler Testamentsvollstrecker)";
		document.getElementById("registrant_name").textContent = "Ein Personenname kann im Feld 'presented_name' öffentlich sichtbar sei. Siehe beispielsweise cira.ca.";
		document.getElementById("registrant_contact_uri").textContent = "Stellt einen Kontaktendpunkt bereit, möglicherweise professionell verwaltet, um den Registranten zu erreichen.";
		document.getElementById("registrant_country_code").textContent = "ISO-2-Ländercode der Niederlassung; Standard-Gerichtsstand, sofern nicht durch Recht oder Richtlinien ersetzt.";
		document.getElementById("registrant_street_address").textContent = "Das Abschirmen von Adressdaten führt zu unordentlichen Daten.";
		document.getElementById("registrant_postal_code").textContent = "In der Datenbank ist eine Indizierung nach Postleitzahl erforderlich. Das vCard-Array stellt ein Hindernis dar.";	
		document.getElementById("registrant_country_name").textContent = "Ein öffentlich sichtbarer Ländername ist auf 'gTLD registrar RDAP' beschränkt (Designänderung).";
		document.getElementById("registrant_identifier_received_at").textContent = proposed + "Nach der Identifizierung wird die Subjektkennung zur Überprüfung entgegengenommen.";
		document.getElementById("registrant_verification_set_at").textContent = proposed + "Die Registry verifiziert die Subjektkennung über den länderspezifischen Web-Domain-Dienst.";
		document.getElementById("registrant_verification_revoked_at").textContent = proposed + "Die Verifizierung der Subjektkennung kann widerrufen werden.";
		document.getElementById("registrant_remarks").textContent = "Weitere Informationen. Siehe beispielsweise france.fr.";
		document.getElementById("registrant_data_uri").textContent = proposed;
		document.getElementById("actor_part").textContent = proposed + "Handelt im Auftrag des Registranten und agiert gemäß dessen Anforderungen.";
		document.getElementById("request_handling_part").textContent = modified + "Bearbeitet eingehende Anfragen und leitet sie bei Bedarf an die zuständige Stelle weiter.";
		document.getElementById("request_handling_subject_identifier").textContent = proposed;
		document.getElementById("issue_reporting_part").textContent = modified + "Nimmt Meldungen zu DNS-Problemen entgegen und bearbeitet diese.";
		document.getElementById("issue_reporting_subject_identifier").textContent = proposed;		
		document.getElementById("billing_part").textContent = "Wird verwendet, wenn die Registry das Domain-Abonnement direkt in Rechnung stellt.";
		document.getElementById("escalation_part").textContent = proposed + "Wird verwendet, wenn eine Eskalation über den primären Ansprechpartner hinaus erforderlich ist.";
		document.getElementById("reseller_part").textContent = "Die Verantwortlichkeiten hängen von Vereinbarungen und den geltenden TLD-Richtlinien ab.";
		document.getElementById("reseller_subject_identifier").textContent = proposed;
		document.getElementById("reseller_identifier_received_at").textContent = proposed;
		document.getElementById("reseller_verification_set_at").textContent = proposed;
		document.getElementById("reseller_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_part").textContent = "Organisation, die von der Registry im Zusammenhang mit dem Domain-Abonnement anerkannt ist.";
		document.getElementById("registrar_subject_identifier").textContent = proposed;
		document.getElementById("registrar_email").textContent = "Ein Registrar muss ohne weitere Hyperlinks erreichbar sein.";
		document.getElementById("registrar_identifier_received_at").textContent = proposed;
		document.getElementById("registrar_verification_set_at").textContent = proposed;
		document.getElementById("registrar_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_abuse_part").textContent = "Stellt Kontaktinformationen für den Registrar oder eine benannte Stelle zur Bearbeitung von Abuse-Meldungen bereit.";
		document.getElementById("registrar_abuse_phone").textContent = "Eine Telefonnummer muss mit dem Typ beginnen. Erlaubt sind grundsätzlich 'voice' und 'fax'.";		
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC sichert DNS gegen Spoofing und Cache-Poisoning.";
		document.getElementById("nameservers_rdap_ds_digest_types").textContent = "Der hexadezimale Digest-Wert unterscheidet nicht zwischen Groß- und Kleinschreibung.";
		document.getElementById("measured_ds_algorithm_numbers").textContent = "Dieses Tool ermittelt DNSSEC-Algorithmen direkt, unabhängig von RDAP, auch bei temporärer Doppelung.";
		document.getElementById("measured_ds_algorithm_names").textContent = "IANA-DNSSEC-Algorithmusnamenskennungen basierend auf den Algorithmusnummern.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algorithmen 13–16 sind aktuell. IANA führt Algorithmus 8 als RECOMMENDED, gilt als auslaufend.";
		document.getElementById("nameservers_ipv4_addresses").textContent = "Ein Glue-Record ist ein DNS-Eintrag, den die übergeordnete Zone bereitstellt, obwohl sie nicht autoritativ ist,";
		document.getElementById("nameservers_ipv6_addresses").textContent = "um zirkuläre Abhängigkeiten bei der Auflösung von Nameservern in der untergeordneten Zone zu verhindern.";		
		document.getElementById("br_tld").textContent = "Der .br-TLD-RDAP-Dienst wurde um eine Nameserver-Validierung erweitert.";
		document.getElementById("raw_data_next").textContent = "Anmerkungen: Die Beziehungen sind hier nach Verantwortung angeordnet. Deutlicher mit '(not provided)'. Eine JSON-Struktur kann genauso gut lesbar sein wie XML.";
	}
	else if (translation == 4)	{
		var modified = '(Modifié) ';
		var proposed = '(Nouveau) ';
		var accessible = "Pour plus de facilité d'utilisation et de clarté, de nouveaux champs peuvent être ajoutés.";
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Informations sur le domaine — Données RDAP & Vérification";
		document.getElementById("modeling").textContent = "Cette modélisation RDAP fournit une référence structurée pour l’interprétation des données de domaine et de la sémantique de divulgation.";
		document.getElementById("instruction").textContent = "Saisissez ici :";
		document.getElementById("field_name").textContent = "Modélisé en snake_case";
		document.getElementById("explanation").textContent = "Modèle RDAP de domaine combinant les données RDAP du registre et du registrar.";
		document.getElementById("notices_part").textContent = legacy + accessible;
		document.getElementById("links_part").textContent = legacy + accessible;
		document.getElementById("verification_state_part").textContent = "Le modèle suivant fournit une vérification mondiale des titulaires de domaines.";
		document.getElementById("verification_state").textContent = verification_state;		
		document.getElementById("publication_state_part").textContent = modified + "Le modèle suivant fournit une visibilité lisible par machine et par l'homme.";
		document.getElementById("publication_state").textContent = publication_state;
		document.getElementById("metadata_part").textContent = proposed + "Les métadonnées fournissent le contexte et des détails sur les éléments de données.";
		document.getElementById("metadata_object_type").textContent = modified;
		document.getElementById("metadata_rdap_version").textContent = modified;
		document.getElementById("metadata_rdap_data_layer").textContent = proposed + "Indique la couche RDAP dont proviennent les données.";
		document.getElementById("metadata_rdap_issue_uri").textContent=proposed+"URI pour signaler des problèmes RDAP ou d’intégrité des données.";
		document.getElementById("metadata_rdap_supplier_uri").textContent = proposed+"URI identifiant le fournisseur du service RDAP, lorsqu'il est publié.";
		document.getElementById("metadata_tld_information_uri").textContent = proposed + "Vers un contexte TLD informatif pouvant prendre en charge un RDAP de domaine simplifié.";
		document.getElementById("metadata_global_data_uri").textContent=proposed+"URI vers une réponse JSON RDAP globale pouvant référencer une autre source RDAP.";
		document.getElementById("metadata_registry_data_uri").textContent=proposed+"URI de la réponse JSON RDAP du registre ; relation 'self'.";
		document.getElementById("metadata_registrar_identifiers").textContent = modified + "Identifiants du bureau d’enregistrement. Pour les ccTLD : un identifiant de registrar IANA valide ou 0000.";
		document.getElementById("metadata_registrar_data_uri").textContent=proposed+"URI de la réponse JSON RDAP du bureau d’enregistrement ; relation 'related'.";
		document.getElementById("metadata_registrar_complaint_uri").textContent=proposed+"URI pour le traitement des plaintes des bureaux d’enregistrement ICANN gTLD.";
		document.getElementById("metadata_registrar_publication_method").textContent=proposed+"Pour les gTLD, la publication peut être activée par champ de titulaire.";
		document.getElementById("metadata_status_explanation_uri").textContent=proposed+"Requis si accrédité IANA ; explications des codes d’état.";
		document.getElementById("metadata_resource_upload_at").textContent = modified + "Horodatage de la mise à jour de l'ensemble de données RDAP en UTC (heure Zulu).";
		document.getElementById("domain_part").textContent = "Un domaine inférieur au niveau TLD est unique au monde et peut être choisi librement selon certaines règles.";
		document.getElementById("domain_ascii_name").textContent = "Pour les caractères spéciaux, les chaînes de caractères ASCII contiennent une transcription Punycode.";
		document.getElementById("domain_unicode_name").textContent = "Champ facultatif qui fournit la représentation Unicode du domaine, le cas échéant.";
		document.getElementById("domain_statuses").textContent = legacy + "Les statuts ne garantissent pas que RDAPv1 indique registre, registrar, ou cycle — éliminable.";
		document.getElementById("domain_policy_statuses").textContent = modified;
		document.getElementById("domain_dns_state").textContent = proposed + "États de résolution DNS modélisés : dns_delegated, dns_undelegated, no_dns_records, unknown.";
		document.getElementById("domain_created_at").textContent = "Les champs de date sont ici classés dans un ordre logique. C'est également facile dans le tableau JSON.";
		document.getElementById("domain_latest_data_mutation_at").textContent = modified + "Niveaux de mutation distincts : objet contact (registrar) vs objet domaine (registre).";
		document.getElementById("domain_expiration_at").textContent = "Limite d’expiration du domaine, après laquelle les droits du registrar diminuent.";
		document.getElementById("domain_lifecycle_phase").textContent = modified + "Le statut 'redemptionPeriod' de l’EPP peut être modifié en 'pending_redemption' dans RDAPv2.";
		document.getElementById("domain_lifecycle_phase_until").textContent = modified;
		document.getElementById("domain_applicable_grace").textContent = modified;
		document.getElementById("domain_applicable_grace_until").textContent = modified;		
		document.getElementById("domain_recoverable_until").textContent = proposed + "Modélisé comme après l’arrêt de publication DNS + TLD pending_redemption_days.";
		document.getElementById("domain_deletion_at").textContent = "Date et heure prévues pour la suppression complète. Une phase de suppression finale peut exister.";
		document.getElementById("domain_extensions").textContent = "'Eligibility' : comment le domaine répond aux exigences spécifiques de la zone racine TLD.";
		document.getElementById("sponsor_part").textContent = "Utilisé lorsqu’une partie assure une supervision liée à l’abonnement du nom de domaine.";
		document.getElementById("registrant_part").textContent = "Le titulaire est la partie principale responsable ; d’autres parties peuvent être contactées selon le contexte.";
		document.getElementById("registrant_tld_global_handle").textContent = proposed + "Sans cet identifiant, un serveur RDAP global ne peut pas être mis à jour.";
		document.getElementById("registrant_source_handle").textContent = modified;
		document.getElementById("registrant_subject_identifier").textContent = proposed + "Une identification juridictionnelle d’une personne morale ou physique, vérifiée par PKI.";
		document.getElementById("registrant_organization_type").textContent = 'La valeur habituelle est "work", ou éventuellement "work", "headquarters".';
		document.getElementById("registrant_organization_name").textContent = "Le nom légal de l'organisation principalement responsable de l'abonnement au domaine.";
		document.getElementById("registrant_presented_name").textContent = "Le nom de la personne principalement responsable ou d’un rôle au sein de l’organisation est attendu.";
		document.getElementById("registrant_kind").textContent = "Vide / 'org' / 'individual' (Pour la continuité : testament biologique + testament + exécuteur testamentaire numérique)";
		document.getElementById("registrant_name").textContent = "Un nom personnel peut être visible publiquement dans le champ 'presented_name'. Voir, par exemple, cira.ca.";
		document.getElementById("registrant_contact_uri").textContent = "Fournit un point de contact, potentiellement géré professionnellement, pour joindre le titulaire.";
		document.getElementById("registrant_country_code").textContent = "Code pays ISO-2 de l’établissement ; juridiction par défaut sauf dérogation par la loi ou la politique.";
		document.getElementById("registrant_street_address").textContent = "Le blindage des données d'adresse génère des données désordonnées.";
		document.getElementById("registrant_postal_code").textContent = "L'indexation par code postal est nécessaire dans la base de données. Le tableau de vCard constitue un obstacle.";
		document.getElementById("registrant_country_name").textContent = "Un nom de pays visible publiquement est limité à 'gTLD registrar RDAP' (changement de conception).";
		document.getElementById("registrant_identifier_received_at").textContent = proposed + "Après l'identification, l'identifiant du sujet est reçu pour vérification.";
		document.getElementById("registrant_verification_set_at").textContent = proposed + "Le registre vérifie l'identifiant du sujet par l'intermédiaire du service national de domaine web.";
		document.getElementById("registrant_verification_revoked_at").textContent = proposed + "La vérification de l'identifiant du sujet peut être révoquée.";
		document.getElementById("registrant_remarks").textContent = "Plus d'informations. Voir, par exemple, france.fr.";
		document.getElementById("registrant_data_uri").textContent = proposed;
		document.getElementById("actor_part").textContent = proposed + "Agit pour le compte du titulaire et opère conformément à ses exigences.";
		document.getElementById("request_handling_part").textContent = modified + "Traite les demandes reçues et les transmet à la partie compétente si nécessaire.";
		document.getElementById("request_handling_subject_identifier").textContent = proposed;
		document.getElementById("issue_reporting_part").textContent = modified + "Reçoit et traite les signalements de problèmes DNS.";
		document.getElementById("issue_reporting_subject_identifier").textContent = proposed;		
		document.getElementById("billing_part").textContent = "Utilisé lorsque le registre facture directement l’abonnement du nom de domaine.";
		document.getElementById("escalation_part").textContent = proposed + "Utilisé lorsqu’une escalade au-delà du contact principal est nécessaire.";
		document.getElementById("reseller_part").textContent = "Les responsabilités dépendent des accords et des politiques TLD applicables.";
		document.getElementById("reseller_subject_identifier").textContent = proposed;
		document.getElementById("reseller_identifier_received_at").textContent = proposed;
		document.getElementById("reseller_verification_set_at").textContent = proposed;
		document.getElementById("reseller_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_part").textContent = "Organisation reconnue par le registre dans le cadre de l’abonnement du nom de domaine.";
		document.getElementById("registrar_subject_identifier").textContent = proposed;
		document.getElementById("registrar_email").textContent = "Un registraire doit être joignable sans lien hypertexte supplémentaire.";
		document.getElementById("registrar_identifier_received_at").textContent = proposed;
		document.getElementById("registrar_verification_set_at").textContent = proposed;
		document.getElementById("registrar_verification_revoked_at").textContent = proposed;
		document.getElementById("registrar_abuse_part").textContent = "Fournit les coordonnées du bureau d’enregistrement ou du contact abuse désigné.";
		document.getElementById("registrar_abuse_phone").textContent = "Un numéro de téléphone doit commencer par le type. Sont autorisés de toute façon 'voice' et 'fax'.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC sécurise le DNS contre le spoofing et l’empoisonnement.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Les algorithmes 13–16 sont actuels. L’IANA classe l’algorithme 8 comme RECOMMENDED, en fin de vie.";
		document.getElementById("nameservers_rdap_ds_digest_types").textContent = "La valeur de hachage hexadécimale n'est pas sensible à la casse.";
		document.getElementById("measured_ds_algorithm_numbers").textContent = "Mesure directe des algorithmes DNSSEC, indépendante du RDAP, y compris duplication temporaire.";
		document.getElementById("measured_ds_algorithm_names").textContent = "Identifiants de noms d’algorithmes DNSSEC de l’IANA basés sur les numéros d’algorithme.";
		document.getElementById("nameservers_ipv4_addresses").textContent = "Un glue record est un enregistrement DNS fourni par la zone parente, bien qu’elle n’en soit pas autoritaire,";
		document.getElementById("nameservers_ipv6_addresses").textContent = "afin d’éviter les dépendances circulaires lors de la résolution des serveurs de noms de la zone enfant.";		
		document.getElementById("br_tld").textContent = "Le service RDAP du TLD .br a été enrichi avec une validation des serveurs de noms.";
		document.getElementById("raw_data_next").textContent = "Remarques : Les relations sont ici organisés selon la responsabilité. Plus clair avec '(not provided)'. Une structure JSON peut être aussi lisible que du XML.";
	}
}
</script><?php
echo '</head>';
if (ini_get("allow_url_fopen") == 1)	{
}
else	{	
	die('allow_url_fopen does not work.'); 	
}
$pd = idn_to_ascii($vd, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
$server_uri = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
$server_uri .= '://'. $_SERVER['HTTP_HOST'];
$server_uri .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);	
$server_uri = dirname($server_uri);
$rdap_uri = $server_uri.'/compose_domain/index.php?batch=0&domain='.$pd;
$context = stream_context_create([
  'http' => [
    'method'           => 'GET',
    'timeout'          => 20,
    'ignore_errors'    => true,
    'protocol_version' => 1.1,
    'header' =>
      "Accept: application/rdap+json, application/json\r\n" .
      "Connection: close\r\n",
  ],
]);
$start = microtime(true);
$json = file_get_contents($rdap_uri, false, $context);
$duration = round(microtime(true) - $start, 2);
if ($json === false) {
	$err = error_get_last();
	$msg = $err['message'] ?? 'Unknown error';
	if (stripos($msg, 'timed out') !== false) {
    	die("RDAP request timed out after {$duration}s (limit: 20s).");
  	}
	die("RDAP request failed after {$duration}s: " . $msg);
}
$status = $http_response_header[0] ?? '';
if (!preg_match('/^HTTP\/\S+\s+2\d\d\b/', $status)) {
	die("RDAP HTTP error: " . ($status ?: 'no response') . " (after {$duration}s)");
}
$data = json_decode($json, true);
if (!is_array($data)) {
	die("RDAP returned invalid JSON (after {$duration}s).");
}
$tld_information_uri = $server_uri.'/modeling_tld/index.php?language='.$viewlanguage.'&tld='.$data[$pd]['registry']['metadata']['tld_ascii_name'];
$raw_whois = $server_uri.'/domain_whois/index.php?language='.$viewlanguage.'&domain='.$vd;
if	(is_null($data))	{
	$tld_information_uri = '';
	$raw_whois = '';
	$reopen = $server_uri.'/modeling_domain/index.php?batch=0&domain=domain';
	sc_redir($reopen);
}
$html_text = '<body onload=SwitchTranslation('.$viewlanguage.')><div style="line-height: 1.2;">
<table class="top-align" style="border-collapse:collapse; font-family:Helvetica, Arial, sans-serif; font-size: 1rem; table-layout: fixed; width:1675px">
<tr><th style="width:325px"></th><th style="width:300px"></th><th style="width:750px"></th><th style="width:300px"></th></tr>';
$html_text .= '<tr style="font-size: 0.9rem"><td colspan="2" id="title" style="font-size: 1.4rem;color:blue;font-weight:bold"></td><td colspan="2" id="modeling"></td></tr>';
$html_text .= '<tr><td id="instruction" style="font-size: 0.8rem; vertical-align:middle; text-align: right;"></td><td><form action='.htmlentities($_SERVER['PHP_SELF']).' method="get">
	<input type="hidden" id="language" name="language" value='.$viewlanguage.'>	
	<input type="text" style="width:90%" id="domain" name="domain" value='.$vd.'></form></td><td>
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(99)">None</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(1)">nl_NL</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(2)">en_US</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(3)">de_DE</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(4)">fr_FR</button> 
	<a style="font-size: 0.9rem" href="https://rdap.hostingtool.nl/modeling_email" target="_blank">Email modeling</a> - <a style="font-size: 0.9rem" href="https://github.com/janwillemstegink/rdap.hostingtool.nl" target="_blank">Code/issues on GitHub</a> - <a style="font-size: 0.9rem" href="https://janwillemstegink.nl/" target="_blank">Insight at janwillemstegink.nl</a></td><td></td></tr>';
if (true or $pd == mb_strtolower($data[$pd]['registry']['domain']['ascii_name']) or empty($data[$pd]['registry']['domain']['ascii_name']))	{
	$html_text .= '<tr style="font-size:1.05rem;font-weight:bold"><td id="field_name"></td><td>domain_from_registry<td id="explanation"></td><td>domain_from_registrar</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(11)">Notices +/-</button><td></td><td id="notices_part"></td><td></td></tr>';
	$html_text .= '<tr id="111" style="display:none"><td colspan="2">'.$data[$pd]['registry']['notices'].'</td><td></td><td>'.$data[$pd]['registrar']['notices'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(12)">Links +/-</button><td></td><td id="links_part"></td><td></td></tr>';
	$html_text .= '<tr id="121" style="display:none"><td colspan="2">'.$data[$pd]['registry']['links'].'</td><td></td><td>'.$data[$pd]['registrar']['links'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(13)">Verification State +/-</button></td><td></td><td id="verification_state_part"></td><td></td></tr>';
	$html_text .= '<tr id="131" style="display:none"><td colspan="2">'.$data[$pd]['registry']['verification_state'].'</td><td id="verification_state" style="white-space: pre-wrap; font-family: monospace; line-height: 1.15; font-size: 12px;"></td><td>'.$data[$pd]['registrar']['verification_state'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(14)">Redacted +/-</button> <button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(15)">Publication State +/-</button></td><td></td><td id="publication_state_part"></td><td></td></tr>';
	$html_text .= '<tr id="141" style="display:none"><td colspan="2">'.$data[$pd]['registry']['publication_state'].'</td><td id="publication_state" style="white-space: pre-wrap; font-family: monospace; line-height: 1.15; font-size: 12px;"></td><td>'.$data[$pd]['registrar']['publication_state'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(20)">Metadata +/-</button></td><td></td><td id="metadata_part"></td><td></td></tr>';
	$html_text .= '<tr id="201" style="display:none"><td>object_type</td><td>'.$data[$pd]['registry']['metadata']['object_type'].'</td><td id="metadata_object_type"></td><td>'.$data[$pd]['registrar']['metadata']['object_type'].'</td></tr>';
	$html_text .= '<tr id="202" style="display:none"><td>rdap_version</td><td>'.$data[$pd]['registry']['metadata']['rdap_version'].'</td><td id="metadata_rdap_version"></td><td>'.$data[$pd]['registrar']['metadata']['rdap_version'].'</td></tr>';
	$html_text .= '<tr id="203" style="display:none"><td>rdap_conformance</td><td colspan="2">'.$data[$pd]['registry']['metadata']['rdap_conformance'].'</td><td>'.$data[$pd]['registrar']['metadata']['rdap_conformance'].'</td></tr>';
	$html_text .= '<tr id="204" style="display:none"><td>rdap_data_layer</td><td>'.$data[$pd]['registry']['metadata']['rdap_data_layer'].'</td><td id="metadata_rdap_data_layer"></td><td>'.$data[$pd]['registrar']['metadata']['rdap_data_layer'].'</td></tr>';
	$html_text .= '<tr id="205" style="display:none"><td>rdap_issue_uri</td><td>'.$data[$pd]['registry']['metadata']['rdap_issue_uri'].'</td><td id="metadata_rdap_issue_uri"></td><td>'.$data[$pd]['registrar']['metadata']['rdap_issue_uri'].'</td></tr>';
	$html_text .= '<tr id="206" style="display:none"><td>rdap_supplier_uri</td><td>'.$data[$pd]['registry']['metadata']['rdap_supplier_uri'].'</td><td id="metadata_rdap_supplier_uri"></td><td>'.$data[$pd]['registrar']['metadata']['rdap_supplier_uri'].'</td></tr>';
	$html_text .= '<tr id="207" style="display:none"><td>global_data_uri</td><td>'.$data[$pd]['registry']['metadata']['global_data_uri'].'</td><td id="metadata_global_data_uri"></td><td>'.$data[$pd]['registrar']['metadata']['global_data_uri'].'</td></tr>';
	$registry_data_uri = str_replace('https://', '', $data[$pd]['registry']['metadata']['registry_data_uri']);
	$validation_registry = 'https://validator.rdap.org/?url=https://'.$registry_data_uri.'&response-type=domain&server-type=gtld-registry&errors-only=1';	
	$html_text .= '<tr><td>registry_data_uri</td><td>'.((!empty($data[$pd]['registry']['metadata']['registry_data_uri'])) ? '<a href='.$data[$pd]['registry']['metadata']['registry_data_uri'].' target="_blank">Registry Response</a> - <a href="' . htmlspecialchars($validation_registry, ENT_QUOTES, "UTF-8") . '" target="_blank">gTLD validator.rdap.org</a>' : '').'</td><td id="metadata_registry_data_uri"></td><td>'.$data[$pd]['registrar']['metadata']['registry_data_uri'].'</td></tr>';
	$registrar_data_uri = str_replace('https://', '', $data[$pd]['registry']['metadata']['registrar_data_uri']);
	$validation_registrar = 'https://validator.rdap.org/?url=https://'.$registrar_data_uri.'&response-type=domain&server-type=gtld-registrar&errors-only=1';
	$html_text .= '<tr><td>registrar_data_uri</td><td>'.((!empty($data[$pd]['registry']['metadata']['registrar_data_uri'])) ? '<a href='.$data[$pd]['registry']['metadata']['registrar_data_uri'].' target="_blank">Registrar Response</a> - <a href="' . htmlspecialchars($validation_registrar, ENT_QUOTES, "UTF-8") . '" target="_blank">gTLD validator.rdap.org</a>' : '').'</td><td id="metadata_registrar_data_uri"></td><td>'.$data[$pd]['registrar']['metadata']['registrar_data_uri'].'</td></tr>';	
	$html_text .= '<tr><td>tld_information_uri</td><td>'.((!empty($tld_information_uri)) ? '<a href="'.$tld_information_uri.'" target="_blank">.'.$data[$pd]['registry']['metadata']['tld_unicode_name'].' TLD Information</a>' : '').'</td><td id="metadata_tld_information_uri"></td><td></td></tr>';
	$html_text .= '<tr id="208" style="display:none"><td>registrar_identifiers</td><td>'.((!empty($data[$pd]['registry']['metadata']['registrar_identifiers'])) ? $data[$pd]['registry']['metadata']['registrar_identifiers'] : '').'</td><td id="metadata_registrar_identifiers"></td><td>'.$data[$pd]['registrar']['metadata']['registrar_identifiers'].'</td></tr>';	
	$html_text .= '<tr id="209" style="display:none"><td>registrar_complaint_uri</td><td>'.((!empty($data[$pd]['registry']['metadata']['registrar_complaint_uri'])) ? '<a href='.$data[$pd]['registry']['metadata']['registrar_complaint_uri'].' target="_blank">icann.org/wicf</a>' : '').'</td><td id="metadata_registrar_complaint_uri"></td><td>'.$data[$pd]['registrar']['metadata']['registrar_complaint_uri'].'</td></tr>';
	$html_text .= '<tr id="2010" style="display:none"><td>registrar_publication_method</td><td>'.$data[$pd]['registry']['metadata']['registrar_publication_method'].'</td><td id="metadata_registrar_publication_method"></td><td>'.$data[$pd]['registrar']['metadata']['registrar_publication_method'].'</td></tr>';
	$html_text .= '<tr id="2011" style="display:none"><td>status_explanation_uri</td><td>'.((!empty($data[$pd]['registry']['metadata']['status_explanation_uri'])) ? '<a href='.$data[$pd]['registry']['metadata']['status_explanation_uri'].' target="_blank">icann.org/epp</a>' : '').'</td><td id="metadata_status_explanation_uri"></td><td>'.$data[$pd]['registrar']['metadata']['status_explanation_uri'].'</td></tr>';
	$html_text .= '<tr><td>resource_upload_at</td><td>'.$data[$pd]['registry']['metadata']['resource_upload_at'].'</td><td id="metadata_resource_upload_at"></td><td>'.$data[$pd]['registrar']['metadata']['resource_upload_at'].'</td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	if (!empty($data[$pd]['registry']['interface_notice']) or !empty($data[$pd]['registrar']['interface_notice']))	{
		$html_text .= '<tr><td><b>interface_notice</b></td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['interface_notice']).'</td><td></td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['interface_notice']).'</td></tr>';
	}
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(30)">Domain Properties +/-</button></td><td><b>'.$vd.'</b></td><td id="domain_part"></td><td></td></tr>';
	$html_text .= '<tr id="301" style="display:none"><td>domain_tld_global_handle</td><td colspan="2">'.$data[$pd]['registry']['domain']['tld_global_handle'].'</td><td>'.$data[$pd]['registrar']['domain']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="302" style="display:none"><td>domain_source_handle</td><td colspan="2">'.$data[$pd]['registry']['domain']['source_handle'].'</td><td>'.$data[$pd]['registrar']['domain']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="303" style="display:none"><td>domain_ascii_name (lowercase not required)</td><td>'.$data[$pd]['registry']['domain']['ascii_name'].'</td><td id="domain_ascii_name"></td><td>'.$data[$pd]['registrar']['domain']['ascii_name'].'</td></tr>';
	$html_text .= '<tr id="304" style="display:none"><td>domain_unicode_name</td><td>'.$data[$pd]['registry']['domain']['unicode_name'].'</td><td id="domain_unicode_name"></td><td>'.$data[$pd]['registrar']['domain']['unicode_name'].'</td></tr>';
	$domain_statuses = (!empty($data[$pd]['registry']['domain']['statuses'])) ? $data[$pd]['registry']['domain']['statuses'] : '';
	$domain_statuses = str_replace('excluded','<br />excluded (without DNS no email protection)', $domain_statuses);
	$domain_statuses = str_replace('locked','<br />locked (ambiguous RDAP use)', $domain_statuses);
	$domain_statuses = str_replace('removed','<br />removed (ambiguous RDAP use)', $domain_statuses);
	$domain_statuses = str_replace('obscured','<br />obscured (ambiguous RDAP use)', $domain_statuses);
	$domain_statuses = str_replace('private','<br />private (ambiguous RDAP use)', $domain_statuses);
	$domain_statuses = str_replace('proxy','<br />proxy (ambiguous RDAP use)', $domain_statuses);
	$domain_statuses = str_replace('associated','<br />associated (ambiguous RDAP use)', $domain_statuses);
	if (str_contains($data[$pd]['registry']['domain']['statuses'], 'renew prohibited'))	{
		if (!str_contains($data[$pd]['registry']['domain']['statuses'], 'server renew prohibited') and !str_contains($data[$pd]['registry']['domain']['statuses'], 'client renew prohibited'))	{
			$domain_statuses = str_replace('renew prohibited','<br />renew prohibited (ambiguous RDAP use)', $domain_statuses);
		}
	}
	if (str_contains($data[$pd]['registry']['domain']['statuses'], 'update prohibited'))	{
		if (!str_contains($data[$pd]['registry']['domain']['statuses'], 'server update prohibited') and !str_contains($data[$pd]['registry']['domain']['statuses'], 'client update prohibited'))	{
			$domain_statuses = str_replace('update prohibited','<br />update prohibited (ambiguous RDAP use)', $domain_statuses);
		}
	}
	if (str_contains($data[$pd]['registry']['domain']['statuses'], 'transfer prohibited'))	{
		if (!str_contains($data[$pd]['registry']['domain']['statuses'], 'server transfer prohibited') and !str_contains($data[$pd]['registry']['domain']['statuses'], 'client transfer prohibited'))	{
			$domain_statuses = str_replace('transfer prohibited','<br />transfer prohibited (ambiguous RDAP use)', $domain_statuses);
		}
	}	
	if (str_contains($data[$pd]['registry']['domain']['statuses'], 'delete prohibited'))	{
		if (!str_contains($data[$pd]['registry']['domain']['statuses'], 'server delete prohibited') and !str_contains($data[$pd]['registry']['domain']['statuses'], 'client delete prohibited'))	{
			$domain_statuses = str_replace('delete prohibited','<br />delete prohibited (ambiguous RDAP use)', $domain_statuses);
		}
	}	
	$html_text .= '<tr id="305" style="display:none"><td>domain_statuses</td><td>'.$domain_statuses.'</td><td id="domain_statuses"></td><td>'.$data[$pd]['registrar']['domain']['statuses'].'</td></tr>';
	$html_text .= '<tr id="306" style="display:none"><td>domain_policy_statuses</td><td>'.$data[$pd]['registry']['domain']['policy_statuses'].'</td><td id="domain_policy_statuses"></td><td>'.$data[$pd]['registrar']['domain']['policy_statuses'].'</td></tr>';
	$html_text .= '<tr><td>domain_dns_state</td><td>'.$data[$pd]['registry']['domain']['dns_state'].'</td><td id="domain_dns_state"></td><td>'.$data[$pd]['registrar']['domain']['dns_state'].'</td></tr>';
	$html_text .= '<tr id="307" style="display:none"><td>domain_created_at</td><td>'.$data[$pd]['registry']['domain']['created_at'].'</td><td id="domain_created_at"></td><td>'.$data[$pd]['registrar']['domain']['created_at'].'</td></tr>';
	$html_text .= '<tr id="308" style="display:none"><td>domain_latest_registrar_transfer_at</td><td>'.$data[$pd]['registry']['domain']['latest_registrar_transfer_at'].'</td><td></td><td>'.$data[$pd]['registrar']['domain']['latest_registrar_transfer_at'].'</td></tr>';		
	$html_text .= '<tr><td>domain_latest_data_mutation_at</td><td>'.$data[$pd]['registry']['domain']['latest_data_mutation_at'].'</td><td id="domain_latest_data_mutation_at"></td><td>'.$data[$pd]['registrar']['domain']['latest_data_mutation_at'].'</td></tr>';	
	$html_text .= '<tr><td>domain_expiration_at</td><td>'.$data[$pd]['registry']['domain']['expiration_at'].'</td><td id="domain_expiration_at"></td><td>'.$data[$pd]['registrar']['domain']['expiration_at'].'</td></tr>';
	$html_text .= '<tr id="309" style="display:none"><td>domain_lifecycle_phase</td><td>'.$data[$pd]['registry']['domain']['lifecycle_phase'].'</td><td id="domain_lifecycle_phase"></td><td>'.$data[$pd]['registrar']['domain']['lifecycle_phase'].'</td></tr>';
	$html_text .= '<tr id="3010" style="display:none"><td>domain_lifecycle_phase_until</td><td>'.$data[$pd]['registry']['domain']['lifecycle_phase_until'].'</td><td id="domain_lifecycle_phase_until"></td><td>'.$data[$pd]['registrar']['domain']['lifecycle_phase_until'].'</td></tr>';
	$html_text .= '<tr id="3011" style="display:none"><td>domain_applicable_grace</td><td>'.$data[$pd]['registry']['domain']['applicable_grace'].'</td><td id="domain_applicable_grace"></td><td>'.$data[$pd]['registrar']['domain']['applicable_grace'].'</td></tr>';
	$html_text .= '<tr id="3012" style="display:none"><td>domain_applicable_grace_until</td><td>'.$data[$pd]['registry']['domain']['applicable_grace_until'].'</td><td id="domain_applicable_grace_until"></td><td>'.$data[$pd]['registrar']['domain']['applicable_grace_until'].'</td></tr>';
	$html_text .= '<tr id="3013" style="display:none"><td>domain_recoverable_until</td><td>'.$data[$pd]['registry']['domain']['recoverable_until'].'</td><td id="domain_recoverable_until"></td><td>'.$data[$pd]['registrar']['domain']['recoverable_until'].'</td></tr>';
	$html_text .= '<tr id="3014" style="display:none"><td>domain_deletion_at</td><td>'.$data[$pd]['registry']['domain']['deletion_at'].'</td><td id="domain_deletion_at"></td><td>'.$data[$pd]['registrar']['domain']['deletion_at'].'</td></tr>';
	if (!empty($data[$pd]['registry']['domain']['statuses']))	{
		if (!empty($data[$pd]['registry']['domain']['lifecycle_phase']))	{
			if (str_contains($data[$pd]['registry']['domain']['lifecycle_phase'], ','))	{
				$html_text .= '<tr id="3015" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>RDAPv2: single-value for lifecycle_phase</td><td></td><td></td></tr>';
			}
		}
		if (str_contains($data[$pd]['registry']['domain']['statuses'], 'pending delete'))	{
			if (str_contains($data[$pd]['registry']['domain']['statuses'], 'redemption period') and str_contains($data[$pd]['registry']['domain']['statuses'], 'pending delete'))	{
				$html_text .= '<tr id="3016" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"pending delete" disregards redemption grace</td><td></td><td></td></tr>';
			}	
			elseif (!empty($data[$pd]['registry']['tld_unicode_name']))	{
				if ($data[$pd]['registry']['tld_unicode_name'] == 'nl')	{
					$html_text .= '<tr id="3017" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"pending delete" refers to "redemption period"</td><td></td><td></td></tr>';
				}	
			}	
		}
		if (str_contains($data[$pd]['registry']['domain']['statuses'], 'redemption period') or str_contains($data[$pd]['registry']['domain']['statuses'], 'pending delete'))	{
			if (!empty($data[$pd]['registry']['domain']['dns_state']))	{
				if ($data[$pd]['registry']['domain']['dns_state'] == 'dns_delegated')	{
					$html_text .= '<tr id="3018" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>upon deletion, DNS publishing is not expected</td><td></td><td></td></tr>';
				}	
			}
		}		
		if (str_contains($data[$pd]['registry']['domain']['statuses'], 'redemption period'))	{
			if (empty($data[$pd]['registry']['domain']['expiration_at']) and empty($data[$pd]['registry']['domain']['deletion_at'])) {
				$html_text .= '<tr id="3019" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"redemption" without date-time provided</td><td></td><td></td></tr>';
			}	
		}
		elseif (str_contains($data[$pd]['registry']['domain']['statuses'], 'pending delete'))	{
			if (empty($data[$pd]['registry']['domain']['expiration_at']) and empty($data[$pd]['registry']['domain']['deletion_at'])) {
				$html_text .= '<tr id="3020" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"pending delete" without date-time provided</td><td></td><td></td></tr>';
			}	
		}
	}
	if (!empty($data[$pd]['registry']['domain']['expiration_at']) and !empty($data[$pd]['registry']['domain']['deletion_at'])) {
    	$expiration = strtotime($data[$pd]['registry']['domain']['expiration_at']);
    	$deletion = strtotime($data[$pd]['registry']['domain']['deletion_at']);
    	if ($expiration !== false and $deletion !== false)	{
			$days_before = floor(($expiration - $deletion) / (60 * 60 * 24));
			if ($days_before > 0) {
       			$html_text .= '<tr id="3021" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"deletion_at" '.$days_before.' days before "expiration_at"</td><td></td><td></td></tr>';
			}	
    	}
	}
	if (!empty($data[$pd]['registry']['domain']['deletion_at'])) {
		$current = strtotime($datetime->format('Y-m-d H:i:s'));
		$deletion = strtotime($data[$pd]['registry']['domain']['deletion_at']);
    	if ($current !== false and $deletion !== false and $current > $deletion) {
			$days_ago = floor(($current - $deletion) / (60 * 60 * 24));
        	$html_text .= '<tr id="3022" style="display:none"><td>(Global table definition addresses ccTLD variation)</td><td>"deletion_at" was '.$days_ago.' days ago?</td><td></td><td></td></tr>';
		}
	}	
	$html_text .= '<tr id="3023" style="display:none"><td>domain_extensions</td><td>'.$data[$pd]['registry']['domain']['extensions'].'</td><td id="domain_extensions"></td><td>'.$data[$pd]['registrar']['domain']['extensions'].'</td></tr>';
	$html_text .= '<tr id="3024" style="display:none"><td>domain_remarks</td><td>'.$data[$pd]['registry']['domain']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['domain']['remarks'].'</td></tr>';
	if (!empty($data[$pd]['registry']['domain']['statuses']))	{
		$sponsor_applicable = (!empty($data[$pd]['registry']['sponsor']['organization_name']) or !empty($data[$pd]['registry']['sponsor']['presented_name'])) ? '(sponsor data exists)' : '(no sponsor data)';
	}
	else	{
		$sponsor_applicable = '';
	}	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(39)">Sponsor +/-</button></td><td>'.$sponsor_applicable.'</td><td id="sponsor_part"></td><td></td></tr>';
	$html_text .= '<tr id="391" style="display:none"><td>sponsor_tld_global_handle</td><td>'.$data[$pd]['registry']['sponsor']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="392" style="display:none"><td>sponsor_source_handle</td><td>'.$data[$pd]['registry']['sponsor']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="393" style="display:none"><td>sponsor_subject_identifier</td><td>'.$data[$pd]['registry']['sponsor']['subject_identifier'].'</td><td id="sponsor_subject_identifier"></td><td>'.$data[$pd]['registrar']['sponsor']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="394" style="display:none"><td>sponsor_organization_type</td><td>'.$data[$pd]['registry']['sponsor']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="395" style="display:none"><td>sponsor_organization_name</td><td>'.$data[$pd]['registry']['sponsor']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="396" style="display:none"><td>sponsor_presented_name</td><td>'.$data[$pd]['registry']['sponsor']['presented_name'].'</td><td id="sponsor_recover"></td><td>'.$data[$pd]['registrar']['sponsor']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="397" style="display:none"><td>sponsor_kind</td><td>'.$data[$pd]['registry']['sponsor']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['kind'].'</td></tr>';
	$html_text .= '<tr id="398" style="display:none"><td>sponsor_name</td><td>'.$data[$pd]['registry']['sponsor']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['name'].'</td></tr>';
	$html_text .= '<tr id="399" style="display:none"><td>sponsor_email</td><td>'.$data[$pd]['registry']['sponsor']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['email'].'</td></tr>';
	$html_text .= '<tr id="3910" style="display:none"><td>sponsor_phone</td><td>'.$data[$pd]['registry']['sponsor']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['phone'].'</td></tr>';
	$html_text .= '<tr id="3911" style="display:none"><td>sponsor_country_code</td><td>'.$data[$pd]['registry']['sponsor']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['country_code'].'</td></tr>';
	$html_text .= '<tr id="3912" style="display:none"><td>sponsor_street_address</td><td>'.$data[$pd]['registry']['sponsor']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['street_address'].'</td></tr>';
	$html_text .= '<tr id="3913" style="display:none"><td>sponsor_city</td><td>'.$data[$pd]['registry']['sponsor']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['city'].'</td></tr>';
	$html_text .= '<tr id="3914" style="display:none"><td>sponsor_state_or_province</td><td>'.$data[$pd]['registry']['sponsor']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="3915" style="display:none"><td>sponsor_postal_code</td><td>'.$data[$pd]['registry']['sponsor']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['country_name'].'</td></tr>';
	$html_text .= '<tr id="3916" style="display:none"><td>sponsor_country_name'.if_filled($data[$pd]['registry']['sponsor']['country_name']).'</td><td>'.$data[$pd]['registry']['sponsor']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['country_name'].'</td></tr>';
	$html_text .= '<tr id="3917" style="display:none"><td>sponsor_preferred_languages</td><td>'.$data[$pd]['registry']['sponsor']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="3918" style="display:none"><td>sponsor_statuses</td><td>'.$data[$pd]['registry']['sponsor']['statuses'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['statuses'].'</td></tr>';
	$html_text .= '<tr id="3919" style="display:none"><td>sponsor_created_at</td><td>'.$data[$pd]['registry']['sponsor']['created_at'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['created_at'].'</td></tr>';
	$html_text .= '<tr id="3920" style="display:none"><td>sponsor_latest_data_mutation_at</td><td>'.$data[$pd]['registry']['sponsor']['latest_data_mutation_at'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['latest_data_mutation_at'].'</td></tr>';
	$html_text .= '<tr id="3921" style="display:none"><td>sponsor_identifier_received_at</td><td>'.$data[$pd]['registry']['sponsor']['identifier_received_at'].'</td><td id="sponsor_identifier_received_at"></td><td>'.$data[$pd]['registrar']['sponsor']['identifier_received_at'].'</td></tr>';
	$html_text .= '<tr id="3922" style="display:none"><td>sponsor_verification_set_at</td><td>'.$data[$pd]['registry']['sponsor']['verification_set_at'].'</td><td id="sponsor_verification_set_at"></td><td>'.$data[$pd]['registrar']['sponsor']['verification_set_at'].'</td></tr>';
	$html_text .= '<tr id="151" style="display:none"><td colspan="2">sponsor_publication_state'.$data[$pd]['registry']['sponsor']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['publication_state'].'</td></tr>';
	$html_text .= '<tr id="3923" style="display:none"><td>sponsor_remarks</td><td>'.$data[$pd]['registry']['sponsor']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['remarks'].'</td></tr>';
	$html_text .= '<tr id="3924" style="display:none"><td>sponsor_links</td><td colspan="2">'.$data[$pd]['registry']['sponsor']['links'].'</td><td>'.$data[$pd]['registrar']['sponsor']['links'].'</td></tr>';
	$html_text .= '<tr id="3925" style="display:none"><td>sponsor_data_uri</td><td>'.$data[$pd]['registry']['sponsor']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['sponsor']['data_uri'].'</td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(40)">Registrant +/-</button></td><td></td><td id="registrant_part"></td><td></td></tr>';
	$html_text .= '<tr id="401" style="display:none"><td>registrant_tld_global_handle</td><td>'.$data[$pd]['registry']['registrant']['tld_global_handle'].'</td><td id="registrant_tld_global_handle"></td><td>'.$data[$pd]['registrar']['registrant']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="402" style="display:none"><td>registrant_source_handle</td><td>'.$data[$pd]['registry']['registrant']['source_handle'].'</td><td id="registrant_source_handle"></td><td>'.$data[$pd]['registrar']['registrant']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="403" style="display:none"><td>registrant_subject_identifier</td><td>'.$data[$pd]['registry']['registrant']['subject_identifier'].'</td><td id="registrant_subject_identifier"></td><td>'.$data[$pd]['registrar']['registrant']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="404" style="display:none"><td>registrant_organization_type</td><td>'.$data[$pd]['registry']['registrant']['organization_type'].'</td><td id="registrant_organization_type"></td><td>'.$data[$pd]['registrar']['registrant']['organization_type'].'</td></tr>';
	$html_text .= '<tr><td>registrant_organization_name</td><td>'.$data[$pd]['registry']['registrant']['organization_name'].'</td><td id="registrant_organization_name"></td><td>'.$data[$pd]['registrar']['registrant']['organization_name'].'</td></tr>';
	$html_text .= '<tr><td>registrant_presented_name (formatted name)</td><td>'.$data[$pd]['registry']['registrant']['presented_name'].'</td><td id="registrant_presented_name"></td><td>'.$data[$pd]['registrar']['registrant']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="405" style="display:none"><td>registrant_kind</td><td>'.$data[$pd]['registry']['registrant']['kind'].'</td><td id="registrant_kind"></td><td>'.$data[$pd]['registrar']['registrant']['kind'].'</td></tr>';
	$html_text .= '<tr id="406" style="display:none"><td>registrant_name</td><td>'.$data[$pd]['registry']['registrant']['name'].'</td><td id="registrant_name"></td><td>'.$data[$pd]['registrar']['registrant']['name'].'</td></tr>';
	$html_text .= '<tr><td>registrant_email</td><td>'.$data[$pd]['registry']['registrant']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['email'].'</td></tr>';
	$html_text .= '<tr><td>registrant_contact_uri</td><td>'.$data[$pd]['registry']['registrant']['contact_uri'].'</td><td id="registrant_contact_uri"></td><td>'.$data[$pd]['registrar']['registrant']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="407" style="display:none"><td>registrant_phone</td><td>'.$data[$pd]['registry']['registrant']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['phone'].'</td></tr>';
	$html_text .= '<tr><td>registrant_country_code (<a style="font-size: 0.9rem" href="https://icann-hamster.nl/ham/soac/ccnso/techday/icann80/2.%20RDAP%20Conformance%20Tool%20-%20Tech%20Day.pdf" target="_blank">"cc"</a>, establishment)</td><td>'.$data[$pd]['registry']['registrant']['country_code'].'</td><td id="registrant_country_code"></td><td>'.$data[$pd]['registrar']['registrant']['country_code'].'</td></tr>';
	$html_text .= '<tr id="408" style="display:none"><td>registrant_street_address</td><td>'.$data[$pd]['registry']['registrant']['street_address'].'</td><td id="registrant_street_address"></td><td>'.$data[$pd]['registrar']['registrant']['street_address'].'</td></tr>';
	$html_text .= '<tr id="409" style="display:none"><td>registrant_city</td><td>'.$data[$pd]['registry']['registrant']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['city'].'</td></tr>';
	$html_text .= '<tr id="4010" style="display:none"><td>registrant_state_or_province</td><td>'.$data[$pd]['registry']['registrant']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="4011" style="display:none"><td>registrant_postal_code</td><td>'.$data[$pd]['registry']['registrant']['postal_code'].'</td><td id="registrant_postal_code"></td><td>'.$data[$pd]['registrar']['registrant']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="4012" style="display:none"><td>registrant_country_name'.if_filled($data[$pd]['registry']['registrant']['country_name']).'</td><td>'.$data[$pd]['registry']['registrant']['country_name'].'</td><td id="registrant_country_name"></td><td>'.$data[$pd]['registrar']['registrant']['country_name'].'</td></tr>';
	$html_text .= '<tr id="4013" style="display:none"><td>registrant_preferred_languages</td><td>'.$data[$pd]['registry']['registrant']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="4014" style="display:none"><td>registrant_statuses</td><td>'.$data[$pd]['registry']['registrant']['statuses'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['statuses'].'</td></tr>';
	$html_text .= '<tr id="4015" style="display:none"><td>registrant_created_at</td><td>'.$data[$pd]['registry']['registrant']['created_at'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['created_at'].'</td></tr>';
	$html_text .= '<tr id="4016" style="display:none"><td>registrant_latest_data_mutation_at</td><td>'.$data[$pd]['registry']['registrant']['latest_data_mutation_at'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['latest_data_mutation_at'].'</td></tr>';
	$html_text .= '<tr id="4017" style="display:none"><td>registrant_identifier_received_at</td><td>'.$data[$pd]['registry']['registrant']['identifier_received_at'].'</td><td id="registrant_identifier_received_at"></td><td>'.$data[$pd]['registrar']['registrant']['identifier_received_at'].'</td></tr>';
	$html_text .= '<tr id="4018" style="display:none"><td>registrant_verification_set_at</td><td>'.$data[$pd]['registry']['registrant']['verification_set_at'].'</td><td id="registrant_verification_set_at"></td><td>'.$data[$pd]['registrar']['registrant']['verification_set_at'].'</td></tr>';
	$html_text .= '<tr id="4019" style="display:none"><td>registrant_verification_revoked_at</td><td>'.$data[$pd]['registry']['registrant']['verification_revoked_at'].'</td><td id="registrant_verification_revoked_at"></td><td>'.$data[$pd]['registrar']['registrant']['verification_revoked_at'].'</td></tr>';
	$html_text .= '<tr id="4020" style="display:none"><td>registrant_remarks</td><td>'.$data[$pd]['registry']['registrant']['remarks'].'</td><td id="registrant_remarks"></td><td>'.$data[$pd]['registrar']['registrant']['remarks'].'</td></tr>';
	$html_text .= '<tr id="4021" style="display:none"><td>registrant_links</td><td colspan="2">'.$data[$pd]['registry']['registrant']['links'].'</td><td>'.$data[$pd]['registrar']['registrant']['links'].'</td></tr>';
	$html_text .= '<tr id="4022" style="display:none"><td>registrant_data_uri</td><td>'.$data[$pd]['registry']['registrant']['data_uri'].'</td><td id="registrant_data_uri"></td><td>'.$data[$pd]['registrar']['registrant']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="152" style="display:none"><td colspan="2">registrant_publication_state'.$data[$pd]['registry']['registrant']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['registrant']['publication_state'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(41)">Actor (On Behalf) +/-</button></td><td></td><td id="actor_part"></td><td></td></tr>';
	$html_text .= '<tr id="411" style="display:none"><td>actor_tld_global_handle</td><td>'.$data[$pd]['registry']['actor']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="412" style="display:none"><td>actor_source_handle</td><td>'.$data[$pd]['registry']['actor']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="413" style="display:none"><td>actor_subject_identifier</td><td>'.$data[$pd]['registry']['actor']['subject_identifier'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="414" style="display:none"><td>actor_organization_type</td><td>'.$data[$pd]['registry']['actor']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="415" style="display:none"><td>actor_organization_name</td><td>'.$data[$pd]['registry']['actor']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="416" style="display:none"><td>actor_presented_name</td><td>'.$data[$pd]['registry']['actor']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="417" style="display:none"><td>actor_kind</td><td>'.$data[$pd]['registry']['actor']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['kind'].'</td></tr>';
	$html_text .= '<tr><td>actor_email</td><td>'.$data[$pd]['registry']['actor']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['email'].'</td></tr>';
	$html_text .= '<tr><td>actor_contact_uri</td><td>'.$data[$pd]['registry']['actor']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="418" style="display:none"><td>actor_phone</td><td>'.$data[$pd]['registry']['actor']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['phone'].'</td></tr>';
	$html_text .= '<tr id="419" style="display:none"><td>actor_country_code</td><td>'.$data[$pd]['registry']['actor']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['country_code'].'</td></tr>';
	$html_text .= '<tr id="153" style="display:none"><td colspan="2">actor_publication_state'.$data[$pd]['registry']['actor']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['actor']['publication_state'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(42)">Request Handling (Administrative) +/-</button></td><td></td><td id="request_handling_part"></td><td></td></tr>';
	$html_text .= '<tr id="421" style="display:none"><td>request_handling_tld_global_handle</td><td>'.$data[$pd]['registry']['request_handling']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="422" style="display:none"><td>request_handling_source_handle</td><td>'.$data[$pd]['registry']['request_handling']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="423" style="display:none"><td>request_handling_subject_identifier</td><td>'.$data[$pd]['registry']['request_handling']['subject_identifier'].'</td><td id="request_handling_subject_identifier"></td><td>'.$data[$pd]['registrar']['request_handling']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="424" style="display:none"><td>request_handling_organization_type</td><td>'.$data[$pd]['registry']['request_handling']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="425" style="display:none"><td>request_handling_organization_name</td><td>'.$data[$pd]['registry']['request_handling']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="426" style="display:none"><td>request_handling_presented_name</td><td>'.$data[$pd]['registry']['request_handling']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="427" style="display:none"><td>request_handling_kind</td><td>'.$data[$pd]['registry']['request_handling']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['kind'].'</td></tr>';
	$html_text .= '<tr id="428" style="display:none"><td>request_handling_name</td><td>'.$data[$pd]['registry']['request_handling']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['name'].'</td></tr>';
	$html_text .= '<tr><td>request_handling_email</td><td>'.$data[$pd]['registry']['request_handling']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['email'].'</td></tr>';
	$html_text .= '<tr><td>request_handling_contact_uri</td><td>'.$data[$pd]['registry']['request_handling']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="429" style="display:none"><td>request_handling_phone</td><td>'.$data[$pd]['registry']['request_handling']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['phone'].'</td></tr>';
	$html_text .= '<tr id="4210" style="display:none"><td>request_handling_country_code</td><td>'.$data[$pd]['registry']['request_handling']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['country_code'].'</td></tr>';
	$html_text .= '<tr id="4211" style="display:none"><td>request_handling_street_address</td><td>'.$data[$pd]['registry']['request_handling']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['street_address'].'</td></tr>';
	$html_text .= '<tr id="4212" style="display:none"><td>request_handling_city</td><td>'.$data[$pd]['registry']['request_handling']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['city'].'</td></tr>';
	$html_text .= '<tr id="4213" style="display:none"><td>request_handling_state_or_province</td><td>'.$data[$pd]['registry']['request_handling']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="4214" style="display:none"><td>request_handling_postal_code</td><td>'.$data[$pd]['registry']['request_handling']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="4215" style="display:none"><td>request_handling_country_name'.if_filled($data[$pd]['registry']['request_handling']['country_name']).'</td><td>'.$data[$pd]['registry']['request_handling']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['country_name'].'</td></tr>';
	$html_text .= '<tr id="4216" style="display:none"><td>request_handling_preferred_languages</td><td>'.$data[$pd]['registry']['request_handling']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="4217" style="display:none"><td>request_handling_remarks</td><td>'.$data[$pd]['registry']['request_handling']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['remarks'].'</td></tr>';
	$html_text .= '<tr id="4218" style="display:none"><td>request_handling_links</td><td colspan="2">'.$data[$pd]['registry']['request_handling']['links'].'</td><td>'.$data[$pd]['registrar']['request_handling']['links'].'</td></tr>';
	$html_text .= '<tr id="4219" style="display:none"><td>request_handling_data_uri</td><td>'.$data[$pd]['registry']['request_handling']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="154" style="display:none"><td colspan="2">request_handling_publication_state'.$data[$pd]['registry']['request_handling']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['request_handling']['publication_state'].'</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(43)">Issue Reporting (Technical) +/-</button></td><td></td><td id="issue_reporting_part"></td><td></td></tr>';
	$html_text .= '<tr id="431" style="display:none"><td>issue_reporting_tld_global_handle</td><td>'.$data[$pd]['registry']['issue_reporting']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="432" style="display:none"><td>issue_reporting_source_handle</td><td>'.$data[$pd]['registry']['issue_reporting']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="433" style="display:none"><td>issue_reporting_subject_identifier</td><td>'.$data[$pd]['registry']['issue_reporting']['subject_identifier'].'</td><td id="issue_reporting_subject_identifier"></td><td>'.$data[$pd]['registrar']['issue_reporting']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="434" style="display:none"><td>issue_reporting_organization_type</td><td>'.$data[$pd]['registry']['issue_reporting']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="435" style="display:none"><td>issue_reporting_organization_name</td><td>'.$data[$pd]['registry']['issue_reporting']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="436" style="display:none"><td>issue_reporting_presented_name</td><td>'.$data[$pd]['registry']['issue_reporting']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="437" style="display:none"><td>issue_reporting_kind</td><td>'.$data[$pd]['registry']['issue_reporting']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['kind'].'</td></tr>';
	$html_text .= '<tr id="438" style="display:none"><td>issue_reporting_name</td><td>'.$data[$pd]['registry']['issue_reporting']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['name'].'</td></tr>';
	$html_text .= '<tr><td>issue_reporting_email</td><td>'.$data[$pd]['registry']['issue_reporting']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['email'].'</td></tr>';
	$html_text .= '<tr><td>issue_reporting_contact_uri</td><td>'.$data[$pd]['registry']['issue_reporting']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="439" style="display:none"><td>issue_reporting_phone</td><td>'.$data[$pd]['registry']['issue_reporting']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['phone'].'</td></tr>';
	$html_text .= '<tr id="4310" style="display:none"><td>issue_reporting_country_code</td><td>'.$data[$pd]['registry']['issue_reporting']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['country_code'].'</td></tr>';
	$html_text .= '<tr id="4311" style="display:none"><td>issue_reporting_street_address</td><td>'.$data[$pd]['registry']['issue_reporting']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['street_address'].'</td></tr>';
	$html_text .= '<tr id="4312" style="display:none"><td>issue_reporting_city</td><td>'.$data[$pd]['registry']['issue_reporting']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['city'].'</td></tr>';
	$html_text .= '<tr id="4313" style="display:none"><td>issue_reporting_state_or_province</td><td>'.$data[$pd]['registry']['issue_reporting']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="4314" style="display:none"><td>issue_reporting_postal_code</td><td>'.$data[$pd]['registry']['issue_reporting']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="4315" style="display:none"><td>issue_reporting_country_name'.if_filled($data[$pd]['registry']['issue_reporting']['country_name']).'</td><td>'.$data[$pd]['registry']['issue_reporting']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['country_name'].'</td></tr>';
	$html_text .= '<tr id="4316" style="display:none"><td>issue_reporting_preferred_languages</td><td>'.$data[$pd]['registry']['issue_reporting']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="4317" style="display:none"><td>issue_reporting_remarks</td><td>'.$data[$pd]['registry']['issue_reporting']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['remarks'].'</td></tr>';
	$html_text .= '<tr id="4318" style="display:none"><td>issue_reporting_links</td><td colspan="2">'.$data[$pd]['registry']['issue_reporting']['links'].'</td><td>'.$data[$pd]['registrar']['issue_reporting']['links'].'</td></tr>';
	$html_text .= '<tr id="4319" style="display:none"><td>issue_reporting_data_uri</td><td>'.$data[$pd]['registry']['issue_reporting']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="155" style="display:none"><td colspan="2">issue_reporting_publication_state'.$data[$pd]['registry']['issue_reporting']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['issue_reporting']['publication_state'].'</td></tr>';	
$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(44)">Billing +/-</button></td><td></td><td id="billing_part"></td><td></td></tr>';
	$html_text .= '<tr id="441" style="display:none"><td>billing_tld_global_handle</td><td>'.$data[$pd]['registry']['billing']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="442" style="display:none"><td>billing_source_handle</td><td>'.$data[$pd]['registry']['billing']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="443" style="display:none"><td>billing_subject_identifier</td><td>'.$data[$pd]['registry']['billing']['subject_identifier'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="444" style="display:none"><td>billing_organization_type</td><td>'.$data[$pd]['registry']['billing']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="445" style="display:none"><td>billing_organization_name</td><td>'.$data[$pd]['registry']['billing']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="446" style="display:none"><td>billing_presented_name</td><td>'.$data[$pd]['registry']['billing']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="447" style="display:none"><td>billing_kind</td><td>'.$data[$pd]['registry']['billing']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['kind'].'</td></tr>';
	$html_text .= '<tr id="448" style="display:none"><td>billing_name</td><td>'.$data[$pd]['registry']['billing']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['name'].'</td></tr>';
	$html_text .= '<tr id="449" style="display:none"><td>billing_email</td><td>'.$data[$pd]['registry']['billing']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['email'].'</td></tr>';
	$html_text .= '<tr id="4410" style="display:none"><td>billing_contact_uri</td><td>'.$data[$pd]['registry']['billing']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="4411" style="display:none"><td>billing_phone</td><td>'.$data[$pd]['registry']['billing']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['phone'].'</td></tr>';
	$html_text .= '<tr id="4412" style="display:none"><td>billing_country_code</td><td>'.$data[$pd]['registry']['billing']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['country_code'].'</td></tr>';
	$html_text .= '<tr id="4413" style="display:none"><td>billing_street_address</td><td>'.$data[$pd]['registry']['billing']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['street_address'].'</td></tr>';
	$html_text .= '<tr id="4414" style="display:none"><td>billing_city</td><td>'.$data[$pd]['registry']['billing']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['city'].'</td></tr>';
	$html_text .= '<tr id="4415" style="display:none"><td>billing_state_or_province</td><td>'.$data[$pd]['registry']['billing']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="4416" style="display:none"><td>billing_postal_code</td><td>'.$data[$pd]['registry']['billing']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="4417" style="display:none"><td>billing_country_name'.if_filled($data[$pd]['registry']['billing']['country_name']).'</td><td>'.$data[$pd]['registry']['billing']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['country_name'].'</td></tr>';
	$html_text .= '<tr id="4418" style="display:none"><td>billing_preferred_languages</td><td>'.$data[$pd]['registry']['billing']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="4419" style="display:none"><td>billing_remarks</td><td>'.$data[$pd]['registry']['billing']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['remarks'].'</td></tr>';
	$html_text .= '<tr id="4420" style="display:none"><td>billing_links</td><td colspan="2">'.$data[$pd]['registry']['billing']['links'].'</td><td>'.$data[$pd]['registrar']['billing']['links'].'</td></tr>';
	$html_text .= '<tr id="4421" style="display:none"><td>billing_data_uri</td><td>'.$data[$pd]['registry']['billing']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="156" style="display:none"><td colspan="2">billing_publication_state'.$data[$pd]['registry']['billing']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['billing']['publication_state'].'</td></tr>';		
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(45)">Escalation +/-</button></td><td></td><td id="escalation_part"></td><td></td></tr>';
	$html_text .= '<tr id="451" style="display:none"><td>escalation_tld_global_handle</td><td>'.$data[$pd]['registry']['escalation']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="452" style="display:none"><td>escalation_source_handle</td><td>'.$data[$pd]['registry']['escalation']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="453" style="display:none"><td>escalation_organization_type</td><td>'.$data[$pd]['registry']['escalation']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="454" style="display:none"><td>escalation_organization_name</td><td>'.$data[$pd]['registry']['escalation']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="455" style="display:none"><td>escalation_presented_name</td><td>'.$data[$pd]['registry']['escalation']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="456" style="display:none"><td>escalation_kind</td><td>'.$data[$pd]['registry']['escalation']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['kind'].'</td></tr>';
	$html_text .= '<tr id="457" style="display:none"><td>escalation_email</td><td>'.$data[$pd]['registry']['escalation']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['email'].'</td></tr>';
	$html_text .= '<tr id="458" style="display:none"><td>escalation_contact_uri</td><td>'.$data[$pd]['registry']['escalation']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="459" style="display:none"><td>escalation_phone</td><td>'.$data[$pd]['registry']['escalation']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['phone'].'</td></tr>';
	$html_text .= '<tr id="4510" style="display:none"><td>escalation_country_code</td><td>'.$data[$pd]['registry']['escalation']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['country_code'].'</td></tr>';
	$html_text .= '<tr id="157" style="display:none"><td colspan="2">escalation_publication_state'.$data[$pd]['registry']['escalation']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['escalation']['publication_state'].'</td></tr>';		
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(50)">Reseller (Conditional) +/-</button></td><td><b>'.$vd.'</b></td><td id="reseller_part"></td><td></td></tr>';
	$html_text .= '<tr id="501" style="display:none"><td>reseller_tld_global_handle</td><td>'.$data[$pd]['registry']['reseller']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="502" style="display:none"><td>reseller_source_handle</td><td>'.$data[$pd]['registry']['reseller']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="503" style="display:none"><td>reseller_subject_identifier</td><td>'.$data[$pd]['registry']['reseller']['subject_identifier'].'</td><td id="reseller_subject_identifier"></td><td>'.$data[$pd]['registrar']['reseller']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="504" style="display:none"><td>reseller_organization_type</td><td>'.$data[$pd]['registry']['reseller']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['organization_type'].'</td></tr>';
	$html_text .= '<tr><td>reseller_organization_name</td><td>'.$data[$pd]['registry']['reseller']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['organization_name'].'</td></tr>';
	$html_text .= '<tr><td>reseller_presented_name</td><td>'.$data[$pd]['registry']['reseller']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="505" style="display:none"><td>reseller_kind</td><td>'.$data[$pd]['registry']['reseller']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['kind'].'</td></tr>';
	$html_text .= '<tr id="506" style="display:none"><td>reseller_name</td><td>'.$data[$pd]['registry']['reseller']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['name'].'</td></tr>';
	$html_text .= '<tr id="507" style="display:none"><td>reseller_email</td><td>'.$data[$pd]['registry']['reseller']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['email'].'</td></tr>';
	$html_text .= '<tr id="508" style="display:none"><td>reseller_contact_uri</td><td>'.$data[$pd]['registry']['reseller']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="509" style="display:none"><td>reseller_phone</td><td>'.$data[$pd]['registry']['reseller']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['phone'].'</td></tr>';
	$html_text .= '<tr id="5010" style="display:none"><td>reseller_country_code</td><td>'.$data[$pd]['registry']['reseller']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['country_code'].'</td></tr>';
	$html_text .= '<tr id="5011" style="display:none"><td>reseller_street_address</td><td>'.$data[$pd]['registry']['reseller']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['street_address'].'</td></tr>';
	$html_text .= '<tr id="5012" style="display:none"><td>reseller_city</td><td>'.$data[$pd]['registry']['reseller']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['city'].'</td></tr>';
	$html_text .= '<tr id="5013" style="display:none"><td>reseller_state_or_province</td><td>'.$data[$pd]['registry']['reseller']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="5014" style="display:none"><td>reseller_postal_code</td><td>'.$data[$pd]['registry']['reseller']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="5015" style="display:none"><td>reseller_country_name'.if_filled($data[$pd]['registry']['reseller']['country_name']).'</td><td>'.$data[$pd]['registry']['reseller']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['country_name'].'</td></tr>';
	$html_text .= '<tr id="5016" style="display:none"><td>reseller_preferred_languages</td><td>'.$data[$pd]['registry']['reseller']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="5017" style="display:none"><td>reseller_statuses</td><td>'.$data[$pd]['registry']['reseller']['statuses'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['statuses'].'</td></tr>';
	$html_text .= '<tr id="5018" style="display:none"><td>reseller_created_at</td><td>'.$data[$pd]['registry']['reseller']['created_at'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['created_at'].'</td></tr>';
	$html_text .= '<tr id="5019" style="display:none"><td>reseller_latest_data_mutation_at</td><td>'.$data[$pd]['registry']['reseller']['latest_data_mutation_at'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['latest_data_mutation_at'].'</td></tr>';
	$html_text .= '<tr id="5020" style="display:none"><td>reseller_identifier_received_at</td><td>'.$data[$pd]['registry']['reseller']['identifier_received_at'].'</td><td id="reseller_identifier_received_at"></td><td>'.$data[$pd]['registrar']['reseller']['identifier_received_at'].'</td></tr>';
	$html_text .= '<tr id="5021" style="display:none"><td>reseller_verification_set_at</td><td>'.$data[$pd]['registry']['reseller']['verification_set_at'].'</td><td id="reseller_verification_set_at"></td><td>'.$data[$pd]['registrar']['reseller']['verification_set_at'].'</td></tr>';
	$html_text .= '<tr id="5022" style="display:none"><td>reseller_verification_revoked_at</td><td>'.$data[$pd]['registry']['reseller']['verification_revoked_at'].'</td><td id="reseller_verification_revoked_at"></td><td>'.$data[$pd]['registrar']['reseller']['verification_revoked_at'].'</td></tr>';
	$html_text .= '<tr id="5023" style="display:none"><td>reseller_remarks</td><td>'.$data[$pd]['registry']['reseller']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['remarks'].'</td></tr>';
	$html_text .= '<tr id="5024" style="display:none"><td>reseller_links</td><td colspan="2">'.$data[$pd]['registry']['reseller']['links'].'</td><td>'.$data[$pd]['registrar']['reseller']['links'].'</td></tr>';
	$html_text .= '<tr id="5025" style="display:none"><td>reseller_data_uri</td><td>'.$data[$pd]['registry']['reseller']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="158" style="display:none"><td colspan="2">reseller_publication_state'.$data[$pd]['registry']['reseller']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['reseller']['publication_state'].'</td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(60)">Registrar +/-</button></td><td></td><td id="registrar_part"></td><td></td></tr>';
	$html_text .= '<tr id="601" style="display:none"><td>registrar_tld_global_handle</td><td>'.$data[$pd]['registry']['registrar']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="602" style="display:none"><td>registrar_source_handle</td><td>'.$data[$pd]['registry']['registrar']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="603" style="display:none"><td>registrar_subject_identifier</td><td>'.$data[$pd]['registry']['registrar']['subject_identifier'].'</td><td id="registrar_subject_identifier"></td><td>'.$data[$pd]['registrar']['registrar']['subject_identifier'].'</td></tr>';
	$html_text .= '<tr id="604" style="display:none"><td>registrar_organization_type</td><td>'.$data[$pd]['registry']['registrar']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['organization_type'].'</td></tr>';
	$html_text .= '<tr><td>registrar_organization_name</td><td>'.$data[$pd]['registry']['registrar']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['organization_name'].'</td></tr>';
	$html_text .= '<tr><td>registrar_presented_name</td><td>'.$data[$pd]['registry']['registrar']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="605" style="display:none"><td>registrar_kind</td><td>'.$data[$pd]['registry']['registrar']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['kind'].'</td></tr>';
	$html_text .= '<tr id="606" style="display:none"><td>registrar_name</td><td>'.$data[$pd]['registry']['registrar']['name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['name'].'</td></tr>';
	$html_text .= '<tr id="607" style="display:none"><td>registrar_email</td><td>'.$data[$pd]['registry']['registrar']['email'].'</td><td id="registrar_email"></td><td>'.$data[$pd]['registrar']['registrar']['email'].'</td></tr>';
	$html_text .= '<tr id="608" style="display:none"><td>registrar_contact_uri</td><td>'.$data[$pd]['registry']['registrar']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="609" style="display:none"><td>registrar_phone</td><td>'.$data[$pd]['registry']['registrar']['phone'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['phone'].'</td></tr>';
	$html_text .= '<tr id="6010" style="display:none"><td>registrar_country_code</td><td>'.$data[$pd]['registry']['registrar']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['country_code'].'</td></tr>';
	$html_text .= '<tr id="6011" style="display:none"><td>registrar_street_address</td><td>'.$data[$pd]['registry']['registrar']['street_address'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['street_address'].'</td></tr>';
	$html_text .= '<tr id="6012" style="display:none"><td>registrar_city</td><td>'.$data[$pd]['registry']['registrar']['city'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['city'].'</td></tr>';
	$html_text .= '<tr id="6013" style="display:none"><td>registrar_state_or_province</td><td>'.$data[$pd]['registry']['registrar']['state_or_province'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['state_or_province'].'</td></tr>';
	$html_text .= '<tr id="6014" style="display:none"><td>registrar_postal_code</td><td>'.$data[$pd]['registry']['registrar']['postal_code'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['postal_code'].'</td></tr>';
	$html_text .= '<tr id="6015" style="display:none"><td>registrar_country_name'.if_filled($data[$pd]['registry']['registrar']['country_name']).'</td><td>'.$data[$pd]['registry']['registrar']['country_name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['country_name'].'</td></tr>';
	$html_text .= '<tr id="6016" style="display:none"><td>registrar_preferred_languages</td><td>'.$data[$pd]['registry']['registrar']['preferred_languages'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['preferred_languages'].'</td></tr>';
	$html_text .= '<tr id="6017" style="display:none"><td>registrar_statuses</td><td>'.$data[$pd]['registry']['registrar']['statuses'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['statuses'].'</td></tr>';
	$html_text .= '<tr id="6018" style="display:none"><td>registrar_created_at</td><td>'.$data[$pd]['registry']['registrar']['created_at'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['created_at'].'</td></tr>';
	$html_text .= '<tr id="6019" style="display:none"><td>registrar_latest_data_mutation_at</td><td>'.$data[$pd]['registry']['registrar']['latest_data_mutation_at'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['latest_data_mutation_at'].'</td></tr>';
	$html_text .= '<tr id="6020" style="display:none"><td>registrar_identifier_received_at</td><td>'.$data[$pd]['registry']['registrar']['identifier_received_at'].'</td><td id="registrar_identifier_received_at"></td><td>'.$data[$pd]['registrar']['registrar']['identifier_received_at'].'</td></tr>';
	$html_text .= '<tr id="6021" style="display:none"><td>registrar_verification_set_at</td><td>'.$data[$pd]['registry']['registrar']['verification_set_at'].'</td><td id="registrar_verification_set_at"></td><td>'.$data[$pd]['registrar']['registrar']['verification_set_at'].'</td></tr>';
	$html_text .= '<tr id="6022" style="display:none"><td>registrar_verification_revoked_at</td><td>'.$data[$pd]['registry']['registrar']['verification_revoked_at'].'</td><td id="registrar_verification_revoked_at"></td><td>'.$data[$pd]['registrar']['registrar']['verification_revoked_at'].'</td></tr>';
	$html_text .= '<tr id="6023" style="display:none"><td>registrar_remarks</td><td>'.$data[$pd]['registry']['registrar']['remarks'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['remarks'].'</td></tr>';
	$html_text .= '<tr id="6024" style="display:none"><td>registrar_links</td><td colspan="2">'.$data[$pd]['registry']['registrar']['links'].'</td><td>'.$data[$pd]['registrar']['registrar']['links'].'</td></tr>';
	$html_text .= '<tr id="6025" style="display:none"><td>registrar_data_uri</td><td>'.$data[$pd]['registry']['registrar']['data_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['data_uri'].'</td></tr>';	
	$html_text .= '<tr id="159" style="display:none"><td colspan="2">registrar_publication_state'.$data[$pd]['registry']['registrar']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar']['publication_state'].'</td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(61)">Registrar Abuse +/-</button></td><td></td><td id="registrar_abuse_part"></td><td></td></tr>';
	$html_text .= '<tr id="611" style="display:none"><td>registrar_abuse_tld_global_handle</td><td>'.$data[$pd]['registry']['registrar_abuse']['tld_global_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['tld_global_handle'].'</td></tr>';
	$html_text .= '<tr id="612" style="display:none"><td>registrar_abuse_source_handle</td><td>'.$data[$pd]['registry']['registrar_abuse']['source_handle'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['source_handle'].'</td></tr>';
	$html_text .= '<tr id="613" style="display:none"><td>registrar_abuse_organization_type</td><td>'.$data[$pd]['registry']['registrar_abuse']['organization_type'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['organization_type'].'</td></tr>';
	$html_text .= '<tr id="614" style="display:none"><td>registrar_abuse_organization_name</td><td>'.$data[$pd]['registry']['registrar_abuse']['organization_name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['organization_name'].'</td></tr>';
	$html_text .= '<tr id="615" style="display:none"><td>registrar_abuse_presented_name</td><td>'.$data[$pd]['registry']['registrar_abuse']['presented_name'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['presented_name'].'</td></tr>';
	$html_text .= '<tr id="616" style="display:none"><td>registrar_abuse_kind</td><td>'.$data[$pd]['registry']['registrar_abuse']['kind'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['kind'].'</td></tr>';
	$html_text .= '<tr id="617" style="display:none"><td>registrar_abuse_email</td><td>'.$data[$pd]['registry']['registrar_abuse']['email'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['email'].'</td></tr>';
	$html_text .= '<tr id="618" style="display:none"><td>registrar_abuse_contact_uri</td><td>'.$data[$pd]['registry']['registrar_abuse']['contact_uri'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['contact_uri'].'</td></tr>';
	$html_text .= '<tr id="619" style="display:none"><td>registrar_abuse_phone</td><td>'.$data[$pd]['registry']['registrar_abuse']['phone'].'</td><td id="registrar_abuse_phone"></td><td>'.$data[$pd]['registrar']['registrar_abuse']['phone'].'</td></tr>';
	$html_text .= '<tr id="6110" style="display:none"><td>registrar_abuse_country_code</td><td>'.$data[$pd]['registry']['registrar_abuse']['country_code'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['country_code'].'</td></tr>';
	$html_text .= '<tr id="1510" style="display:none"><td colspan="2">registrar_abuse_publication_state'.$data[$pd]['registry']['registrar_abuse']['publication_state'].'</td><td></td><td>'.$data[$pd]['registrar']['registrar_abuse']['publication_state'].'</td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(63)">Nameserver Data +/-</button></td><td><b>'.$vd.'</b></td><td></td><td></td></tr>';
	$html_text .= '<tr id="631" style="display:none"><td>tld_global_handles</td><td colspan="2">'.$data[$pd]['registry']['nameservers']['tld_global_handles'].'</td><td>'.$data[$pd]['registrar']['nameservers']['tld_global_handles'].'</td></tr>';
	$html_text .= '<tr id="632" style="display:none"><td>source_handles</td><td colspan="2">'.$data[$pd]['registry']['nameservers']['source_handles'].'</td><td>'.$data[$pd]['registrar']['nameservers']['source_handles'].'</td></tr>';
	$html_text .= '<tr id="633" style="display:none"><td>ascii_names</td><td colspan="2">'.$data[$pd]['registry']['nameservers']['ascii_names'].'</td><td>'.$data[$pd]['registrar']['nameservers']['ascii_names'].'</td></tr>';
	$html_text .= '<tr id="634" style="display:none"><td>unicode_names</td><td colspan="2">'.$data[$pd]['registry']['nameservers']['unicode_names'].'</td><td>'.$data[$pd]['registrar']['nameservers']['unicode_names'].'</td></tr>';
	$html_text .= '<tr id="635" style="display:none"><td>ipv4_addresses</td><td>'.$data[$pd]['registry']['nameservers']['ipv4_addresses'].'</td><td style="vertical-align:bottom" id="nameservers_ipv4_addresses"></td><td>'.$data[$pd]['registrar']['nameservers']['ipv4_addresses'].'</td></tr>';
	$html_text .= '<tr id="636" style="display:none"><td>ipv6_addresses</td><td>'.$data[$pd]['registry']['nameservers']['ipv6_addresses'].'</td><td id="nameservers_ipv6_addresses"></td><td>'.$data[$pd]['registrar']['nameservers']['ipv6_addresses'].'</td></tr>';
	$html_text .= '<tr id="637" style="display:none"><td>statuses</td><td>'.$data[$pd]['registry']['nameservers']['statuses'].'</td><td></td><td>'.$data[$pd]['registrar']['nameservers']['statuses'].'</td></tr>';
	$html_text .= '<tr id="638" style="display:none"><td>nameserver_check_result</td><td>'.$data[$pd]['registry']['nameservers']['nameserver_check_result'].'</td><td id="br_tld"></td><td>'.$data[$pd]['registrar']['nameservers']['nameserver_check_result'].'</td></tr>';
	$html_text .= '<tr id="639" style="display:none"><td>nameserver_check_dates</td><td>'.$data[$pd]['registry']['nameservers']['nameserver_check_dates'].'</td><td></td><td>'.$data[$pd]['registrar']['nameservers']['nameserver_check_dates'].'</td></tr>';
	$html_text .= '<tr id="6310" style="display:none"><td>last_valid_nameserver_check_dates</td><td>'.$data[$pd]['registry']['nameservers']['last_valid_nameserver_check_dates'].'</td><td></td><td>'.$data[$pd]['registrar']['nameservers']['last_valid_nameserver_check_dates'].'</td></tr>';
	$html_text .= '<tr id="6311" style="display:none"><td>rdap_dnssec_signed</td><td>'.$data[$pd]['registry']['nameservers']['rdap_dnssec_signed'].'</td><td id="nameservers_rdap_dnssec_signed"></td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['nameservers']['rdap_dnssec_signed']).'</td></tr>';
	$html_text .= '<tr id="6312" style="display:none"><td>rdap_ds_key_tags</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['nameservers']['rdap_ds_key_tags']).'</td><td></td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['nameservers']['rdap_ds_key_tags']).'</td></tr>';
	$html_text .= '<tr id="6313" style="display:none"><td>rdap_ds_algorithm_numbers</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['nameservers']['rdap_ds_algorithm_numbers']).'</td><td id="nameservers_rdap_ds_algorithm_numbers"></td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['nameservers']['rdap_ds_algorithm_numbers']).'</td></tr>';
	$html_text .= '<tr id="6314" style="display:none"><td>rdap_ds_digest_types</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['nameservers']['rdap_ds_digest_types']).'</td><td id="nameservers_rdap_ds_digest_types"></td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['nameservers']['rdap_ds_digest_types']).'</td></tr>';
	$html_text .= '<tr id="6315" style="display:none"><td>rdap_ds_digests</td><td colspan="2">'.str_replace(',',',<br />',$data[$pd]['registry']['nameservers']['rdap_ds_digests']).'</td><td>'.str_replace(',',',<br />',$data[$pd]['registrar']['nameservers']['rdap_ds_digests']).'</td></tr>';
	$html_text .= '<tr id="6316" style="display:none"><td>measured_ds_key_tags</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['measured_ds_key_tags']).'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><b>measured_ds_algorithm_numbers</b></td><td><b>'.str_replace(',',',<br />',$data[$pd]['registry']['measured_ds_algorithm_numbers']).'</b></td><td id="measured_ds_algorithm_numbers"></td><td></td></tr>';
	$html_text .= '<tr id="6317" style="display:none"><td>measured_ds_algorithm_names</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['measured_ds_algorithm_names']).'</td><td id="measured_ds_algorithm_names"></td><td></td></tr>';
	$html_text .= '<tr id="6318" style="display:none"><td>measured_ds_digest_types</td><td>'.str_replace(',',',<br />',$data[$pd]['registry']['measured_ds_digest_types']).'</td><td></td><td></td></tr>';
	$html_text .= '<tr id="6319" style="display:none"><td>measured_ds_digests</td><td colspan="2">'.str_replace(',',',<br />',$data[$pd]['registry']['measured_ds_digests']).'</td><td></td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(75)">Raw RDAP +/-</button> | ' . ((!empty($raw_whois)) ? '<a href="'.$raw_whois.'" target="_blank">Whois Domain Information</a>' : 'No Whois Domain Information').'</td><td id="raw_data_next" colspan="2"></td><td></td></tr>';
	$html_text .= '<tr id="751" style="display:none;;"><td colspan="2">'.$data[$pd]['registry']['raw_rdap'].'</td><td></td><td>'.$data[$pd]['registrar']['raw_rdap'].'</td></tr>';
}
$html_text .= '</table></div></body></html>';
echo $html_text;

function getClientIP() {
    $ip = '';
    if (isset($_SERVER)) {
        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ipList = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
            $ip = trim($ipList[0]);
        } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipList = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
            $ip = trim($ipList[0]);
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
    }
    return $ip;
}

function get_block($ip) {
    $rdap_uri = "https://rdap.db.ripe.net/ip/".$ip;
    $response = @file_get_contents($rdap_uri);
	$data = json_decode($response, true);
	$country = '';
	if (!empty($data['country']))	{
    	$country = $data['country'];
	}
	$orgName = '';
	if (!empty($data['entities'])) {
        foreach ($data['entities'] as $entity) {
            if (isset($entity['vcardArray'][1])) {
                foreach ($entity['vcardArray'][1] as $vcardField) {
                    if ($vcardField[0] === 'fn') {
                        $orgName .= $vcardField[3].'; ';
                    }

                }
            }
        }
    }
	return (!empty($country)) ? $country . '; ' . $orgName : $orgName;	
}

function if_filled($inputvalue)	{
	if (!empty($inputvalue))	{
		return ' (to be empty) ⚠️';
	}
	return ' (to be empty)';
}

?>