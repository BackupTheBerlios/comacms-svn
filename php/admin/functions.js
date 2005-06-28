function preview_style()
{
	//das <select> element auswählen
	data = document.getElementById('stylepreviewselect');
	//den <iframe> auswählen
	dframe = document.getElementById('previewiframe');
	//das ausgewählte in den <iframe> übertragen
	dframe.src = "stylepreview.php?style=" + data.value;
}