<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//$_GET['domain'] = 'hostingtool.nl';
//$_GET['domain'] = 'cyberfusion.nl';
//$_GET['domain'] = 'münchen.de';
//$_GET['domain'] = 'example.tel';
//$_GET['domain'] = 'rdap.org';
//$_GET['domain'] = 'france.fr';
//$_GET['domain'] = 'domaincontrolregister.org';
//$_GET['domain'] = 'icann.org';
//$_GET['domain'] = 'amsterdam.amsterdam';
//$_GET['domain'] = 'eurid.eu';
//$_GET['domain'] = 'denic.de';
//$_GET['domain'] = 'internet.nl';
//$_GET['domain'] = 'nic.vermögensberater';
//$_GET['domain'] = 'teamblue.domains';

if (!empty($_GET['domain']))	{
	if (strlen($_GET['domain']))	{
		$domain = $_GET['domain'];
		$batch = 0;
		if (isset($_GET['batch']) && trim($_GET['batch']) === '1') {
		    $batch = 1;
		}
		$domain = mb_strtolower($domain);
		$domain = str_replace('http://','', $domain);
		$domain = str_replace('https://','', $domain);
		if (preg_match('/^www\.(.+)$/i', $domain, $m)) {
    		if (substr_count($m[1], '.') >= 1) {
        		$domain = $m[1];
    		}
		}
		$pos = mb_stripos($domain, '/');
		if ($pos !== false) {
		    $domain = mb_substr($domain, 0, $pos);
		}
		$pos = mb_stripos($domain, ':');
		if ($pos !== false) {
    		$domain = mb_substr($domain, 0, $pos);
		}
		$domain_unicode_name = value_to_unicode($domain);
		$domain_ascii_name = value_to_ascii($domain);
		$stripos = mb_stripos($domain_unicode_name, '.');
		if ($stripos !== false) {
            $tld_unicode_name = mb_substr($domain_unicode_name, mb_strrpos($domain_unicode_name, '.') + 1);
			$tld_ascii_name = value_to_ascii($tld_unicode_name);
        }
        else {
            $tld_unicode_name = 'tld';
			$tld_ascii_name = 'tld';
        }
		$registry_rdap = [];
		$registry_rdap = write_file($tld_ascii_name, $domain_ascii_name, $batch, '');
		$registry_interface = $registry_rdap['interface_notice'] ?? '';
		$registry_rdap['metadata']['tld_unicode_name'] = $tld_unicode_name ?? null;
		$registry_rdap['metadata']['tld_ascii_name'] = $tld_ascii_name ?? null;
		$registry_rdap['metadata']['domain_unicode_name'] = $domain_unicode_name ?? null;
		$registry_rdap['metadata']['domain_ascii_name'] = $domain_ascii_name ?? null;
		$registrar_rdap = [];
		$registrar_interface = '';
		$registry_statuses = $registry_rdap['domain']['statuses'] ?? null;
		if (!empty($registry_statuses)) {
			$registry_rdap['metadata']['rdap_data_layer'] = 'registry_rdap';
			$registrar_identifier = $registry_rdap['metadata']['registrar_identifier'] ?? null;
			$iana_id = (int) $registrar_identifier;
			$registry_self_uri = $registry_rdap['metadata']['registry_domain_uri'] ?? null;
			$registry_related_uri = $registry_rdap['metadata']['registrar_domain_uri'] ?? null;
			if (empty($registry_self_uri)) {
				$registry_interface .= 'The registry rel="self" link is a SHOULD.';
			}		
			elseif (strcasecmp($registry_rdap['metadata']['request_uri'], $registry_self_uri) !== 0) {
 				$registry_interface .= 'Registry RDAP has an uneven rel="self" link.';
			}
			elseif ($iana_id > 9990 and strcasecmp($registry_related_uri, $registry_self_uri) === 0) {
    			$registry_interface .= 'Registry RDAP "related" equals "self" (' . $iana_id . ')';
			}
			elseif (strcasecmp($registry_related_uri, $registry_self_uri) === 0) {
    			$registry_interface .= 'Registry RDAP "related" equals "self"';
			}
			if (mb_strlen($tld_unicode_name) > 2) {	
				//$registry_related_uri = 'https://rdap.gandi.net/domain/tel.tel';
				if (!empty($registry_self_uri) and strcasecmp($registry_related_uri, $registry_self_uri) === 0)	{	
				}	
				elseif (!empty($registry_related_uri)) {
       				$registrar_rdap = write_file($tld_ascii_name, $domain_ascii_name, $batch, $registry_related_uri);
					$registrar_interface = $registrar_rdap['interface_notice'] ?? '';
					$registry_rdap['metadata']['registrar_domain_uri'] = $registry_related_uri;
					$registrar_rdap['metadata']['rdap_data_layer'] = 'registrar_rdap';
					$registrar_rdap['metadata']['tld_unicode_name'] = $tld_unicode_name ?? null;
					$registrar_rdap['metadata']['tld_ascii_name'] = $tld_ascii_name ?? null;
					$registrar_rdap['metadata']['domain_unicode_name'] = $domain_unicode_name ?? null;
					$registrar_rdap['metadata']['domain_ascii_name'] = $domain_ascii_name ?? null;
				}
				elseif (!empty($registrar_identifier))	{
					if ($iana_id > 0 and $iana_id < 9990) {
						$base_url = fetchIanaRegistrarRdapBaseUrl($iana_id);
		    			if ($base_url) {
							$registrar_uri = rtrim($base_url, '/') . '/domain/' . rawurlencode($domain);
							$registry_rdap['metadata']['registrar_domain_uri'] = $registrar_uri;
       						$registrar_rdap = write_file($tld_ascii_name, $domain_ascii_name, $batch, $registrar_uri);
							$registrar_interface = $registrar_rdap['interface_notice'] ?? '';
							$registrar_statuses = $registrar_rdap['domain']['statuses'] ?? null;
							if (!empty($registrar_statuses)) {						
								$registrar_rdap['metadata']['rdap_data_layer'] = 'registrar_rdap';
								$registrar_rdap['metadata']['tld_unicode_name'] = $tld_unicode_name ?? null;
								$registrar_rdap['metadata']['tld_ascii_name'] = $tld_ascii_name ?? null;
								$registrar_rdap['metadata']['domain_unicode_name'] = $domain_unicode_name ?? null;
								$registrar_rdap['metadata']['domain_ascii_name'] = $domain_ascii_name ?? null;
								if (strlen($registry_interface))	{
									$registry_interface .= "<br />";
								}
								$registry_interface .= 'The registry rel="related" link here is a MUST.';
							}	
    					}	
						else	{
							if (strlen($registrar_interface))	{
								$registrar_interface .= "<br />";
							}
							$registrar_interface .= $iana_id . " - no retrieval";	
						}	
					}
				}
				if (!empty($registrar_rdap['metadata']['registrar_domain_uri'])) {
					if (strlen($registrar_interface))	{
						$registrar_interface .= "<br />";
					}
					$registrar_interface .= 'Unexpected rel="related" link.';
				}
			}					
			if (empty($registry_rdap['metadata']['registry_domain_uri'])) {
				$registry_rdap['metadata']['registry_domain_uri'] = $registry_rdap['metadata']['request_uri'] ?? null;
			}
		}
		$dnssecInfo = getDnssecInfo($domain);
		$registry_rdap['measured_ds_key_tags'] = '';
		$registry_rdap['measured_ds_algorithm_numbers'] = '';
		$registry_rdap['measured_ds_digest_types'] = '';
		$registry_rdap['measured_ds_digests'] = '';
		foreach ($dnssecInfo['ds_data'] as $index => $ds) {
    		$registry_rdap['measured_ds_key_tags'] .= $index . ': ' . $ds['keyTag'] . ',';
    		$registry_rdap['measured_ds_algorithm_numbers'] .= $index . ': ' . $ds['algorithm'] . ',';
			$registry_rdap['measured_ds_algorithm_names'] .= $index . ': ' . $ds['algorithm_name'] . ',';
    		$registry_rdap['measured_ds_digest_types'] .= $index . ': ' . $ds['digestType'] . ',';
    		$registry_rdap['measured_ds_digests'] .= $index . ': ' . $ds['digest'] . ',';
		}
		$registry_rdap['measured_ds_key_tags'] = rtrim($registry_rdap['measured_ds_key_tags'], ',');
		$registry_rdap['measured_ds_algorithm_numbers'] = rtrim($registry_rdap['measured_ds_algorithm_numbers'], ',');
		$registry_rdap['measured_ds_algorithm_names'] = rtrim($registry_rdap['measured_ds_algorithm_names'], ',');
		$registry_rdap['measured_ds_digest_types'] = rtrim($registry_rdap['measured_ds_digest_types'], ',');
		$registry_rdap['measured_ds_digests'] = rtrim($registry_rdap['measured_ds_digests'], ',');		
		$registry_rdap['interface_notice'] = $registry_interface;
		$registrar_rdap['interface_notice'] = $registrar_interface;
		$merged = [];
		$merged[$domain_ascii_name]['registry']  = $registry_rdap ?? [];
		$merged[$domain_ascii_name]['registrar'] = $registrar_rdap ?? [];
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

function value_to_ascii(string $inputvalue): string	{
    $ascii = idn_to_ascii($inputvalue, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    return $ascii !== false ? strtolower($ascii) : strtolower($inputvalue);
}

function value_to_unicode(string $inputvalue): string	{
    $unicode = idn_to_utf8($inputvalue, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    return $unicode !== false ? $unicode : $inputvalue;
}

function interprete_remark($inputkey, $inputvalue) {
    $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $out = '';

    if ($inputkey === 'title' && $inputvalue !== '') {
        $out .= '<strong>'.$esc($inputvalue).'</strong> ';
    }
    elseif ($inputkey === 'type' && $inputvalue !== '') {
        // We'll add a colon, but we’ll also include a cleanup step at the caller to strip it if nothing follows.
        $out .= '<em>'.$esc($inputvalue).'</em>: ';
    }
    elseif ($inputkey === 'description') {
        if (is_array($inputvalue)) {
            $out .= implode('<br />', array_map($esc, $inputvalue));
        }
		elseif ($inputvalue !== null && $inputvalue !== '') {
            $out .= $esc($inputvalue);
        }
    }
    elseif ($inputkey === 'links' && is_array($inputvalue)) {
        // Collect and join to avoid a trailing <br />
        $links = [];
        foreach ($inputvalue as $link) {
            if (!empty($link['href'])) {
                $text = $link['value'] ?? $link['href'];
                $links[] = '<a href="'.$esc($link['href']).'" target="_blank" rel="noopener">'.$esc($text).'</a>';
            }
        }
        if ($links) {
            $out .= implode('<br />', $links);
        }
    }
    else {
        // Fallback for unexpected fields
        if ($inputvalue !== null && $inputvalue !== '') {
            $out .= $esc($inputvalue);
        }
    }

    return $out;
}

function fetchWithCache(string $url, string $cacheName, int $ttl = 3600, $context = null): string
{
    $cacheDir = __DIR__ . '/cache';

    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
        throw new RuntimeException('Unable to create cache directory: ' . $cacheDir);
    }

    $cacheFile = $cacheDir . '/' . $cacheName . '.cache';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $cached = file_get_contents($cacheFile);
        if ($cached !== false) {
            return $cached;
        }
    }

    $err = null;
    set_error_handler(function ($severity, $message) use (&$err) {
        $err = $message;
        return true;
    });

    try {
        $data = $context !== null
            ? file_get_contents($url, false, $context)
            : file_get_contents($url);
    } finally {
        restore_error_handler();
    }

    if ($data !== false) {
        file_put_contents($cacheFile, $data, LOCK_EX);
        return $data;
    }

    if (file_exists($cacheFile)) {
        $stale = file_get_contents($cacheFile);
        if ($stale !== false) {
            return $stale;
        }
    }

    throw new RuntimeException(
        'Failed to fetch URL and no cache available: ' . $url .
        ($err ? ' | PHP warning: ' . $err : '')
    );
}

function fetchIanaRegistrarRdapBaseUrl(int $ianaId): ?string
{
    static $rdapMap = null;

    if ($rdapMap === null) {
        $csvUrl = 'https://www.iana.org/assignments/registrar-ids/registrar-ids-1.csv';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'user_agent' => 'rdap-tool/1.0',
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        try {
            $csvContent = fetchWithCache($csvUrl, 'registrar_ids_csv', 3600, $ctx);
        } catch (\Throwable $e) {
            error_log('CSV fetch failed: ' . $e->getMessage());
            return null;
        }

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $csvContent);
        rewind($fp);

        $header = fgetcsv($fp);
        if (!$header) {
            fclose($fp);
            return null;
        }

        $header = array_map(static function ($v) {
            $v = (string) $v;
            $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);
            return trim($v);
        }, $header);

        $idAliases   = ['Registrar ID', 'IANA Registrar ID', 'ID'];
        $rdapAliases = ['RDAP Base URL', 'RDAP URL', 'RDAP'];

        $find = static function (array $header, array $aliases) {
            foreach ($aliases as $a) {
                $idx = array_search($a, $header, true);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return false;
        };

        $idIndex   = $find($header, $idAliases);
        $rdapIndex = $find($header, $rdapAliases);

        if ($idIndex === false || $rdapIndex === false) {
            fclose($fp);
            return null;
        }

        $rdapMap = [];

        while (($row = fgetcsv($fp)) !== false) {
            $id   = isset($row[$idIndex]) ? (int) trim((string) $row[$idIndex]) : 0;
            $rdap = isset($row[$rdapIndex]) ? trim((string) $row[$rdapIndex]) : '';

            if ($id > 0 && $rdap !== '') {
                $rdapMap[$id] = rtrim($rdap, '/');
            }
        }

        fclose($fp);
    }

    return $rdapMap[$ianaId] ?? null;
}

/**
 * Fetch DNSSEC information for a domain.
 *
 * Returns:
 * - domain
 * - signed (bool)
 *
 * DNSKEY-derived:
 * - dnskey_algorithms (array<int>)
 * - dnskey_algorithms_csv (string)
 * - dnskey_keytags (array<int>)
 * - dnskey_keytags_csv (string)
 * - dnskey_data (array<array{flags:int,protocol:int,algorithm:int,keytag:int|null}>)
 *
 * DS-derived:
 * - ds_keytags (array<int>)
 * - ds_keytags_csv (string)
 * - ds_algorithms (array<int>)
 * - ds_algorithms_csv (string)
 * - ds_digest_types (array<int>)
 * - ds_digest_types_csv (string)
 * - ds_digests (array<string>)
 * - ds_digests_csv (string)
 * - ds_data (array<array{keyTag:int,algorithm:int,digestType:int,digest:string}>)
 *
 * Raw:
 * - dnskey_records (array<string>)
 * - ds_records (array<string>)
 *
 * Meta:
 * - error (string|null)
 */
function getDnssecInfo(string $domain): array
{
    $domain = strtolower(trim($domain));
    $domain = rtrim($domain, '.');

    $algorithmNames = [
        1  => 'RSAMD5',
        3  => 'DSA',
        5  => 'RSASHA1',
        6  => 'DSA-NSEC3-SHA1',
        7  => 'RSASHA1-NSEC3-SHA1',
        8  => 'RSASHA256',
        10 => 'RSASHA512',
        12 => 'ECC-GOST',
        13 => 'ECDSAP256SHA256',
        14 => 'ECDSAP384SHA384',
        15 => 'ED25519',
        16 => 'ED448',
    ];

    $result = [
        'domain' => $domain,
        'signed' => false,

        'dnskey_algorithms' => [],
        'dnskey_algorithm_names' => [],
        'dnskey_algorithms_csv' => '',
        'dnskey_algorithm_names_csv' => '',
        'dnskey_keytags' => [],
        'dnskey_keytags_csv' => '',
        'dnskey_data' => [],

        'ds_keytags' => [],
        'ds_keytags_csv' => '',
        'ds_algorithms' => [],
        'ds_algorithm_names' => [],
        'ds_algorithms_csv' => '',
        'ds_algorithm_names_csv' => '',
        'ds_digest_types' => [],
        'ds_digest_types_csv' => '',
        'ds_digests' => [],
        'ds_digests_csv' => '',
        'ds_data' => [],

        'dnskey_records' => [],
        'ds_records' => [],

        'error' => null,
    ];

    if ($domain === '') {
        $result['error'] = 'Empty domain';
        return $result;
    }

    $runner = findDnsCommand();
    if ($runner === null) {
        $result['error'] = 'Neither dig nor drill was found on this system';
        return $result;
    }

    $dnskeyLines = runDnsQuery($runner, $domain, 'DNSKEY');
    $dsLines = runDnsQuery($runner, $domain, 'DS');

    $result['dnskey_records'] = $dnskeyLines;
    $result['ds_records'] = $dsLines;

    foreach ($dnskeyLines as $line) {
        $parsed = parseDnskeyLine($line);
        if ($parsed === null) {
            continue;
        }

        $algorithm = (int) $parsed['algorithm'];
        $algorithmName = $algorithmNames[$algorithm] ?? 'UNKNOWN';

        $result['signed'] = true;
        $result['dnskey_algorithms'][] = $algorithm;
        $result['dnskey_algorithm_names'][] = $algorithmName;

        if ($parsed['keytag'] !== null) {
            $result['dnskey_keytags'][] = $parsed['keytag'];
        }

        $result['dnskey_data'][] = [
            'flags' => $parsed['flags'],
            'protocol' => $parsed['protocol'],
            'algorithm' => $algorithm,
            'algorithm_name' => $algorithmName,
            'keytag' => $parsed['keytag'],
        ];
    }

    foreach ($dsLines as $line) {
        $parsed = parseDsLine($line);
        if ($parsed === null) {
            continue;
        }

        $algorithm = (int) $parsed['algorithm'];
        $algorithmName = $algorithmNames[$algorithm] ?? 'UNKNOWN';

        $result['signed'] = true;

        $result['ds_keytags'][] = $parsed['keytag'];
        $result['ds_algorithms'][] = $algorithm;
        $result['ds_algorithm_names'][] = $algorithmName;
        $result['ds_digest_types'][] = $parsed['digest_type'];
        $result['ds_digests'][] = $parsed['digest'];

        $result['ds_data'][] = [
            'keyTag' => $parsed['keytag'],
            'algorithm' => $algorithm,
            'algorithm_name' => $algorithmName,
            'digestType' => $parsed['digest_type'],
            'digest' => $parsed['digest'],
        ];
    }

    $result['dnskey_algorithms'] = normalizeIntList($result['dnskey_algorithms']);
    $result['dnskey_algorithm_names'] = normalizeStringList($result['dnskey_algorithm_names']);
    $result['dnskey_keytags'] = normalizeIntList($result['dnskey_keytags']);

    $result['ds_keytags'] = normalizeIntList($result['ds_keytags']);
    $result['ds_algorithms'] = normalizeIntList($result['ds_algorithms']);
    $result['ds_algorithm_names'] = normalizeStringList($result['ds_algorithm_names']);
    $result['ds_digest_types'] = normalizeIntList($result['ds_digest_types']);
    $result['ds_digests'] = normalizeStringList($result['ds_digests']);

    $result['dnskey_algorithms_csv'] = implode(',', $result['dnskey_algorithms']);
    $result['dnskey_algorithm_names_csv'] = implode(',', $result['dnskey_algorithm_names']);
    $result['dnskey_keytags_csv'] = implode(',', $result['dnskey_keytags']);

    $result['ds_keytags_csv'] = implode(',', $result['ds_keytags']);
    $result['ds_algorithms_csv'] = implode(',', $result['ds_algorithms']);
    $result['ds_algorithm_names_csv'] = implode(',', $result['ds_algorithm_names']);
    $result['ds_digest_types_csv'] = implode(',', $result['ds_digest_types']);
    $result['ds_digests_csv'] = implode(',', $result['ds_digests']);

    return $result;
}

/**
 * Find available DNS tool.
 */
function findDnsCommand(): ?string
{
    $candidates = ['dig', 'drill'];

    foreach ($candidates as $cmd) {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($cmd) . ' 2>/dev/null'));
        if ($path !== '') {
            return $cmd;
        }
    }

    return null;
}

