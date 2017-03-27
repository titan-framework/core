
function backMenu (current, next)
{
	$('menuMain_' + current).up (1).scrollTop = 0;
	$('menuMain_' + current).style.left = '260px';
	$('menuMain_' + next).style.left = '0px';
}

function slideMenu (current, next)
{
	$('menuMain_' + current).up (1).scrollTop = 0;
	$('menuMain_' + current).style.left = '-260px';
	$('menuMain_' + next).style.left = '0px';
}

function showMenu ()
{
	var menu = $('menuBox');

	modalMsg.close ();

	if (window.Modalbox && Modalbox.initialized)
		Modalbox.hide ();

	if (menu.style.left == '-260px')
		menu.style.left = '0px';
	else
		menu.style.left = '-260px';

	return true;
}
