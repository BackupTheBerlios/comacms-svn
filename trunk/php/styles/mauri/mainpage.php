		<div id="top">
			<img id="logo" src="{STYLE_PATH}/logo.jpg" alt="Ein Bild der St. Mauritius-Kirche von der Seite"/>
			<ul id="secondmenu">
<MENU2:loop>
	<li><a href="{LINK}">{LINK_TEXT}</a></li>
</MENU2>
			</ul>
						<h1 id="title">Seelsorgeeinheit<br /> Sankt Mauritius und Sankt Michael</h1><notathome:condition>
						<span id="position">Pfad: {PATH}</span></notathome>
		</div>
		<div id="menupart">
			<ul id="menu">
				<MENU:loop>
				<li{LINK_STYLE}><a href="{LINK}">{LINK_TEXT}</a></li>
				</MENU>
							
			</ul><notinadmin:condition>
			<a href="http://www.wjt2005.de" target="_blank" title="Homepage des Weltjugendtages 2005">
				<img id="footerimage" alt="Das ist das Logo des Weltjugendtages 2005" src="{STYLE_PATH}/wjt2005_logo_deu.gif"/>
			</a></notinadmin>
		</div>
		<inlinemenu:condition>
		<div id="inlinemenu">
			{INLINEMENU_IMAGE}
			<br />
			{INLINEMENU_TEXT}
		</div>
		</inlinemenu>
		<div id="text">
			{TEXT}
		</div>