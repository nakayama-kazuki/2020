<?php

function utilSupportActionQuery()
{
	/*
		its <query> component is replaced by query
		https://www.w3.org/TR/2011/WD-html5-20110525/association-of-controls-and-forms.html#submit-mutate-action
	*/
	print <<<EOC
<script>
window.addEventListener('load', () => {
	let forms = document.getElementsByTagName('FORM');
	for (let i = 0; i < forms.length; i++) {
		let form = forms.item(i);
		let parsed = new URL(form.action);
		if (!parsed.search) {
			continue;
		}
		let params = parsed.search.substr(1).split('&');
		params.forEach(param => {
			let name, value;
			[name, value] = param.split('=');
			let input = document.createElement('INPUT');
			input.type = 'hidden';
			input.name = name;
			input.value = decodeURIComponent(value);
			form.appendChild(input);
		});
	}
});
</script>
EOC;
}

function utilConvSeparator($in_s1, $in_s2, $in_value)
{
	$items = explode($in_s1, $in_value);
	$buff = array();
	foreach ($items as $item) {
		if (trim($item)) {
			array_push($buff, trim($item));
		}
	}
	return implode($in_s2, $buff);
}

class TestController
{
	private $handlerTable = array();
	private $commonParams = array();
	public const CTRLPARAM = 'fa292226d16deeb30e71ee8ed911f900';
	private $defaultHandler;
	function __construct() {
		$this->defaultHandler = function() {
			header('Content-Type: text/plain');
			print 'Call registerDefaultHandler() API.';
		};
	}
	public function registerDefaultHandler($in_handler) {
		$this->defaultHandler = $in_handler;
	}
	public function registerHandler($in_content_id, $in_handler) {
		$this->handlerTable[$in_content_id] = $in_handler;
	}
	public function registerCommonParam($in_name, $in_value) {
		$this->commonParams[$in_name] = $in_value;
	}
	public function createURL($in_content_id, $in_params = array()) {
		$buff = array();
		array_push($buff, self::CTRLPARAM . "={$in_content_id}");
		foreach ($in_params as $key => $value) {
			array_push($buff, "{$key}=" . rawurlencode($value));
		}
		return "https://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}?" . implode('&', $buff);
	}
	public function renderContent($in_params) {
		ob_start();
		if (array_key_exists(self::CTRLPARAM, $in_params)) {
			$contentId = $in_params[self::CTRLPARAM];
			if (array_key_exists($contentId, $this->handlerTable)) {
				call_user_func($this->handlerTable[$contentId], $this->commonParams, $in_params);
				return;
			}
		}
		call_user_func($this->defaultHandler, $this->commonParams);
		$fragment = explode('</form>', ob_get_clean(), 2);
		if (count($fragment) === 1) {
			print $fragment[0];
		} else {
			print $fragment[0];
			print '</form>' . "\n";
			utilSupportActionQuery();
			print $fragment[1];
		}
		ob_end_flush();
	}
}

$ctrl = new TestController();

$ctrl->registerCommonParam('CT_LOGFILE', (pathinfo($_SERVER['SCRIPT_NAME']))['filename'] . '.log');
$ctrl->registerCommonParam('CT_DOMAIN1', $_SERVER['HTTP_HOST']);
$ctrl->registerCommonParam('CT_DOMAIN2', ($_SERVER['HTTP_HOST'] === '127.0.0.1' ? 'localhost' : '127.0.0.1'));