/**
 * Run DNS query and return non-empty lines.
 */
function runDnsQuery(string $runner, string $domain, string $type): array
{
    $domainArg = escapeshellarg($domain);
    $typeArg = escapeshellarg($type);

    if ($runner === 'dig') {
        $cmd = "dig +short {$domainArg} {$typeArg} 2>/dev/null";
    } elseif ($runner === 'drill') {
        // drill has no exact +short equivalent, so filter answer-ish lines
        $cmd = "drill {$domainArg} {$typeArg} 2>/dev/null";
    } else {
        return [];
    }

    $output = shell_exec($cmd);
    if (!is_string($output) || $output === '') {
        return [];
    }

    $lines = preg_split('/\R/', $output) ?: [];
    $lines = array_map('trim', $lines);

    $lines = array_values(array_filter(
        $lines,
        static function ($line) use ($runner, $type) {
            if ($line === '') {
                return false;
            }

            if ($runner === 'dig') {
                return true;
            }

            // drill output: keep only record lines that contain the requested type
            return stripos($line, ' IN ' . $type . ' ') !== false;
        }
    ));

    return $lines;
}

/**
 * Parse a DNSKEY line.
 *
 * dig +short example:
 * 256 3 13 AbCd...
 *
 * drill example:
 * example.com. 3600 IN DNSKEY 256 3 13 AbCd...
 *
 * Returns:
 * - flags
 * - protocol
 * - algorithm
 * - keytag (computed if possible)
 */
