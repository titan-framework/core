xoad.controls = {};
xoad.controls.list = [];
xoad.controls.parsedControls = [];
xoad.controls.parseInterval = 1000;
xoad.controls.pageLoaded = false;
xoad.controls.observers = [];
xoad.controls.getAttribute = function(element, attribute, defaultValue)
{
var attributeValue = null;
try {
attributeValue = element.getAttribute(attribute);
} catch (e) {}
if (
((attributeValue == null) || (attributeValue.length < 1)) &&
(typeof(defaultValue) != 'undefined')) {
attributeValue = defaultValue;
}
return attributeValue;
};
xoad.controls.getAttributeNS = function(element, attribute, namespace, defaultValue)
{
var attributeValue = null;
try {
attributeValue = element.getAttribute(attribute);
} catch (e) {}
if (
((attributeValue == null) || (attributeValue.length < 1)) &&
(typeof(element.getAttributeNS) == 'function')) {
if (
(typeof(namespace) == 'undefined') ||
(namespace == null)) {
namespace = 'http://www.xoad.org/controls';
}
try {
attributeValue = element.getAttributeNS(namespace, attribute.substring(attribute.indexOf(':') + 1));
} catch (e) {}
}
if (
((attributeValue == null) || (attributeValue.length < 1)) &&
(typeof(defaultValue) != 'undefined')) {
attributeValue = defaultValue;
}
return attributeValue;
};
xoad.controls.initializeControl = function(control, element, parentElement, controlData)
{
control.element = element;
control.parentElement = parentElement;
control.controlData = controlData;
if (typeof(controlData.serverClass) != 'undefined') {
try {
eval('control.serverObject = new ' + controlData.serverClass + '();');
} catch (e) {};
}
control.getAttribute = function(attribute, defaultValue) {
return xoad.controls.getAttribute(this.element, attribute, defaultValue);
};
control.getAttributeNS = function(attribute, namespace, defaultValue) {
return xoad.controls.getAttributeNS(this.element, attribute, namespace, defaultValue);
};
control.bindStyle = function(styleKey, defaultValue) {
eval(
'if ('
+ '(typeof(this.element.style.' + styleKey + ') == "undefined") ||'
+ '(this.element.style.' + styleKey + ' == null) ||'
+ '(this.element.style.' + styleKey + '.toString().length < 1)) {'
+ 'this.element.style.' + styleKey + ' = this.getAttribute("' + styleKey + '", defaultValue);'
+ '}');
};
if (xoad.controls.notifyObservers('controlInit', control)) {
if (typeof(control.OnInit) == 'function') {
control.OnInit();
}
}
if (xoad.controls.pageLoaded) {
if (xoad.controls.notifyObservers('controlLoad', control)) {
if (typeof(control.OnLoad) == 'function') {
control.OnLoad();
}
}
}
};
xoad.controls.createControl = function(element, parentElement, controlData)
{
try {
eval('var control = new ' + controlData.clientClass + '(element, controlData);');
if (xoad.controls.notifyObservers('controlCreated', control)) {
xoad.controls.initializeControl(control, element, parentElement, controlData);
xoad.controls.parsedControls[xoad.controls.parsedControls.length] = control;
return control;
}
} catch (e) {};
return null;
};
xoad.controls.parseControls = function(root)
{
if (typeof(root) == 'undefined') {
root = document;
}
if (typeof(root.childNodes) != 'undefined') {
for (var iterator = 0; iterator < root.childNodes.length; iterator ++) {
var child = root.childNodes[iterator];
if (
(typeof(child.tagName) != 'undefined') &&
(typeof(child.__xoad_parsedControl) == 'undefined')) {
if (
(typeof(child.childNodes) != 'undefined') &&
(child.childNodes.length > 0)) {
xoad.controls.parseControls(child);
}
var tagName = child.tagName.toLowerCase();
if (typeof(child.scopeName) != 'undefined') {
tagName = child.scopeName.toLowerCase() + ':' + tagName;
if (tagName.substr(0, 5) == 'html:') {
tagName = tagName.substr(5);
}
}
if (xoad.controls.notifyObservers('elementParse', child, tagName)) {
for (var listIterator = 0; listIterator < xoad.controls.list.length; listIterator ++) {
if (xoad.controls.list[listIterator].tagName == tagName) {
child.attachedControl = xoad.controls.createControl(child, root, xoad.controls.list[listIterator]);
}
}
}
child.__xoad_parsedControl = true;
}
}
}
};
xoad.controls.onPageLoad = function()
{
if (typeof(xoad.controls.parseIntervalId) != 'undefined') {
window.clearInterval(xoad.controls.parseIntervalId);
}
xoad.controls.parseControls();
xoad.controls.pageLoaded = true;
for (var iterator = 0; iterator < xoad.controls.parsedControls.length; iterator ++) {
var control = xoad.controls.parsedControls[iterator];
if (xoad.controls.notifyObservers('controlLoad', control)) {
if (typeof(control.OnLoad) == 'function') {
control.OnLoad();
}
}
}
};
xoad.controls.onPageUnload = function()
{
for (var iterator = 0; iterator < xoad.controls.parsedControls.length; iterator ++) {
var control = xoad.controls.parsedControls[iterator];
if (xoad.controls.notifyObservers('controlUnload', control)) {
if (typeof(control.OnUnload) == 'function') {
control.OnUnload();
}
}
}
};
xoad.controls.addObserver = function(observer)
{
xoad.controls.observers[xoad.controls.observers.length] = observer;
return true;
};
xoad.controls.notifyObservers = function(event)
{
if (xoad.controls.observers.length < 1) {
return true;
}
var eventMethod = 'on' + event.charAt(0).toUpperCase() + event.substr(1);
var notifyArguments = [];
var iterator = 0;
for (iterator = 1; iterator < arguments.length; iterator ++) {
notifyArguments[notifyArguments.length] = arguments[iterator];
}
for (iterator = 0; iterator < xoad.controls.observers.length; iterator ++) {
xoad.invokeMethod(xoad.controls.observers[iterator], eventMethod, notifyArguments);
}
return true;
};
xoad.controls.parseIntervalId = window.setInterval('xoad.controls.parseControls()', xoad.controls.parseInterval);
if (document.all) {
window.attachEvent('onload', xoad.controls.onPageLoad);
window.attachEvent('onunload', xoad.controls.onPageUnload);
} else {
window.addEventListener('load', xoad.controls.onPageLoad, true);
window.addEventListener('unload', xoad.controls.onPageUnload, true);
}