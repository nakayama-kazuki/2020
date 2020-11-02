<?php

function magic($in_no, $in_digit = 5)
{
	return substr(md5($in_no), 0, $in_digit);
}

$C_TEST = magic(0);
$C_REPORT = magic(1);
$C_NONCE = magic(2);
$C_FIELD1 = magic(3);
$C_FIELD2 = magic(4);
$C_SCRIPT = $_SERVER['SCRIPT_NAME'];
$C_LOGFILE = (pathinfo($C_SCRIPT))['filename'] . '.log';
$C_URL_INIT = $C_SCRIPT;
$C_URL_TEST = "{$C_SCRIPT}?{$C_TEST}";
$C_URL_REPORT = "{$C_SCRIPT}?{$C_REPORT}";
$C_URL_LOGFILE = (pathinfo($C_SCRIPT))['dirname'] . "/{$C_LOGFILE}";

function textarea_explode($in_post)
{
	$items = explode("\n", $in_post);
	$ret = array();
	foreach ($items as $item) {
		if (!trim($item)) {
			continue;
		}
		array_push($ret, trim($item));
	}
	return $ret;
}

switch ($_SERVER['QUERY_STRING']) {

/*
	---------------------------------------------------------------
	Test Application for Content-Security-Policy-Report-Only Header
	---------------------------------------------------------------
*/

case $C_TEST :
	$src = implode(' ', textarea_explode($_POST[$C_FIELD1]));
	header("Content-Security-Policy-Report-Only: script-src 'nonce-{$C_NONCE}' {$src}; report-uri {$C_URL_REPORT}");
	$js_literal_array = '["' . implode('", "', textarea_explode($_POST[$C_FIELD2])) . '"]';
	print <<<EOTEST
<style type='text/css'>
.disp {
	border: solid 1px gray;
	width: 90%;
	height: 300px;
}
</style>
<script nonce='{$C_NONCE}'>
let dst = document.getElementsByTagName('SCRIPT').item(0);
let appendlist = {$js_literal_array};
for (let i = 0; i < appendlist.length; i++) {
	let s = document.createElement('SCRIPT');
	s.src = appendlist[i];
	dst.parentNode.appendChild(s);
}
</script>
<div>report :</div>
<textarea readonly class='disp'></textarea>
<br />
<a href='{$C_URL_INIT}'>back</a>
<script nonce='{$C_NONCE}'>
let wait_report_uri_ms = 500;
window.setTimeout(function() {
	fetch('{$C_URL_LOGFILE}')
		.then(res => res.ok ? res.text() : Promise.reject(new Error('404')))
		.then(txt => document.getElementsByClassName('disp').item(0).value = txt)
		.catch(err => console.log(err));
}, wait_report_uri_ms);
</script>
EOTEST;
	break;

/*
	--------------------
	POST to "report-uri"
	--------------------
*/

case $C_REPORT :
	define('APPEND', 'a');
	$fp = fopen($C_LOGFILE, APPEND);
	fwrite($fp, file_get_contents('php://input'));
	fwrite($fp, "\n");
	fclose($fp);
	break;

/*
	-------------------
	Init (Test Setting)
	-------------------
*/

default :
	if (file_exists($C_LOGFILE)) {
		unlink($C_LOGFILE);
	}
	print <<<EOSETTING
<style type='text/css'>
TEXTAREA {
	border: solid 1px gray;
	width: 90%;
	height: 100px;
}
</style>
<form action='{$C_URL_TEST}' method='post' enctype='application/x-www-form-urlencoded'>
<div>script-src :</div>
<textarea name='{$C_FIELD1}'>
https://tpc.googlesyndication.com/
https://pagead2.googlesyndication.com/
</textarea>
<br />
<br />
<div>resource (javascript) :</div>
<textarea name='{$C_FIELD2}'>
https://tpc.googlesyndication.com/sodar/sodar2.js
https://pagead2.googlesyndication.com/bg/36t2pzUCsky2p8StOfRDuZ2SQrRQGkwFUvrIpVyovYo.js
</textarea>
<br />
<br />
<button type='submit'>test</button>
</form>
EOSETTING;
	break;
}

?>
