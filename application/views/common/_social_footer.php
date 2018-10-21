		</div><!--#main.container-->
		<footer id="footer" class="footer-area">
			
			<div class="footer-top container">
				<div class="row">
					<div class="widget col-xs-12 col-sm-4">

						<h4 class="widget-title">Quick Links</h4>

						<ul class="entries links links-2-cols">
							<li><a href="/activity/fleets">Fleet Schedule</a></li>
							<li><a href="/doctrine">Doctrines & Fits</a></li>
							<li><a href="/feedback">Submit Feedback</a></li>
							<li><a href="/activity/FC/">FC Profiles</a></li>
							<li><a href="/articles">Articles</a></li>
							<li><a href="/polls">Polls</a></li>
							<li><a href="/mumble">Mumble</a></li>
							<li><a href="/portal">User Portal</a></li>
						</ul>

					</div><!--/.col-4-->

					<div class="clearfix visible-xs"></div>

					<div class="widget col-xs-6 col-sm-2">

						<h4 class="widget-title">Information</h4>

						<ul class="entries links links">
							<li><a href="/about"><span class="fa fa-info-circle fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;About</a></li>
							<li><a href="/contact"><span class="fa fa-phone-square fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;Contact</a></li>
							<li><a href="/advertise"><span class="fa fa-file-image-o fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;Advertise </a></li>
							<li><a href="/submit"><span class="fa fa-file-text-o fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;Submit</a></li>
						</ul>

					</div><!--/.col-2-->

					<div class="widget col-xs-6 col-sm-2">

						<h4 class="widget-title">Follow Us</h4>

						<ul class="entries links">
							<li><a href="https://www.twitch.tv/spectrefleet"><span class="fa fa-twitch fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;Twitch</a></li>
							<li><a href="https://twitter.com/SpectreFleet"><span class="fa fa-twitter fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;Twitter</a></li>
							<li><a href="https://www.youtube.com/c/SpectreFleetGaming"><span class="fa fa-youtube fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;YouTube</a></li>
							<li><a href="/rss"><span class="fa fa-rss fa-fw fa-2x" style="vertical-align:middle;"></span>&nbsp;RSS<span class="hidden-sm"> (Articles)</span></a></li>
						</ul>
						
						<!-- For Website Crawlers -->
						<a href="https://www.facebook.com/SpectreFleet/"></a>
						<a href="https://plus.google.com/+SpectreFleetGaming"></a>
						
					</div><!--/.col-2-->

					<div class="clearfix visible-xs"></div>
					
					<div class="col-xs-12 col-sm-4">
						<iframe
							style="width:100%; margin:auto; min-height:193px;"
							src="/social"
							scrolling="no"
							frameborder="0">
						</iframe>
					</div><!--/.col-4-->
					
				</div><!--row.-->
			</div>

			<div class="footer-bottom">

				<div class="container aligncenter">

					<p>&copy;2018 by Spectre Fleet Gaming Corp. All Right Reserved. <p>

				</div>

			</div>

		</footer>
		
		<!-- Scripts -->
		<script src="/vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="/vendor/bootstrap-submenu/js/bootstrap-submenu.min.js"></script><?php
		if( isset( $SELECT2 ) )
		{ ?>
			<script src="/vendor/select2/dist/js/select2.min.js"></script><?php
		}
		if( isset( $CKEDITOR ) )
		{ ?>
			<script src="/vendor/ckeditor/ckeditor.js"></script><?php
		} ?>
		<script src="/js/main.js"></script><?php
		if( !isset( $NO_TRACKING ) )
		{
			$this->load->view( 'common/analyticstracking' );
		} ?>
		
	</body>
</html>