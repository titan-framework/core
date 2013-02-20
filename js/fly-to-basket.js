/************************************************************************************************************
(C) www.dhtmlgoodies.com, March 2006

This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	

Terms of use:
You are free to use this script as long as the copyright message is kept intact. However, you may not
redistribute, sell or repost it without our permission.

Version:
	1.0	Released	March. 3rd 2006

Thank you!

www.dhtmlgoodies.com
Alf Magne Kalleland

************************************************************************************************************/

var flyingSpeed = 25;

var shopping_cart_div = false;
var flyingDiv = false;
var currentProductDiv = false;

var shopping_cart_x = false;
var shopping_cart_y = false;

var slide_xFactor = false;
var slide_yFactor = false;

var diffX = false;
var diffY = false;

var currentXPos = false;
var currentYPos = false;

var ajaxObjects = new Array();

var countProducts = 0;

function shoppingCart_getTopPos (inputObj)
{		
	var returnValue = inputObj.offsetTop;
	
	while((inputObj = inputObj.offsetParent) != null)
		if(inputObj.tagName!='HTML')returnValue += inputObj.offsetTop;
		
	return returnValue;
}

function shoppingCart_getLeftPos (inputObj)
{
	var returnValue = inputObj.offsetLeft;
	
	while((inputObj = inputObj.offsetParent) != null)
		if(inputObj.tagName!='HTML')returnValue += inputObj.offsetLeft;
	
	return returnValue;
}
	

function addToBasket (fieldId, docId)
{
	if(!shopping_cart_div)
		shopping_cart_div = document.getElementById('tiedFiles');
	
	if(!flyingDiv)
	{
		flyingDiv = document.createElement('DIV');
		flyingDiv.style.position = 'absolute';
		document.body.appendChild(flyingDiv);
	}
	
	if (!shopping_cart_x)
		shopping_cart_x = shoppingCart_getLeftPos (shopping_cart_div);
	
	if (!shopping_cart_y)
		shopping_cart_y = shoppingCart_getTopPos (shopping_cart_div);

	currentProductDiv = document.getElementById (fieldId + '_selected');
	
	productId = document.getElementById(fieldId + '_real_id').value;
	
	if (currentProductDiv.style.display != '' || productId == 0 || docId == 0)
		return false;
	
	showWait ();
	
	currentXPos = shoppingCart_getLeftPos(currentProductDiv);
	currentYPos = shoppingCart_getTopPos(currentProductDiv);
	
	diffX = shopping_cart_x - currentXPos;
	diffY = (shopping_cart_y + (126 * countProducts)) - currentYPos;
	
	countProducts++;
	
	var shoppingContentCopy = currentProductDiv.cloneNode(true);
	shoppingContentCopy.id='';
	flyingDiv.innerHTML = '';
	flyingDiv.style.left = currentXPos + 'px';
	flyingDiv.style.top = currentYPos + 'px';
	flyingDiv.appendChild(shoppingContentCopy);
	flyingDiv.style.display='';
	flyingDiv.style.width = currentProductDiv.offsetWidth + 'px';
	
	document.getElementById (fieldId + '_real_id').value = 0;
	currentProductDiv.style.display = 'none';
	document.getElementById (fieldId).value = '';
	
	flyToBasket (productId, docId);
}


function flyToBasket(productId, docId)
{
	var maxDiff = Math.max(Math.abs(diffX),Math.abs(diffY));
	
	var moveX = (diffX / maxDiff) * flyingSpeed;;
	var moveY = (diffY / maxDiff) * flyingSpeed;	
	
	currentXPos = currentXPos + moveX;
	currentYPos = currentYPos + moveY;
	
	flyingDiv.style.left = Math.round(currentXPos) + 'px';
	flyingDiv.style.top = Math.round(currentYPos) + 'px';	
	
	if(moveX>0 && currentXPos > shopping_cart_x)
	{
		document.body.removeChild(flyingDiv);
		flyingDiv = false;
	}
	if(moveX<0 && currentXPos < shopping_cart_x)
	{
		document.body.removeChild(flyingDiv);
		flyingDiv = false;
	}
	
	if(flyingDiv)
		setTimeout('flyToBasket("' + productId + '", ' + docId + ')',10);
	else
		ajaxAddProduct (productId, docId);	
}

function showAjaxBasketContent (productId, docId)
{
	var itemBox = document.getElementById('tiedFiles');
	
	var src = '<div id="content_basket_' + productId + '" style="display:; position: relative; width: 300px; height: 124px; border: #CCCCCC 1px solid; margin-top: 2px; background-color: #FFFFFF;">' + tAjax.getFileResume (productId) + '<div style="position: absolute; top: 106px; width: 294px; height: 12px; background-color: #CCCCCC; text-align: right; padding: 3px;"><a href="#" onclick="JavaScript: ajaxRemoveProduct (' + productId + ', ' + docId + '); return false;" style="color: #FFFFFF;">Remover</a></div></div>';
	
	itemBox.innerHTML = itemBox.innerHTML + src;
	
	countProducts--;
	
	ajax.delay (function () { hideWait (); });
}

function ajaxRemoveProduct(productId, docId)
{
	showWait ();
	
	if (!ajax.removeFile (productId, docId))
	{
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	ajax.loadFiles (docId);
	
	ajax.delay (function () { hideWait (); });
}

function ajaxAddProduct (productId, docId)
{
	if (!ajax.addFile (productId, docId))
	{
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	showAjaxBasketContent (productId, docId);
}