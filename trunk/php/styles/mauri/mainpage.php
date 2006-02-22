		<div id="top">
			<img id="logo" src="{STYLE_PATH}/mauri_logo.jpg" alt="Ein Bild der Stankt Mauritius-Kirche"/>
			<img id="logo2" src="{STYLE_PATH}/michael_logo.jpg" alt="Ein Bild der Stankt Michel-Kirche"/>
			<ul id="secondmenu">
<MENU2:loop>
	<li><a href="{LINK}">{LINK_TEXT}</a></li>
</MENU2>
			</ul>
						<h1 id="title">Katholische Kirchengemeinden <span class="subtitle">Sankt Mauritius Hildesheim und Sankt Michael Hildesheim-Neuhof</span></h1><notathome:condition>
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