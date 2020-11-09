<?php

function magic($in_no, $in_digit = 5)
{
	return substr(md5($in_no), 0, $in_digit);
}

$C_MODE = magic(0);
$C_MODE_INIT = magic(1);
$C_MODE_TEST = magic(2);
$C_MODE_INCL = magic(3);
$C_SELF = $_SERVER['SCRIPT_NAME'];
$C_URL_INIT = "{$C_SELF}?{$C_MODE}={$C_MODE_INIT}";
$C_URL_TEST = "{$C_SELF}?{$C_MODE}={$C_MODE_TEST}";
$C_URL_INCL = "{$C_SELF}?{$C_MODE}={$C_MODE_INCL}";
$C_ELEM = magic(4);
$C_INCL = magic(5);

define('C_RAND', rand(0, 99));
define('C_SIZE', 30);

$elem_spec = array(
	'IMG' => array(
		'attr' => 'src',
		'handle' => array('gif', 'svg'),
		'close' => FALSE
	),
	'SCRIPT' => array(
		'attr' => 'src',
		'handle' => array('javascript'),
		'close' => TRUE
	),
	'IFRAME' => array(
		'attr' => 'src',
		'handle' => array('gif', 'svg', 'html'),
		'close' => TRUE
	),
	'OBJECT' => array(
		'attr' => 'data',
		'handle' => array('gif', 'svg', 'html'),
		'close' => TRUE
	)
);

function create_gif()
{
	list($rand, $size) = array(C_RAND, C_SIZE);
	$im = imagecreatetruecolor($size, $size);
	imagefilledrectangle($im, 0, 0, ($size - 1), ($size - 1), 0xFFFF00);
	$font = 5;
	imagestring($im, $font, 7, 7, $rand, 0x000000);
	ob_start();
	imagegif($im);
	imagedestroy($im);
	return ob_get_clean();
}

function create_svg()
{
	list($rand, $size) = array(C_RAND, C_SIZE);
	return <<<EOSVG
<svg xmlns='http://www.w3.org/2000/svg' width='{$size}' height='{$size}' viewBox='0 0 {$size} {$size}'>
<text x='0' y='{$size}' font-family='monospace' font-size='{$size}'>{$rand}</text>
</svg>
EOSVG;
}

function create_javascript()
{
	list($rand, $size) = array(C_RAND, C_SIZE);
	return <<<EOJS
alert($rand);
console.log($rand);
EOJS;
}

function create_html()
{
	list($rand, $size) = array(C_RAND, C_SIZE);
	return <<<EOHTML
<div>{$rand}</div>
EOHTML;
}

$incl_spec = array(
	'gif' => array(
		'type' => 'image/gif',
		'data' => create_gif()
	),
	'svg' => array(
		'type' => 'image/svg+xml',
		'data' => create_svg()
	),
	'javascript' => array(
		'type' => 'text/javascript',
		'data' => create_javascript()
	),
	'html' => array(
		'type' => 'text/html',
		'data' => create_html()
	)
);

define('MODE', (array_key_exists($C_MODE, $_GET) ? $_GET[$C_MODE] : $C_MODE_INIT));

switch (MODE) {

/*
	----
	Test
	----
*/

case $C_MODE_TEST :
	$elem = $_GET[$C_ELEM];
	$spec = $elem_spec[$elem];
	$incl = $_GET[$C_INCL];
	if ($spec['close']) {
		$ml = "<{$elem} {$spec['attr']}='{$C_URL_INCL}&{$C_INCL}={$incl}'></{$elem}>";
	} else {
		$ml = "<{$elem} {$spec['attr']}='{$C_URL_INCL}&{$C_INCL}={$incl}' />";
	}
	header('Content-Type: text/html');
	print <<<EOTEST
<body>
<div><a href='{$C_URL_INIT}'>[back]</a></div>
<div>move element after 2000msec</div>
{$ml}
<script>
window.setTimeout(function() {
	console.log('timer-eveint');
	let e = document.getElementsByTagName('{$elem}').item(0);
	document.body.appendChild(e.parentNode.removeChild(e));
}, 2000);
</script>
</body>
EOTEST;
	break;

/*
	-----------------
	Request Inclusion
	-----------------
*/

case $C_MODE_INCL :
	$incl = $_GET[$C_INCL];
	$spec = $incl_spec[$incl];
	header("Content-Type: {$spec['type']}");
	print $spec['data'];
	break;

/*
	--------------
	Init (Setting)
	--------------
*/

case $C_MODE_INIT :
default :
	header('Content-Type: text/html');
	print <<<EOCSS
<style type='text/css'>
TABLE {
	border-collapse: collapse;
}
TD {
	padding: 5px;
	border: solid 1px black;
}
.cTH {
	color: white;
	background-color: gray;
}
</style>
EOCSS;
	print "<table>\n";
	foreach ($elem_spec as $elem => $espec) {
		print "<tr>";
		print "\t<td class='cTH'>{$elem}</td>\n";
		foreach ($incl_spec as $incl => $ispec) {
			if (in_array($incl, $espec['handle'])) {
				print "\t<td><a href='{$C_URL_TEST}&{$C_ELEM}={$elem}&{$C_INCL}={$incl}'>{$incl}</a></td>\n";
			} else {
				print "\t<td>(n/a)</td>\n";
			}
		}
		print "</tr>\n";
	}
	print "</table>\n";
	break;
}

?>