function parseDnskeyLine(string $line): ?array
{
    $line = trim($line);

    if (preg_match('/^(?:\S+\s+\d+\s+IN\s+DNSKEY\s+)?(\d+)\s+(\d+)\s+(\d+)\s+(.+)$/i', $line, $m)) {
        $flags = (int) $m[1];
        $protocol = (int) $m[2];
        $algorithm = (int) $m[3];
        $publicKeyB64 = preg_replace('/\s+/', '', $m[4]);

        if ($publicKeyB64 === null || $publicKeyB64 === '') {
            return null;
        }

        $keytag = computeDnskeyKeyTag($flags, $protocol, $algorithm, $publicKeyB64);

        return [
            'flags' => $flags,
            'protocol' => $protocol,
            'algorithm' => $algorithm,
            'keytag' => $keytag,
        ];
    }

    return null;
}

/**
 * Parse a DS line.
 *
 * dig +short example:
 * 26755 8 2 F341...
 *
 * drill example:
 * example.com. 3600 IN DS 26755 8 2 F341...
 *
 * Returns:
 * - keytag
 * - algorithm
 * - digest_type
 * - digest
 */
function parseDsLine(string $line): ?array
{
    $line = trim($line);

    if (preg_match('/^(?:\S+\s+\d+\s+IN\s+DS\s+)?(\d+)\s+(\d+)\s+(\d+)\s+(.+)$/i', $line, $m)) {
        $keytag = (int) $m[1];
        $algorithm = (int) $m[2];
        $digestType = (int) $m[3];
		$digest = (string) preg_replace('/\s+/', '', $m[4]);

        if ($digest === '') {
            return null;
        }

        return [
            'keytag' => $keytag,
            'algorithm' => $algorithm,
            'digest_type' => $digestType,
            'digest' => $digest,
        ];
    }

    return null;
}

/**
 * Compute DNSKEY key tag per RFC 4034 Appendix B.
 */
function computeDnskeyKeyTag(int $flags, int $protocol, int $algorithm, string $publicKeyB64): ?int
{
    $publicKey = base64_decode($publicKeyB64, true);
    if ($publicKey === false) {
        return null;
    }

    $rdata = pack('nCC', $flags, $protocol, $algorithm) . $publicKey;

    $ac = 0;
    $len = strlen($rdata);

    for ($i = 0; $i < $len; $i++) {
        $ac += ($i & 1) ? ord($rdata[$i]) : (ord($rdata[$i]) << 8);
    }

    $ac += ($ac >> 16) & 0xFFFF;

    return $ac & 0xFFFF;
}

/**
 * Normalize list of integers: unique + ascending.
 */
function normalizeIntList(array $values): array
{
    $values = array_map('intval', $values);
    $values = array_values(array_unique($values));
    sort($values, SORT_NUMERIC);
    return $values;
}

/**
 * Normalize list of strings: trim + unique + ascending.
 */
function normalizeStringList(array $values): array
{
	$values = array_map(
        static fn($value) => trim((string) $value),
        $values
    );
	
    $values = array_values(array_filter(
        $values,
        static fn($value) => $value !== ''
    ));
    $values = array_values(array_unique($values));
    sort($values, SORT_STRING);
    return $values;
}

function getConformanceGroup($value) {
    if (str_starts_with($value, 'rdap_level_')) return 10;
    if (str_starts_with($value, 'icann_rdap_response_profile_')) return 20;
    if (str_starts_with($value, 'icann_rdap_technical_implementation_guide_')) return 30;
    if ($value === 'redacted') return 40;

    return 999;
}

function write_file($inputtld, $inputdomain, $inputbatch, $inputurl) {

    $arr = array();
    $arr['interface_notice'] = "";
    $time_start = microtime(true);
    if (strlen($inputurl)) {
        $url = $inputurl;
    }
    else {
        $stripos = mb_strpos($inputdomain, '.');
        if ($stripos !== false) {
        }
        else {
            return $arr;
        }
        $url = '';
        $stealth = false;
        switch ($inputtld) {
            case 'nl':
                $url = 'https://rdap.sidn.nl/';
                break;
            case 'biz':
                $url = 'https://rdap.nic.biz/';
                break;
            case 'com':
                $url = 'https://rdap.verisign.com/com/v1/';
                break;
            case 'net':
                $url = 'https://rdap.verisign.com/net/v1/';
                break;
            case 'org':
                $url = 'https://rdap.publicinterestregistry.org/rdap/';
                break;
            case 'tel':
                $url = 'https://rdap.nic.tel/';
                break;
            case 'ca':
                $url = 'https://rdap.ca.fury.ca/rdap/';
                break;
            case 'fr':
                $url = 'https://rdap.nic.fr/';
                break;
            case 'uk':
                $url = 'https://rdap.nominet.uk/uk/';
                break;
            case 'amsterdam':
                $url = 'https://rdap.nic.amsterdam/';
                break;
            case 'politie':
                $url = 'https://rdap.nic.politie/';
                break;
            case 'aw':
                $url = 'https://rdap.nic.aw/';
                break;
            case 'frl':
                $url = 'https://rdap.centralnic.com/frl/';
                break;

            case 'de':
                $url = 'https://rdap.denic.de/';
                $stealth = true;
                break;
            case 'at':
                $url = 'https://rdap.nic.at/';
                $stealth = true;
                break;
            case 'ch':
                $url = 'https://rdap.nic.ch/';
                $stealth = true;
                break;
            case 'li':
                $url = 'https://rdap.nic.li/';
                $stealth = true;
                break;
            //case 'eu':
            //    $url = 'https://rdap.eu/';
            //    $stealth = true;
            //    break;
            case 'int':
                $url = 'https://rdap.iana.org/';
                $stealth = true;
                break;
            case 'jp':
                $url = 'https://rdap.jprs.jp/';
                $stealth = true;
                break;
            case 'kr':
                $url = 'https://rdap.kr/';
                $stealth = true;
                break;
            case 'cn':
                $url = 'https://rdap.cnnic.cn/';
                $stealth = true;
                break;
            case 'br':
                $url = 'https://rdap.registro.br/';
                $stealth = true;
                break;
            case 'za':
                $url = 'https://rdap.registry.net.za/';
                $stealth = true;
                break;
            case 'mx':
                $url = 'https://rdap.mx/';
                $stealth = true;
                break;
            case 'ai':
                $url = 'https://rdap.nic.ai/';
                $stealth = true;
                break;
            case 'io':
                $url = 'https://rdap.nic.io/';
                $stealth = true;
                break;
            case 'gg':
                $url = 'https://rdap.gg/';
                $stealth = true;
                break;
            case 'je':
                $url = 'https://rdap.je/';
                $stealth = true;
                break;
			case 'co':
                $url = 'https://rdap.hello.co/';
                $stealth = true;
                break;				
            default:
                // die("No match with a top level domain.");
        }
        $lookup_endpoints_uri = 'https://data.iana.org/rdap/dns.json';
        if (!strlen($url) || $stealth) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'user_agent' => 'rdap-tool/1.0',
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            try {
                $rdap = json_decode(
                    fetchWithCache($lookup_endpoints_uri, 'rdap_dns_json', 3600, $ctx),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            }
			catch (\Throwable $e) {
                $rdap = null;
            }
            $temp_key = -1;

            if (is_array($rdap)) {
                foreach ($rdap as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            foreach ($value3 as $key4 => $value4) {
                                if ($key3 == 0 && $value4 == $inputtld) {
                                    // echo 'match tld at key2=' . $key2 . '<br>';
                                    $temp_key = $key2;
                                    break;
                                }
                                elseif ($key3 == 1 && $key2 == $temp_key) {
                                    // echo 'match url=' . $value4 . '<br>';
                                    $url = $value4;
                                    break 4;
                                }
								
                            }
                        }
                    }
                }
            }
        }
        if (!strlen($url)) {
            $arr['interface_notice'] = $inputtld . " - Operational RDAP unknown";
            return $arr;
        }
        $url = rtrim($url, '/') . '/domain/' . $inputdomain;
    }
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
    $time_pass = microtime(true) - $time_start;
    if ($time_pass < 1.05) {
        usleep((int)((1.05 - $time_pass) * 1_000_000));
    }
    $start_monotonic = microtime(true);
    $start_utc_iso   = gmdate('c');
    $server_seen = $_SERVER['SERVER_ADDR'] ?? null;	
	$fp = fopen($url,'r',false,$context);
	if ($fp===false) {
    	$phpError=error_get_last();
    	$parts=['no valid response from RDAP endpoint'];
    	if(!empty($url))$parts[]=$url;
    	if(!empty($start_utc_iso)||!empty($server_seen)){
        	$meta=[];
        	if(!empty($start_utc_iso))$meta[]='UTC: '.$start_utc_iso;
        	if(!empty($server_seen))$meta[]='IP: '.$server_seen;
        	$parts[]=implode(', ',$meta);
    	}
    	if(!empty($phpError['message']))$parts[]=$phpError['message'];
    	$arr['interface_notice']=implode(', ',$parts);
    	return $arr;
	}
	$response=stream_get_contents($fp);
	fclose($fp);
	$http_code=null;
	if (!empty($http_response_header)&&preg_match('#^HTTP/\S+\s+(\d{3})#',$http_response_header[0],$m))$http_code=(int)$m[1];
	$elapsed_seconds=microtime(true)-$start_monotonic;
	$obs=($start_utc_iso?" at $start_utc_iso UTC":"").' in '.round($elapsed_seconds,2).' sec'.($server_seen?" observed from $server_seen":"");
	if ($http_code===null) {$arr['interface_notice']="No HTTP status line$obs";return $arr;}
	if ($http_code===429) {$arr['interface_notice']="429 - Rate limit exceeded$obs";return $arr;}
	if ($http_code!==200) {$arr['interface_notice']=$http_code." - Insufficient HTTP response$obs";return $arr;}
	try {$obj=json_decode($response,true,512,JSON_THROW_ON_ERROR);}
	catch(JsonException $e){$arr['interface_notice']="200 - JSON decode exception: ".$e->getMessage().$obs;return $arr;}
	if (!is_array($obj)) {$arr['interface_notice']="200 - Invalid JSON structure$obs";return $arr;}

