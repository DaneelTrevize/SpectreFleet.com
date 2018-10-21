<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Commanders</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Commanders
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-meta">
				<span class="author">by Jayne Fillon (Retired)</a></span>
			</div>

			<div class="entry-content">

				<p class="lead">
					Spectre Fleet is an Online Gaming Community that was founded in 2013. What started as a small group of friends who had met through Eve Online, has since become a large community with over 8000 active members from every major timezone. As a community we focus on the social aspect of gaming, providing a place where gamers can hang out and play together, with no obligations. 
				</p>
				
				<p>
					Spectre operates with different ranks, and disciplines.
				</p>


				<div class="col-md-6 col-md-offset-3 aligncenter">
					<p>
						Our Great Leader: Jayne Fillon (Retired)
					</p>
                    <img class="img-rounded" src="/media/image/pages/commanders/JayneGreatLeader.jpg" style="max-height:500px;">
					<br>
					<br>
                </div>
				
				<div class="row">
				<div class="col-md-6">
					<p>
						The "Staff" are individuals who manage the administrative side of Spectre, set policy, manage internal disputes, enforce rules and set the strategic goals for Spectre. These are currently:
						<ul><?php
							foreach( $staff as $user )
							{
								echo '<li>'.$user['CharacterName']."</li>\n";
							} ?>
						</ul>
							<!--<li>Pomagre</li>
							<li>Vorn</li>-->
					</p>
				</div>
				<div class="col-md-6">
					<p>
						The “Senior Fleet Commanders” are the individuals who are highly experienced in one or more specific combat styles, they Lead Fleets, Take in new FC's, Promote Junior FC's to FC, lead the day to day of Spectre Fleet. The current people who hold this rank are:
						
						<ul><?php
							foreach( $sorted_commanders as $FC )
							{
								if( $FC['Rank'] == Command_model::RANK_SFC )
								{
									echo '<li>'.$FC['CharacterName']."</li>\n";
								}
							} ?>
						</ul>
						
					</p>
				</div>
				</div>
				
				<p>
					The “Fleet Commanders” are the individuals who run fleets and provide content to the community on a daily basis. Fleet Commanders have either proven themselves capable, or have previous experience, and are free to run fleets whenever they want, in whatever doctrine they want. An FC is expected to be a responsible individual, be there for the JuniorFC's and his/her fleet members.
				</p>
				
				<p>
					The “Junior Fleet Commanders” are individuals who are still learning how to run fleets, and are limited to T1 Battle Cruiser and below. There is no formal training program, although many Fleet Commanders develop mentor relationships with Junior Fleet Commanders to help them learn.
				</p>

				<p>
					"Tech Support" take care of the Spectre fleet website and the services we use, such as Mumble and Discord. Contact @Tech on Discord with any issues.
				</p>

				<p>
					"Media Team" is responsible for the Spectre fleet promotion, Youtube videos, Twitch streams, etc. Contact @Media on Discord with any queries.
				</p>

				<p>
					"Arena Team" while not a permanent team, is responsible for obliterating Spectre Fleet's enemies in tournaments such as EVE_NT and the Alliance&nbsp;Tournament. Team leads for these have been Virion Stoneshard and Commander Aze (Retired).
				</p>
		
			</div>

		</article>

	</div>

</div><!--/#content-->