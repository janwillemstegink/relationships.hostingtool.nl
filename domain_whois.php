<?php
session_start();  // is needed with no Scriptcase PHP Generator
$datetime = new DateTime('now', new DateTimeZone('UTC'));
$utc = $datetime->format('Y-m-d H:i:s');
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
else	{
	$viewlanguage = $_GET["language"];
}
if (!empty(trim($_GET['domain'])))	{
	$vd = trim($_GET['domain']);
	$vd = mb_strtolower($vd);
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
$log_file = "/home/admin/logging/" . $internal . "whois_tool_" . $datetime->format('Ym') . ".txt";
$log_line = $datetime->format('Y-m-d H:i:s') . " UTC, lang" . $viewlanguage . ", " . $vd . ", " . [ip] . ", " . [block] . "\n";
file_put_contents($log_file, $log_line, FILE_APPEND);
echo '<!DOCTYPE html><html lang="en" style="font-size: 90%"><head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="robots" content="index">
<title>Domain Information</title>';
?><script>
	
function SwitchDisplay(type) {
	if (type == 29)	{ // metadata
		var pre = '29';
		var max = 1
	}
	else if (type == 70)	{ // raw whois data
		var pre = '70';
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

function SwitchTranslation(translation)	{
	document.getElementById("language").value = translation;
	if (translation == 99)	{
		var modified = '';
		var proposed = '';
		var accessible = '';
		var legacy = '';
		document.getElementById("title").textContent = "Domain Information";
		document.getElementById("subtitle").textContent = "Whois (Legacy)";
		document.getElementById("instruction").textContent = "Fill in and press Enter to retrieve.";
	}
	else if (translation == 1)	{
		var modified = '(Gewijzigd) ';
		var proposed = '(Nieuw) ';
		var accessible = 'Nieuwe velden moeten de bruikbaarheid vergroten en voor duidelijkheid zorgen.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domeininformatie";
		document.getElementById("subtitle").textContent = "Whois (Legacy)";
		document.getElementById("instruction").textContent = "Typ een domeinnaam en druk op Enter.";
	}
	else if (translation == 2)	{
		var modified = '(Modified) ';
		var proposed = '(New) ';
		var accessible = 'New fields should boost usability and bring clarity.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domain Information";
		document.getElementById("subtitle").textContent = "Whois (Legacy)";
		document.getElementById("instruction").textContent = "Type a domain, then press Enter.";
	}
	else if (translation == 3)	{
		var modified = '(Geändert) ';
		var proposed = '(Neu) ';
		var accessible = 'Neue Felder sollten die Benutzerfreundlichkeit verbessern und für Klarheit sorgen.';
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Domaininformationen";
		document.getElementById("subtitle").textContent = "Whois (Legacy)";
		document.getElementById("instruction").textContent = "Geben Sie eine Domain ein und drücken Sie Enter.";
	}
	else if (translation == 4)	{
		var modified = '(Modifié) ';
		var proposed = '(Nouveau) ';
		var accessible = "Les nouveaux champs devraient améliorer l’utilisabilité et apporter de la clarté.";
		var legacy = '(Legacy) ';
		document.getElementById("title").textContent = "Informations sur le domaine";
		document.getElementById("subtitle").textContent = "Whois (Legacy)";
		document.getElementById("instruction").textContent = "Saisissez un nom de domaine, puis appuyez sur Entrée.";
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
$server_url = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
$server_url .= '://'. $_SERVER['HTTP_HOST'];
$server_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);	
$server_url = dirname($server_url);
$whois_url = $server_url . '/domain_whois_data/index.php?batch=0&domain=' . urlencode($pd);
$json = @file_get_contents($whois_url);
$data = null;
$terms_and_conditions = '';
if ($json !== false) {
    $data = json_decode($json, true);
}
if (is_array($data) && isset($data[$pd]['metadata']['zone_identifier'])) {
    $terms_and_conditions = $server_url . '/tld/index.php?language=' . $viewlanguage . '&tld=' . $data[$pd]['metadata']['zone_identifier'];
}
else {
    $reopen = $server_url . '/domain_whois/index.php?batch=0&domain=hostingtool.nl';
    sc_redir($reopen);
}
$html_text = '<body onload=SwitchTranslation('.$viewlanguage.')><div style="border-collapse:collapse; line-height:120%">
<table style="font-family:Helvetica, Arial, sans-serif; font-size: 1rem; table-layout: fixed; width:1375px">
<tr><th style="width:325px"></th><th style="width:300px"></th><th style="width:750px"></th></tr>';
$html_text .= '<tr style="font-size: .8rem"><td id="title" style="font-size: 1.3rem;color:blue;font-weight:bold"></td><td id="instruction"></td><td></td></tr>';
$html_text .= '<tr style="font-size: .8rem"><td id="subtitle" style="font-size: 1.0rem;color:blue;font-weight:bold"></td><td><form action='.htmlentities($_SERVER['PHP_SELF']).' method="get">
	<input type="hidden" id="language" name="language" value='.$viewlanguage.'>	
	<input type="text" style="width:90%" id="domain" name="domain" value='.$vd.'></form></td><td>
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(99)">None</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(1)">nl_NL</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(2)">en_US</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(3)">de_DE</button> 
	<button style="cursor:pointer;font-size:1.0rem" onclick="SwitchTranslation(4)">fr_FR</button> 
	<a style="font-size: 0.9rem" href="https://github.com/janwillemstegink/relationships.hostingtool.nl" target="_blank">Code/issues on GitHub</a> - <a style="font-size: 0.9rem" href="https://janwillemstegink.nl/" target="_blank">Insight at janwillemstegink.nl</a></td></tr>';
//echo $pd.'#'.$data[$pd]['domain']['ascii_name'];
if (true or $pd == mb_strtolower($data[$pd]['domain']['ascii_name']) or empty($data[$pd]['domain']['ascii_name']))	{
	$html_text .= '<tr style="font-size:1.05rem;font-weight:bold"><td></td><td><td></td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td></tr>';
	$html_text .= '<tr><td colspan="3">'.$data[$pd]['raw_whois'].'</td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td><td><hr></td></tr>';
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
    $rdap_url = "https://rdap.db.ripe.net/ip/".$ip;
    $response = @file_get_contents($rdap_url);
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
	return (strlen($country)) ? $country . '; ' . $orgName : $orgName;	
}						
?>