$notices = '';	
$links = '';		
$redacted = '';
$interface_notice = '';	
$resource_upload_at = null;
$object_type = $obj['objectClassName'];
$rdap_version = '';	
if (is_array($obj['rdapConformance'])) {
    usort($obj['rdapConformance'], function ($a, $b) {
        $ga = getConformanceGroup($a);
        $gb = getConformanceGroup($b);
        if ($ga !== $gb) {
            return $ga <=> $gb;
        }
        return strcmp($a, $b);
    });
}
$rdap_conformance = (is_array($obj['rdapConformance'])) ? implode(",<br />", $obj['rdapConformance']) : $obj['rdapConformance'];
$language_codes = (is_array($obj['lang'])) ? implode(",<br />", $obj['lang']) : $obj['lang'];
$registrar_identifiers = '';
$registrar_identifier = null;
$registry_domain_uri = '';
$registrar_domain_uri = '';
$registrar_complaint_uri = '';	
$status_explanation_uri = '';
$registrant_subject_identifier = '';
if ($inputtld == 'nl' or $inputtld == 'frl')	{		
	$registrant_subject_identifier = 'NL88COMM01234567890123456789012345';	
}
$created_at = null;
$latest_registrar_transfer_at = null;			
$latest_data_mutation_at = null;
$server_statuses = '';
$client_statuses = '';
$lifecycle_phase = '';	
$indeterminate_statuses = '';	
	
$dns_state = 'dns_undelegated';
$expiration_at = null;
$lifecycle_phase = '';	
$deletion_at = null;	
$extensions = '';
$remarks = '';
$registrant_statuses = '';
$registrant_created_at = null;
$registrant_latest_transfer_at = null;	
$registrant_latest_data_mutation_at = null;
$registrant_expiration_at = null;	
$registrant_deletion_at = null;	
$registrant_remarks = '';		
$request_handling_remarks = '';
$issue_reporting_remarks = '';
$billing_remarks = '';		
$reseller_statuses = '';
$reseller_created_at = null;
$reseller_latest_transfer_at = null;	
$reseller_latest_data_mutation_at = null;
$reseller_expiration_at = null;	
$reseller_deletion_at = null;
$reseller_remarks = '';		
$registrar_statuses = '';	
$registrar_created_at = null;
$registrar_latest_transfer_at = null;	
$registrar_latest_data_mutation_at = null;
$registrar_expiration_at = null;	
$registrar_deletion_at = null;		
$registrar_remarks = '';
$sponsor_statuses = '';
$sponsor_created_at = null;
$sponsor_latest_transfer_at = null;	
$sponsor_latest_data_mutation_at = null;
$sponsor_expiration_at = null;	
$sponsor_deletion_at = null;
$sponsor_remarks = '';
$tld_global_handle	= '';
$registrar_handle = $obj['handle'];
$ascii_name = $obj['ldhName'];
$unicode_name = $obj['unicodeName'];
$nameservers_rdap_dnssec_signed = '';
$nameservers_rdap_ds_key_tags = '';	
$nameservers_rdap_ds_algorithm_numbers = '';
$nameservers_rdap_ds_digest_types = '';
$nameservers_rdap_ds_digests = '';	
	
$sponsor_handle = '';
$sponsor_organization_type = '';	
$sponsor_organization_name = '';		
$sponsor_presented_name = '';	
$sponsor_kind = '';
$sponsor_name = '';
$sponsor_email = '';
$sponsor_phone = '';
$sponsor_country_code = '';		
$sponsor_street_address = '';
$sponsor_city = '';
$sponsor_state_or_province = '';	
$sponsor_postal_code = '';
$sponsor_country_name = '';
$sponsor_links = '';	
$registrant_handle = '';
$registrant_organization_type = '';
$registrant_organization_name = '';	
$registrant_presented_name = '';
$registrant_kind = '';
$registrant_name = '';
$registrant_email = '';
$registrant_contact_uri = '';	
$registrant_phone = '';
$registrant_country_code = '(not provided)';
$registrant_street_address = '';
$registrant_city = '';
$registrant_state_or_province = '';
$registrant_postal_code = '';
$registrant_country_name = '';	
$registrant_preferred_languages = '';
$registrant_links = '';	
$request_handling_handle = '';
$request_handling_organization_type = '';
$request_handling_organization_name = '';	
$request_handling_presented_name = '';
$request_handling_kind = '';
$request_handling_name = '';	
$request_handling_email = '';
$request_handling_contact_uri = '';	
$request_handling_phone = '';
$request_handling_country_code = '';	
$request_handling_street_address = '';
$request_handling_city = '';	
$request_handling_state_or_province = '';
$request_handling_postal_code = '';	
$request_handling_country_name = '';
$request_handling_preferred_languages = '';
$request_handling_links = '';	
$issue_reporting_handle = '';
$issue_reporting_organization_type = '';
$issue_reporting_organization_name = '';	
$issue_reporting_presented_name = '';
$issue_reporting_kind = '';
$issue_reporting_name = '';	
$issue_reporting_email = '';
$issue_reporting_contact_uri = '';	
$issue_reporting_phone = '';
$issue_reporting_country_code = '';	
$issue_reporting_street_address = '';
$issue_reporting_city = '';	
$issue_reporting_state_or_province = '';
$issue_reporting_postal_code = '';	
$issue_reporting_country_name = '';
$issue_reporting_preferred_languages = '';
$issue_reporting_links = '';	
$billing_handle = '';
$billing_organization_type = '';
$billing_organization_name = '';	
$billing_presented_name = '';
$billing_kind = '';
$billing_name = '';		
$billing_email = '';
$billing_contact_uri = '';	
$billing_phone = '';
$billing_country_code = '';	
$billing_street_address = '';
$billing_city = '';	
$billing_state_or_province = '';	
$billing_postal_code = '';	
$billing_country_name = '';
$billing_links = '';	

$reseller_handle = '';
$reseller_organization_type = '';	
$reseller_organization_name = '';	
$reseller_presented_name = '';	
$reseller_kind = '';	
$reseller_name = '';
$reseller_email = '';	
$reseller_contact_uri = '';	
$reseller_phone = '';
$reseller_country_code = '';	
$reseller_street_address = '';
$reseller_city = '';
$reseller_state_or_province = '';	
$reseller_postal_code = '';
$reseller_country_name = '';	
$reseller_preferred_languages = '';
$reseller_links = '';
	
$registrar_handle = '';
$registrar_organization_type = '';
$registrar_organization_name = '';	
$registrar_presented_name = '';	
$registrar_kind = '';
$registrar_name = '';	
$registrar_email = '';
$registrar_contact_uri = '';	
$registrar_phone = '';
$registrar_country_code = '';	
$registrar_street_address = '';
$registrar_city = '';
$registrar_state_or_province = '';	
$registrar_postal_code = '';
$registrar_country_name = '';	
$registrar_preferred_languages = '';
$registrar_links = '';
	
$registrar_abuse_handle = '';	
$registrar_abuse_organization_type = '';
$registrar_abuse_organization_name = '';
$registrar_abuse_presented_name = '';
$registrar_abuse_kind = '';
$registrar_abuse_email = 'Abuse contact email unavailable.';
$registrar_abuse_contact_uri = '';	
$registrar_abuse_phone = '';
$registrar_abuse_country_code = '';	
	
$nameservers_handles = '';
$nameservers_ascii = '';
$nameservers_unicode = '';
$nameservers_ipv4 = '';
$nameservers_ipv6 = '';
$nameservers_statuses = '';
$nameserver_check_result = '';
$nameservers_check_dates = '';
$nameservers_last_valid_nameserver_check_dates = '';
	
$entity_sponsor = -1;	
$entity_registrant = -1;
$entity_request_handling = -1;
$entity_technical = -1;
$entity_billing = -1;	
$entity_reseller = -1;		
$entity_registrar = -1;
$entity_key4_sponsor = -1;	
$entity_key4_registrant = -1;	
$entity_key4_request_handling = -1;
$entity_key4_tech = -1;
$entity_key4_billing = -1;
$entity_key4_reseller = -1;		
$entity_key4_registrar = -1;
$entity_key4_registrar_abuse = -1;	

$raw_rdap_data = '';
	
