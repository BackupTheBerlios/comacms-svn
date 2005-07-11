/* Der Quellcode stammt teilweise aus dem mediawiki
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

function preview_style()
{
	//das <select> element auswählen
	data = document.getElementById('stylepreviewselect');
	//den <iframe> auswählen
	dframe = document.getElementById('previewiframe');
	//das ausgewählte in den <iframe> übertragen
	dframe.src = "stylepreview.php?style=" + data.value;
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

