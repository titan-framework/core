<script language="javascript" type="text/javascript">
'global.City'.namespace ();

global.City.ajax = <?= class_exists ('xCity', FALSE) ? XOAD_Client::register (new xCity) : 'null' ?>;

global.City.load = function (cityId, stateId)
{
	var str, city, state;
	
	state = $(stateId)[$(stateId).selectedIndex].value;
	
	cityId = 'field_' + cityId;
	
	city = $(cityId);
	
	city.options.length = 0;
	
	global.City.add (city, 'Aguarde...', '', false, city.length);
	
	str = global.City.ajax.loadCity (state);
	
	eval (str);
	
	city.focus ();
	
	city.options.length = 0;
	
	global.City.add (city, 'Selecione', '', false, city.length);
	
	for (var i = 0 ; i < cityIds.length ; i++)
 		global.City.add (city, cityNames[i], cityIds[i], false, city.length);
}

global.City.add = function (obj, strText, strValue, blSel, intPos)
{
	var newOpt, i, ArTemp, selIndex;
	 
	selIndex = (blSel) ? intPos : obj.selectedIndex; 
	
	newOpt = new Option (strText, strValue);
	 
	Len = obj.options.length + 1;
	
	if (intPos > Len)
		return;
	
	obj.options.length = Len;
	
	if (intPos != Len) 
	{ 
		ArTemp = new Array();
		 
		for(i = intPos ; i < obj.options.length - 1 ; i++) 
			ArTemp [i] = Array (obj.options[i].text, obj.options[i].value);
		 
		for(i = intPos + 1 ; i < Len ; i++) 
			obj.options [i] = new Option (ArTemp[i-1][0], ArTemp[i-1][1]); 
	} 
	
	obj.options[intPos] = newOpt;
	 
	if (selIndex > intPos) 
		obj.selectedIndex = selIndex + 1; 
	else 
		if (selIndex == intPos)
			obj.selectedIndex = intPos; 
}
</script>