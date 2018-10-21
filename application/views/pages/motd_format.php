<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>></a></li>
			<li class="active">MOTD Format Guide 2.3</li>
		</ol>
	</div>

	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				MOTD Format Guide 2.3
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-meta">
				<span class="author">by Daneel Trevize</span>
			</div>
			
			<div class="entry-content">
				
				<h3>The ingame Upcoming Fleet listing format:</h3>
				
				<p class="lead"><code>(Day)/(Month) - (Time) - (Doctrine) w/ (Fleet Commander) @ (Location)</code><br>
				The above is the simple form that an upcoming fleet listing is expected to take. All values inside brackets should be replaced with the specifics of the fleet, and the brackets do not exist in the actual ingame format.<br>
				<code>w/</code> is short for "with", and <code>@</code> is short for "at". Full forms can be used, but by default we favour short forms to save characters (see 'Why this exists' below).<br>
				Day and Month replacements should be in double-digit format, as should the 24-hour clock Time. All times should be in EVE-time (UTC).<br>
				For example:<br>
				<code>18/03 - 19:00 - Ganked 264 Nomens Land w/ Pomagre @Amarr fleetID:178</code><p>
				
				<h3>The ingame Active fleets listing format:</h3>
				<p class="lead"><code>(XUP Channel Link) - (Doctrine) w/ (Fleet Commander)</code><br>
				The above is the simple form that an active fleet listing is expected to take. Again values inside brackets are to be replaced with specifics.<br>
				Ideally the listing will also include a form-up location after the FC's name, and fleetID component.</p>
				
				<h3>Online convertions</h3>
				<p>An optional <code>fleetID:</code> component can be included (ideally in the Doctrine portion), followed by a series of numbers which should correspond to the ID of a fleet <a href="/doctrine/fleets">listed on the Spectre website</a>. This component will be converted into a link to the specific fleet webpage.<br>
				The <code>w/</code> and <code>@</code> components are used to box the Fleet Commander's name, to assist determining the Eve character name.<br>
				The <code>@</code> component will cause the subsequent Location to be converted into a Dotlan system link.<br>
				The <code>@</code> component can be replaced with a <code>~</code>, which will indicate the location is vague/<code>Near</code>, with the same Location linking effect.<br>
				The Location can be stated as simply a series of <code>?</code>s, this will not be converted into a Dotlan link, but rather indicate the location is currently <code>Undecided</code>.</br>
				The Location is deemed to end with either a space, a comma or a new line.</p>
				
				<p>Ingame Channel, System and Item links are converted to plain text. Thus only if system links are prefixed by the <code>@</code> or <code>~</code> syntax within an Upcoming Fleet line will they become Dotlan links online.<br>
				Ingame HTTP links are convert to HTML ones. <strong>Please use a URL shortening service such as <a href="https://goo.gl/">goo.gl</a> if you must include a URL</strong>.<br>
				If you wish to run a Poll, please strongly consider <a href="/polls/manage">our SSO-backed Poll feature</a>.</p>
				
				<p>Be aware that while you <i>can</i> use these ingame links to ingame and external items without breaking the online MOTD, they take up a large number of hidden characters ingame, which may lead to MOTD truncation! Keep reading to find out why...</p>
				
				<h3>Why this exists</h3>
				
				<p>Spectre Fleet as a community depends upon the ingame channel to most easily communicate and coordinate with its members. Unfortunately, there are limitations to this mechanism, specifically in the size and complexity of the formatted MOTD.</p>
				
				<p>The ingame MOTD editor does permit channel operators to apply a variety of text formatting (sizing, bold, colours, etc), and to embed ingame and external links in the message. However, as of the June 2017 "fix", <b>it still does not correctly count the character cost</b> of the EVE-specific hidden formatting syntax added around these elements while informing the operator of how many characters they have used of the 4000 limit. This also includes exceeding it up to displaying 4222/4000 before it prevents input.<br>
				Eve itself <i>does</i> count the hidden formatting towards the MOTD character limit and <b>will truncate messages that are too long</b>. In some cases this limit is actually 3000 instead of the displayed 4000...<br>
				An additional Eve bug means <b>this truncation may not be immediately noticable to the operator</b> who changes the MOTD, unless they leave and rejoin the channel, or re-log.</p>
				
				<p>To overcome these limitations, Spectre Fleet uses the EVE API to retrieve the ingame MOTD and convert the formatting to HTML, where we additionally support converting Spectre-specific formatting/syntax into additional online content. This allows operators to use as few characters as possible ingame to form expanded words and links in the online version of the MOTD, leaving as much room as possible ingame for higher priority ingame links & other formatting.<p>
				<p>E.g. the fleetID: syntax allows 9-11 characters to generate a link that would take closer to 100 characters ingame, without being overly cryptic or confusing to those unaware of the convertion. And each online fit listing in turn saves hundreds of characters from subsequent MOTDs in fleet-specific fit-listing channels, that FCs were forced to use prior to having such online resources.</p>
				
				<br>
				
				<div class="text-muted small">
				<h3>The overall MOTD convertion (mildly technical)</h3>
				<p>This is described in an effort to assist FCs with correcting the ingame MOTD, should the online MOTD become malformed due to ingame problems.</p>
				<p>Everything prior to the (last, though there shouldn't be duplicates) visible line containing <code>Upcoming Fleets:</code> is discarded.<br>
				The entirety of said line is retained, in an effort to preserve formatting XML, such as &lt;font size=?? color=ff??????&gt;, etc.<br>
				The <code>Upcoming Fleets:</code>, <code>Special Bulletins:</code>, <code>Kills of the Day:</code> and <code>Active Fleets:</code> strings are styled with HTML header tags.<br>
				A linebreak before or after these headers will be removed.<br>
				Each line containing one of these section header strings determines how the following lines will be grouped.<br>
				Each of the 4 sections are optional, and are converted & styled differently.<br>
				Scheduled Fleet listing are converted per line, requiring date, time, FC and location patterns to match to be listed online.<br>
				The website will attempt to inform #command on Disord if there is a problem with a non-blank line found in the fleet sections.<br>
				The colour that a fleet listing line starts with determines the type of fleet (Lowsec/Nullsec/Special/etc).<br>
				The <code>fleetID:</code> component is case-insensitive.<br>
				Fleet Commander names are still detected even if the full <code>with</code> word prefix is used.<br>
				The <code>@</code> and <code>~</code> characters do not have to be followed by a space prior to the Location.<br>
				Locations are still converted to links even if the full <code>at </code>&nbsp; or <code>near </code>&nbsp; word prefixes (including a space suffix) are used.<br>
				The end of a Location or fleetID:<i>number</i> component is actually determined to be either a space, comma, linebreak, or closing &lt;/font&gt; tags.<br>
				Ingame killreport links are converted into zKillboard killmail links.<br>
				Style tags can start or end within text that forms an ingame link. Thus we attempt to exclude these tags from forming URIs such as system Location or zkillboard links, and thus preserve the tag pairings until subsequently converted or discarded.<br>
				&lt;b&gt; and &lt;/b&gt; tags are discarded.<br>
				It is assumed that the channel XML format doesn't produce nested or interleaved font tags.</p>
				</div>
				
			</div>
			
		</article>

	</div>

</div><!--/#content-->