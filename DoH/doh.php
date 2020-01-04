<?php

class cDNSMessage
{
	const BigEndian = [
		1 => 'C',
		2 => 'n',
		4 => 'N'
	];
	const LBL = [
		'MASK' => 0b1100000000000000,
		'TERM' => 0
	];
	const RES = [
		'A'		=> 1,
		'NS'	=> 2,
		'MX'	=> 15,
		'AAAA'	=> 28
	];
	const CLS = 1;
	const TTL = 128;
	const HEADERS = [
		'ID'		=> 16,
		'FLAGS'		=> [
			'QR'		=> 1,
			'OPCODE'	=> 4,
			'AA'		=> 1,
			'TC'		=> 1,
			'RD'		=> 1,
			'RA'		=> 1,
			'Z'			=> 1,
			'AD'		=> 1,
			'CD'		=> 1,
			'RCODE'		=> 4
		],
		'QDCOUNT'	=> 16,
		'ANCOUNT'	=> 16,
		'NSCOUNT'	=> 16,
		'ARCOUNT'	=> 16
	];
	const QSECTION = [
		'NAME'	=> -1,
		'TYPE'	=> 16,
		'CLASS'	=> 16
	];
	const OSECTION = [
		'NAME'	=> -1,
		'TYPE'	=> 16,
		'CLASS'	=> 16,
		'TTL'	=> 32,
		'RDLEN'	=> 16,
		'RES'	=> -1
	];
	private $bytestream;
	private $available = FALSE;
	private $parsed = array(
		'HEADERS' => array(),
		'QD_RECS' => array(),
		'AN_RECS' => array(),
		'NS_RECS' => array(),
		'AR_RECS' => array()
	);
	function setByteStream($in_bytestream) {
		$this->bytestream = $in_bytestream;
		$this->_parse();
		/*
			DNS メッセージは質問も応答も解析可能だが、
			応答を構築するための API 利用は質問が 1 件の場合に限定（実装が面倒）。
			通常は質問は 1 件のようなので問題はない … と思う。
		*/
		if ($this->parsed['HEADERS']['QDCOUNT'] == 1) {
			$this->available = TRUE;
		}
	}
	function getByteStream() {
		if (!$this->available) {
			return NULL;
		}
		$this->_compose();
		return $this->bytestream;
	}
	function setAnswer($in_ip) {
		if (!$this->available) {
			return;
		}
		$this->_set_answer(array($in_ip));
	}
	function getQuery() {
		if (!$this->available) {
			return NULL;
		}
		return ($this->_get_query())[0];
	}
	private function _set_answer($in_array) {
		$this->parsed['HEADERS']['FLAGS']['QR'] = 1;
		for ($i = 0; $i < count($in_array); $i++) {
			array_push($this->parsed['AN_RECS'], array(
				'NAME'	=> $this->parsed['QD_RECS'][$i]['NAME'],
				'TYPE'	=> self::RES['A'],
				'CLASS'	=> self::CLS,
				'TTL'	=> self::TTL,
				'RDLEN'	=> 4,
				'RES'	=> $in_array[$i]
			));
		}
		$this->parsed['HEADERS']['ANCOUNT'] = count($in_array);
	}
	private function _get_query() {
		$ret = array();
		$q = $this->parsed['QD_RECS'];
		for ($i = 0; $i < count($q); $i++) {
			array_push($ret, $q[$i]['NAME']);
		}
		return $ret;
	}
	private function _parse() {
		/*
			$this->bytestream から $this->parsed を構築するための処理。
			各 _parse_xxx の戻りは $this->bytestream を読み進めた length 値。
		*/
		$next = 0;
		$next += $this->_parse_header($next);
		$next += $this->_parse_section($next, $this->parsed['HEADERS']['QDCOUNT'], self::QSECTION, $this->parsed['QD_RECS']);
		$next += $this->_parse_section($next, $this->parsed['HEADERS']['ANCOUNT'], self::OSECTION, $this->parsed['AN_RECS']);
		$next += $this->_parse_section($next, $this->parsed['HEADERS']['NSCOUNT'], self::OSECTION, $this->parsed['NS_RECS']);
		$next += $this->_parse_section($next, $this->parsed['HEADERS']['ARCOUNT'], self::OSECTION, $this->parsed['AR_RECS']);
		if (strlen($this->bytestream) != $next) {
			$this->_debug();
		}
	}
	private function _parse_header($in_start) {
		$next = $in_start;
		foreach (self::HEADERS as $key => $bits) {
			if ($key == 'FLAGS') {
				$next += $this->_parse_header_flg($next);
			} else {
				$bytes = $bits / 8;
				$this->parsed['HEADERS'][$key] = $this->_get($next, $bytes, TRUE);
				$next += $bytes;
			}
		}
		return ($next - $in_start);
	}
	private function _parse_header_flg($in_start) {
		$bytes = array_sum(self::HEADERS['FLAGS']) / 8;
		$dec = $this->_get($in_start, $bytes, TRUE);
		$this->parsed['HEADERS']['FLAGS'] = array();
		$start_bit = 0;
		foreach (self::HEADERS['FLAGS'] as $key => $bits) {
			$this->parsed['HEADERS']['FLAGS'][$key] = $this->_extract($dec, $start_bit, $bits);
			$start_bit += $bits;
		}
		return $bytes;
	}
	private function _parse_section($in_start, $in_repeat, $in_format, &$in_ref_parsed) {
		$next = $in_start;
		for ($i = 0; $i < $in_repeat; $i++) {
			$temp = array();
			foreach ($in_format as $key => $bits) {
				if ($bits > 0) {
					$bytes = $bits / 8;
					$temp[$key] = $this->_get($next, $bytes, TRUE);
					$next += $bytes;
					continue;
				}
				/*
					以下は可変長の項目。
					NAME と RES 以外が可変長になることは想定していないので、
					必要になったら実装。
				*/
				if ($key == 'NAME') {
					$ret = $this->_parse_label($next);
					$temp[$key] = $ret['data'];
					$next += $ret['size'];
					continue;
				}
				if ($key != 'RES') {
					$this->_debug();
				}
				switch ($temp['TYPE']) {
				case self::RES['A'] :
					$ip = array();
					for ($oc = 0; $oc < 4; $oc++) {
						array_push($ip, $this->_get($next + $oc, 1, TRUE));
					}
					$temp[$key] = implode('.', $ip);
					break;
				case self::RES['NS'] :
					$ret = $this->_parse_label($next);
					$temp[$key] = $ret['data'];
					break;
				case self::RES['AAAA'] :
				case self::RES['MX'] :
				default :
					/*
						A, NS 以外のリソースタイプの解析は、必要に応じて実装。
						（簡易 DoH には不要なので割愛）
					*/
					$temp[$key] = '(omitted)';
					break;
				}
				$next += $temp['RDLEN'];
			}
			array_push($in_ref_parsed, $temp);
		}
		return ($next - $in_start);
	}
	private function _parse_label($in_start) {
		$next = $in_start;
		$label = array();
		while (TRUE) {
			$offset = $this->_get($next, 2, TRUE);
			if (($offset & self::LBL['MASK']) == 0) {
				$len = $this->_get($next, 1, TRUE);
				if ($len == self::LBL['TERM']) {
					$next += 1;
					break;
				} else {
					$next += 1;
					array_push($label, $this->_get($next, $len));
					$next += $len;
				}
			} else {
				$offset &= ~self::LBL['MASK'];
				$ret = $this->_parse_label($offset);
				array_push($label, $ret['data']);
				$next += 2;
				break;
			}
		}
		return array(
			'data' => implode('.', $label),
			'size' => ($next - $in_start));
	}
	private function _compose() {
		/*
			$this->parsed から $this->bytestream に書き戻すための処理。
			各 _compose_xxx の戻りは $this->bytestream に書き込んだ length 値。
			応答を 1 件返す処理に限定しているためそれ以外は truncate する。
			（簡易 DoH には不要なので割愛）
		*/
		$this->parsed['HEADERS']['NSCOUNT'] = 0;
		$this->parsed['HEADERS']['ARCOUNT'] = 0;
		$next = 0;
		$next += $this->_compose_header($next);
		$next += $this->_compose_section($next, self::QSECTION, $this->parsed['QD_RECS']);
		$next += $this->_compose_section($next, self::OSECTION, $this->parsed['AN_RECS']);
		$this->bytestream = substr($this->bytestream, 0, ($next - 1));
	}
	private function _compose_header($in_start) {
		$next = $in_start;
		foreach (self::HEADERS as $key => $mixed) {
			$ref = &$this->parsed['HEADERS'][$key];
			if ($key == 'FLAGS') {
				$flag = 0;
				$flag_bits = array_sum(self::HEADERS['FLAGS']);
				$start_bit = 0;
				foreach ($mixed as $flag_name => $bits) {
					$start_bit += $bits;
					$flag |= $ref[$flag_name] << ($flag_bits - $start_bit);
				}
				$this->_set($flag, $next, $flag_bits / 8);
				$next += $flag_bits / 8;
			} else {
				$this->_set($ref, $next, $mixed / 8);
				$next += $mixed / 8;
			}
		}
		return ($next - $in_start);
	}
	private function _compose_section($in_start, $in_format, $in_parsed) {
		$next = $in_start;
		for ($i = 0; $i < count($in_parsed); $i++) {
			foreach ($in_format as $key => $bits) {
				if ($bits > 0) {
					$this->_set($in_parsed[$i][$key], $next, $bits / 8);
					$next += $bits / 8;
					continue;
				}
				/*
					以下は可変長の項目。
					NAME と RES 以外が可変長になることは想定していないので、
					必要になったら実装。
				*/
				if ($key == 'NAME') {
					$next += $this->_compose_label($next, $in_parsed[$i][$key]);
					continue;
				}
				if ($key != 'RES') {
					$this->_debug();
				}
				switch ($in_parsed[$i]['TYPE']) {
				case self::RES['A'] :
					$next += $this->_compose_ip($next, $in_parsed[$i][$key]);
					break;
				case self::RES['NS'] :
				case self::RES['AAAA'] :
				case self::RES['MX'] :
				default :
					/*
						A 以外のリソースタイプの解析は、必要に応じて実装。
						（簡易 DoH には不要なので割愛）
					*/
					$this->_set(str_pad('', $in_parsed[$i]['RDLEN'], '*'), $next);
					break;
				}
				$next += $in_parsed[$i]['RDLEN'];
			}
		}
		return ($next - $in_start);
	}
	private function _compose_label($in_start, $in_label) {
		$next = $in_start;
		$temp = explode('.', $in_label);
		foreach ($temp as $label) {
			$this->_set(strlen($label), $next, 1);
			$next += 1;
			$this->_set($label, $next);
			$next += strlen($label);
		}
		$this->_set(self::LBL['TERM'], $next, 1);
		$next += 1;
		return ($next - $in_start);
	}
	private function _compose_ip($in_start, $in_ip) {
		$next = $in_start;
		$temp = explode('.', $in_ip);
		foreach ($temp as $num) {
			$this->_set($num, $next, 1);
			$next += 1;
		}
		return ($next - $in_start);
	}
	private function _get($in_start, $in_len, $in_unpack = FALSE) {
		$ret = substr($this->bytestream, $in_start, $in_len);
		if ($in_unpack) {
			return unpack(self::BigEndian[$in_len], $ret)[1];
		}
		return $ret;
	}
	private function _set($in_data, $in_start, $in_len = -1) {
		if ($in_len > 0) {
			$set = pack(self::BigEndian[$in_len], $in_data);
			$len = $in_len;
		} else {
			$set = $in_data;
			$len = strlen($in_data);
		}
		$this->bytestream = substr_replace($this->bytestream, $set, $in_start, $len);
	}
	private function _mask($in_bits) {
		return (2 ** $in_bits - 1);
	}
	private function _extract($in_dec, $in_start, $in_bits, $in_dec_bits = 16) {
		return (($in_dec << $in_start) & $this->_mask($in_dec_bits)) >> ($in_dec_bits - $in_bits);
	}
	function _debug() {
		header('Content-Type: text/plain');
		print_r($this->parsed);
		exit;
	}
}