foreach($obj as $key1 => $value1) {
	$raw_rdap_data .= $key1 . ': ' . $value1 . "\n";
    foreach($value1 as $key2 => $value2) {
		$raw_rdap_data .= "+". $key2 . ': ' . $value2 . "\n";
		foreach($value2 as $key3 => $value3) {
			$raw_rdap_data .= "++" . $key3 . ': ' . $value3 . "\n";
			foreach($value3 as $key4 => $value4) {
				$raw_rdap_data .= "+++" . $key4 . ': ' . $value4 . "\n";
				if ($value4 == 'registrant')	{
					$entity_registrant = $key2;
				}
				elseif ($value4 == 'administrative')	{
					$entity_request_handling = $key2;
				}
				elseif ($value4 == 'technical')	{
					$entity_technical = $key2;
				}
				elseif ($value4 == 'billing')	{
					$entity_billing = $key2;
				}
				elseif ($value4 == 'reseller')	{
					$entity_reseller = $key2;
				}
				elseif ($value4 == 'registrar')	{
					$entity_registrar = $key2;
				}
				elseif ($value4 == 'sponsor')	{
					$entity_sponsor = $key2;
				}
				foreach($value4 as $key5 => $value5) {
					$raw_rdap_data .= "++++" . $key5 . ': ' . $value5 . "\n";
					foreach($value5 as $key6 => $value6) {
						$raw_rdap_data .= "+++++" . $key6 . ': ' . $value6 . "\n";
						if ($value6 == 'registrant')	{
							$entity_key4_registrant = $key4;
						}
						elseif ($value6 == 'administrative')	{
							$entity_key4_request_handling = $key4;
						}
						elseif ($value6 == 'technical')	{
							$entity_key4_tech = $key4;
						}
						elseif ($value6 == 'billing')	{
							$entity_key4_billing = $key4;
						}						
						elseif ($value6 == 'reseller')	{
							$entity_key4_reseller = $key4;
						}
						elseif ($value6 == 'registrar')	{
							$entity_key4_registrar = $key4;
						}
						elseif ($value6 == 'sponsor')	{
							$entity_key4_sponsor = $key4;
						}
						elseif ($value6 == 'abuse')	{
							$entity_key4_registrar_abuse = $key4;
						}
						foreach($value6 as $key7 => $value7) {
							$raw_rdap_data .= "++++++" . $key7 . ': ' . $value7 . "\n";
							foreach($value7 as $key8 => $value8) {
								$raw_rdap_data .= "+++++++" . $key8 . ': ' . $value8 . "\n";
								foreach($value8 as $key9 => $value9) {
									$raw_rdap_data .= "++++++++" . $key9 . ': ' . $value9 . "\n";	
								}
							}
						}	
					}	
				}
			}
		}
	}
}
$raw_rdap_data = nl2br(htmlspecialchars($raw_rdap_data));	
$raw_rdap_data = str_replace(' Array','', $raw_rdap_data);
foreach($obj as $key1 => $value1) {
	if ($key1 == 'extensions')	{	
		$extensions .= (is_array($value1)) ? implode(",<br />", $value1) : $value1;
	}
	foreach($value1 as $key2 => $value2) {
		if ($key1 == 'status')	{
			$rdap_version = 'RDAPv1';
			if (str_starts_with($value2, 'server'))	{
				$server_statuses .= $value2 . ",";
			}
			elseif (str_starts_with($value2, 'client'))	{
				$client_statuses .= $value2 . ",";
			}
			elseif (str_starts_with($value2, 'pending'))	{
				$lifecycle_phase .= $value2 . ",";
			}
			elseif (str_contains($value2, 'redemption'))	{
				$lifecycle_phase .= $value2 . ",";
			}			
			else	{
				$indeterminate_statuses .= $value2 . ",";
			}
		}
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
		if ($key1 == 'redacted')	{
			$parts = [];
			if (!empty($value2['type'])) { $parts[] = "type: " . $value2['type']; }
			if (!empty($value2['name']['type'])) { $parts[] = "name.type: " . $value2['name']['type']; }
			if (!empty($value2['name']['description'])) { $parts[] = "description: " . $value2['name']['description']; }
   			if (!empty($value2['method'])) { $parts[] = "m: " . $value2['method']; }
   			if (!empty($value2['reason']['description'])) { $parts[] = "r: " . $value2['reason']['description']; }
   			if (!empty($parts)) { $redacted .= implode(' | ', $parts) . "<br />"; }			
		}		
		foreach($value2 as $key3 => $value3) {
			if ($key1 == 'notices')	{
				if (!is_array($value3))	{
					$notices .= $key2.': '.$key3.': '.$value3."<br />";		
				}
			}				
			if ($key1 == 'links')	{
				$links .= $key2.': '.$key3.': '.$value3."<br />";
				if ($key3 == 'rel' and $value3 == 'self') {
					$registry_domain_uri = $value2['href'];
				}
				elseif ($key3 == 'rel' and $value3 == 'related') {
					$registrar_domain_uri = $value2['href'];
				}				
			}	
			if ($key1 == 'remarks')	{
				if (strlen($remarks))	{
					$remarks .= "<br />";				
				}
				$remarks .= interprete_remark($key3, $value3);
			}			
			if ($key1 == 'events')	{
				if ($key3 == 'eventAction' and $value3 == 'registration')	{
					$created_at = $value2['eventDate'];
				}
				elseif ($key3 == 'eventAction' and $value3 == 'transfer')	{
					$latest_registrar_transfer_at = $value2['eventDate'];
				}
				elseif ($key3 == 'eventAction' and $value3 == 'last changed')	{
					$latest_data_mutation_at = $value2['eventDate'];
				}				
				elseif ($key3 == 'eventAction' and $value3 == 'expiration')	{
					$expiration_at = $value2['eventDate'];
				}
				elseif ($key3 == 'eventAction' and $value3 == 'deletion')	{
					$deletion_at = $value2['eventDate'];
				}
				elseif ($key3 == 'eventAction' and $value3 == 'last update of RDAP database')	{
					$resource_upload_at = $value2['eventDate'];				
				}
					
			}			
			if ($key1 == 'entities')	{
				if ($key3 == 'handle')	{
					if ($key2 == $entity_sponsor)	{
						$sponsor_handle = $value3;
					}
					if ($key2 == $entity_registrant)	{
						$registrant_handle = $value3;
					}				
					if ($key2 == $entity_request_handling)	{
						$request_handling_handle = $value3;
					}
					if ($key2 == $entity_technical)	{
						$issue_reporting_handle = $value3;
					}
					if ($key2 == $entity_reseller)	{
						$reseller_handle = $value3;
					}
					if ($key2 == $entity_registrar)	{
						$registrar_handle = $value3;
					}	
				}
				if ($key3 == 'status')	{
					if ($key2 == $entity_registrant)	{
						$registrant_statuses .= (is_array($value3)) ? implode(",<br />", $value3) : $value3;
						//$registrant_statuses .= $key1.'#'.$value1.'#'.$key2.'#'.$value2.'#'.$key3.'#'.$value3.'#'.$key4.'#'.$value4;
					}
					if ($key2 == $entity_reseller)	{
						$reseller_statuses .= (is_array($value3)) ? implode(",<br />", $value3) : $value3;
					}
					if ($key2 == $entity_registrar)	{
						$registrar_statuses .= (is_array($value3)) ? implode(",<br />", $value3) : $value3;
					}
					if ($key2 == $entity_sponsor)	{
						$sponsor_statuses .= (is_array($value3)) ? implode(",<br />", $value3) : $value3;
					}
				}
			}	
			if ($key1 == 'nameservers')	{
				if ($key3 == 'handle') {
					$nameservers_handles .= $key2.': '.$value3."<br />";
				}
				elseif ($key3 == 'ldhName') {
					$nameservers_ascii .= $key2.': '.$value3."<br />";
					$dns_state = 'dns_delegated';
				}
				elseif ($key3 == 'unicodeName')	{
					$nameservers_unicode .= $key2.': '.$value3."<br />";
					$dns_state = 'dns_delegated';
				}
				elseif ($key3 == 'status')	{
					$nameservers_statuses .= $key2.': '.$value3[0]."<br />";	
				}
			}
			if ($key1 == 'secureDNS')	{
				if ($key2 == 'dsData') {
					$nameservers_rdap_ds_key_tags .= $key3.': '.$value3['keyTag'].",";	
					$nameservers_rdap_ds_algorithm_numbers .= $key3.': '.$value3['algorithm'].",";	
					$nameservers_rdap_ds_digest_types .= $key3.': '.$value3['digestType'].",";	
					$nameservers_rdap_ds_digests .= $key3.': '.$value3['digest'].",";
				}				
			}
			foreach($value3 as $key4 => $value4) {
				if ($key1 == 'notices')	{
					if (!is_array($value4))	{
						$notices .= $key2.': '.$key3.': '.$key4.': '.$value4."<br />";				
					}
				}	
				if ($key1 == 'entities')	{
					if ($key2 == $entity_registrar and $key3 == 'publicIds')	{
						$registrar_identifiers .= $value4['type'].': '.$value4['identifier']."<br />";
						$registrar_identifier = $value4['identifier'];
					}					
				}
				foreach($value4 as $key5 => $value5) {
					if ($key1 == 'notices')	{
						if (!is_array($value5))	{
							$notices .= $key2.': '.$key3.': '.$key4.': '.$key5.': '.$value5."<br />";
						}	
						if ($key3 == 'links')	{
							if ($key5 == 'href' and str_contains($value5, 'icann.org/wicf')) $registrar_complaint_uri = $value5; 
							if ($key5 == 'href' and str_contains($value5, 'icann.org/epp')) $status_explanation_uri = $value5;
						}
					}
					if ($key1 == 'entities')	{
						if ($key2 == $entity_registrant and $key3 == 'events')	{
							if ($key5 == 'eventAction' and $value5 == 'registration')	{
								$registrant_created_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'transfer')	{
								$registrant_latest_transfer_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last changed')	{
								$registrant_latest_data_mutation_at = $value4['eventDate'];
							}				
							elseif ($key5 == 'eventAction' and $value5 == 'expiration')	{
								$registrant_expiration_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'deletion')	{
								$registrant_deletion_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last update of RDAP database')	{
								$registrant_resource_upload_at = $value4['eventDate'];				
							}
						}
						if ($key2 == $entity_reseller and $key3 == 'events')	{
							if ($key5 == 'eventAction' and $value5 == 'registration')	{
								$reseller_created_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'transfer')	{
								$reseller_latest_transfer_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last changed')	{
								$reseller_latest_data_mutation_at = $value4['eventDate'];
							}				
							elseif ($key5 == 'eventAction' and $value5 == 'expiration')	{
								$reseller_expiration_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'deletion')	{
								$reseller_deletion_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last update of RDAP database')	{
								$reseller_resource_upload_at = $value4['eventDate'];				
							}
						}				
						if ($key2 == $entity_registrar and $key3 == 'events')	{
							if ($key5 == 'eventAction' and $value5 == 'registration')	{
								$registrar_created_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'transfer')	{
								$registrar_latest_transfer_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last changed')	{
								$registrar_latest_data_mutation_at = $value4['eventDate'];
							}				
							elseif ($key5 == 'eventAction' and $value5 == 'expiration')	{
								$registrar_expiration_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'deletion')	{
								$registrar_deletion_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last update of RDAP database')	{
								$registrar_resource_upload_at = $value4['eventDate'];				
							}
						}
						if ($key2 == $entity_sponsor and $key3 == 'events')	{
							if ($key5 == 'eventAction' and $value5 == 'registration')	{
								$sponsor_created_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'transfer')	{
								$sponsor_latest_transfer_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last changed')	{
								$sponsor_latest_data_mutation_at = $value4['eventDate'];
							}				
							elseif ($key5 == 'eventAction' and $value5 == 'expiration')	{
								$sponsor_expiration_at = $valu4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'deletion')	{
								$sponsor_deletion_at = $value4['eventDate'];
							}
							elseif ($key5 == 'eventAction' and $value5 == 'last update of RDAP database')	{
								$sponsor_resource_upload_at = $value4['eventDate'];				
							}
						}
						if ($key2 == $entity_sponsor and $key3 == 'remarks')	{
							if (strlen($sponsor_remarks))	{
								$sponsor_remarks .= "<br />";				
							}
            				$sponsor_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_registrant and $key3 == 'remarks')	{
							if (strlen($registrant_remarks))	{
								$registrant_remarks .= "<br />";				
							}
		        			$registrant_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_request_handling and $key3 == 'remarks')	{
							if (strlen($request_handling_remarks))	{
								$request_handling_remarks .= "<br />";				
							}
		        			$request_handling_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_technical and $key3 == 'remarks')	{
							if (strlen($issue_reporting_remarks))	{
								$issue_reporting_remarks .= "<br />";				
							}
		        			$issue_reporting_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_billing and $key3 == 'remarks')	{
							if (strlen($billing_remarks))	{
								$billing_remarks .= "<br />";				
							}
		        			$billing_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_reseller and $key3 == 'remarks')	{
							if (strlen($reseller_remarks))	{
								$reseller_remarks .= "<br />";				
							}
		        			$reseller_remarks .= interprete_remark($key5, $value5);
						}
						if ($key2 == $entity_registrar and $key3 == 'remarks')	{
							if (strlen($registrar_remarks))	{
								$registrar_remarks .= "<br />";				
							}
							$registrar_remarks .= interprete_remark($key5, $value5);	
						}
						if ($key2 == $entity_sponsor and $key3 == 'links')	{
							$sponsor_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_registrant and $key3 == 'links')	{
							$registrant_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_request_handling and $key3 == 'links')	{
							$request_handling_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_technical and $key3 == 'links')	{
							$issue_reporting_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_billing and $key3 == 'links')	{
							$billing_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_reseller and $key3 == 'links')	{
							$reseller_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
						if ($key2 == $entity_registrar and $key3 == 'links')	{
							$registrar_links .= $key4.': '.$key5.': '.$value5."<br />";
						}
					}		
					if ($key1 == 'nameservers')	{							
						if ($key3 == 'events')	{
							if ($key4 == 0)	{	
								if ($key5 == 'eventAction' and $value5 == 'delegation check')	{
									$nameservers_check_dates .= $key2.': '.$value4['eventDate']."<br />";
									$nameservers_check_status = (is_array($value4['status'])) ? implode(",", $value4['status']): $value4['status'];
									$nameservers_check_status = str_replace("ns aa", "ns aa - authoritative", $nameservers_check_status);
									$nameserver_check_result .= $key2.': '. $nameservers_check_status."<br />";								}
							}	
							elseif ($key4 == 1)	{	
								if ($key5 == 'eventAction' and $value5 == 'last correct delegation check')	{
									$nameservers_last_valid_nameserver_check_dates .= $key2.': '.$value4['eventDate']."<br />";
								}
							}						
						}
						if ($key3 == 'ipAddresses') {
							if ($key4 == 'v4') {
								$nameservers_ipv4 .= $key2.': '.$value5."<br />";
								$dns_state = 'dns_delegated';
							}
							elseif ($key4 == 'v6') {
								$nameservers_ipv6 .= $key2.': '.$value5."<br />";
								$dns_state = 'dns_delegated';
							}
						}					
					}
					foreach($value5 as $key6 => $value6) {
						if ($key1 == 'notices')	{
							if (!is_array($value6))	{
								$notices .= $key2.': '.$key3.': '.$key4.': '.$key5.': '.$key6.': '.$value6."<br />";
							}
						}	
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'email' and $value6 == 'email')	{
							if ($key2 == $entity_sponsor)	{
								$sponsor_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}							
							if ($key2 == $entity_registrant)	{
								$registrant_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_billing)	{
								$billing_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_email .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'contact-uri' and $value6 == 'uri')	{
							if ($key2 == $entity_sponsor)	{
								$sponsor_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_registrant)	{
								$registrant_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_billing)	{
								$billing_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_contact_uri .= (is_array($value5[3])) ? implode(",<br />",$value5[3]) : $value5[3];
							}
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'tel' and $value6 == 'tel')	{
							$typeresult = '';
							if (is_array($value5[1]))	{ 
								foreach($value5[1] as $typekey => $typevalue)	{
									if (is_array($typevalue))	{ 
										foreach($typevalue as $typekey2 => $typevalue2)	{											
											$typeresult .= $typevalue2 . ' ';												
										}
									}
									else	{
										$typeresult .= $typevalue . ' ';
									}
								}	
							}
							else	{
								$typeresult .= $value5[1] . ' ';								
							}
							if ($key2 == $entity_registrant)	{
								$registrant_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}
							if ($key2 == $entity_billing)	{
								$billing_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}
							if ($key2 == $entity_registrar)	{
								$registrar_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_phone .= $typeresult . $value5[2] . ' ' . $value5[3]."<br />";
							}	
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'fn' and $value6 == 'fn')	{
							if ($key2 == $entity_registrant)	{
								$registrant_presented_name = $value5[3];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_presented_name = $value5[3];
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_presented_name = $value5[3];
							}
							if ($key2 == $entity_billing)	{
								$billing_presented_name = $value5[3];
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_presented_name = $value5[3];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_presented_name = $value5[3];
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_presented_name = $value5[3];
							}	
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'n' and $value6 == 'n')	{
							if ($key2 == $entity_registrant)	{
								$registrant_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$registrant_name .= ', '. $value5[3][1];
								}
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$request_handling_name .= ', '. $value5[3][1];
								}
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$issue_reporting_name .= ', '. $value5[3][1];
								}
							}
							if ($key2 == $entity_billing)	{
								$billing_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$billing_name .= ', '. $value5[3][1];
								}
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$reseller_name .= ', '. $value5[3][1];
								}
							}
							if ($key2 == $entity_registrar)	{
								$registrar_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$registrar_name .= ', '. $value5[3][1];
								}
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_name = $value5[3][0];	
								if (strlen($value5[3][1]))	{
									$sponsor_name .= ', '. $value5[3][1];
								}
							}
						}	
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'kind' and $value6 == 'kind')	{
							if ($key2 == $entity_registrant)	{
								$registrant_kind = $value5[3];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_kind = $value5[3];
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_kind = $value5[3];
							}
							if ($key2 == $entity_billing)	{
								$biiling_kind = $value5[3];
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_kind = $value5[3];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_kind = $value5[3];
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_kind = $value5[3];
							}							
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'org' and $value6 == 'org')	{
							if ($key2 == $entity_registrant)	{
								$registrant_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$registrant_organization_name = $value5[3];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$request_handling_organization_name = $value5[3];
							}
							if ($key2 == $entity_technical)	{
								$issue_reporting_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$issue_reporting_organization_name = $value5[3];
							}
							if ($key2 == $entity_billing)	{
								$billing_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$billing_organization_name = $value5[3];
							}							
							if ($key2 == $entity_reseller)	{
								$reseller_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$reseller_organization_name = $value5[3];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$registrar_organization_name = $value5[3];
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_organization_type = (is_array($value5[1]['type'])) ? implode(",<br />",$value5[1]['type']) : $value5[1]['type'];
								$sponsor_organization_name = $value5[3];
							}
						}						
						if ($key1 == 'entities' && $key3 == 'vcardArray' && ($value5[0] ?? null) == 'lang' && ($value5[3] ?? null) !== null) {
    						$pref = (int)($value6['pref'] ?? 0);
    						$language_code = $value5[3];						
    						if ($key2 == $entity_registrant) {
        						if ($pref == 1) $registrant_preferred_languages .= $language_code;
        						if ($pref == 2) $registrant_preferred_languages .= ', ' . $language_code;
    						}						
    						if ($key2 == $entity_request_handling) {
        						if ($pref == 1) $request_handling_preferred_languages .= $language_code;
        						if ($pref == 2) $request_handling_preferred_languages .= ', ' . $language_code;
    						}
    						if ($key2 == $entity_technical) {
        						if ($pref == 1) $issue_reporting_preferred_languages .= $language_code;
        						if ($pref == 2) $issue_reporting_preferred_languages .= ', ' . $language_code;
    						}						
    						if ($key2 == $entity_billing) {
        						if ($pref == 1) $billing_preferred_languages .= $language_code;
        						if ($pref == 2) $billing_preferred_languages .= ', ' . $language_code;
    						}
    						if ($key2 == $entity_reseller) {
        						if ($pref == 1) $reseller_preferred_languages .= $language_code;
        						if ($pref == 2) $reseller_preferred_languages .= ', ' . $language_code;
    						}						
    						if ($key2 == $entity_registrar) {
        						if ($pref == 1) $registrar_preferred_languages .= $language_code;
        						if ($pref == 2) $registrar_preferred_languages .= ', ' . $language_code;
							}						
    						if ($key2 == $entity_sponsor) {
        						if ($pref == 1) $sponsor_preferred_languages .= $language_code;
        						if ($pref == 2) $sponsor_preferred_languages .= ', ' . $language_code;
    						}
						}						
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'adr' and $key6 == 1)	{
							if ($key2 == $entity_registrant)	{
								$registrant_country_code = detect_country_code($registrant_country_code, $value6['CC'], $value6['cc']);
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_country_code = detect_country_code($request_handling_country_code, $value6['CC'], $value6['cc']);
							}	
							if ($key2 == $entity_technical)	{
								$issue_reporting_country_code = detect_country_code($issue_reporting_country_code, $value6['CC'], $value6['cc']);
							}
							if ($key2 == $entity_billing)	{
								$billing_country_code = detect_country_code($billing_country_code, $value6['CC'], $value6['cc']);
							}
							if ($key2 == $entity_reseller)	{
								$reseller_country_code = detect_country_code($reseller_country_code, $value6['CC'], $value6['cc']);
							}
							if ($key2 == $entity_registrar)	{
								$registrar_country_code = detect_country_code($registrar_country_code, $value6['CC'], $value6['cc']);
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_country_code = detect_country_code($sponsor_country_code, $value6['CC'], $value6['cc']);
							}
						}
						if ($key1 == 'entities' and $key3 == 'vcardArray' and $value5[0] == 'adr' and $key6 == 3)	{
							if ($key2 == $entity_registrant)	{
								$registrant_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$registrant_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$registrant_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$registrant_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$registrant_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}
							if ($key2 == $entity_request_handling)	{
								$request_handling_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$request_handling_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$request_handling_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$request_handling_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$request_handling_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}	
							if ($key2 == $entity_technical)	{
								$issue_reporting_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$issue_reporting_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$issue_reporting_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$issue_reporting_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$issue_reporting_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}
							if ($key2 == $entity_billing)	{
								$billing_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$billing_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$billing_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$billing_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$billing_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}
							if ($key2 == $entity_reseller)	{
								$reseller_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$reseller_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$reseller_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$reseller_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$reseller_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}
							if ($key2 == $entity_registrar)	{
								$registrar_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$registrar_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$registrar_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$registrar_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$registrar_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];	
							}
							if ($key2 == $entity_sponsor)	{
								$sponsor_street_address = (is_array($value6[2])) ? implode(",<br />",$value6[2]) : $value6[2];
								$sponsor_city = (is_array($value6[3])) ? implode(",<br />",$value6[3]) : $value6[3];
								$sponsor_state_or_province = (is_array($value6[4])) ? implode(",<br />",$value6[4]) : $value6[4];
								$sponsor_postal_code = (is_array($value6[5])) ? implode(",<br />",$value6[5]) : $value6[5];
								$sponsor_country_name = (is_array($value6[6])) ? implode(",<br />",$value6[6]) : $value6[6];
							}
						}
						foreach($value6 as $key7 => $value7)	{
							foreach($value7 as $key8 => $value8) {
								if ($key1 == 'entities' and $key2 == $entity_registrar and $key3 == 'entities' 
									and $key4 == $entity_key4_registrar_abuse and $key5 == 'vcardArray' and $key6 == 1)	{
									if ($value7[0] == 'handle' and $value8 == 'handle')	{
										$registrar_abuse_handle = $value7[3];
									}
									elseif ($value7[0] == 'org' and $value8 == 'org')	{
										$registrar_abuse_organization_type = $value7[1]['type'];
										$registrar_abuse_organization_name = $value7[3];
									}
									elseif ($value7[0] == 'fn' and $value8 == 'fn')	{
										$registrar_abuse_presented_name = $value7[3];
									}
									elseif ($value7[0] == 'kind' and $value8 == 'kind')	{
										$registrar_abuse_kind = $value7[3];
									}	
									elseif ($value7[0] == 'email' and $value8 == 'email')	{
										$registrar_abuse_email = $value7[3];
									}
									elseif ($value7[0] == 'contact-uri' and $value8 == 'uri')	{
										$registrar_abuse_contact_uri = $value7[3];
									}
									elseif ($value7[0] == 'tel' and $value8 == 'tel')	{
										$typeresult = '';
										if (is_array($value7[1]))	{ 
											foreach($value7[1] as $typekey => $typevalue)	{
												if (is_array($typevalue))	{ 
													foreach($typevalue as $typekey2 => $typevalue2)	{											
														$typeresult .= $typevalue2 . ' ';												
													}
												}
												else	{
													$typeresult .= $typevalue . ' ';
												}
											}	
										}
										else	{
											$typeresult .= $value7[1] . ' ';								
										}						
										$registrar_abuse_phone .= $typeresult . $value7[2] . ' ' . $value7[3]."<br />";
									}
									elseif ($value7[0] == 'adr' and $key8 == 1)	{
										$registrar_abuse_country_code = detect_country_code($registrar_abuse_country_code, $value8['CC'], $value8['cc']);				
									}							
								}
								//echo 'k4: '.$key4. ' v4: '.$value4.' k5: '.$key5.' v5: '.$value5.' k6: '.$key6.' v6: '.$value6.' k7: '.$key7.' value73: '.$value7[3]."<br />";
								if ($key1 == 'entities' and $key5 == 'vcardArray' and $value7[0] == 'email' and $value8 == 'email')	{					
									if ($key4 == $entity_key4_registrant)	{
										$registrant_email .= $value7[3]."<br />";
									}
									if ($key4 == $entity_key4_request_handling)	{
										$request_handling_email .= $value7[3]."<br />";
									}
									if ($key4 == $entity_key4_tech)	{
										$issue_reporting_email .= $value7[3]."<br />";
									}
									if ($key4 == $entity_key4_reseller)	{
										$reseller_email .= $value7[3]."<br />";
									}
									if ($key4 == $entity_key4_registrar)	{
										$registrar_email .= $value7[3]."<br />";
									}
									if ($key4 == $entity_key4_sponsor)	{
										$sponsor_email .= $value7[3]."<br />";
									}							
								}
								if ($key1 == 'entities' and $key5 == 'vcardArray' and $value7[0] == 'tel' and $value8 == 'tel')	{
									if ($key4 == $entity_key4_registrant)	{
										$registrant_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}
									if ($key4 == $entity_key4_request_handling)	{
										$request_handling_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}
									if ($key4 == $entity_key4_tech)	{
										$issue_reporting_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}	
									if ($key4 == $entity_key4_reseller)	{
										$reseller_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}
									if ($key4 == $entity_key4_registrar)	{
										$registrar_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}
									if ($key4 == $entity_key4_sponsor)	{
										$sponsor_phone .= implode(",<br />",$value7[1]) . ': ' . $value7[3] . "<br />";
									}							
								}
							}	
						}	
					}					
				}
			}
		}
	}
}	

