<?php
session_start();  // is needed with no PHP Generator Scriptcase
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$datetime = new DateTime('now', new DateTimeZone('UTC'));
if (empty([ip]) or empty([block]))	{
	[ip] = getClientIP();
	[block] = get_block([ip]);
}
$log_file = "/home/admin/logging/email_modeling_" . date("Ym") . ".txt";
$log_line = $datetime->format('Y-m-d H:i:s') . " UTC, " . [ip] . ", " . [block] . "\n";
file_put_contents($log_file, $log_line, FILE_APPEND);
if (!function_exists('simplexml_load_file')) {
	die('simpleXML functions are not available.');
}
if (ini_get("allow_url_fopen") == 1)	{
}
else	{	
	die('allow_url_fopen does not work.'); 	
}
$dataFile = '/home/admin/rdap_files/data_email.xml';
$inputdomain = 'webhostingtech.nl';
$url1 = "https://rdap.hostingtool.nl/compose_data/index.php?domain=$inputdomain";
if (file_exists($dataFile))	{
	$xml1 = simplexml_load_file($dataFile) or die("Cannot load xml1 from folder.");
}
elseif (@get_headers($url1))	{ // RDAP does not contain all data
	$xml1 = simplexml_load_file($url1, "SimpleXMLElement", LIBXML_NOCDATA) or die("Cannot load " . $url1);
}
$html_text = '<!DOCTYPE html><html lang="en" style="font-size:100%"><head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta charset="UTF-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<meta name="robots" content="index" />
<title>Web domain email modeling</title></head>';
$html_text .= '<body><div style="border-collapse:collapse; line-height:120%">
<table style="margin-left: 50px; font-family:Helvetica, Arial, sans-serif; font-size:.85rem">
<tr><th style="width:225px"></th><th style="width:650px"></th></tr>';
$html_text .= '<tr><td style="font-size:1.1rem;color:blue;font-weight:bold">Web Domain Email Model</td><td style="font-size:.8rem"><a style="font-size: 0.9rem" href="https://github.com/janwillemstegink/relationships.hostingtool.nl" target="_blank">Code/issues on GitHub</a> - <a style="font-size: 0.9rem" href="https://janwillemstegink.nl/" target="_blank">Insight at janwillemstegink.nl</a></td></tr>';
$html_text .= '<tr><td><hr></td><td><hr></td></tr>';
$html_text .= '<tr><td COLSPAN="2">- English version of this message below –</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />Geachte ontvanger,</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />U ontvangt dit bericht omdat uw verantwoordelijkheid voor verzoekafhandeling inzake bovenstaand domein relevant is voor deze melding.</td></tr>';
$html_text .= '<tr><td COLSPAN="2">team.blue nl B.V. heeft ten minste één domeingegeven gewijzigd. De relevante domeingegevens zijn:</td></tr>';
foreach ($xml1->xpath('//domain') as $item)	{
	simplexml_load_string($item->asXML());
	$html_text .= '<tr><td COLSPAN="2"><br /><u>Domein / Domain:</u></td></tr>';
	$html_text .= '<tr><td>ascii_name</td><td>'.$item->details->ascii_name.'</td></tr>';
	$html_text .= '<tr><td>unicode_name</td><td>'.$item->details->unicode_name.'</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br /><u>Houder / Registrant:</u></td></tr>';
	$html_text .= '<tr><td>subject_code</td><td>'.$item->registrant->subject_code.'</td></tr>';
	$html_text .= '<tr><td>organization_type</td><td>'.$item->registrant->organization_type.'</td></tr>';
	$html_text .= '<tr><td>organization_name</td><td>'.$item->registrant->organization_name.'</td></tr>';
	$html_text .= '<tr><td>presented_name</td><td>'.$item->registrant->presented_name.'</td></tr>';
	$html_text .= '<tr><td>kind</td><td>'.$item->registrant->kind.'</td></tr>';
	$html_text .= '<tr><td>name</td><td>'.$item->registrant->name.'</td></tr>';
	$html_text .= '<tr><td>email</td><td>'.$item->registrant->email.'</td></tr>';
	$html_text .= '<tr><td>contact_uri</td><td>'.$item->registrant->contact_uri.'</td></tr>';
	$html_text .= '<tr><td>telephone</td><td>'.$item->registrant->telephone.'</td></tr>';
	$html_text .= '<tr><td>country_code</td><td>'.$item->registrant->country_code.'</td></tr>';
	$html_text .= '<tr><td>street</td><td>'.$item->registrant->street.'</td></tr>';
	$html_text .= '<tr><td>postal_code</td><td>'.$item->registrant->postal_code.'</td></tr>';
	$html_text .= '<tr><td>city</td><td>'.$item->registrant->city.'</td></tr>';
	$html_text .= '<tr><td>state_or_province</td><td>'.$item->registrant->state_or_province.'</td></tr>';	
	$html_text .= '<tr><td>country_name</td><td>'.$item->registrant->country_name.'</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br /><u>Verzoekafhandeling / Request Handling:</u></td></tr>';
	$html_text .= '<tr><td>subject_code</td><td>'.$item->administrative->subject_code.'</td></tr>';
	$html_text .= '<tr><td>organization_type</td><td>'.$item->administrative->organization_type.'</td></tr>';
	$html_text .= '<tr><td>organization_name</td><td>'.$item->administrative->organization_name.'</td></tr>';
	$html_text .= '<tr><td>presented_name</td><td>'.$item->administrative->presented_name.'</td></tr>';
	$html_text .= '<tr><td>kind</td><td>'.$item->administrative->kind.'</td></tr>';
	$html_text .= '<tr><td>name</td><td>'.$item->administrative->name.'</td></tr>';
	$html_text .= '<tr><td>email</td><td>'.$item->administrative->email.'</td></tr>';
	$html_text .= '<tr><td>contact_uri</td><td>'.$item->administrative->contact_uri.'</td></tr>';
	$html_text .= '<tr><td>telephone</td><td>'.$item->administrative->telephone.'</td></tr>';
	$html_text .= '<tr><td>country_code</td><td>'.$item->administrative->country_code.'</td></tr>';
	$html_text .= '<tr><td>street</td><td>'.$item->administrative->street.'</td></tr>';
	$html_text .= '<tr><td>postal_code</td><td>'.$item->administrative->postal_code.'</td></tr>';
	$html_text .= '<tr><td>city</td><td>'.$item->administrative->city.'</td></tr>';
	$html_text .= '<tr><td>state_or_province</td><td>'.$item->administrative->state_or_province.'</td></tr>';
	$html_text .= '<tr><td>country_name</td><td>'.$item->administrative->country_name.'</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br /><u>Facturering (indien de registry dit onderhoudt) / Billing (if the registry maintains this):</u></td></tr>';
	$html_text .= '<tr><td>subject_code</td><td>'.$item->billing->subject_code.'</td></tr>';
	$html_text .= '<tr><td>organization_type</td><td>'.$item->billing->organization_type.'</td></tr>';
	$html_text .= '<tr><td>organization_name</td><td>'.$item->billing->organization_name.'</td></tr>';
	$html_text .= '<tr><td>presented_name</td><td>'.$item->billing->presented_name.'</td></tr>';
	$html_text .= '<tr><td>kind</td><td>'.$item->billing->kind.'</td></tr>';
	$html_text .= '<tr><td>name</td><td>'.$item->billing->name.'</td></tr>';
	$html_text .= '<tr><td>email</td><td>'.$item->billing->email.'</td></tr>';
	$html_text .= '<tr><td>contact_uri</td><td>'.$item->billing->contact_uri.'</td></tr>';
	$html_text .= '<tr><td>telephone</td><td>'.$item->billing->telephone.'</td></tr>';
	$html_text .= '<tr><td>country_code</td><td>'.$item->billing->country_code.'</td></tr>';
	$html_text .= '<tr><td>street</td><td>'.$item->billing->street.'</td></tr>';
	$html_text .= '<tr><td>postal_code</td><td>'.$item->billing->postal_code.'</td></tr>';
	$html_text .= '<tr><td>city</td><td>'.$item->billing->city.'</td></tr>';
	$html_text .= '<tr><td>state_or_province</td><td>'.$item->billing->state_or_province.'</td></tr>';
	$html_text .= '<tr><td>country_name</td><td>'.$item->billing->country_name.'</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br />Is er iets mis? Deze domeinnaam wordt beheerd door team.blue nl B.V. en mogelijk door de reseller TransIP.</td></tr>';
	$html_text .= '<tr><td COLSPAN="2">Als u als registrant via het menu van uw registrar of reseller wijzigingen kunt aanbrengen,</td></tr>';
	$html_text .= '<tr><td COLSPAN="2">dan bent u primair verantwoordelijk voor de juistheid van deze domeingegevens.</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br />Met vriendelijke groet,</td></tr>';
	$html_text .= '<tr><td COLSPAN="2">SIDN</td></tr>';
	$html_text .= '<tr><td COLSPAN="2"><br />Beantwoord deze mail niet. Als je vragen of feedback hebt, neem dan contact op met <a href="mailto:support@sidn.nl">support@sidn.nl</a>.</td></tr>';
	$html_text .= '<tr><td><hr></td><td><hr></td></tr>';
	break;
}
$html_text .= '<tr><td COLSPAN="2"><br />Dear recipient,</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />You are receiving this message because your request handling responsibility for the above domain is relevant to this notification.</td></tr>';
$html_text .= '<tr><td COLSPAN="2">team.blue nl B.V. has changed at least one domain detail. The relevant domain information is provided above.</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />Is there something wrong? This domain name is managed by team.blue nl B.V. and possibly through reseller TransIP.</td></tr>';
$html_text .= '<tr><td COLSPAN="2">If, as a registrant, you can make changes via the menu of your registrar or reseller,</td></tr>';
$html_text .= '<tr><td COLSPAN="2">you are primarily responsible for the accuracy of this domain information.</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />Kind regards</td></tr>';
$html_text .= '<tr><td COLSPAN="2">SIDN</td></tr>';
$html_text .= '<tr><td COLSPAN="2"><br />Please don&#39;t reply to this mail. If you have any questions or feedback, please contact <a href="mailto:support@sidn.nl">support@sidn.nl</a>.</td></tr>';
$html_text .= '<tr><td><hr></td><td><hr></td></tr>';
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
}?>