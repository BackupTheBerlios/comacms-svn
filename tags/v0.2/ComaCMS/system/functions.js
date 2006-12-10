
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

/* SetHover 
 * This function applies the hover-functionality for all html tags in Internet Explorer
 * 
 * TagName: The name of the tag which should have the hover functionality
 * ClassName: The name of the css-class, which the tag has
 * HoverClassName: The name of the css-class, which applies the hover-style
 * Additional: Some additional code to make workrounds possible
 */
function SetHover(TagName, ClassName, HoverClassName, Additional) {
	// IE only
	if(document.all) {
		// Get the count of all $TagName elements
		var tagsCount = document.getElementsByTagName(TagName).length;
		for(var i = 0; i < tagsCount; i++){
			// Check it for the right css-class
			if(document.getElementsByTagName(TagName)[i].className == ClassName) {
				var rowSpan = document.getElementsByTagName(TagName)[i];
				// Remove the border
				rowSpan.style.border = 'none';
				// Set onMouseOver
				rowSpan.onmouseover = function onmouseover(event) { Hover(this, ClassName, HoverClassName);Additional(); };
				// Set onMouseOut
				rowSpan.onmouseout =  function onmouseover(event) { HoverOut(this, ClassName);Additional(); };
			}
		}
		Additional();
	}
}

function Hover(ObjectToHover, ClassName, HoverClassName) {
	// IE only
	if(document.all)
		// Set both css-classes
		ObjectToHover.className = ClassName + " " + HoverClassName;
}

function HoverOut(ObjectToHover, ClassName) {
	// IE only
	if(document.all)
		// Reset To default css-class
		ObjectToHover.className = ClassName;
}

function preview_style()
{
	//das <select> element auswaehlen
	data = document.getElementById('stylepreviewselect');
	//den <iframe> auswaehlen
	dframe = document.getElementById('previewiframe');
	//das ausgewaehlte in den <iframe> ?bertragen
	dframe.src = "index.php?style=" + data.value;
}

function resizeBox(add) {
	var textarea = document.getElementById('editor');
	var style = textarea.getAttribute('style');
	style = (style != null) ? style : '';
	if(style == '' && add > 0)
		style = 'height:22em;';
	else if(style != '') {
		var startHeight = style.indexOf('height');
		var endHeight = style.indexOf(';', startHeight);
		var middleHeight = style.indexOf(':', startHeight);
		var length = style.length;
		var size = style.substr(middleHeight+1, endHeight - middleHeight - 1);
		size = size.replace(/ /,'');
		size = size.replace(/em/,'');
		size = eval(size) + add;
		if(size < 17)
			size = 17;
		size = 'height:' + size+ 'em;';
		style = style.substr(0, startHeight) + size + style.substr(endHeight+1,length - endHeight);
		
	}
	//var start_height = 
	textarea.setAttribute('style', style);
	//alert(textarea.rootElement.getPropertyValue('height'));
}

function writeButton(image, toolTip, tagOpen, tagClose, example, accessKey)
{
	document.write("<a ");
	
	if(accessKey)
		document.write("accesskey=\"" + accessKey + "\" ");
	document.write("href=\"javascript:formatText('" + tagOpen + "', '" + tagClose + "', '" + example + "')\" ");
	document.write("title=\"" + toolTip + "\" ");
	document.write(">");
	document.write("<img src=\"" + image + "\" class=\"editbutton\" alt=\"" + toolTip + "\" title=\"" + toolTip + "\" width=\"25\" height=\"25\" />");
	document.write("</a> ");
}
 
/* Der Quellcode ab hier stammt teilweise aus dem mediawiki
 *
 *
 */

var clientPC = navigator.userAgent.toLowerCase();

var is_gecko = ((clientPC.indexOf('gecko')!=-1) && (clientPC.indexOf('spoofer')==-1) && (clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0')==-1));
var is_safari = ((clientPC.indexOf('AppleWebKit')!=-1) && (clientPC.indexOf('spoofer')==-1));
var is_khtml = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera')!=-1) {
    var is_opera = true;
    var is_opera_preseven = (window.opera && !document.childNodes);
    var is_opera_seven = (window.opera && document.childNodes);
}




// aus mediawiki
function formatText(tagOpen, tagClose, example)
{
	var txtarea = document.getElementById('editor');
	//IE
	if(document.selection && !is_gecko)
	{
		var theSelection = document.selection.createRange().text;
		var replaced = true;
		if(!theSelection)
		{
			replaced = false;
			theSelection = example;
		}
		txtarea.focus();
		// This has change
		text = theSelection;
		
		// exclude ending space char, if any
		if(theSelection.charAt(theSelection.length - 1) == " ")
		{
			theSelection = theSelection.substring(0, theSelection.length - 1);
			r = document.selection.createRange();
			r.text = tagOpen + theSelection + tagClose + " ";
		}
		else
		{
		r = document.selection.createRange();
		r.text = tagOpen + theSelection + tagClose;
		}
		if(!replaced)
		{
			r.moveStart('character',-text.length-tagClose.length);
			r.moveEnd('character',-tagClose.length);
		}
		r.select();
		// Mozilla
	}
	else if(txtarea.selectionStart || txtarea.selectionStart == '0')
	{
		var replaced = false;
		var startPos = txtarea.selectionStart;
		var endPos   = txtarea.selectionEnd;
		if(endPos - startPos) replaced = true;
		var scrollTop = txtarea.scrollTop;
		var myText = (txtarea.value).substring(startPos, endPos);
		if(!myText)
			myText = example;
		// exclude ending space char, if any
		if(myText.charAt(myText.length - 1) == " ")
		{
			subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " ";
		}
		else
		{
			subst = tagOpen + myText + tagClose;
		}
		txtarea.value = txtarea.value.substring(0, startPos) + subst +
		txtarea.value.substring(endPos, txtarea.value.length);
		txtarea.focus();
 			//set new selection
		if(replaced)
		{
			var cPos = startPos + (tagOpen.length + myText.length + tagClose.length);
			txtarea.selectionStart = cPos;
			txtarea.selectionEnd = cPos;
		}
		else
		{
			txtarea.selectionStart = startPos + tagOpen.length;
			txtarea.selectionEnd = startPos + tagOpen.length + myText.length;
			txtarea.scrollTop = scrollTop;
		}
	
	}
	else // All others
	{
		var copy_alertText = alertText;
		var re1 = new RegExp("\\$1", "g");
		var re2 = new RegExp("\\$2", "g");
		copy_alertText = copy_alertText.replace(re1, example);
		copy_alertText = copy_alertText.replace(re2, tagOpen + example + tagClose);
		var text;
		if (example)
		{
			text = prompt(copy_alertText);
		}
		else
		{
			text="";
		}
		if(!text)
			text = sampleText;
		text = tagOpen + text + tagClose;
		//append to the end
		txtarea.value += "\n"+text;
		// in Safari this causes scrolling
		if(!is_safari)
			txtarea.focus();
	}
	// reposition cursor if possible
	if(txtarea.createTextRange)
		 txtarea.caretPos = document.selection.createRange().duplicate();
}
