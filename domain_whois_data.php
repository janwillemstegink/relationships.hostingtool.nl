<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//$_GET['domain'] = 'hostingtool.nl';
//$_GET['domain'] = 'cyberfusion.nl';
//$_GET['domain'] = 'münchen.de';
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
		$domain = htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
	//$command = escapeshellcmd("/usr/bin/python3.9 /home/admin/scripts/get_whois_domain_data.py");
	//$raw_whois_data = nl2br(htmlspecialchars(shell_exec($command . " " . $inputdomain)));	
	
	//$command = "/usr/bin/python3.9 /home/admin/scripts/get_whois_domain_data.py";
	//$raw_whois_data = shell_exec($command . " " . escapeshellarg($inputdomain) . " --format html 2>&1");	
	
	$command = "/usr/bin/python3.9 /home/admin/scripts/get_whois_domain_data.py";
	$raw_whois_data = shell_exec($command . " " . escapeshellarg($inputdomain) . " 2>&1");
	
	//$raw_whois_data = shell_exec($command . " " . $inputdomain);	
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
$arr[$inputdomain]['metadata']['tld_information_url'] = $tld_information_url;
$arr[$inputdomain]['raw_whois'] = $raw_whois_data;	

return $arr;
}
?>