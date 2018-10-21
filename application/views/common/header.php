<!DOCTYPE html>
<html class="no-js">
	<head>
		<meta charset="utf-8">
		
		<title><?php if( isset( $PAGE_TITLE ) )
		{
			echo $PAGE_TITLE . ' | ';
		} ?>Spectre Fleet</title>
		
		<meta name="author" content="<?php if( isset( $PAGE_AUTHOR ) )
		{
			echo $PAGE_AUTHOR;
		}
		else
		{
			echo 'Spectre Fleet';
		} ?>">
		<meta name="keywords" content="Spectre, Fleet, PvP, Eve Online, NPSI">
		<meta name="description" content="<?php if( isset( $PAGE_DESC ) )
		{
			echo $PAGE_DESC;
		}
		else
		{
			echo 'Spectre Fleet is Eve Online\'s largest public community with over 13,000 members from every major timezone.';
		} ?>">
		
		<!-- Mobile Devices Viewport Reset-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes">
		
		<link rel="icon" type="image/png" href="/media/image/logo/favicon_purple_32px.png">
		<link rel="apple-touch-icon" href="/media/image/logo/brandmark_purple_512px_transparent.png">
		
		<link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.css">
		<link rel="stylesheet" href="/vendor/bootstrap-submenu/css/bootstrap-submenu.min.css">
		<link rel="stylesheet" href="/vendor/font-awesome/css/font-awesome.min.css">
		
		<!-- Google Fonts -->
		<link href='https://fonts.googleapis.com/css?family=Electrolize' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Lato:400,700,400italic,300' rel='stylesheet' type='text/css'>

		<!-- Stylesheets -->
		<link rel="stylesheet" href="/css/base.css">
		<link rel="stylesheet" href="/css/components.css">
		<link rel="stylesheet" href="/css/jquery-ui.min.css">
		<link rel="stylesheet" href="/css/jquery-ui.structure.min.css">
		<link rel="stylesheet" href="/css/jquery-ui.theme.min.css">
		<link rel="stylesheet" href="/vendor/select2/dist/css/select2.css">
		<link rel="stylesheet" href="/css/theme/dark.css">
		<link rel="stylesheet" href="/css/custom.css">
		
		<!-- Scripts -->
		<script src="/js/jquery-3.3.1.min.js"></script>
		<script src="/js/jquery-ui.min.js"></script>
		<?php
		if( !isset( $NO_LOGGEDIN ) )
		{ ?>
			<script src="/js/start_logged_in.js"></script><?php
		}
		if( isset( $UNVEIL ) )
		{ ?>
			<script src="/js/jquery-migrate-3.0.0.min.js"></script><!-- only for unveil now it seems -->
			<script src="/js/jquery.unveil.min.js"></script>
			<script src="/js/start_unveil.js"></script><?php
		}
		if( isset( $CAROUSEL ) )
		{ ?>
			<script src="/js/start_carousel.js"></script><?php
		} ?>
	</head>
<?php
$this->load->view( 'common/main_menu' );

$this->load->view( 'common/main_content' ); ?>