$ctrl->registerDefaultHandler(function($in_common_params) {
	global $ctrl;
	if (file_exists($in_common_params['CT_LOGFILE'])) {
		unlink($in_common_params['CT_LOGFILE']);
	}
	$action = $ctrl->createURL('CT_ID_PARENT');
	print <<<EOC
<style type='text/css'>
TEXTAREA {
	border: solid 1px gray;
	width: 800px;
	height: 100px;
}
UL {
	list-style: none;
}
</style>
<form action='{$action}' method='get' enctype='application/x-www-form-urlencoded'>
<ul>
<li>
	<div>HEADER</div>
	<ul>
		<li>
			<div>Content-Security-Policy: script-src</div>
			<textarea name='CT_HEADER'></textarea>
		</li>
	</ul>
</li>
<li>
	<div>BODY ({$in_common_params['CT_DOMAIN1']})</div>
	<ul>
		<li>
			<div>SCRIPTS</div>
			<textarea name='CT_SCRIPT_IN_PARENT'></textarea>
		</li>
		<li>
			<div>IFRAME ({$in_common_params['CT_DOMAIN2']})</div>
			<ul>
				<li>
					<div>SCRIPTS</div>
					<textarea name='CT_SCRIPT_IN_IFRAME'></textarea>
				</li>
			</ul>
		</li>
	</ul>
</li>
</ul>
<div><button type='submit'>test</button></div>
</form>
<script>

const cDefaultTestData = [
	{
		subRes : 'https://tpc.googlesyndication.com/sodar/sodar2.js',
		allowed : true
	},
	{
		subRes : 'https://pagead2.googlesyndication.com/bg/36t2pzUCsky2p8StOfRDuZ2SQrRQGkwFUvrIpVyovYo.js',
		allowed : true
	},
	{
		subRes : 'https://b92.yahoo.co.jp/js/s_retargeting.js',
		allowed : false
	}
];

let appendText = (in_name, in_text) => {
	let nodelist = document.getElementsByName(in_name);
	for (i = 0; i < nodelist.length; i++) {
		nodelist.item(i).value += in_text + "\\n";
	}
};

cDefaultTestData.forEach(obj => {
	if (obj.allowed) {
		const parsed = new URL(obj.subRes);
		appendText('CT_HEADER', parsed.origin);
	}
	appendText('CT_SCRIPT_IN_PARENT', obj.subRes);
	appendText('CT_SCRIPT_IN_IFRAME', obj.subRes);
});

</script>
EOC;
});

function crlf2space($in_value)
{
	return utilConvSeparator("\n", " ", $in_value);
}

$ctrl->registerHandler('CT_ID_PARENT', function($in_common_params, $in_params) {
	global $ctrl;
	$src = crlf2space($in_params['CT_HEADER']);
	$report = $ctrl->createURL('CT_ID_REPORT');
	$iframe = $ctrl->createURL('CT_ID_IFRAME', array('CT_SCRIPT_IN_IFRAME' => $in_params['CT_SCRIPT_IN_IFRAME']));
	$iframe = str_replace($in_common_params['CT_DOMAIN1'], $in_common_params['CT_DOMAIN2'], $iframe);
	header("Content-Security-Policy-Report-Only: script-src 'unsafe-inline' {$src}; report-uri {$report}");
	$scripts = explode("\n", $in_params['CT_SCRIPT_IN_PARENT']);
	foreach ($scripts as $script) {
		if (trim($script)) {
			print "<script src='{$script}'></script>\n";
		}
	}
	print <<<EOC
<style type='text/css'>
IFRAME, .disp {
	border: solid 1px gray;
	width: 90%;
	height: 300px;
}
</style>
<div>
<iframe src='{$iframe}'></iframe>
</div>
<div>
report :<br />
<textarea readonly class='disp'></textarea>
</div>
<script>

let wait_report_uri_ms = 500;
window.setTimeout(function() {
	fetch('{$in_common_params['CT_LOGFILE']}')
		.then(res => res.ok ? res.text() : Promise.reject(new Error('404')))
		.then(txt => document.getElementsByClassName('disp').item(0).value = txt)
		.catch(err => console.log(err));
}, wait_report_uri_ms);

</script>
EOC;
});

$ctrl->registerHandler('CT_ID_REPORT', function($in_common_params, $in_params) {
	define('APPEND', 'a');
	$fp = fopen($in_common_params['CT_LOGFILE'], APPEND);
	fwrite($fp, file_get_contents('php://input'));
	fwrite($fp, "\n");
	fclose($fp);
});

$ctrl->registerHandler('CT_ID_IFRAME', function($in_common_params, $in_params) {
	$scripts = explode("\n", $in_params['CT_SCRIPT_IN_IFRAME']);
	print "<div>scripts in iframe ... \n";
	foreach ($scripts as $script) {
		if (trim($script)) {
			print "<div>{$script}</div>\n";
			print "<script src='{$script}'></script>\n";
		}
	}
	print <<<EOC
EOC;
});

$ctrl->renderContent($_GET);

?>
