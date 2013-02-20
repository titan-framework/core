<?php

class Timeout
{
	function Invoke()
	{
		sleep(5);

		return 'OK';
	}

	function Invoke2()
	{
		sleep(5);

		return 'OK';
	}
}

define('XOAD_AUTOHANDLE', true);

require_once('../xoad.php');

?>
<?= XOAD_Utilities::header('..') . "\n" ?>
<script type="text/javascript">

var obj = <?= XOAD_Client::register(new Timeout()) ?>;

function handleError(error) {

	if (error.code == XOAD_ERROR_TIMEOUT) {

		alert('Global: Timeout.');
	}
};

xoad.setErrorHandler(handleError);

obj.onInvokeError = function(error) {

	if (error.code == XOAD_ERROR_TIMEOUT) {

		alert('Method: Timeout.');
	}

	return true;
};

obj.setTimeout(2000);
obj.invoke(xoad.asyncCall);

obj.setTimeout(4000);
obj.invoke2(xoad.asyncCall);

</script>