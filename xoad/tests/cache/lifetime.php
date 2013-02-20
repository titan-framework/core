<?php

class Class1
{
	function invoke()
	{
		sleep(2);
	}
}

class Class2
{
	function invoke()
	{
		sleep(2);
	}
}

require_once('../../xoad.php');

XOAD_Cache::allowCaching(null, null, 10);

if (XOAD_Server::runServer()) {

	exit;
}

?>
<?= XOAD_Utilities::header('../..') . "\n" ?>
<script type="text/javascript">

var class1 = <?= XOAD_Client::register(new Class1()) . "\n" ?>;
var class2 = <?= XOAD_Client::register(new Class2()) . "\n" ?>;

class1.invoke(function() {

	alert('Class1.invoke...');

	class2.invoke(function() {

		alert('Class2.invoke...');
	});
});

</script>