if ($inputbatch)	{
	$raw_rdap_data = '';
}
	
$arr['notices'] = $notices;
$arr['links'] = $links;
$arr['publication_state'] = $redacted;
$arr['interface_notice'] = $interface_notice;
	
$arr['metadata']['object_type'] = $object_type;
$arr['metadata']['rdap_version'] = $rdap_version;
$arr['metadata']['rdap_conformance'] = $rdap_conformance;
$arr['metadata']['registry_geo_location'] = '';
$arr['metadata']['global_domain_uri'] = '';	
$arr['metadata']['registry_domain_uri'] = $registry_domain_uri;
$arr['metadata']['registrar_domain_uri'] = $registrar_domain_uri;
$arr['metadata']['registry_tld_uri'] = '';	
$arr['metadata']['registrar_identifiers'] = $registrar_identifiers;		
$arr['metadata']['registrar_identifier'] = $registrar_identifier;
$arr['metadata']['request_uri'] = $url;
$arr['metadata']['registrar_complaint_uri'] = $registrar_complaint_uri;
$arr['metadata']['status_explanation_uri'] = $status_explanation_uri;
$arr['metadata']['resource_upload_at'] = $resource_upload_at;		
	
$arr['domain']['tld_global_handle'] = $tld_global_handle;
$arr['domain']['source_handle'] = $registrar_handle;
$arr['domain']['ascii_name'] = $ascii_name;	
$arr['domain']['unicode_name'] = $unicode_name;
$arr['domain']['statuses'] = rtrim($indeterminate_statuses . $server_statuses . $client_statuses . $lifecycle_phase, ",");
$arr['domain']['policy_statuses'] = rtrim($server_statuses . $client_statuses, ",");
$arr['domain']['dns_state'] = $dns_state;
$arr['domain']['created_at'] = $created_at;	
$arr['domain']['latest_registrar_transfer_at'] = $latest_registrar_transfer_at;
$arr['domain']['latest_data_mutation_at'] = $latest_data_mutation_at;
$arr['domain']['expiration_at'] = $expiration_at;
$arr['domain']['lifecycle_phase'] = rtrim($lifecycle_phase, ",");
$arr['domain']['deletion_at'] = $deletion_at;
$arr['domain']['extensions'] = $extensions;
$arr['domain']['remarks'] = $remarks;			
	
