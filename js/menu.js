var changeMenuSpeed = 10;

function backMenu (idCurrent, idNext)
{
	var obj  = document.getElementById('menuMain_' + idNext);
	var objPrevious = document.getElementById('menuMain_' + idCurrent);

	var left = obj.style.left.replace('px','')/1;
	var leftPrevious = objPrevious.style.left.replace('px','')/1;

	left += changeMenuSpeed;
	leftPrevious += changeMenuSpeed;

	if(left > 0)
	{
		left = 0;
		leftPrevious = 260;
	}

	objPrevious.style.left = leftPrevious + 'px';
	obj.style.left = left + 'px';


	if(left < 0)
		setTimeout('backMenu (\'' + idCurrent + '\',\'' + idNext + '\')', 5);
}

function slideMenu (idCurrent, idNext, menuSpeed)
{
	if (!menuSpeed)
		menuSpeed = changeMenuSpeed;

	var obj  = document.getElementById('menuMain_' + idNext);
	var objPrevious = document.getElementById('menuMain_' + idCurrent);

	obj.style.display = 'block';

	var left = obj.style.left.replace('px','')/1;
	var leftPrevious = objPrevious.style.left.replace('px','')/1;

	left -= menuSpeed;
	leftPrevious -= menuSpeed;

	if(left < 0)
	{
		left = 0;
		leftPrevious = -260;
	}

	obj.style.left = left + 'px';
	objPrevious.style.left = leftPrevious + 'px';

	if(left > 0)
		setTimeout('slideMenu (\'' + idCurrent + '\',\'' + idNext + '\',\'' + menuSpeed + '\')', 5);
}

function showMenu (obj, bottom)
{
	var menu = document.getElementById ('menuBox');

	modalMsg.close ();

	if (window.Modalbox && Modalbox.initialized)
		Modalbox.hide ();

	if (menu.style.left == '-260px')
		menu.style.left = '0px';
	else
		menu.style.left = '-260px';
	/*
	{
		if (bottom == 1)
		{
			var ns = (navigator.appName.indexOf("Netscape") != -1);
			var aux = ns ? pageYOffset + innerHeight : document.body.scrollTop + document.body.clientHeight;

			menu.style.left = (obj.offsetLeft - 90) + 'px';

			menu.style.top = (aux - menuHeight - 58) + 'px';
		}
		else
		{
			menu.style.left = (obj.offsetLeft - 90) + 'px';
			menu.style.top = (obj.offsetTop - 4) + 'px';
		}

		menu.style.display = 'block';
	}
	*/

	return true;
}
