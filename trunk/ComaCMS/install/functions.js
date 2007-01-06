
// get the user-agent of the user
var browser = navigator.userAgent.toLowerCase();
var isGecko = (browser.indexOf('gecko') != -1 && browser.indexOf('spoofer') == -1 && browser.indexOf('khtml') == -1 && browser.indexOf('netscape/7.0') == -1);
var isSafari = (browser.indexOf('applewebkit') != -1 && browser.indexOf('spoofer') == -1);
var isKHTML = false;
if(navigator.vendor)
	isKHTML = (navigator.vendor.toLowerCase() == 'kde' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if(browser.indexOf('opera')!=-1) {
	var isOpera = true;
	var isOperaPreSeven = (window.opera && !document.childNodes);
	var isOperaSeven = (window.opera && document.childNodes);
}

function preview_style()
{
	//das <select> element auswaehlen
	data = document.getElementById('stylepreviewselect');
	//den <iframe> auswaehlen
	dframe = document.getElementById('previewiframe');
	//das ausgewaehlte in den <iframe> ?bertragen
	dframe.src = "install.php?style=" + data.value;
}