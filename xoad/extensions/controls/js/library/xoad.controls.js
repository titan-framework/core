xoad.controls.actions = {};

xoad.controls.actions.alertAction = function() {

	alert(this.xoadGetAttribute('value'));
};

xoad.controls.actions.showHideAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.display = (elements[iterator].style.display == 'none' ? '' : 'none');
	}
};

xoad.controls.actions.showAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.display = '';
	}
};

xoad.controls.actions.hideAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.display = 'none';
	}
};

xoad.controls.actions.visibleInvisibleAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.visibility = (elements[iterator].style.visibility == 'hidden' ? 'visible' : 'hidden');
	}
};

xoad.controls.actions.visibleAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.visibility = 'visible';
	}
};

xoad.controls.actions.invisibleAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].style.visibility = 'hidden';
	}
};

xoad.controls.actions.focusAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].focus();
	}
};

xoad.controls.actions.blurAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		elements[iterator].blur();
	}
};

xoad.controls.actions.historyBackAction = function() {

	history.go(-1);
};

xoad.controls.actions.historyForwardAction = function() {

	history.go(1);
};

xoad.controls.actions.historyGoAction = function() {

	history.go(parseInt(this.xoadGetAttribute('value')));
};

xoad.controls.actions.attachClassAction = function() {

	var elements = this.xoadFindElements();

	var newClassName = this.xoadGetAttribute('value');

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		var attachedClasses = elements[iterator].className.split(' ');

		for (var classIterator = 0; classIterator < attachedClasses.length; classIterator ++) {

			if (attachedClasses[classIterator] == newClassName) {

				return;
			}
		}

		elements[iterator].className += ' ' + newClassName;
	}
};

xoad.controls.actions.dettachClassAction = function() {

	var elements = this.xoadFindElements();

	for (var iterator = 0; iterator < elements.length; iterator ++) {

		if (elements[iterator].className.indexOf(this.xoadGetAttribute('value')) >= 0) {

			var attachedClasses = elements[iterator].className.split(' ');

			var newClassName = '';

			for (var classIterator = 0; classIterator < attachedClasses.length; classIterator ++) {

				if (
				(attachedClasses[classIterator].length > 0) &&
				(attachedClasses[classIterator] != this.xoadGetAttribute('value'))) {

					newClassName += ' ' + attachedClasses[classIterator];
				}
			}

			elements[iterator].className = newClassName.substr(1);
		}
	}
};

xoad.controls.actions.cloneAction = function() {

	var targetElements = this.xoadFindElements();
	var sourceElements = this.xoadFindElements(this.xoadGetAttribute('source'));

	var mode = this.xoadGetAttribute('mode', 'last');

	var deepClone = this.xoadGetAttribute('deep', true);

	if (typeof(deepClone) != 'boolean') {

		if (
		(deepClone == 'yes') ||
		(deepClone == 'true') ||
		(deepClone == '1')) {

			deepClone = true;

		} else {

			deepClone = false;
		}
	}

	for (var sourceIterator = 0; sourceIterator < sourceElements.length; sourceIterator ++) {

		var cloneSourceNode = function() {

			return sourceElements[sourceIterator].cloneNode(deepClone);
		};

		for (var targetIterator = 0; targetIterator < targetElements.length; targetIterator ++) {

			var target = targetElements[targetIterator];

			if (mode == 'last') {

				target.appendChild(cloneSourceNode());

			} else if (mode == 'first') {

				target.insertBefore(cloneSourceNode(), target.firstChild);

			} else if (mode == 'before') {

				var childNodes = cssQuery(this.xoadGetAttribute('node'), target);

				if (childNodes.length == 1) {

					target.insertBefore(cloneSourceNode(), childNodes[0]);

				} else {

					for (var childIterator = 0; childIterator < childNodes.length; childIterator ++) {

						target.insertBefore(cloneSourceNode(), childNodes[childIterator]);
					}
				}

			} else if (mode == 'after') {

				var childNodes = cssQuery(this.xoadGetAttribute('node'), target);

				if (childNodes.length == 1) {

					target.insertBefore(cloneSourceNode(), childNodes[0].nextSibling);

				} else {

					for (var childIterator = 0; childIterator < childNodes.length; childIterator ++) {

						target.insertBefore(cloneSourceNode(), childNodes[childIterator].nextSibling);
					}
				}

			} else if (mode == 'replace') {

				var childNodes = cssQuery(this.xoadGetAttribute('node'), target);

				if (childNodes.length == 1) {

					target.replaceChild(cloneSourceNode(), childNodes[0]);

				} else {

					for (var childIterator = 0; childIterator < childNodes.length; childIterator ++) {

						target.replaceChild(cloneSourceNode(), childNodes[childIterator]);
					}
				}
			}
		}
	}
};

xoad.controls.actions.scriptAction = function() {

	eval(this.xoadGetAttribute('value'));
};

xoad.controls.actions.onElementParse = function(element, tagName) {

	var action = xoad.controls.getAttributeNS(element, 'xoad:action');

	if (
	(action != null) &&
	(action.length > 0)) {

		var methodName = '';

		var actionChar = null;
		var nextUpper = false;

		for (var iterator = 0; iterator < action.length; iterator ++) {

			actionChar = action.charAt(iterator);

			if (
			((actionChar < 'a') || (actionChar > 'z')) &&
			((actionChar < 'A') || (actionChar > 'Z'))) {

				nextUpper = true;

				continue;
			}

			methodName += (nextUpper ? actionChar.toUpperCase() : actionChar);

			nextUpper = false;
		}

		try {

			eval('element.__xoad_handleAction = xoad.controls.actions.' + methodName + 'Action;');

			element.xoadGetAttribute = function(attribute, defaultValue) {

				if (typeof(defaultValue) == 'undefined') {

					return xoad.controls.getAttributeNS(this, 'xoad:' + attribute);

				} else {

					return xoad.controls.getAttributeNS(this, 'xoad:' + attribute, null, defaultValue);
				}
			};

			element.xoadFindElements = function(search) {

				if (typeof(search) == 'undefined') {

					search = this.xoadGetAttribute('target');
				}

				var elements = null;

				if (
				(search == null) ||
				(search.length < 1) ||
				(search == 'self')) {

					elements = [this];

				} else {

					elements = cssQuery(search);
				}

				return elements;
			};

			if (
			(tagName == 'a') &&
			(element.href.length < 1)) {

				element.href = '#action';
			}

			element.onclick = function(e) {

				e = (e || window.event);

				this.__xoad_handleAction();

				e.returnValue = false;

				if (typeof(e.preventDefault) != 'undefined') {

					e.preventDefault();
				}

				return false;
			};

		} catch (e) {}
	}

	return true;
};

xoad.controls.addObserver(xoad.controls.actions);