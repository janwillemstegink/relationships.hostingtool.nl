<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//$_GET['domain'] = 'hostingtool.nl';
//$_GET['domain'] = 'cyberfusion.nl';
//$_GET['domain'] = 'example.tel';

if (!empty($_GET['domain']))	{
	if (strlen($_GET['domain']))	{
		$domain = $_GET['domain'];
		$batch = false;
		if (!empty($_GET['batch']))	{
			if (trim($_GET['batch'] == '1'))	{
				$batch = true;
			}
		}
		$domain = trim($domain);
		$domain = mb_strtolower($domain);
		$domain = str_replace('http://','', $domain);
		$domain = str_replace('https://','', $domain);
		if (substr_count($domain, '.') > 1)	{
			$domain = str_replace('www.','', $domain);
		}
		$strpos = mb_strpos($domain, '/');
		if ($strpos)	{
			$domain = mb_substr($domain, 0, $strpos);
		}
		$strpos = mb_strpos($domain, ':');
		if ($strpos)	{
			$domain = mb_substr($domain, 0, $strpos);
		}
		$domain = toPunycodeIfNeeded($domain);
		if (!preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $domain)) {
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode(['error' => 'Invalid domain name']);
			exit;
		}
		header('Content-Type: application/json');
		echo json_encode(write_file($domain, $batch), JSON_PRETTY_PRINT);
		die();
	}
	else	{	
		die("No domain name is filled as input");	
	}
}
else	{	
	die("No domain name variable as input");
}

function detect_country_code($inputdefault, $inputCC, $inputcc)	{	
	$outputcc = $inputdefault;
	if (strlen($inputCC))	$outputcc = $inputCC.' "CC"=>"cc"';
	if (strlen($inputcc))	$outputcc = $inputcc;
	return $outputcc;
}

function toPunycodeIfNeeded($inputdomain) {
    if (strpos($inputdomain, 'xn--') === false) {
        $punycode = idn_to_ascii($inputdomain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($punycode !== false) {
            return $punycode;
        }
		else {
            die("Invalid domain: $inputdomain");
        }
    }
    return $inputdomain;
}

function write_file($inputdomain, $inputbatch)	{

if ($inputbatch)	{
	$raw_whois_data = '';
}
else	{
	usleep(1.05 * 1_000_000);
	// Legacy: external Python process (requires shell_exec).
	// $command = "/home/admin/venv-lookup/bin/python /home/admin/scripts/relationships_whois.py";
	// $raw_whois_data = shell_exec($command . " " . escapeshellarg($inputdomain) . " 2>&1");
	$raw_whois_data = lookupWhois($inputdomain);
}
	
$strpos = mb_strpos($inputdomain, '.');
if ($strpos)	{
	$zone_identifier = mb_substr($inputdomain, strrpos($inputdomain, '.') + 1);
}
else	{
	$zone_identifier = $inputdomain;
}
$arr = array();	
	
$arr[$inputdomain]['metadata']['zone_identifier'] = $zone_identifier;
$arr[$inputdomain]['metadata']['tld_information_url'] = null;
$arr[$inputdomain]['raw_whois'] = $raw_whois_data;	

return $arr;
}
function whoisQuery(string $server, string $query): string {
    if (!preg_match('/^[a-z0-9.-]+$/i', $server)) return '';
    $addresses = gethostbynamel($server) ?: [];
    $addresses = array_values(array_filter($addresses, fn($ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)));
    if (!$addresses) return '';
    $socket = @stream_socket_client('tcp://' . $addresses[0] . ':43', $errno, $errstr, 5);
    if (!$socket) return '';
    stream_set_timeout($socket, 8);
    fwrite($socket, $query . "\r\n");
    $output = '';
    while (!feof($socket) && strlen($output) < 1048576) {
        $chunk = fread($socket, min(8192, 1048576 - strlen($output)));
        if ($chunk === false || $chunk === '') break;
        $output .= $chunk;
    }
    fclose($socket);
    return $output;
}

function lookupWhois(string $domain): string {
    $tld = substr($domain, strrpos($domain, '.') + 1);
    $iana = whoisQuery('whois.iana.org', $tld);
    if ($iana === '') return 'ERROR: IANA WHOIS lookup unavailable';
    preg_match('/^whois:\s*([a-z0-9.-]+)/im', $iana, $m);
    $server = strtolower($m[1] ?? '');
    if ($server === '') return 'ERROR: No WHOIS server published by IANA';
    $raw = whoisQuery($server, $domain);
    if ($raw === '') return 'ERROR: WHOIS query failed (' . htmlspecialchars($server, ENT_QUOTES, 'UTF-8') . ')';
    $seen = [$server => true];
    $current = $raw;
    for ($i = 0; $i < 2; $i++) {
        if (!preg_match('/^(?:Registrar WHOIS Server|Whois Server|ReferralServer):\s*(?:whois:\/\/)?([a-z0-9.-]+)/im', $current, $m)) break;
        $next = strtolower($m[1]);
        if (isset($seen[$next])) break;
        $seen[$next] = true;
        $extra = whoisQuery($next, $domain);
        if ($extra === '') break;
        $raw .= "\n------------------------------------\n" . $extra;
        $current = $extra;
    }
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $deduped = [];
    foreach ($lines as $line) {
        if (!$deduped || $line !== end($deduped)) $deduped[] = $line;
    }
    return nl2br(htmlspecialchars(implode("\n", $deduped), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
}