/*

$TESTDATA = array(
	0x00, 0x00, 0x81, 0x80, 0x00, 0x01, 0x00, 0x01,
	0x00, 0x03, 0x00, 0x05, 0x03, 0x77, 0x77, 0x77,
	0x04, 0x6a, 0x70, 0x72, 0x73, 0x02, 0x63, 0x6f,
	0x02, 0x6a, 0x70, 0x00, 0x00, 0x01, 0x00, 0x01,
	0xc0, 0x0c, 0x00, 0x01, 0x00, 0x01, 0x00, 0x00,
	0x01, 0x2c, 0x00, 0x04, 0x75, 0x68, 0x85, 0xa7,
	0xc0, 0x10, 0x00, 0x02, 0x00, 0x01, 0x00, 0x01,
	0x21, 0x51, 0x00, 0x06, 0x03, 0x6e, 0x73, 0x32,
	0xc0, 0x10, 0xc0, 0x10, 0x00, 0x02, 0x00, 0x01,
	0x00, 0x01, 0x21, 0x51, 0x00, 0x06, 0x03, 0x6e,
	0x73, 0x31, 0xc0, 0x10, 0xc0, 0x10, 0x00, 0x02,
	0x00, 0x01, 0x00, 0x01, 0x21, 0x51, 0x00, 0x06,
	0x03, 0x6e, 0x73, 0x33, 0xc0, 0x10, 0xc0, 0x4e,
	0x00, 0x01, 0x00, 0x01, 0x00, 0x01, 0x49, 0xa3,
	0x00, 0x04, 0xca, 0x0b, 0x10, 0x31, 0xc0, 0x4e,
	0x00, 0x1c, 0x00, 0x01, 0x00, 0x01, 0x49, 0xa3,
	0x00, 0x10, 0x20, 0x01, 0x0d, 0xf0, 0x00, 0x08,
	0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
	0xa1, 0x53, 0xc0, 0x3c, 0x00, 0x01, 0x00, 0x01,
	0x00, 0x01, 0x21, 0x51, 0x00, 0x04, 0xca, 0x0b,
	0x10, 0x3b, 0xc0, 0x3c, 0x00, 0x1c, 0x00, 0x01,
	0x00, 0x01, 0x21, 0x51, 0x00, 0x10, 0x20, 0x01,
	0x0d, 0xf0, 0x00, 0x08, 0x00, 0x00, 0x00, 0x00,
	0x00, 0x00, 0x00, 0x00, 0xa2, 0x53, 0xc0, 0x60,
	0x00, 0x01, 0x00, 0x01, 0x00, 0x01, 0x21, 0x51,
	0x00, 0x04, 0x3d, 0xc8, 0x53, 0xcc
);

function createTestData($in_arr)
{
	$ret = '';
	for ($i = 0; $i < count($in_arr); $i++) {
		$ret .= pack('C', $in_arr[$i]);
	}
	return $ret;
}

$dns = new cDNSMessage();
$dns->setByteStream(createTestData($TESTDATA));
$dns->_debug();

*/

$rand = rand(0, 99);
if (array_key_exists('doh', $_COOKIE)) {
	$cookie = $_COOKIE['doh'];
} else {
	$cookie = '(none)';
}

$dns = new cDNSMessage();
$dns->setByteStream(file_get_contents('php://input'));
$query = $dns->getQuery();
$answer = '127.0.0.1';
$dns->setAnswer($answer);
$output = $dns->getByteStream();

$responseHeaders = array(
	"Content-Type: application/dns-message",
	"Content-Length: " . strlen($output),
	"Cache-Control: max-age=0",
	"X-Resolve: {$query} --> {$answer}",
	"X-Sent-Cookie: {$cookie}",
	"Connection: Close",
	"Set-Cookie: doh={$rand}; Secure; HttpOnly"
);

foreach ($responseHeaders as $header) {
	header($header);
}

print $output;

?>