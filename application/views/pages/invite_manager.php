<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Fleet & Invite Manager Guide</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Fleet & Invite Manager Guide 2.5
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-meta">
				<span class="author">by Daneel Trevize</span>
			</div>

			<div class="entry-content">

				<p class="lead">
					When you are preparing to FC a fleet, or wish to form one on another FC's behalf, this online tool should assist you with consistently setting up the in-game fleet, as well as pinging Discord #Ops with the details, and automatically invite all those who desired such via using <a href="/invites/request">the online request feature</a>.
				</p>
				<div class="row aligncenter">
					<div class="col-md-offset-8">
						<a href="/media/image/pages/invite_manager/invite request.png"><img class="img-rounded" src="/media/image/pages/invite_manager/invite request thumb.png" style="max-height:200px;"></a>
					</div>
				</div>
				
				<h3>General usage</h3>
				
				<ol>
				
					<li>
						<ul>
							<li>Log into the Spectre Fleet website using an FC character, and be logged into Eve Online <strong>using the same character</strong>.</li>
							<li>Form a fleet in-game, <strong>ensuring that you are the Boss</strong> (have the fleet star&nbsp;<i class="fa fa-star" aria-hidden="true"></i>).</li>
						</ul>
						<br>
						<div class="row aligncenter">
							<div class="col-md-6 col-md-offset-3">
								<a href="/media/image/pages/invite_manager/manager step 1.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 1.png" style="max-height:200px;"></a>
							</div>
						</div>
					</li>
					
					<li>
						Navigate to <a href="/fleets2">the Fleet & Invite Manager</a>.<br>
						If you have not yet provided Spectre Fleet with permission to read & write to your fleets, you will be redirected to authenticate with CCP via SSO, to grant Spectre Fleet rights ("scopes") to your character & their live fleet. Please ensure you <strong>select the same character</strong> as you are using in-game and on the Spectre Fleet website.
						<br>
						<div class="row aligncenter">
							<div class="col-md-6 col-md-offset-3">
								<a href="/media/image/fleets/fleet scopes.png"><img class="img-rounded" src="/media/image/fleets/fleet scopes.png" style="max-height:200px;"></a>
							</div>
						</div>
						<br>
					</li>
					
					<li>
						The tool should now attempt to Auto-Detect your fleet, or ask you for a special URL that your Eve client can provide you with.<br>
						Either:<ul>
						<li>let the Auto-Detect Fleet feature run (it will turn off after 5 minutes);</li>
						<li>or use the in-game "Copy External Fleet Link" fleet menu option and Submit the Fleet Link to the website.</li>
						</ul>
						<br>
						<div class="row aligncenter">
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 3.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 3 thumb.png" style="max-height:200px;"></a>
							</div>
							<div class="col-md-6">
								<a href="/media/image/fleets/external fleet url 2.png"><img class="img-rounded" src="/media/image/fleets/external fleet url 2.png" style="max-height:200px;"></a>
							</div>
						</div>
						<br>
					</li>
					
					<li>
						You should be presented with a summary of your in-game fleet, grouped by hull type.<br>
						It will update at least once per minute while you <strong>remain the Fleet Boss</strong>&nbsp;<i class="fa fa-star" aria-hidden="true"></i>.<br>
						You will have some common options:
						<ul>
							<li>to Forget the Fleet URL, which will reset this invite manager tool;</li>
							<li>to kick Banned characters from fleet membership, according to the Spectre Fleet unified channels blocked list.</li>
							<li>to be provided with a Reset/Blank MOTD to use in XUP channels.</li>
						</ul>
						To form for a scheduled fleet and to manage the invites for it, select the "Form A Scheduled Fleet" button, and select which entry you are forming for, from the Upcoming Fleets listing.
						<br>
						<br>
						<div class="row aligncenter">
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 4.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 4 thumb.png" style="max-height:200px;"></a>
							</div>
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 5.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 5 thumb.png" style="max-height:200px;"></a>
							</div>
						</div>
						<br>
					</li>
					
					<li>
						You will then be presented with the summary page again, with several new of options:
						<ul>
							<li>to Forget the specific schedule entry you had chosen (use this if you made a mistake);</li>
							<li>to Manage the Invites for that chosen scheduled fleet. Click this button to progress.</li>
						</ul>
						Additionally you can choose:
						<ul>
							<li>to Ping the Discord #Ops channel with a standardised message using the chosen scheduled fleet details;</li>
							<li>to generate a specific (using the chosen scheduled fleet details) variation of the XUP channels' MOTD, to be manually used in-game;</li>
							<li>to reset your in-game fleet's MOTD to a standardised template using the chosen scheduled fleet details, and to enable Free-Move for fleet members;</li>
							<li> to list all the fits for the associated doctrine as in-game links in a temporary fleet MOTD (if the chosen scheduled fleet has a recognised online doctrine), after which the prior MOTD will be restored;</li>
						</ul>
						Example of the Discord ping message:<br>
						<div class="row aligncenter">
							<a href="/media/image/pages/invite_manager/discord ping.png"><img class="img-rounded" src="/media/image/pages/invite_manager/discord ping.png" style="max-height:200px;"></a>
						</div>
						
						Example of the actions available once a scheduled fleet has been chosed, and of the fleet MOTD changes:<br>
						<div class="row aligncenter">
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 6.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 6 thumb.png" style="max-height:200px;"></a>
							</div>
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/list fits.png"><img class="img-rounded" src="/media/image/pages/invite_manager/list fits.png" style="max-height:200px;"></a>
							</div>
						</div>
						<br>
					</li>
					
					<li>
						From the "Manage Fleet Invites" page you have several options:
						<ul>
							<li>to manually trigger the sending of invites to all those who had requested them via the online feature;</li>
							<li>to toggle on and off the automated sending of such invites, <strong>for as long as you remain on this page</strong>.</li>
						</ul>
						There is also an expandable summary of the chosen scheduled fleet's details, a summary of the overall invite requests for this scheduled fleet, and a table of all outstanding invitees for it. These details will also update at least once per minute.<br>
						<br>
						<div class="row aligncenter">
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 7.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 7 thumb.png" style="max-height:200px;"></a>
							</div>
							<div class="col-md-6">
								<a href="/media/image/pages/invite_manager/manager step 8.png"><img class="img-rounded" src="/media/image/pages/invite_manager/manager step 8 thumb.png" style="max-height:200px;"></a>
							</div>
						</div>
						<br>
						<h4><strong>Feel free to use the available options to assist with managing your fleet</strong></h4>
						<ul>
							<li>All repeatable actions are processed on a queue-basis, in the order of Set MOTD > Send Invites. The relevant button should become temporarily disabled upon use until the queued action has been completed, which can take around 5 seconds each.</li>
							<li>The Ping Discord #Ops action is not intended to be repeated for a given fleet, it will become disabled once used.</li>
						</ul>
					</li>
					
					<li>
						Once you are done with your Fleet you can either choose to "Forget Fleet URL" to reset the invite manager, or once you lose the Fleet Boss in-game role you will be redirected from the tool back to your online Portal page.
						<br>
					</li>
					
				</ol>
				
				<br>
				
				<div class="text-muted">
				<h3>Additional Notes for those interested in the technicalities</h3>
				
				<p>
					Wing and Fleet Commander positions are not utilised by this tool for the purposes of inviting new fleet members, thus a full in-game classic fleet can have a maximum capacity for 250 Squad Members (5 Wings of 5 Squads, each holding 10 Members), and 6 additional people manually assigned by the Fleet Boss to the higher positions in the fleet hierarchy.<br>
					June 2017 game changes mean you can form differing squad and wing hierarchies, but this tool only invites new fleet members to squad member positions.
				</p>
				<p>
					While there are outstanding requested actions in the queue (due to FC interaction or automated inviting), or the page has just been loaded, the details will attempt to refresh roughly every 5 seconds, as frequently as CCP's ESI API effectively permits. After a period of no page-interaction by the FC (roughly 20 seconds), the page will update roughly every 1 minute.
				</p>
				<p>
					Each invitee is handled individually, such that their total Invites Sent counters are independant. An invite will only be sent to a specific character every 1 minute, though invites may be send to other not-recently-invited characters every 5 seconds. Thus spamming the Send Invites Manually button should not spam players, and can invite newly requesting invitees faster than the 1 minute Automated Invites action may next occur.
				</p>
				<ul>
					<li>If a character is offline or already in a fleet, the API will immediately respond that the invite was rejected.</li>
					<li>If a character is online and the invite is presumably waiting on their screen to be accepted, the API will only confirm that the invite has been send, and may take up to 1 minute to be accepted or fail to be so.</li>
				</ul>
				<p>
					Once at least 15 invites have been sent to a specific character for the chosen scheduled fleet, their invites will only be sent in a batch every 5th minute of the hour. E.g. 12:20, 12:25, 12:30.
				</p>
				</div>

			</div>

		</article>

	</div>

</div><!--/#content-->