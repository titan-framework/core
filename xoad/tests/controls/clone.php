<?php require_once('../../xoad.php') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:xoad="http://www.xoad.org/controls" xml:lang="en-US">
	<head>
		<title>XOAD Controls</title>
		<?= XOAD_Utilities::header('../..') . "\n" ?>
	</head>
	<body style="font: normal 0.7em tahoma, verdana, arial, serif; margin: 0; padding: 10px;">
		<span>Clone Me!</span>
		<hr />
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:mode="first">First</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:mode="last">Last</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:node="h3" xoad:mode="before">Before H3</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:node="h3" xoad:mode="after">After H3</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:node="h2, h4" xoad:mode="before">Before H2 and H4</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container" xoad:node="h2, h4" xoad:mode="after">After H2 and H4</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container h2" xoad:node="span" xoad:mode="replace">Replace H2</a> |
		<a xoad:action="clone" xoad:source="body > span" xoad:target="#container h2, #container h4" xoad:node="span" xoad:mode="replace">Replace H2 and H4</a>
		<hr />
		<div id="container">
			<h2><span>Header 2</span></h2>
			<p><span>Some text 2...</span></p>
			<h3><span>Header 3</span></h3>
			<p><span>Some text 3...</span></p>
			<h4><span>Header 4</span></h4>
			<p><span>Some text 4...</span></p>
		</div>
	</body>
</html>