$arr['sponsor']['source_handle'] = $sponsor_handle;
$arr['sponsor']['subject_identifier'] = $sponsor_subject_identifier;		
$arr['sponsor']['organization_type'] = $sponsor_organization_type;	
$arr['sponsor']['organization_name'] = $sponsor_organization_name;	
$arr['sponsor']['presented_name'] = $sponsor_presented_name;	
$arr['sponsor']['kind'] = $sponsor_kind;	
$arr['sponsor']['name'] = $sponsor_name;		
$arr['sponsor']['email'] = $sponsor_email;	
$arr['sponsor']['phone'] = $sponsor_phone;
$arr['sponsor']['country_code'] = $sponsor_country_code;		
$arr['sponsor']['street_address'] = $sponsor_street_address;
$arr['sponsor']['city'] = $sponsor_city;
$arr['sponsor']['state_or_province'] = $sponsor_state_or_province;
$arr['sponsor']['postal_code'] = $sponsor_postal_code;
$arr['sponsor']['country_name'] = $sponsor_country_name;	
$arr['sponsor']['preferred_languages'] = $sponsor_preferred_languages;
$arr['sponsor']['statuses'] = $sponsor_statuses;
$arr['sponsor']['created_at'] = $sponsor_created_at;
$arr['sponsor']['latest_data_mutation_at'] = $sponsor_latest_data_mutation_at;
$arr['sponsor']['remarks'] = $sponsor_remarks;
$arr['sponsor']['links'] = $sponsor_links;	
	
$arr['registrant']['source_handle'] = $registrant_handle;
$arr['registrant']['subject_identifier'] = $registrant_subject_identifier;		
$arr['registrant']['organization_type'] = $registrant_organization_type;	
$arr['registrant']['organization_name'] = $registrant_organization_name;	
$arr['registrant']['presented_name'] = $registrant_presented_name;	
$arr['registrant']['kind'] = $registrant_kind;	
$arr['registrant']['name'] = $registrant_name;		
$arr['registrant']['email'] = $registrant_email;
$arr['registrant']['contact_uri'] = $registrant_contact_uri;
$arr['registrant']['phone'] = $registrant_phone;
$arr['registrant']['country_code'] = $registrant_country_code;		
$arr['registrant']['street_address'] = $registrant_street_address;
$arr['registrant']['city'] = $registrant_city;
$arr['registrant']['state_or_province'] = $registrant_state_or_province;
$arr['registrant']['postal_code'] = $registrant_postal_code;
$arr['registrant']['country_name'] = $registrant_country_name;	
$arr['registrant']['preferred_languages'] = $registrant_preferred_languages;
$arr['registrant']['statuses'] = $registrant_statuses;
$arr['registrant']['created_at'] = $registrant_created_at;
$arr['registrant']['latest_data_mutation_at'] = $registrant_latest_data_mutation_at;
$arr['registrant']['remarks'] = $registrant_remarks;
$arr['registrant']['links'] = $registrant_links;	
	
