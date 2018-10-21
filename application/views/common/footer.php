
		</div><!--#main.container-->
		
		<footer id="footer" class="footer-area">
			
			<?php if( !isset( $HIDE_LINKS ) )
			{
				$this->load->view( 'common/footer_widgets' );
			} ?>
			
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
		}
		if( isset( $TOGGLES ) )
		{ ?>
			<link rel="stylesheet" href="/vendor/bootstrap-toggle/bootstrap-toggle.css">
			<script src="/vendor/bootstrap-toggle/bootstrap-toggle.js"></script><?php
		}
		if( isset( $TABLESORTER ) )
		{ ?>
			<script src="/vendor/tablesorter/js/jquery.tablesorter.combined.js"></script><?php
		} ?>
		<script src="/js/main.js"></script><?php
		if( !isset( $NO_TRACKING ) )
		{
			$this->load->view( 'common/analyticstracking' );
		} ?>
		
	</body>
</html>