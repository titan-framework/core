function bissext (year)
{
    global.Date.bissext (year);
}

function dateValidate (id)
{
	global.Date.validate (id);
}

function alterTime (id)
{
	global.Time.alter (id);
}

function formatInteger (campo, e)
{
	global.Integer.format (campo, e);
}

function formatAmount (campo, e)
{
	global.Amount.format (campo, e);
}

function formatFloat (campo, e)
{
	global.Float.format (campo, e);
}

function formatCpf (campo, e)
{
	global.Cpf.format (campo, e);
}

function formatRga (campo, e)
{
	global.Rga.format (campo, e);
}

function formatCnpj (campo, e)
{	
	global.Cnpj.format (campo, e);
}

function formatCep (campo, e)
{
	global.Cep.format (campo, e);
}

function loadCity (cityId, stateId)
{
	global.City.load (cityId, stateId);
}

function alterCheckbox (id)
{
	global.Boolean.alter (id);
}

function passwordValidate (assign)
{
	global.Password.validate (assign);
}

function showCalendar (id)
{
	global.Date.showCalendar (id);
}

function loadFile (fileId, fieldId)
{
	global.File.load (fileId, fieldId);
}

function enableUnsetFile (fieldId)
{
	global.File.enableUnset (fieldId);
}

function disableUnsetFile (fieldId)
{
	global.File.disableUnset (fieldId);
}

function unsetFile (fieldId, hiddenLabel)
{
	global.File.unset (fieldId, hiddenLabel);
}

function uploadFile (fieldId)
{
	global.File.upload (fieldId);
}

function getUploadFilter (fieldId)
{
	global.File.getFilter (fieldId);
}

function addUploadFilter (fieldId, mimes)
{
	global.File.addFilter (fieldId);
}

function showCollectionCreate (fieldId, fatherId)
{
	global.Collection.create (fieldId, fatherId);
}

function saveCollection (fatherId, fatherColumn, fieldId, file)
{
	global.Collection.save (fatherId, fatherColumn, fieldId, file);
}

function addRowCollection (itemId, fieldId, file)
{
	global.Collection.addRow (itemId, fieldId, file);
}

function deleteCollection (fieldId, file, itemId)
{
	global.Collection.delRow (fieldId, file, itemId);
}

function selectSearch (fieldId)
{
	global.Select.showSearch (fieldId);
}

function selectChoose (fieldId, itemId, text)
{
	global.Select.choose (fieldId, itemId, text);
}

function clearSearch (fieldId)
{
	global.Select.clear (fieldId);
}