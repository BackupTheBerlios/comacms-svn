		<div id="all">
			<div id="top">
				<h1 id="title">ComaCMS</h1>
			</div>
			<ul id="menu">
				<MENU:loop>
				<li type="square"{LINK_STYLE}><a href="{LINK}">{LINK_TEXT}</a></li>
				</MENU>		
					</ul>
			<div id="content">
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
			</div>
			<div id="bottom">
				<a href="http://developer.berlios.de" title="BerliOS Developer"> <img src="http://developer.berlios.de/bslogo.php?group_id=5648" width="124" height="32" border="0" alt="BerliOS Developer Logo" /></a><br />
				<a href="http://developer.berlios.de/projects/comacms" title="Coma Content Management System">&copy;2005 The ComaCMS-Team</a>
			</div>
		</div>