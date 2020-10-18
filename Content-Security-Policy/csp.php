<?php

function magic($in_no, $in_digit = 5)
{
	return substr(md5($in_no), 0, $in_digit);
}

$C_TEST = magic(0);
$C_REPORT = magic(1);
$C_NONCE = magic(2);
$C_FNAME1 = magic(3);
$C_FNAME2 = magic(4);
$C_LOGFILE = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1) . '.log';
$C_URL_SETTING = $_SERVER['SCRIPT_NAME'];
$C_URL_TEST = "{$_SERVER['SCRIPT_NAME']}?{$C_TEST}";
$C_URL_REPORT = "{$_SERVER['SCRIPT_NAME']}?{$C_REPORT}";

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
	$src = implode(' ', textarea_explode($_POST[$C_FNAME1]));
	header("Content-Security-Policy-Report-Only: script-src 'nonce-{$C_NONCE}' {$src}; report-uri {$C_URL_REPORT}");
	$array = '["' . implode('", "', textarea_explode($_POST[$C_FNAME2])) . '"]';
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
let appendlist = {$array};
for (let i = 0; i < appendlist.length; i++) {
	let s = document.createElement('SCRIPT');
	s.src = appendlist[i];
	dst.parentNode.appendChild(s);
}
</script>
<div>report :</div>
<textarea readonly class='disp'></textarea>
<br />
<a href='{$C_URL_SETTING}'>back</a>
<script nonce='{$C_NONCE}'>
let wait_report_uri_ms = 500;
window.setTimeout(function() {
	fetch('{$C_LOGFILE}')
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
	------------
	Test Setting
	------------
*/

default :
	if (file_exists($C_LOGFILE)) {
		unlink($C_LOGFILE);
	}
	print <<<EOSETTING
<style type='text/css'>
TEXTAREA {
	border: solid 1px gray;
	width: 50%;
	height: 90px;
}
</style>
<form action='{$C_URL_TEST}' method='post' enctype='application/x-www-form-urlencoded'>
<div>script-src :</div>
<textarea name='{$C_FNAME1}'>
https://yimg.jp/
https://yahoo.jp/
</textarea>
<br />
<br />
<div>resource (javascript) :</div>
<textarea name='{$C_FNAME2}'>
https://b92.yahoo.co.jp/js/s_retargeting.js
https://s.yimg.jp/images/advertising/common/js/iicon.min.js
</textarea>
<br />
<br />
<button type='submit'>test</button>
</form>
EOSETTING;
	break;
}

?>