$arr['request_handling']['source_handle'] = $request_handling_handle;
$arr['request_handling']['subject_identifier'] = $request_handling_subject_identifier;		
$arr['request_handling']['organization_type'] = $request_handling_organization_type;	
$arr['request_handling']['organization_name'] = $request_handling_organization_name;	
$arr['request_handling']['presented_name'] = $request_handling_presented_name;	
$arr['request_handling']['kind'] = $request_handling_kind;	
$arr['request_handling']['name'] = $request_handling_name;		
$arr['request_handling']['email'] = $request_handling_email;
$arr['request_handling']['contact_uri'] = $request_handling_contact_uri;	
$arr['request_handling']['phone'] = $request_handling_phone;
$arr['request_handling']['country_code'] = $request_handling_country_code;		
$arr['request_handling']['street_address'] = $request_handling_street_address;
$arr['request_handling']['city'] = $request_handling_city;
$arr['request_handling']['state_or_province'] = $request_handling_state_or_province;
$arr['request_handling']['postal_code'] = $request_handling_postal_code;
$arr['request_handling']['country_name'] = $request_handling_country_name;	
$arr['request_handling']['preferred_languages'] = $request_handling_preferred_languages;
$arr['request_handling']['statuses'] = $request_handling_statuses;
$arr['request_handling']['created_at'] = $request_handling_created_at;
$arr['request_handling']['latest_data_mutation_at'] = $request_handling_latest_data_mutation_at;
$arr['request_handling']['remarks'] = $request_handling_remarks;
$arr['request_handling']['links'] = $request_handling_links;

$arr['issue_reporting']['source_handle'] = $issue_reporting_handle;
$arr['issue_reporting']['subject_identifier'] = $issue_reporting_subject_identifier;		
$arr['issue_reporting']['organization_type'] = $issue_reporting_organization_type;	
$arr['issue_reporting']['organization_name'] = $issue_reporting_organization_name;	
$arr['issue_reporting']['presented_name'] = $issue_reporting_presented_name;	
$arr['issue_reporting']['kind'] = $issue_reporting_kind;	
$arr['issue_reporting']['name'] = $issue_reporting_name;		
$arr['issue_reporting']['email'] = $issue_reporting_email;
$arr['issue_reporting']['contact_uri'] = $issue_reporting_contact_uri;	
$arr['issue_reporting']['phone'] = $issue_reporting_phone;
$arr['issue_reporting']['country_code'] = $issue_reporting_country_code;		
$arr['issue_reporting']['street_address'] = $issue_reporting_street_address;
$arr['issue_reporting']['city'] = $issue_reporting_city;
$arr['issue_reporting']['state_or_province'] = $issue_reporting_state_or_province;
$arr['issue_reporting']['postal_code'] = $issue_reporting_postal_code;
$arr['issue_reporting']['country_name'] = $issue_reporting_country_name;	
$arr['issue_reporting']['preferred_languages'] = $issue_reporting_preferred_languages;
$arr['issue_reporting']['statuses'] = $issue_reporting_statuses;
$arr['issue_reporting']['created_at'] = $issue_reporting_created_at;
$arr['issue_reporting']['latest_data_mutation_at'] = $issue_reporting_latest_data_mutation_at;
$arr['issue_reporting']['remarks'] = $issue_reporting_remarks;
$arr['issue_reporting']['links'] = $issue_reporting_links;	
	
$arr['billing']['source_handle'] = $billing_handle;
$arr['billing']['subject_identifier'] = $billing_subject_identifier;		
$arr['billing']['organization_type'] = $billing_organization_type;	
$arr['billing']['organization_name'] = $billing_organization_name;	
$arr['billing']['presented_name'] = $billing_presented_name;	
$arr['billing']['kind'] = $billing_kind;	
$arr['billing']['name'] = $billing_name;		
$arr['billing']['email'] = $billing_email;
$arr['billing']['contact_uri'] = $billing_contact_uri;	
$arr['billing']['phone'] = $billing_phone;
$arr['billing']['country_code'] = $billing_country_code;		
$arr['billing']['street_address'] = $billing_street_address;
$arr['billing']['city'] = $billing_city;
$arr['billing']['state_or_province'] = $billing_state_or_province;
$arr['billing']['postal_code'] = $billing_postal_code;
$arr['billing']['country_name'] = $billing_country_name;	
$arr['billing']['preferred_languages'] = $billing_preferred_languages;
$arr['billing']['statuses'] = $billing_statuses;
$arr['billing']['created_at'] = $billing_created_at;
$arr['billing']['latest_data_mutation_at'] = $billing_latest_data_mutation_at;
$arr['billing']['remarks'] = $billing_remarks;
$arr['billing']['links'] = $billing_links;	

$arr['reseller']['source_handle'] = $reseller_handle;
$arr['reseller']['subject_identifier'] = $reseller_subject_identifier;		
$arr['reseller']['organization_type'] = $reseller_organization_type;	
$arr['reseller']['organization_name'] = $reseller_organization_name;	
$arr['reseller']['presented_name'] = $reseller_presented_name;	
$arr['reseller']['kind'] = $reseller_kind;	
$arr['reseller']['name'] = $reseller_name;		
$arr['reseller']['email'] = $reseller_email;
$arr['reseller']['contact_uri'] = $reseller_contact_uri;	
$arr['reseller']['phone'] = $reseller_phone;
$arr['reseller']['country_code'] = $reseller_country_code;		
$arr['reseller']['street_address'] = $reseller_street_address;
$arr['reseller']['city'] = $reseller_city;
$arr['reseller']['state_or_province'] = $reseller_state_or_province;
$arr['reseller']['postal_code'] = $reseller_postal_code;
$arr['reseller']['country_name'] = $reseller_country_name;	
$arr['reseller']['preferred_languages'] = $reseller_preferred_languages;
$arr['reseller']['statuses'] = $reseller_statuses;
$arr['reseller']['created_at'] = $reseller_created_at;
$arr['reseller']['latest_data_mutation_at'] = $reseller_latest_data_mutation_at;
$arr['reseller']['remarks'] = $reseller_remarks;
$arr['reseller']['links'] = $reseller_links;	

$arr['registrar']['source_handle'] = $registrar_handle;
$arr['registrar']['subject_identifier'] = $registrar_subject_identifier;		
$arr['registrar']['organization_type'] = $registrar_organization_type;	
$arr['registrar']['organization_name'] = $registrar_organization_name;	
$arr['registrar']['presented_name'] = $registrar_presented_name;	
$arr['registrar']['kind'] = $registrar_kind;
$arr['registrar']['name'] = $registrar_name;		
$arr['registrar']['email'] = $registrar_email;
$arr['registrar']['contact_uri'] = $registrar_contact_uri;	
$arr['registrar']['phone'] = $registrar_phone;
$arr['registrar']['country_code'] = $registrar_country_code;		
$arr['registrar']['street_address'] = $registrar_street_address;
$arr['registrar']['city'] = $registrar_city;
$arr['registrar']['state_or_province'] = $registrar_state_or_province;
$arr['registrar']['postal_code'] = $registrar_postal_code;
$arr['registrar']['country_name'] = $registrar_country_name;	
$arr['registrar']['preferred_languages'] = $registrar_preferred_languages;
$arr['registrar']['statuses'] = $registrar_statuses;
$arr['registrar']['created_at'] = $registrar_created_at;
$arr['registrar']['latest_data_mutation_at'] = $registrar_latest_data_mutation_at;
$arr['registrar']['remarks'] = $registrar_remarks;
$arr['registrar']['links'] = $registrar_links;	
	
$arr['registrar_abuse']['source_handle'] = $registrar_abuse_handle;
$arr['registrar_abuse']['organization_type'] = $registrar_abuse_organization_type;
$arr['registrar_abuse']['organization_name'] = $registrar_abuse_organization_name;	
$arr['registrar_abuse']['presented_name'] = $registrar_abuse_presented_name;
$arr['registrar_abuse']['kind'] = $registrar_abuse_kind;
$arr['registrar_abuse']['email'] = $registrar_abuse_email;
$arr['registrar_abuse']['contact_uri'] = $registrar_abuse_contact_uri;	
$arr['registrar_abuse']['phone'] = $registrar_abuse_phone;
$arr['registrar_abuse']['country_code'] = $registrar_abuse_country_code;
	
$arr['nameservers']['source_handles'] = $nameservers_handles;
$arr['nameservers']['ascii_names'] = $nameservers_ascii;
$arr['nameservers']['unicode_names'] = $nameservers_unicode;	
$arr['nameservers']['ipv4_addresses'] = $nameservers_ipv4;	
$arr['nameservers']['ipv6_addresses'] = $nameservers_ipv6;	
$arr['nameservers']['statuses'] = $nameservers_statuses;	
$arr['nameservers']['nameserver_check_result'] = $nameserver_check_result;
$arr['nameservers']['nameserver_check_dates'] = $nameservers_check_dates;	
$arr['nameservers']['last_valid_nameserver_check_dates'] = $nameservers_last_valid_nameserver_check_dates;	
$arr['nameservers']['rdap_dnssec_signed'] = $nameservers_rdap_dnssec_signed;
$arr['nameservers']['rdap_ds_key_tags'] = rtrim($nameservers_rdap_ds_key_tags, ",");
$arr['nameservers']['rdap_ds_algorithm_numbers'] = rtrim($nameservers_rdap_ds_algorithm_numbers, ",");
$arr['nameservers']['rdap_ds_digest_types'] = rtrim($nameservers_rdap_ds_digest_types, ",");
$arr['nameservers']['rdap_ds_digests'] = rtrim($nameservers_rdap_ds_digests, ",");
	
$arr['raw_rdap'] = $raw_rdap_data;

return $arr;
}
?>