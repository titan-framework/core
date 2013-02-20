var XOAD_ERROR_USER = 0x400;
var XOAD_ERROR_TIMEOUT = 0x401;
var xoad = {};
xoad.errorHandler = null;
xoad.callbacks = {};
xoad.callbacks.table = {};
xoad.callbacks.count = 0;
xoad.events = {};
xoad.events.table = [];
xoad.events.postTable = [];
xoad.events.timeout = 5000;
xoad.events.startInterval = 250;
xoad.events.refreshInterval = 2000;
xoad.events.status = 0;
xoad.observers = [];
xoad.asyncCall = function() {};
xoad.callSuspender = function()
{
return {
suspend : function() {
this.suspended = true;
},
suspended : false
}
};
xoad.getError = function(errorCode, errorMessage)
{
return {
code : errorCode,
message : errorMessage
}
};
xoad.getXmlHttp = function()
{
var xmlHttp = null;
try {
xmlHttp = new XMLHttpRequest();
} catch (e) {
var progIds = ['MSXML2.XMLHTTP', 'Microsoft.XMLHTTP', 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0'];
var success = false;
for (var iterator = 0; (iterator < progIds.length) && ( ! success); iterator ++) {
try {
xmlHttp = new ActiveXObject(progIds[iterator]);
success = true;
} catch (e) {}
}
if ( ! success ) {
return null;
}
}
return xmlHttp;
};
xoad.clone = function(target, source)
{
var wipeKeys = [];
var key = null;
for (key in target.__meta) {
if (typeof(source[key]) == 'undefined') {
wipeKeys[wipeKeys.length] = key;
}
}
if (wipeKeys.length > 0) {
for (var iterator = 0; iterator < wipeKeys.length; iterator ++) {
target[wipeKeys[iterator]] = null;
}
}
for (key in source.__meta) {
if (source[key] == null) {
target[key] = null;
} else {
target[key] = source[key];
}
}
target.__meta = source.__meta;
target.__size = source.__size;
target.__timeout = source.__timeout;
};
xoad.serialize = function(data)
{
if (data == null) {
return 'N;';
}
var type = typeof(data);
var code = '';
var iterator = 0;
var length = null;
var asciiCode = null;
var key = null;
if (type == 'boolean') {
code += 'b:' + (data ? 1 : 0) + ';';
} else if (type == 'number') {
if (Math.round(data) == data) {
code += 'i:' + data + ';';
} else {
code += 'd:' + data + ';';
}
} else if (type == 'string') {
length = data.length;
for (iterator = 0; iterator < data.length; iterator ++) {
asciiCode = data.charCodeAt(iterator);
if ((asciiCode >= 0x00000080) && (asciiCode <= 0x000007FF)) {
length += 1;
} else if ((asciiCode >= 0x00000800) && (asciiCode <= 0x0000FFFF)) {
length += 2;
} else if ((asciiCode >= 0x00010000) && (asciiCode <= 0x001FFFFF)) {
length += 3;
} else if ((asciiCode >= 0x00200000) && (asciiCode <= 0x03FFFFFF)) {
length += 4;
} else if ((asciiCode >= 0x04000000) && (asciiCode <= 0x7FFFFFFF)) {
length += 5;
}
}
code += 's:' + length + ':"' + data + '";';
} else if (type == 'object') {
if (typeof(data.__class) == 'undefined') {
length = 0;
if (
(typeof(data.length) == 'number') &&
(data.length > 0) &&
(typeof(data[0]) != 'undefined')) {
for (iterator = 0; iterator < data.length; iterator ++) {
if (typeof(data[iterator]) != 'function') {
code += xoad.serialize(iterator);
code += xoad.serialize(data[iterator]);
length ++;
}
}
} else {
for (key in data) {
if (typeof(data[key]) != 'function') {
if (/^[0-9]+$/.test(key)) {
code += xoad.serialize(parseInt(key));
} else {
code += xoad.serialize(key);
}
code += xoad.serialize(data[key]);
length ++;
}
}
}
code = 'a:' + length + ':{' + code + '}';
} else {
code += 'O:' + data.__class.length + ':"' + data.__class + '":' + data.__size + ':{';
if (data.__meta != null) {
for (key in data.__meta) {
if (typeof(data[key]) != 'function') {
code += xoad.serialize(key);
code += xoad.serialize(data[key]);
}
}
}
code += '}';
}
} else {
code = 'N;'
}
return code;
};
xoad.setErrorHandler = function(handler)
{
if (
(handler != null) &&
(typeof(handler) == 'function')) {
xoad.errorHandler = handler;
return true;
}
return false;
};
xoad.restoreErrorHandler = function()
{
xoad.errorHandler = null;
return true;
};
xoad.throwException = function(error, throwArguments)
{
if (typeof(throwArguments) != 'undefined') {
var sender = throwArguments[0];
var method = throwArguments[1];
method = 'on' + method.charAt(0).toUpperCase() + method.substr(1) + 'Error';
if (xoad.invokeMethod(sender, method, [error])) {
return false;
}
}
if (
(xoad.errorHandler != null) &&
(typeof(xoad.errorHandler) == 'function')) {
xoad.errorHandler(error);
return false;
}
throw error;
};
xoad.invokeMethod = function(obj, method, invokeArguments)
{
if (
(obj == null) ||
(typeof(obj) != 'object')) {
return false;
}
var type = eval('typeof(obj.' + method + ')');
if (type == 'function') {
var invokeCode = 'obj.' + method + '(';
if (typeof(invokeArguments) != 'undefined') {
for (var iterator = 0; iterator < invokeArguments.length; iterator ++) {
invokeCode += 'invokeArguments[' + iterator + ']';
if (iterator < invokeArguments.length - 1) {
invokeCode += ', ';
}
}
}
invokeCode += ')';
return eval(invokeCode);
}
return false;
};
xoad.call = function(obj, method, callArguments)
{
if (
(obj == null) ||
(typeof(obj) != 'object') ||
(typeof(obj.__class) != 'string')) {
return false;
}
var methodCallback = null;
var methodArgs = [];
for (var iterator = 0; iterator < callArguments.length; iterator ++) {
if (
(typeof(callArguments[iterator]) == 'function') &&
(iterator == callArguments.length - 1)) {
methodCallback = callArguments[iterator];
continue;
}
methodArgs[methodArgs.length] = callArguments[iterator];
}
var xmlHttp = xoad.getXmlHttp();
var requestBody = {
source : obj,
className : obj.__class,
method : method,
arguments : methodArgs
};
xoad.notifyObservers('call', requestBody);
requestBody.source = xoad.serialize(requestBody.source);
requestBody.arguments = xoad.serialize(requestBody.arguments);
requestBody = xoad.serialize(requestBody);
var url = obj.__url;
if (url.indexOf('?') < 0) {
url += '?';
} else {
url += '&';
}
url += 'xoadCall=true';
if (methodCallback != null) {
xmlHttp.open('POST', url, true);
} else {
xmlHttp.open('POST', url, false);
}
var callId = null;
var callTimeout = obj.getTimeout();
if (callTimeout != null) {
callId = xoad.callbacks.count;
}
xoad.callbacks.count ++;
var callResult = true;
var requestCompleted = function() {
if (typeof(callResult) == 'object') {
if (callResult.suspended) {
return false;
}
}
if (callId != null) {
if (eval('xoad.callbacks.table.call' + callId + '.timeout')) {
return false;
}
eval('window.clearTimeout(xoad.callbacks.table.call' + callId + '.id)');
eval('xoad.callbacks.table.call' + callId + ' = null');
}
if (xmlHttp.status != 200) {
return xoad.throwException(xoad.getError(xmlHttp.status, xmlHttp.statusText), [obj, method]);
} else {
if (xmlHttp.responseText == null) {
return xoad.throwException(xoad.getError(xmlHttp.status, 'Empty response.'), [obj, method]);
}
if (xmlHttp.responseText.length < 1) {
return xoad.throwException(xoad.getError(xmlHttp.status, 'Empty response.'), [obj, method]);
}
try {
eval('var xoadResponse = ' + xmlHttp.responseText + ';');
} catch(e) {
return xoad.throwException(xoad.getError(xmlHttp.status, 'Invalid response.'), [obj, method]);
}
if (typeof(xoadResponse.exception) != 'undefined') {
return xoad.throwException(xoad.getError(XOAD_ERROR_USER, xoadResponse.exception), [obj, method]);
}
if (xoad.notifyObservers('callCompleted', xoadResponse)) {
obj.__clone(xoadResponse.returnObject);
if (typeof(xoadResponse.output) != 'undefined') {
obj.__output = xoadResponse.output;
} else {
obj.__output = null;
}
return {
returnValue : xoadResponse.returnValue
};
}
}
return false;
};
try {
xmlHttp.setRequestHeader('Content-Length', requestBody.length);
xmlHttp.setRequestHeader('Content-Type', 'text/plain; charset=ISO-8859-1');
xmlHttp.setRequestHeader('Accept-Charset', 'ISO-8859-1');
} catch (e) {}
if (methodCallback != null) {
xmlHttp.onreadystatechange = function() {
if (xmlHttp.readyState == 4) {
var response = requestCompleted();
if (typeof(response.returnValue) != 'undefined') {
methodCallback(response.returnValue);
}
}
}
}
if (callTimeout != null) {
eval('xoad.callbacks.table.call' + callId + ' = {}');
eval('xoad.callbacks.table.call' + callId + '.timeout = false');
eval('xoad.callbacks.table.call' + callId + '.source = obj');
eval('xoad.callbacks.table.call' + callId + '.id = '
+ 'window.setTimeout(\'xoad.callbacks.table.call' + callId + '.timeout = true; '
+ 'xoad.throwException(xoad.getError(XOAD_ERROR_TIMEOUT, "Timeout."), [xoad.callbacks.table.call' + callId + '.source, "' + method + '"]);\', callTimeout)');
}
xmlHttp.send(requestBody);
if (methodCallback == null) {
var response = requestCompleted();
if (typeof(response.returnValue) != 'undefined') {
return response.returnValue;
}
return null;
} else {
callResult = new xoad.callSuspender();
return callResult;
}
};
xoad.catchEvent = function(obj, eventArguments)
{
if (eventArguments.length < 2) {
eventArguments[1] = null;
}
var eventData = {
listener : obj,
event : eventArguments[0],
filter : eventArguments[1]
};
xoad.events.table[xoad.events.table.length] = eventData;
xoad.events.tableLength ++;
if (xoad.events.status < 1) {
xoad.events.status = 1;
window.setTimeout('xoad.dispatchEvents()', xoad.events.startInterval);
}
return true;
};
xoad.ignoreEvent = function(obj, eventArguments)
{
if (xoad.events.tableLength < 1) {
return false;
}
if (eventArguments.length < 2) {
eventArguments[1] = null;
}
for (var iterator = xoad.events.table.length - 1; iterator >= 0; iterator --) {
var event = xoad.events.table[iterator];
if (
(event.listener.__uid == obj.__uid) &&
(event.event == eventArguments[0]) &&
(event.filter == eventArguments[1])) {
xoad.events.table[iterator] = null;
xoad.events.tableLength --;
break;
}
}
return true;
};
xoad.queueDispatchEvents = function(time)
{
if (typeof(time) == 'undefined') {
time = xoad.events.refreshInterval;
}
window.setTimeout('xoad.dispatchEvents()', time);
};
xoad.dispatchEvents = function()
{
if (xoad.events.tableLength < 1) {
xoad.events.status = 0;
return false;
}
if (
(typeof(xoad.events.callbackUrl) != 'string') ||
(typeof(xoad.events.lastRefresh) != 'number')) {
xoad.events.status = 0;
return false;
}
xoad.events.status = 1;
var eventsData = [];
for (var iterator = 0; iterator < xoad.events.table.length; iterator ++) {
var event = xoad.events.table[iterator];
if (event != null) {
eventsData[eventsData.length] = {
className : event.listener.__class,
event : event.event,
filter : event.filter
};
}
}
var xmlHttp = xoad.getXmlHttp();
var requestBody = xoad.serialize({
eventsCallback : true,
time : xoad.events.lastRefresh,
data : eventsData
});
var url = xoad.events.callbackUrl;
if (url.indexOf('?') < 0) {
url += '?';
} else {
url += '&';
}
url += 'xoadCall=true';
xmlHttp.open('POST', url, true);
var callId = xoad.callbacks.count ++;
var requestCompleted = function() {
if (eval('xoad.callbacks.table.call' + callId + '.timeout')) {
return false;
}
eval('window.clearTimeout(xoad.callbacks.table.call' + callId + '.id)');
eval('xoad.callbacks.table.call' + callId + ' = null');
if (xmlHttp.status != 200) {
xoad.queueDispatchEvents();
return false;
} else {
if (xmlHttp.responseText == null) {
xoad.queueDispatchEvents();
return false;
}
if (xmlHttp.responseText.length < 1) {
xoad.queueDispatchEvents();
return false;
}
try {
eval('var xoadResponse = ' + xmlHttp.responseText + ';');
} catch(e) {
xoad.queueDispatchEvents();
return false;
}
if (typeof(xoadResponse) != 'object') {
xoad.queueDispatchEvents();
return false;
}
if (xoad.notifyObservers('dispatchEventsCompleted', xoadResponse)) {
for (var serverIterator = 0; serverIterator < xoadResponse.result.length; serverIterator ++) {
var serverEvent = xoadResponse.result[serverIterator];
for (var clientIterator = 0; clientIterator < xoad.events.table.length; clientIterator ++) {
var clientEvent = xoad.events.table[clientIterator];
if (clientEvent != null) {
if (
(serverEvent.event == clientEvent.event) &&
(serverEvent.className.toLowerCase() == clientEvent.listener.__class.toLowerCase()) &&
(serverEvent.filter == clientEvent.filter)) {
eval('if (typeof(clientEvent.listener.' + clientEvent.event + ') == "function") { '
+ 'clientEvent.listener.' + clientEvent.event + '(serverEvent.eventData.sender, serverEvent.eventData.data) }');
}
}
}
if (serverEvent.time > xoad.events.lastRefresh) {
xoad.events.lastRefresh = serverEvent.time;
}
}
xoad.queueDispatchEvents();
return true;
}
}
return false;
};
try {
xmlHttp.setRequestHeader('Content-Length', requestBody.length);
xmlHttp.setRequestHeader('Content-Type', 'text/plain; charset=ISO-8859-1');
xmlHttp.setRequestHeader('Accept-Charset', 'ISO-8859-1');
} catch (e) {}
xmlHttp.onreadystatechange = function() {
if (xmlHttp.readyState == 4) {
xoad.events.status = 3;
requestCompleted();
xoad.events.status = 1;
}
};
eval('xoad.callbacks.table.call' + callId + ' = {}');
eval('xoad.callbacks.table.call' + callId + '.timeout = false');
eval('xoad.callbacks.table.call' + callId + '.id = '
+ 'window.setTimeout(\'xoad.callbacks.table.call' + callId + '.timeout = true; '
+ 'xoad.queueDispatchEvents();\', xoad.events.timeout)');
xoad.events.status = 2;
xmlHttp.send(requestBody);
return true;
};
xoad.queuePostEvent = function(eventId)
{
if (typeof(xoad.events.postTable[eventId]) == 'object') {
xoad.postEvent(xoad.events.postTable[eventId].sender, [
xoad.events.postTable[eventId].event,
xoad.events.postTable[eventId].data,
xoad.events.postTable[eventId].filter,
eventId]);
}
};
xoad.postEvent = function(obj, eventArguments)
{
if (typeof(xoad.events.callbackUrl) != 'string') {
return false;
}
var eventName = eventArguments[0];
var eventData = (eventArguments.length > 1) ? eventArguments[1] : null;
var eventFilter = (eventArguments.length > 2) ? eventArguments[2] : null;
var eventId = (eventArguments.length > 3) ? eventArguments[3] : xoad.events.postTable.length;
xoad.events.postTable[eventId] = {
sender : obj,
event : eventName,
data : eventData,
filter : eventFilter
};
var xmlHttp = xoad.getXmlHttp();
var requestBody = xoad.serialize({
eventPost : true,
className : obj.__class,
sender : xoad.serialize(obj),
event : eventName,
data : eventData,
filter : eventFilter
});
var url = xoad.events.callbackUrl;
if (url.indexOf('?') < 0) {
url += '?';
} else {
url += '&';
}
url += 'xoadCall=true';
xmlHttp.open('POST', url, true);
var requestCompleted = function() {
if (xmlHttp.status != 200) {
xoad.queuePostEvent(eventId);
return false;
} else {
if (xmlHttp.responseText == null) {
xoad.queuePostEvent(eventId);
return false;
}
if (xmlHttp.responseText.length < 1) {
xoad.queuePostEvent(eventId);
return false;
}
try {
eval('var xoadResponse = ' + xmlHttp.responseText + ';');
} catch(e) {
xoad.queuePostEvent(eventId);
return false;
}
if (typeof(xoadResponse) != 'object') {
xoad.queuePostEvent(eventId);
return false;
}
if (xoadResponse.status != true) {
xoad.queuePostEvent(eventId);
return false;
}
if (xoad.notifyObservers('postEventCompleted', xoadResponse)) {
xoad.events.postTable[eventId] = null;
return true;
}
}
return false;
};
try {
xmlHttp.setRequestHeader('Content-Length', requestBody.length);
xmlHttp.setRequestHeader('Content-Type', 'text/plain; charset=ISO-8859-1');
xmlHttp.setRequestHeader('Accept-Charset', 'ISO-8859-1');
} catch (e) {}
xmlHttp.onreadystatechange = function() {
if (xmlHttp.readyState == 4) {
requestCompleted();
}
};
xmlHttp.send(requestBody);
return true;
};
xoad.addObserver = function(observer)
{
xoad.observers[xoad.observers.length] = observer;
return true;
};
xoad.notifyObservers = function(event)
{
if (xoad.observers.length < 1) {
return true;
}
var eventMethod = 'on' + event.charAt(0).toUpperCase() + event.substr(1);
var notifyArguments = [];
var iterator = 0;
for (iterator = 1; iterator < arguments.length; iterator ++) {
notifyArguments[notifyArguments.length] = arguments[iterator];
}
for (iterator = 0; iterator < xoad.observers.length; iterator ++) {
xoad.invokeMethod(xoad.observers[iterator], eventMethod, notifyArguments);
}
return true;
};