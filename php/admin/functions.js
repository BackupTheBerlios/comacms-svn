function preview_style()
{
	//das <select> element ausw�hlen
	data = document.getElementById('stylepreviewselect');
	//den <iframe> ausw�hlen
	dframe = document.getElementById('previewiframe');
	//das ausgew�hlte in den <iframe> �bertragen
	dframe.src = "stylepreview.php?style=" + data.value;
}