<?php
session_start();  // is needed with no PHP Generator Scriptcase
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
if (!empty(trim($_GET['tld'])))	{
	$_GET["tld"] = trim($_GET['tld']);
	$_GET["tld"] = str_replace("'", "", $_GET["tld"]);
	$vd = mb_strtolower($_GET["tld"]);
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
	$vd = 'tld';
}
if (empty([ip]) or empty([block]))	{
	[ip] = getClientIP();
	[block] = get_block([ip]);
}
$internal = (str_contains([block],'Freedom')) ? '_internal_' : '';
$log_file = "/home/admin/logging/" . $internal . "tld_tool_" . $datetime->format('Ym') . ".txt";
$log_line = $datetime->format('Y-m-d H:i:s') . " UTC, lang" . $viewlanguage . ", " . $vd . ", " . [ip] . ", " . [block] . "\n";
file_put_contents($log_file, $log_line, FILE_APPEND);
echo '<!DOCTYPE html><html lang="en" style="font-size: 90%"><head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="robots" content="index">
<title>TLD Information</title>';
?><script>
	
function SwitchDisplay(type) {
	if (type == 21)			{ // governance notices
		var pre = '21';
		var max = 1
	}
	else if (type == 22)	{ // governance URIs
		var pre = '22';
		var max = 5
	}
	else if (type == 31)	{ // root URIs
		var pre = '31';
		var max = 4
	}
	else if (type == 32)	{ // function identifiers
		var pre = '32';
		var max = 1
	}
	else if (type == 33)	{ // indeterminate RDAP statuses
		var pre = '33';
		var max = 1
	}
	else if (type == 34)	{ // lifecycle period ranges
		var pre = '34';
		var max = 1
	}
	else if (type == 35)	{ // root accepted workload
		var pre = '35';
		var max = 1
	}
	else if (type == 38)	{ // tld links
		var pre = '38';
		var max = 1
	}
	else if (type == 39)	{ // tld properties
		var pre = '39';
		var max = 7
	}
	else if (type == 41)	{ // tld URIs
		var pre = '41';
		var max = 6
	}
	else if (type == 42)	{ // tld functions
		var pre = '42';
		var max = 1
	}
	else if (type == 43)	{ // tld relationships
		var pre = '43';
		var max = 1
	}
	else if (type == 44)	{ // tld accepted workload
		var pre = '44';
		var max = 1
	}
	else if (type == 51)	{ // lifecycle information
		var pre = '51';
		var max = 2
	}
	else if (type == 52)	{ // status meanings
		var pre = '52';
		var max = 1
	}
	else if (type == 53)	{ // operational_periods
		var pre = '53';
		var max = 1
	}
	else if (type == 61)	{ // nameservers
		var pre = '61';
		var max = 10
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

function SwitchTranslation(translation)	{
	document.getElementById("language").value = translation;
	if (translation == 99)	{
		var modified = '';
		var proposed = '';
		var legacy = '';
		document.getElementById("title").textContent = "TLD Information";
		document.getElementById("instruction").textContent = "Enter here:";
		document.getElementById("modeling").textContent = "";
		document.getElementById("field").textContent = "Modeled in snake_case";
		document.getElementById("explanation").textContent = "";
		document.getElementById("governance_notices").textContent = legacy;		
		document.getElementById("governance_part").textContent = proposed;
		document.getElementById("governance_services_uri").textContent = proposed;
		document.getElementById("governance_policies_uri").textContent = proposed;
		document.getElementById("governance_privacy_policy_uri").textContent = proposed;
		document.getElementById("governance_delegation_uri").textContent = proposed;		
		document.getElementById("governance_registrar_accreditation_uri").textContent = proposed;	
		document.getElementById("root_part").textContent = proposed;
		document.getElementById("root_services_uri").textContent = proposed;
		document.getElementById("root_tlds_uri").textContent = proposed;
		document.getElementById("root_registrar_ids_uri").textContent = proposed;
		document.getElementById("root_lookup_endpoints_uri").textContent = proposed;
		document.getElementById("root_function_identifiers").textContent = proposed;
		document.getElementById("root_ambiguous_rdap_statuses").textContent = proposed;
		document.getElementById("root_lifecycle_period_ranges").textContent = proposed;
		document.getElementById("root_accepted_workload").textContent = proposed;
		document.getElementById("tld_links").textContent = legacy;	
		document.getElementById("tld_part").textContent = "";
		document.getElementById("tld_data_active_from").textContent = proposed;
		document.getElementById("tld_category").textContent = proposed;
		document.getElementById("tld_type").textContent = proposed;
		document.getElementById("tld_ascii_name").textContent = modified;
		document.getElementById("tld_unicode_name").textContent = modified;
		document.getElementById("tld_statuses").textContent = modified;
		document.getElementById("tld_storage_model").textContent = proposed;
		document.getElementById("tld_response_model").textContent = proposed;
		document.getElementById("tld_search_engine_deletion_phase_ready").textContent = proposed;
		document.getElementById("tld_services_uri").textContent = proposed;		
		document.getElementById("tld_standardized_price_list_uri").textContent = proposed;
		document.getElementById("tld_root_data_uri").textContent = modified;
		document.getElementById("tld_registry_data_uri").textContent = proposed;
		document.getElementById("tld_data_usage_policy_uri").textContent = proposed;
		document.getElementById("tld_privacy_policy_uri").textContent = proposed;
		document.getElementById("tld_functions").textContent = proposed;
		document.getElementById("tld_accepted_workload").textContent = proposed;
		document.getElementById("tld_relationships").textContent = proposed;
		document.getElementById("lifecycle_part").textContent = proposed;
		document.getElementById("lifecycle_data_active_from").textContent = proposed;
		document.getElementById("lifecycle_upon_termination").textContent = proposed;
		document.getElementById("lifecycle_status_meanings").textContent = proposed;
		document.getElementById("lifecycle_operational_periods").textContent = proposed;
		document.getElementById("nameservers_part").textContent = "";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "";
		document.getElementById("nameservers_ip").textContent = "";
	}
	else if (translation == 1)	{
		var modified = '(Gewijzigd) ';
		var proposed = '(Nieuw) ';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "TLD-informatie";
		document.getElementById("instruction").textContent = "Geef hier in:";
		document.getElementById("modeling").textContent = "Een voorgesteld informatiemodel voor consistente TLD-relaties, governance en de verdere ontwikkeling van RDAP.";
		document.getElementById("field").textContent = "Gemodelleerd in snake_case";
		document.getElementById("explanation").textContent = "TLD RDAP-model dat IANA-gegevens combineert met TLD-specifieke gegevens.";
		document.getElementById("governance_notices").textContent = legacy;
		document.getElementById("governance_part").textContent = proposed;
		document.getElementById("governance_services_uri").textContent = proposed + "Governancegerelateerde diensten en informatie die worden aangeboden onder ICANN-beleid."; 
		document.getElementById("governance_policies_uri").textContent = proposed;
		document.getElementById("governance_privacy_policy_uri").textContent = proposed;
		document.getElementById("governance_delegation_uri").textContent = proposed + 'URI die verwijst naar het delegatierecord voor de TLD onder toepasselijk governancebeleid.';		
		document.getElementById("governance_registrar_accreditation_uri").textContent = proposed + "Informatieve pagina over registrar-accreditatie onder ICANN-beleid.";
		document.getElementById("root_part").textContent = proposed;
		document.getElementById("root_services_uri").textContent = proposed + "De Internet Assigned Numbers Authority beheert DNS-rootdiensten en coördineert IP- en AS-toewijzingen.";
		document.getElementById("root_tlds_uri").textContent = proposed + 'Lijst van TLD’s beheerd door de Internet Assigned Numbers Authority in de DNS-root.';
		document.getElementById("root_registrar_ids_uri").textContent = proposed + "Lijst van registrar-identificaties beheerd door de Internet Assigned Numbers Authority.";
		document.getElementById("root_lookup_endpoints_uri").textContent = proposed + "Versie-endpoints (bijv. /v1, /v2) ondersteunen RDAP-evolutie.";
		document.getElementById("root_function_identifiers").textContent = proposed + "Deze functiebenamingen zijn voorlopig. Ze kunnen nog veranderen.";
		document.getElementById("root_ambiguous_rdap_statuses").textContent = proposed + "Deze statussen zijn niet betrouwbaar interpreteerbaar.";
		document.getElementById("root_lifecycle_period_ranges").textContent = proposed + "Registries kunnen baat hebben bij gedeelde richtlijnen.";
		document.getElementById("root_accepted_workload").textContent = proposed + "IANA-servers kunnen in de toekomst limieten toepassen.";
		document.getElementById("tld_links").textContent = legacy;
		document.getElementById("tld_part").textContent = "Top-Level Domain (TLD)";
		document.getElementById("tld_data_active_from").textContent = proposed;
		document.getElementById("tld_category").textContent = proposed + 'Geeft een generieke TLD (gTLD) of een landcode-TLD (ccTLD) aan.';
		document.getElementById("tld_type").textContent = proposed + 'Het TLD-type, bijvoorbeeld gTLD, grTLD, sTLD, ccTLD, tTLD, iTLD of geoTLD.';
		document.getElementById("tld_ascii_name").textContent = modified;
		document.getElementById("tld_unicode_name").textContent = modified;
		document.getElementById("tld_statuses").textContent = modified;
		document.getElementById("tld_storage_model").textContent = proposed + "Opslagmodel voor TLD-domeingegevens (thin of thick).";
		document.getElementById("tld_response_model").textContent = proposed + "RDAP-responsmodel voor domeingegevens (thin, delegated of thick).";
		document.getElementById("tld_search_engine_deletion_phase_ready").textContent = proposed + 'Of zoekmachines kunnen vertrouwen op de “pending delete”-fase om resultaten te verwijderen.';
		document.getElementById("tld_services_uri").textContent = proposed + 'Een TLD-specifiek informatiemenu, beschikbaar onder een subdomein zoals "regmenu".';
		document.getElementById("tld_standardized_price_list_uri").textContent = proposed + "Een machineleesbare gestandaardiseerde prijslijst zou de transparantie van registry-diensten ondersteunen.";
		document.getElementById("tld_root_data_uri").textContent = modified + "Machineleesbare, informatieve root-TLD- en RDAP-gegevens.";
		document.getElementById("tld_registry_data_uri").textContent = proposed + "Machineleesbare, informatieve registry-TLD-gegevens."; 
		document.getElementById("tld_data_usage_policy_uri").textContent = proposed + "Beperkt gebruik. Interpretatie hangt af van TLD- en RDAP-context.";
		document.getElementById("tld_privacy_policy_uri").textContent = proposed;
		document.getElementById("tld_functions").textContent = proposed + "Een formeel functioneel aanspreekbaarheidsmodel onderscheidt juridische en gepresenteerde identiteiten.";
		document.getElementById("tld_accepted_workload").textContent = proposed + "Deze modellering ondersteunt de modernisering van IANA-databasetabellen.";
		document.getElementById("tld_relationships").textContent = proposed;
		document.getElementById("lifecycle_part").textContent = proposed;
		document.getElementById("lifecycle_data_active_from").textContent = proposed;
		document.getElementById("lifecycle_upon_termination").textContent = proposed;
		document.getElementById("lifecycle_status_meanings").textContent = proposed + "Let op: Er bestaat een globale tabeldefinitie; ICANN speelt nog geen leidende rol.";
		document.getElementById("lifecycle_operational_periods").textContent = proposed + 'Meerjarige registratie soms mogelijk; max. verschilt per TLD en registrar.';
		document.getElementById("nameservers_part").textContent = "Authoritatieve zone: DNS-gegevens die door deze nameservers worden geserveerd.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC beveiligt DNS tegen spoofing en cachevergiftiging.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algoritmen 13–16 zijn actueel. IANA voert algoritme 8 als RECOMMENDED, geldt als uitlopend.";
		document.getElementById("nameservers_ip").textContent = "IP addresses from RDAP, if available. Usually present for TLD zones.";
	}
	else if (translation == 2)	{
		var modified = '(Modified) ';
		var proposed = '(New) ';
		var legacy = '(Legacy) ';		
		document.getElementById("title").textContent = "TLD Information";
		document.getElementById("instruction").textContent = "Enter here:";
		document.getElementById("modeling").textContent = "A proposed information model for consistent TLD relationships, governance and RDAP evolution.";
		document.getElementById("field").textContent = "Modeled in snake_case";
		document.getElementById("explanation").textContent = "TLD RDAP model combining IANA data with TLD-specific data.";
		document.getElementById("governance_notices").textContent = legacy;
		document.getElementById("governance_part").textContent = proposed;
		document.getElementById("governance_services_uri").textContent = proposed + "Governance-related services and information provided under ICANN policy.";
		document.getElementById("governance_policies_uri").textContent = proposed;
		document.getElementById("governance_privacy_policy_uri").textContent = proposed;
		document.getElementById("governance_delegation_uri").textContent = proposed + 'URI pointing to the delegation record for the TLD under applicable governance.';		
		document.getElementById("governance_registrar_accreditation_uri").textContent = proposed + "Informational page on registrar accreditation under ICANN policy.";
		document.getElementById("root_part").textContent = proposed;
		document.getElementById("root_services_uri").textContent = proposed + "IANA manages DNS root services and coordinates IP and AS allocations.";
		document.getElementById("root_tlds_uri").textContent = proposed + 'List of TLDs maintained by IANA in the DNS root.';
		document.getElementById("root_registrar_ids_uri").textContent = proposed + "List of registrar identifiers maintained by IANA.";		
		document.getElementById("root_lookup_endpoints_uri").textContent = proposed + "Versioned endpoints (e.g. /v1, /v2) support RDAP evolution.";
		document.getElementById("root_function_identifiers").textContent = proposed + "These function names are draft. They may change.";
		document.getElementById("root_ambiguous_rdap_statuses").textContent = proposed + "These statuses are not reliably interpretable.";
		document.getElementById("root_lifecycle_period_ranges").textContent = proposed + "Registries may benefit from shared timing guidelines.";
		document.getElementById("root_accepted_workload").textContent = proposed + "IANA servers may apply limits in the future.";
		document.getElementById("tld_links").textContent = legacy;
		document.getElementById("tld_part").textContent = "Top-Level Domain (TLD)";
		document.getElementById("tld_data_active_from").textContent = proposed;
		document.getElementById("tld_category").textContent = proposed + 'Indicates generic TLD (gTLD) or a country-code TLD (ccTLD).';
		document.getElementById("tld_type").textContent = proposed + 'The TLD type, such as gTLD, grTLD, sTLD, ccTLD, tTLD, iTLD, or geoTLD.';
		document.getElementById("tld_ascii_name").textContent = modified;
		document.getElementById("tld_unicode_name").textContent = modified;		
		document.getElementById("tld_statuses").textContent = modified;
		document.getElementById("tld_storage_model").textContent = proposed + "Storage model for TLD domain data (thin or thick).";
		document.getElementById("tld_response_model").textContent = proposed + "RDAP response model for domain data (thin, delegated or thick).";
		document.getElementById("tld_search_engine_deletion_phase_ready").textContent = proposed + 'Whether search engines can rely on the “pending delete” phase to remove results.';
		document.getElementById("tld_services_uri").textContent = proposed + 'A TLD specific information menu, available under a subdomain such as "regmenu".';
		document.getElementById("tld_standardized_price_list_uri").textContent = proposed + "A machine-readable standardized price list would support registry transparency.";
		document.getElementById("tld_root_data_uri").textContent = modified + "Machine-readable, informative root TLD and RDAP data.";
		document.getElementById("tld_registry_data_uri").textContent = proposed + "Machine-readable, informative registry TLD data.";
		document.getElementById("tld_data_usage_policy_uri").textContent = proposed + "Restricted use. Interpretation depends on TLD and RDAP context.";
		document.getElementById("tld_privacy_policy_uri").textContent = proposed;		
		document.getElementById("tld_functions").textContent = proposed + "A formal function addressability model distinguishes legal and presented identities.";
		document.getElementById("tld_accepted_workload").textContent = proposed + "This modeling supports IANA database table modernization efforts.";
		document.getElementById("tld_relationships").textContent = proposed;
		document.getElementById("lifecycle_part").textContent = proposed;
		document.getElementById("lifecycle_data_active_from").textContent = proposed;
		document.getElementById("lifecycle_upon_termination").textContent = proposed;	
		document.getElementById("lifecycle_status_meanings").textContent = proposed + "Note: A global table definition exists; ICANN is not yet in a leading role.";
		document.getElementById("lifecycle_operational_periods").textContent = proposed + 'Multi-year registration sometimes possible; max varies by TLD & registrar.';
		document.getElementById("nameservers_part").textContent = "Authoritative zone: DNS data served by these nameservers.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC secures DNS against spoofing and cache poisoning.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algorithms 13–16 are current. IANA lists algorithm 8 as RECOMMENDED, considered phasing out.";
		document.getElementById("nameservers_ip").textContent = "IP addresses from RDAP, if available. Usually present for TLD zones.";
	}
	else if (translation == 3)	{
		var modified = '(Geändert) ';
		var proposed = '(Neu) ';
		var legacy = '(Legacy) ';		
		document.getElementById("title").textContent = "TLD-Informationen";
		document.getElementById("instruction").textContent = "Hier eingeben:";
		document.getElementById("modeling").textContent = "Ein vorgeschlagenes Informationsmodell für konsistente TLD-Beziehungen, Governance und die Weiterentwicklung von RDAP.";
		document.getElementById("field").textContent = "Modelliert in snake_case";
		document.getElementById("explanation").textContent = "TLD-RDAP-Modell, das IANA-Daten mit TLD-spezifischen Daten kombiniert.";
		document.getElementById("governance_notices").textContent = legacy;
		document.getElementById("governance_part").textContent = proposed;
		document.getElementById("governance_services_uri").textContent = proposed + "Governancebezogene Dienste und Informationen, die im Rahmen der ICANN-Richtlinien bereitgestellt werden.";
		document.getElementById("governance_policies_uri").textContent = proposed;
		document.getElementById("governance_privacy_policy_uri").textContent = proposed;
		document.getElementById("governance_delegation_uri").textContent = proposed + 'URI, die auf den Delegationseintrag für die TLD unter der jeweils geltenden Governance verweist.';
		document.getElementById("governance_registrar_accreditation_uri").textContent = proposed + "Informationsseite zur Registrar-Akkreditierung im Rahmen der ICANN-Richtlinien.";
		document.getElementById("root_part").textContent = proposed;
		document.getElementById("root_services_uri").textContent = proposed + "Die Internet Assigned Numbers Authority verwaltet DNS-Root und koordiniert IP- und AS-Zuweisungen.";
		document.getElementById("root_tlds_uri").textContent = proposed + 'Liste der von der Internet Assigned Numbers Authority in der DNS-Root verwalteten TLDs.';
		document.getElementById("root_registrar_ids_uri").textContent = proposed + "Liste der von der Internet Assigned Numbers Authority verwalteten Registrar-Kennungen.";
		document.getElementById("root_lookup_endpoints_uri").textContent = proposed + "Versionierte Endpunkte (z. B. /v1, /v2) unterstützen RDAP-Evolution.";
		document.getElementById("root_function_identifiers").textContent = proposed + "Diese Funktionsnamen sind vorläufig und können sich ändern.";
		document.getElementById("root_ambiguous_rdap_statuses").textContent = proposed + "Diese Statusangaben sind nicht zuverlässig interpretierbar.";
		document.getElementById("root_lifecycle_period_ranges").textContent = proposed + "Viele Registries können von gemeinsamen Regeln profitieren.";
		document.getElementById("root_accepted_workload").textContent = proposed + "IANA kann zukünftig Limits festlegen.";
		document.getElementById("tld_links").textContent = legacy;		
		document.getElementById("tld_part").textContent = "Top-Level Domain (TLD)";
		document.getElementById("tld_data_active_from").textContent = proposed;
		document.getElementById("tld_root_data_uri").textContent = modified + "Maschinenlesbare, informative Root-TLD- und RDAP-Daten.";
		document.getElementById("tld_registry_data_uri").textContent = proposed + "Maschinenlesbare, informative Registry-TLD-Daten.";
		document.getElementById("tld_category").textContent = proposed + 'Zeigt eine generische TLD (gTLD) oder eine länderspezifische TLD (ccTLD) an.';
		document.getElementById("tld_type").textContent = proposed + 'Der TLD-Typ, z. B. gTLD, grTLD, sTLD, ccTLD, tTLD, iTLD oder geoTLD.';
		document.getElementById("tld_ascii_name").textContent = modified;
		document.getElementById("tld_unicode_name").textContent = modified;		
		document.getElementById("tld_statuses").textContent = modified;
		document.getElementById("tld_storage_model").textContent = proposed + "Speichermodell für TLD-Domaindaten (thin oder thick).";
		document.getElementById("tld_response_model").textContent = proposed + "RDAP-Antwortmodell für Domaindaten (thin, delegated oder thick).";
		document.getElementById("tld_standardized_price_list_uri").textContent = proposed + "Eine maschinenlesbare standardisierte Preisliste würde die Transparenz von Registry-Diensten unterstützen.";
		document.getElementById("tld_services_uri").textContent = proposed + 'Ein TLD-spezifisches Informationsmenü, verfügbar unter einer Subdomäne wie "regmenu".';
		document.getElementById("tld_data_usage_policy_uri").textContent = proposed + "Eingeschränkte Nutzung. Interpretation hängt vom TLD- und RDAP-Kontext ab.";
		document.getElementById("tld_privacy_policy_uri").textContent = proposed;		
		document.getElementById("tld_search_engine_deletion_phase_ready").textContent = proposed + "Ob Suchmaschinen sich auf die 'Pending-Delete-Phase' verlassen können, um Ergebnisse zu entfernen.";
		document.getElementById("tld_functions").textContent = proposed + "Ein formales Funktions-Adressierbarkeitsmodell unterscheidet rechtliche und dargestellte Identitäten.";
		document.getElementById("tld_accepted_workload").textContent = proposed + "Dieses Modell unterstützt die Modernisierung der IANA-Datenbanktabellen."; 
		document.getElementById("tld_relationships").textContent = proposed;
		document.getElementById("lifecycle_part").textContent = proposed;
		document.getElementById("lifecycle_data_active_from").textContent = proposed;
		document.getElementById("lifecycle_upon_termination").textContent = proposed;
		document.getElementById("lifecycle_status_meanings").textContent = proposed + "Hinweis: Eine globale Tabellendefinition existiert; ICANN übernimmt noch keine führende Rolle.";
		document.getElementById("lifecycle_operational_periods").textContent = proposed + 'Mehrjährige Registrierung teils möglich; max. variiert je nach TLD und Registrar.';
		document.getElementById("nameservers_part").textContent = "Autoritative Zone: DNS-Daten, die von diesen Nameservern bereitgestellt werden.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC sichert DNS gegen Spoofing und Cache-Poisoning.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Algorithmen 13–16 sind aktuell. IANA führt Algorithmus 8 als RECOMMENDED, gilt als auslaufend.";
		document.getElementById("nameservers_ip").textContent = "RDAP-IP-Adressen, sofern verfügbar. Meist für TLD-Zonen vorhanden.";
	}
	else if (translation == 4)	{
		var modified = '(Modifié) ';
		var proposed = '(Nouveau) ';
		var legacy = '(Legacy) ';		
		document.getElementById("title").textContent = "Informations TLD — modélisées";
		document.getElementById("instruction").textContent = "Saisissez ici :";
		document.getElementById("modeling").textContent = "Un modèle d'information proposé pour des relations TLD cohérentes, la gouvernance et l'évolution du RDAP.";
		document.getElementById("field").textContent = "Modélisé en snake_case";
		document.getElementById("explanation").textContent = "Modèle RDAP de TLD combinant les données de l’IANA avec des données spécifiques au TLD.";
		document.getElementById("governance_notices").textContent = legacy;
		document.getElementById("governance_part").textContent = proposed;
		document.getElementById("governance_services_uri").textContent = proposed + "Services et informations liés à la gouvernance fournis dans le cadre de la politique de l’ICANN.";
		document.getElementById("governance_policies_uri").textContent = proposed;
		document.getElementById("governance_privacy_policy_uri").textContent = proposed;
		document.getElementById("governance_delegation_uri").textContent = proposed + "URI pointant vers l’enregistrement de délégation du TLD selon la gouvernance applicable.";
		document.getElementById("governance_registrar_accreditation_uri").textContent = proposed + "Page d’information sur l’accréditation des registrars sous politique ICANN.";	
		document.getElementById("root_part").textContent = proposed;
		document.getElementById("root_services_uri").textContent = proposed + "L’Internet Assigned Numbers Authority gère la racine DNS et coordonne IP et AS.";
		document.getElementById("root_tlds_uri").textContent = proposed + "Liste des TLD maintenus par l’Internet Assigned Numbers Authority dans la racine DNS.";
		document.getElementById("root_registrar_ids_uri").textContent = proposed + "Liste des identifiants de bureaux d’enregistrement maintenue par l’Internet Assigned Numbers Authority.";
		document.getElementById("root_lookup_endpoints_uri").textContent = proposed + "Endpoints versionnés (p. ex. /v1, /v2) soutiennent l’évolution RDAP.";
		document.getElementById("root_function_identifiers").textContent = proposed + "Ces noms de fonctions sont provisoires. Ils peuvent changer.";
		document.getElementById("root_ambiguous_rdap_statuses").textContent = proposed + "Ces statuts ne sont pas interprétables de manière fiable.";
		document.getElementById("root_lifecycle_period_ranges").textContent = proposed + "Les registres peuvent profiter de lignes directrices communes.";
		document.getElementById("root_accepted_workload").textContent = proposed + "Les serveurs IANA pourront appliquer des limites.";
		document.getElementById("tld_links").textContent = legacy;	
		document.getElementById("tld_part").textContent = "Top-Level Domain (TLD)";
		document.getElementById("tld_data_active_from").textContent = proposed;
		document.getElementById("tld_category").textContent = proposed + "Indique un TLD générique (gTLD) ou un TLD de code pays (ccTLD).";
		document.getElementById("tld_type").textContent = proposed + "Le type de TLD, tel que gTLD, grTLD, sTLD, ccTLD, tTLD, iTLD ou geoTLD.";
		document.getElementById("tld_ascii_name").textContent = modified;
		document.getElementById("tld_unicode_name").textContent = modified;		
		document.getElementById("tld_statuses").textContent = modified;
		document.getElementById("tld_storage_model").textContent = proposed + "Mode de stockage des données de domaine du TLD (thin ou thick).";
		document.getElementById("tld_response_model").textContent = proposed + "Mode de réponse RDAP pour les données de domaine (thin, delegated ou thick).";
		document.getElementById("tld_search_engine_deletion_phase_ready").textContent = proposed + "Si les moteurs de recherche peuvent se fier à la phase 'pending delete' pour supprimer des résultats.";
		document.getElementById("tld_services_uri").textContent = proposed + "Un menu d'informations spécifique au TLD, disponible sous un sous-domaine tel que 'regmenu'.";
		document.getElementById("tld_standardized_price_list_uri").textContent = proposed + "Une liste de prix standardisée lisible par machine soutiendrait la transparence des services de registre.";
		document.getElementById("tld_root_data_uri").textContent = modified + "Données TLD racine et RDAP lisibles par machine et informatives.";
		document.getElementById("tld_registry_data_uri").textContent = proposed + "Données TLD du registre lisibles par machine et informatives.";
		document.getElementById("tld_data_usage_policy_uri").textContent = proposed + "Utilisation restreinte. L’interprétation dépend du contexte TLD et RDAP.";
		document.getElementById("tld_privacy_policy_uri").textContent = proposed;		
		document.getElementById("tld_functions").textContent = proposed + "Un modèle formel d'adressabilité des fonctions distingue les identités légales et présentées.";
		document.getElementById("tld_accepted_workload").textContent = proposed + "Cette modélisation soutient la modernisation des tables de la base de données IANA.";
		document.getElementById("tld_relationships").textContent = proposed;
		document.getElementById("lifecycle_part").textContent = proposed;
		document.getElementById("lifecycle_data_active_from").textContent = proposed;
		document.getElementById("lifecycle_upon_termination").textContent = proposed;
		document.getElementById("lifecycle_status_meanings").textContent = proposed + "Remarque : Une définition de table globale existe ; l’ICANN ne joue pas encore un rôle de premier plan.";
		document.getElementById("lifecycle_operational_periods").textContent = proposed + "Enregistrement pluriannuel parfois possible ; max. selon TLD et bureau d’enregistrement.";
		document.getElementById("nameservers_part").textContent = "Zone autoritative : données DNS servies par ces serveurs de noms.";
		document.getElementById("nameservers_rdap_dnssec_signed").textContent = "DNSSEC sécurise le DNS contre le spoofing et l’empoisonnement.";
		document.getElementById("nameservers_rdap_ds_algorithm_numbers").textContent = "Les algorithmes 13–16 sont actuels. L’IANA classe l’algorithme 8 comme RECOMMENDED, en fin de vie.";
		document.getElementById("nameservers_ip").textContent = "Adresses IP RDAP, si disponibles. Généralement présentes pour les zones TLD.";
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
$rdap_uri = $server_uri.'/tld_data/index.php?tld='.$pd;
if (@get_headers($rdap_uri))	{ 
	$json = file_get_contents($rdap_uri) or die("An entered tld could not be read.");
	$data = json_decode($json, true);
}
if	(is_null($data))	{
	$reopen = $server_uri.'/tld/index.php?tld=tld';
	sc_redir($reopen);
}
$html_text = '<body onload=SwitchTranslation('.$viewlanguage.')><div style="border-collapse:collapse; line-height:120%">
<table style="font-family:Helvetica, Arial, sans-serif; font-size: 1rem; table-layout: fixed; width:1675px">
<tr><th style="width:325px"></th><th style="width:300px"></th><th style="width:750px"></th><th style="width:300px"></tr>';
$html_text .= '<tr style="font-size: .8rem"><td colspan="2" id="title" style="font-size: 1.4rem;color:blue;font-weight:bold"></td><td id="modeling"></td><td></td></tr>';
$html_text .= '<tr style="font-size: .8rem"><td id="instruction" style="vertical-align:middle; text-align: right"></td><td><form action='.htmlentities($_SERVER['PHP_SELF']).' method="get">
	<input type="hidden" id="language" name="language" value='.$viewlanguage.'>	
	<input type="text" style="width:90%" id="tld" name="tld" value='.$vd.'></form></td><td>
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(99)">None</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(1)">nl_NL</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(2)">en_US</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(3)">de_DE</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(4)">fr_FR</button> 
	<a style="font-size: 0.9rem" href="https://relationships.hostingtool.nl/menu_modeling" target="_blank">Menu modeling</a> - <a style="font-size: 0.9rem" href="https://github.com/janwillemstegink/relationships.hostingtool.nl" target="_blank">Code/issues on GitHub</a> - <a style="font-size: 0.9rem" href="https://janwillemstegink.nl/" target="_blank">Insight at janwillemstegink.nl</a></td><td></td></tr>';
if (true or $pd == mb_strtolower($data[$pd]['domain']['ascii_name']) or empty($data[$pd]['domain']['ascii_name']))	{
	$html_text .= '<tr style="font-size:1.05rem;font-weight:bold"><td id="field"></td><td>tld_from_root</td><td id="explanation"></td><td>tld_from_registry (modeled)</td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(21)">Governance Notices +/-</button><td></td><td id="governance_notices"></td><td>data moved</td></tr>';
	$html_text .= '<tr id="211" style="display:none;vertical-align:top"><td colspan="3">'.$data[$pd]['governance']['notices'].'</td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(22)">Governance URIs +/-</button><td></td><td id="governance_part"></td><td>data partial</td></tr>';
	$html_text .= '<tr id="221" style="display:table-row"><td>governance_services_uri</td><td><a href='.$data[$pd]['governance']['services_uri'].' target="_blank">Governance Services</a></td><td id="governance_services_uri"></td><td></td></tr>';
	$html_text .= '<tr id="222" style="display:table-row"><td>governance_policies_uri</td><td>'.((!empty($data[$pd]['governance']['policies_uri'])) ? '<a href='.$data[$pd]['governance']['policies_uri'].' target="_blank">Governance Policies</a>' : '').'</td><td id="governance_policies_uri"></td><td></td></tr>';
	$html_text .= '<tr id="223" style="display:table-row"><td>governance_privacy_policy_uri</td><td>'.((!empty($data[$pd]['governance']['privacy_policy_uri'])) ? '<a href='.$data[$pd]['governance']['privacy_policy_uri'].' target="_blank">Governance Privacy</a>' : '').'</td><td id="governance_privacy_policy_uri"></td><td></td></tr>';
	$html_text .= '<tr id="224" style="display:table-row"><td>governance_delegation_uri</td><td><a href='.$data[$pd]['governance']['delegation_uri'].' target="_blank">TLD Delegation</a></td><td id="governance_delegation_uri"></td><td></td></tr>';
	$html_text .= '<tr id="225" style="display:table-row"><td>governance_registrar_accreditation_uri</td><td><a href='.$data[$pd]['governance']['registrar_accreditation_uri'].' target="_blank">Registrar Accreditation</a></td><td id="governance_registrar_accreditation_uri"></td><td></td></tr>';	
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(31)">Root URIs +/-</button></td><td></td><td id="root_part"></td><td>data partial</td></tr>';	
	$html_text .= '<tr id="311" style="display:table-row"><td>root_services_uri</td><td><a href='.$data[$pd]['root']['services_uri'].' target="_blank">Root Services</a></td><td id="root_services_uri"></td><td></td></tr>';
	$html_text .= '<tr id="312" style="display:table-row"><td>root_tlds_uri</td><td><a href='.$data[$pd]['root']['tlds_uri'].' target="_blank">Root TLDs</a></td><td id="root_tlds_uri"></td><td></td></tr>';
	$html_text .= '<tr id="313" style="display:table-row"><td>root_registrar_ids_uri</td><td><a href='.$data[$pd]['root']['registrar_ids_uri'].' target="_blank">Registrar IDs</a></td><td id="root_registrar_ids_uri"></td><td></td></tr>';
	$html_text .= '<tr id="314" style="display:table-row"><td>root_lookup_endpoints_uri</td><td><a href='.$data[$pd]['root']['lookup_endpoints_uri'].' target="_blank">Lookup Endpoints</a></td><td id="root_lookup_endpoints_uri"></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(32)">Function Identifiers +/-</button></td><td></td><td id="root_function_identifiers"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="321" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['root']['function_identifiers'].'</td><td></td><td></td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(33)">Ambiguous RDAP Statuses +/-</button></td><td></td><td id="root_ambiguous_rdap_statuses"></td><td>data needed</td></tr>';	
	$html_text .= '<tr id="331" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['root']['ambiguous_rdap_statuses'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(34)">Lifecycle Period Ranges +/-</button></td><td></td><td id="root_lifecycle_period_ranges"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="341" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['root']['lifecycle_period_ranges'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(35)">Root Accepted Workload +/-</button></td><td></td><td id="root_accepted_workload"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="351" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['root']['accepted_workload'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(38)">TLD Links +/-</button><td></td><td id="tld_links"></td><td>data moved</td></tr>';
	$html_text .= '<tr id="381" style="display:none;vertical-align:top"><td colspan="3">'.$data[$pd]['tld']['links'].'</td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(39)">TLD Properties +/-</button></td><td><b>'.$vd.'</b></td><td id="tld_part"></td><td>data partial</td></tr>';
	$html_text .= '<tr id="391" style="display:none"><td>tld_data_active_from</td><td> '.$data[$pd]['tld']['data_active_from'].'</td><td id="tld_data_active_from"></td><td></td></tr>';
	$html_text .= '<tr><td>tld_category</td><td>'.$data[$pd]['tld']['category'].'</td><td id="tld_category"></td><td></td></tr>';
	$html_text .= '<tr><td>tld_type</td><td>'.$data[$pd]['tld']['type'].'</td><td id="tld_type"></td><td></td></tr>';
	$html_text .= '<tr id="392" style="display:none"><td>tld_ascii_name</td><td>'.$data[$pd]['tld']['ascii_name'].'</td><td id="tld_ascii_name"></td><td></td></tr>';
	$html_text .= '<tr id="393" style="display:none"><td>tld_unicode_name</td><td>'.$data[$pd]['tld']['unicode_name'].'</td><td id="tld_unicode_name"></td><td></td></tr>';
	$html_text .= '<tr id="394" style="display:none"><td>tld_statuses</td><td> '.$data[$pd]['tld']['statuses'].'</td><td id="tld_statuses"></td><td></td></tr>';
	$html_text .= '<tr id="395" style="display:none"><td>tld_storage_model</td><td> '.$data[$pd]['tld']['storage_model'].'</td><td id="tld_storage_model"></td><td></td></tr>';
	$html_text .= '<tr id="396" style="display:none"><td>tld_response_model</td><td> '.$data[$pd]['tld']['response_model'].'</td><td id="tld_response_model"></td><td></td></tr>';
	$html_text .= '<tr id="397" style="display:none"><td>tld_search_engine_deletion_phase_ready</td><td>'.$data[$pd]['tld']['search_engine_deletion_phase_ready'].'</td><td id="tld_search_engine_deletion_phase_ready"></td><td></td></tr>';	
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(41)">TLD URIs +/-</button></td><td></td><td></td><td>data partial</td></tr>';	
	$html_text .= '<tr id="411" style="display:table-row"><td>tld_services_uri</td><td>'.((!empty($data[$pd]['tld']['services_uri'])) ? '<a href='.$data[$pd]['tld']['services_uri'].' target="_blank">TLD Services</a>' : '').'</td><td id="tld_services_uri"></td><td></td></tr>';
	$html_text .= '<tr id="412" style="display:table-row"><td>tld_standardized_price_list_uri</td><td>'.((!empty($data[$pd]['tld']['standardized_price_list_uri'])) ? '<a href='.$data[$pd]['tld']['standardized_price_list_uri'].' target="_blank">TLD Prices</a>' : '').'</td><td id="tld_standardized_price_list_uri"></td><td></td></tr>';
	$html_text .= '<tr id="413" style="display:table-row"><td>tld_root_data_uri</td><td>'.((!empty($data[$pd]['tld']['root_data_uri'])) ? '<a href='.$data[$pd]['tld']['root_data_uri'].' target="_blank">Root TLD Data</a>' : '').'</td><td id="tld_root_data_uri"></td><td></td></tr>';
	$html_text .= '<tr id="414" style="display:table-row"><td>tld_registry_data_uri</td><td>'.((!empty($data[$pd]['tld']['registry_data_uri'])) ? '<a href='.$data[$pd]['tld']['registry_data_uri'].' target="_blank">Registry TLD Data</a>' : '').'</td><td id="tld_registry_data_uri"></td><td></td></tr>';
	$html_text .= '<tr id="415" style="display:table-row"><td>tld_data_usage_policy_uri</td><td>'.((!empty($data[$pd]['tld']['data_usage_policy_uri'])) ? '<a href='.$data[$pd]['tld']['data_usage_policy_uri'].' target="_blank">TLD Data Usage</a>' : '').'</td><td id="tld_data_usage_policy_uri"></td><td></td></tr>';
	$html_text .= '<tr id="416" style="display:table-row"><td>tld_privacy_policy_uri</td><td>'.((!empty($data[$pd]['tld']['privacy_policy_uri'])) ? '<a href='.$data[$pd]['tld']['privacy_policy_uri'].' target="_blank">TLD Privacy</a>' : '').'</td><td id="tld_privacy_policy_uri"></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(42)">Functions +/-</button></td><td></td><td id="tld_functions"></td><td>data needed</td></tr>';
	$html_text .= '<tr><td colspan="3">tld_registrant_formatted_name: '.$data[$pd]['tld']['registrant_formatted_name'].'</td><td>data replaced</td></tr>';
	$html_text .= '<tr id="421" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['tld']['functions'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(44)">Accepted Workload +/-</button></td><td></td><td id="tld_accepted_workload"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="441" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['tld']['accepted_workload'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(43)">Relationships +/-</button></td><td></td><td id="tld_relationships"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="431" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['tld']['relationships'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(51)">Lifecycle Information +/-</button></td><td></td><td id="lifecycle_part"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="511" style="display:none"><td>data_active_from</td><td>'.$data[$pd]['lifecycle']['data_active_from'].'</td><td id="lifecycle_data_active_from"></td><td></td></tr>';
	$html_text .= '<tr id="512" style="display:none"><td>upon_termination</td><td>'.$data[$pd]['lifecycle']['upon_termination'].'</td><td id="lifecycle_upon_termination"></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(52)">Status Meanings +/-</button></td><td></td><td id="lifecycle_status_meanings"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="521" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['lifecycle']['status_meanings'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(53)">Operational Periods +/-</button></td><td></td><td id="lifecycle_operational_periods"></td><td>data needed</td></tr>';
	$html_text .= '<tr id="531" style="display:none;vertical-align:top"><td colspan="2">'.$data[$pd]['lifecycle']['operational_periods'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td><button style="cursor:pointer;font-size:0.8rem" onclick="SwitchDisplay(61)">Nameserver Data +/-</button></td><td><b>'.$vd.'</b></td><td id="nameservers_part"></td><td>data exists</td></tr>';
	$html_text .= '<tr id="611" style="display:none;vertical-align:top"><td>handles</td><td colspan="2">'.$data[$pd]['nameservers']['handles'].'</td><td></td></tr>';
	$html_text .= '<tr id="612" style="display:none;vertical-align:top"><td>ascii_names</td><td colspan="2">'.$data[$pd]['nameservers']['ascii_names'].'</td><td></td></tr>';
	$html_text .= '<tr id="613" style="display:none;vertical-align:top"><td>unicode_names</td><td colspan="2">'.$data[$pd]['nameservers']['unicode_names'].'</td><td></td></tr>';
	$html_text .= '<tr id="614" style="display:none;vertical-align:top"><td>ipv4_addresses</td><td>'.$data[$pd]['nameservers']['ipv4_addresses'].'</td><td id="nameservers_ip"></td><td></td></tr>';
	$html_text .= '<tr id="615" style="display:none;vertical-align:top"><td>ipv6_addresses</td><td>'.$data[$pd]['nameservers']['ipv6_addresses'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr id="616" style="display:none;vertical-align:top"><td>statuses</td><td>'.$data[$pd]['nameservers']['statuses'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr id="617" style="display:none;vertical-align:top"><td>rdap_dnssec_signed</td><td>'.$data[$pd]['nameservers']['rdap_dnssec_signed'].'</td><td id="nameservers_rdap_dnssec_signed"></td><td></td></tr>';
	$html_text .= '<tr id="618" style="display:none;vertical-align:top"><td>rdap_ds_key_tags</td><td>'.$data[$pd]['nameservers']['rdap_ds_key_tags'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr style="vertical-align:top"><td>rdap_ds_algorithm_numbers</td><td>'.$data[$pd]['nameservers']['rdap_ds_algorithm_numbers'].'</td><td id="nameservers_rdap_ds_algorithm_numbers"></td><td></td></tr>';	
	$html_text .= '<tr id="619" style="display:none;vertical-align:top"><td>rdap_ds_digest_types</td><td>'.$data[$pd]['nameservers']['rdap_ds_digest_types'].'</td><td></td><td></td></tr>';
	$html_text .= '<tr id="6110" style="display:none;vertical-align:top"><td>rdap_ds_digests</td><td colspan="2">'.$data[$pd]['nameservers']['rdap_ds_digests'].'</td><td></td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr>';
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
?>