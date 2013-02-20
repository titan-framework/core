xoad.html = {};
xoad.html.onCallCompleted = function(response) {
if (typeof(response.html) == 'string') {
if (response.html.length > 0) {
try {
eval(response.html);
} catch (e) {};
}
}
};
xoad.html.exportForm = function(id) {
var form = document.getElementById(id);
if (form == null) {
return null;
}
if (typeof(form.elements) == 'undefined') {
return null;
}
var formData = {};
for (var iterator = 0; iterator < form.elements.length; iterator ++) {
var element = form.elements[iterator];
if (element.disabled) {
continue;
}
var elementType = element.tagName.toLowerCase();
var elementName = null;
var elementValue = null;
if (
(typeof(element.name) != 'undefined') &&
(element.name.length > 0)) {
elementName = element.name;
} else if (
(typeof(element.id) != 'undefined') &&
(element.id.length > 0)) {
elementName = element.id;
}
if (elementName != null) {
if (elementType == 'input') {
if (
(element.type == 'text') ||
(element.type == 'password') ||
(element.type == 'button') ||
(element.type == 'submit') ||
(element.type == 'hidden')) {
elementValue = element.value;
} else if (element.type == 'checkbox') {
elementValue = element.checked;
} else if (element.type == 'radio') {
if (element.checked) {
elementValue = element.value;
} else {
try {
var type = eval('typeof(formData.' + elementName + ')');
if (type != 'undefined') {
continue;
}
} catch (e) {
continue;
}
}
}
} else if (elementType == 'select') {
if (element.options.length > 0) {
if (element.multiple) {
elementName = elementName.replace(/\[\]$/ig, '');
elementValue = [];
for (var optionsIterator = 0; optionsIterator < element.options.length; optionsIterator ++) {
if (element.options[optionsIterator].selected) {
elementValue.push(element.options[optionsIterator].value);
}
}
} else {
if (element.selectedIndex >= 0) {
elementValue = element.options[element.selectedIndex].value;
}
}
}
} else if (elementType == 'textarea') {
elementValue = element.value;
}
try {
eval('formData.' + elementName + ' = elementValue;');
//eval('alert(formData.' + elementName + ');');
} catch (e) {}
}
}
return formData;
};
xoad.html.importForm = function(id, formData) {
var form = document.getElementById(id);
if (
(formData == null) ||
(form == null)) {
return false;
}
if (typeof(form.elements) == 'undefined') {
return false;
}
for (var iterator = 0; iterator < form.elements.length; iterator ++) {
var element = form.elements[iterator];
if (element.disabled) {
continue;
}
var elementType = element.tagName.toLowerCase();
var elementName = null;
if (
(typeof(element.name) != 'undefined') &&
(element.name.length > 0)) {
elementName = element.name;
} else if (
(typeof(element.id) != 'undefined') &&
(element.id.length > 0)) {
elementName = element.id;
}
if (elementName != null) {
if (elementType == 'select') {
if (element.multiple) {
elementName = elementName.replace(/\[\]$/ig, '');
}
}
var elementValue = null;
try {
var valueType = eval('typeof(formData.' + elementName + ')');
if (valueType != 'undefined') {
elementValue = eval('formData.' + elementName);
} else {
continue;
}
} catch (e) {
continue;
}
if (elementType == 'input') {
if (
(element.type == 'text') ||
(element.type == 'password') ||
(element.type == 'button') ||
(element.type == 'submit') ||
(element.type == 'hidden')) {
element.value = elementValue;
} else if (element.type == 'checkbox') {
element.checked = elementValue;
} else if (element.type == 'radio') {
if (element.value == elementValue) {
element.checked = true;
} else {
element.checked = false;
}
}
} else if (elementType == 'select') {
if (element.options.length > 0) {
if (element.multiple) {
element.selectedIndex = -1;
} else {
elementValue = [elementValue];
element.selectedIndex = 0;
}
for (var valuesIterator = 0; valuesIterator < elementValue.length; valuesIterator ++) {
for (var optionsIterator = 0; optionsIterator < element.options.length; optionsIterator ++) {
if (element.options[optionsIterator].value == elementValue[valuesIterator]) {
element.options[optionsIterator].selected = true;
}
}
}
}
} else if (elementType == 'textarea') {
element.value = elementValue;
}
}
}
return true;
};
xoad.addObserver(xoad.html);