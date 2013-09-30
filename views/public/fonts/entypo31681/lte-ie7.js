/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'entypo\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-location' : '&#xe000;',
			'icon-arrow-left' : '&#xe001;',
			'icon-arrow-down' : '&#xe002;',
			'icon-arrow-up' : '&#xe003;',
			'icon-arrow-right' : '&#xe004;',
			'icon-map' : '&#xe005;',
			'icon-checkmark' : '&#xe006;',
			'icon-cross' : '&#xe007;',
			'icon-reply' : '&#xe008;',
			'icon-tools' : '&#xe009;',
			'icon-house' : '&#xe00a;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};