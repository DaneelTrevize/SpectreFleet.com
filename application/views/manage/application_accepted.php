
			<h2>Application Accepted</h2>
			
			<p>Please send the following mail to "<?php echo $Username; ?>" to inform them that they have been accepted the Spectre Fleet Command team, and given the rank of "<?php echo $Rank; ?>". They should contact you using Discord within the next 7 days for an interview. If you have any questions about the onboarding process, please talk to a current member of Staff.</p>
			
			<textarea rows="15" readonly="readonly" style="min-width:100%;" onclick="this.select();"><?php echo htmlentities( $Accepted_mail, ENT_QUOTES ); ?></textarea>
			
			<h3>Interview Process</h3>
			
			<p>If the applicant fails the interview, thank them for their time, and remove them from the FC list using the Admin tools on the User Portal.</p>
			
			<p>If the applicant passes the interview, invite them to the "Spectre Command" channel and give them FC roles on Discord, ensure they know when to use the in-game command channel, and when to use the #command channel on Discord. Ask them to introduce themselves to the rest of the command team.</p>
			
			<p>Grant the applicant JFC roles on Mumble, and explain that this will allow them to mute people who are disrupting their fleet, but that should only be used as a last resort. Explain the setup of the various channels, as well as how the "quiet" channel works.</p>
			
			<p>Explain that they will not have MOTD permission until after their first successful fleet. They can ask any member of the Command Team to schedule their first fleet by placing it in the main channel's MOTD, and then again helping them again with the XUP channels once the time comes to actually run their fleet.</p>
			
			<p>If the feedback received after the trial fleet is positive, then MOTD permissions for both the main channel and the XUP channels will be given, and the applicant will be considered a Junior Fleet Commander with all applicable roles and privileges. If the feedback received after the trial fleet is negative, then remove the member from the "SF Spectre Command" channel and the FC channels & roles on Discord.</p>
