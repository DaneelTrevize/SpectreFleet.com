<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Mumble</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Mumble
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-meta">
				<span class="author">by Jayne Fillon (Retired)</span>
			</div>

			<div class="entry-content">

				<p class="lead">
					Mumble is a cross-platform voice communication tool that allows members of Spectre Fleet to talk with one another while playing Eve Online, or any other game. Capable of handling a large number of consecutive users, Mumble provides high quality, low-latency voice chat with encrypted identities for users, among other fancy features. Prior registration is not required. 
				</p>
				
				<p>
					If you do not already have Mumble installed, you can download the client directly from the <a href="https://github.com/mumble-voip/mumble/releases">Mumble GitHub Project</a>. If you're using Windows, download the <code>.msi</code> file. If you're using Mac, download the <code>.dmg</code> file. The official documentation can be found on the <a href="https://wiki.mumble.info/wiki/Main_Page">Mumble Wiki</a> - it is important to note that Mumble.com is neither owned nor operated by the creators of Mumble. 
				</p>
				
				<h3>Connecting to Mumble</h3>
				
				<p>
					Once you have the downloaded and opened the Mumble client, you should immediately see the <code>Mumble Server Connect</code> window. Then, click on the <code>Add New...</code> button and you should see the <code>Add Server</code> window appear.
				</p>
				
				<div class="aligncenter">
					<img class="img-rounded" src="/media/image/pages/mumble/cxkRokg.png" style="max-height:200px;">
					<img class="img-rounded" src="/media/image/pages/mumble/x62rfNS.png" style="max-height:200px;"><br>
				</div>
				
				<p>
					Input the details as show above, replacing <code>Your Username</code> with your in-game Character's name. Please do not include any Corporation or Alliance tags when you connect. If you have done everything correctly, you should now be located in the <code>Lobby</code> channel on the Spectre Fleet Mumble Server.
				</p>
				
				<img class="img-rounded" src="/media/image/pages/mumble/eCVnoqE.png" style="align:middle;margin:auto;display:block;max-height:200px;"><br>
				
				<p>
					It is highly recommended that you run both the Audio Wizard and the Certificate Wizard if you have never used Mumble before. They can be found in the <code>Configure</code> dropdown menu. A certificate is required in order for you to register your username of for you to be granted any permissions on the server.
				</p>
				
				<h3>Communicating with tens or hundreds of people in a single channel</h3>
				
				<p>Please ensure you choose Push-To-Talk rather than Voice Activated Detection to control your transmissions to your current channel. If you have a physical configuration involving speakers and a mic, or some other issue causing looping/feedback, or just accidentally frequently 'key-up' in general (ensure your PTT key binding isn't something you otherwise often use while playing Eve), you may find yourself temporarily muted on the Mumble server until you confirm you have resolved your issue.</p>
				
				<h3>Whisper and Shout</h3>
				
				<p>
					All fleet channels on the Spectre Fleet Mumble Server are linked together in order to provide a proper communication structure. In order to properly utilize this setup, you'll need to set a whisper key.
				</p>

				<div class="tab-content">
					<div class="tab-pane active" id="VAD">
						Open the Shortcuts tab of the Mumble Configuration window located at <code>Configure > Settings > Shortcuts</code> and click on the <code>Add</code> button, located at the bottom of the window. Under <code>Function</code> select <code>Whisper/Shout</code>, and set your desired whisper hotkey using the <code>Shortcut</code> column. Finally, in the <code>Data</code> column, click once, then click the <code>...</code> in order to open the <code>Whisper Target</code> window.<br><br>
								
						<img class="img-rounded" src="/media/image/pages/mumble/LFKCDjq.png" style="align:middle;margin:auto;display:block;max-height:400px;"><br>
						
						Select the <code>Shout to Channel</code> radio button, then select <code>> Current</code> from the <code>Channel Target</code> section. Press <code>OK</code>. If you've done everything correctly, then your shortcuts should look similar to the picture bellow.<br><br>
						
						<img class="img-rounded" src="/media/image/pages/mumble/b6xdFDg.png" style="align:middle;margin:auto;display:block;max-height:200px;">
					</div>
					<div class="tab-pane" id="PTT">
						Open the Shortcuts tab of the Mumble Configuration window located at <code>Configure > Settings > Shortcuts</code> and click on the <code>Add</code> button, located at the bottom of the window. Under <code>Function</code> select <code>Whisper/Shout</code>, and set your desired whisper hotkey using the <code>Shortcut</code> column. Finally, in the <code>Data</code> column, click once, then click the <code>...</code> in order to open the <code>Whisper Target</code> window.<br><br>
								
						<img class="img-rounded" src="/media/image/pages/mumble/LFKCDjq.png" style="margin:auto;display:block;max-height:400px;"><br>
						
						Select the <code>Shout to Channel</code> radio button, then select <code>> Current</code> from the <code>Channel Target</code> section. Press <code>OK</code>. Following this, add another Shortcut with the <code>Function</code> set to <code>Push-to-Talk</code> instead of <code>Whisper/Shout</code>, and bind it to a different hotkey. If you've done everything correctly, then your shortcuts should now look similar to the picture bellow.<br><br>
						
						<img class="img-rounded" src="/media/image/pages/mumble/wkhIFg5.png" style="align:middle;margin:auto;display:block;max-height:200px;">
					</div>
				</div>
		
				<h3>Message Notifications</h3>
				
				<p>
					Most people find the default notification settings for Mumble extremely annoying due to the inclusion of <code>Text-To-Speech</code> being the default configuration. In order to disable this as well as most unecessary audio notifications open the <code>Mumble Configuration</code> window again, and click the <code>Advanced</code> box in the bottom left corner. Open the <code>Messages</code> section that has just appeared, and customize to your liking. A recommended setting is shown bellow.
				</p>

				<img class="img-rounded" src="/media/image/pages/mumble/bojOrGM.png" style="margin:auto;display:block;max-height:500px;">
				
				<h3>Fleet Operations</h3>
				
				<p>
					Once you've setup Mumble correctly and join a Fleet, you'll see a number of named channels. Join the channel that best describes your role in the fleet. If you are unsure which channel you should join, then join the <code>Primary</code> channel until your fleet commander instructs you otherwise. If you are in one of the named <code>Fleet Operations</code> channels, you'll see a special channel called <code>Quiet</code>. This channel mutes all other channels except for those with Fleet Commander permissions. Usage of this channel is completely optional.
				</p>
				
				<p>
					By default, you will be able to speak to and hear all of the channels that you are linked with. In order to speak with only those members of your channel, use the Whisper key that you just setup. This allows for groups such as Logistics or Capitals to coordinate amongst themselves without interrupting the rest of the fleet.
				</p>

				<h3>Backing up Mumble credentials</h3>

                <p>
                	Mumble uses certificates as the key for identification. If you want to continue to have your own name reserved, or if you are an FC and want your rights on another computer, you need to backup your Mumble certificate. Backing up allows you to import the certificate on a new machine or on a new installation of the same machine, while keeping your name and any existing rights.
                </p>

                <p>
                    See the images below for how to backup the certificate. Go to the top menu on Mumble and click <code>Configure</code>. A drop-down menu will open, select <code>Certificate Wizard</code>. A popup will open, select <code>Export current certificate</code>. In the next screen select <code>Save as</code> to wherever you can ensure you keep it safe and secure. Mail it to yourself, put it on a USB stick, whatever works best for you, but do not lose it or hand it to other people!
                </p>

                <div class="aligncenter">
                	<img class="img-rounded" src="/media/image/pages/mumble/mumblecertwizardmenu.png" style="max-height:300px;">
                	<img class="img-rounded" src="/media/image/pages/mumble/mumbleexportcert.png" style="max-height:400px;"><br>
                	<img class="img-rounded" src="/media/image/pages/mumble/mumblesavecert.png" style="max-height:400px;"><br>
                </div>

                <p>
                    To now import the certificate on another machine or on a new installation, go to the top menu on Mumble again. Select <code>Configure</code>. In the popup go to <code>Import certificate</code>. In the next screen, select the certificate from your mail or desktop, OneDrive, etc using the <code>Open</code> button. Now, just like magic, you have your own name ownership and Mumble rights restored on a different machine or installation.
                </p>

                <div class="aligncenter">
                	<img class="img-rounded" src="/media/image/pages/mumble/mumbleimportcert.png" style="max-height:400px;"><br>
                	<img class="img-rounded" src="/media/image/pages/mumble/mumbleopencert.png" style="max-height:400px;"><br>
                </div>

			</div>

		</article>

	</div>

</div><!--/#content-->