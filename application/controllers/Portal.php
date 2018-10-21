<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Portal extends SF_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model( 'Command_model' );
		$this->load->library( 'Authorization' );
		$this->load->model( 'Killmails_model' );
	}// __construct()

	public function index()
	{
		$this->_ensure_logged_in();
		
		$this->load->helper( 'discord' );
        $this->config->load( 'discord' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_discord'), 'oauth_discord' );
		$this->load->model( 'Discord_model' );
		
		$UserID = $this->session->user_session['UserID'];
		
		$fleetRole = NULL;
		$editorRole = NULL;
		$adminRole = NULL;
		$roles = $this->User_model->get_roles_by_UserID( $UserID );
		if( $roles != FALSE )
		{
			$fleetRole = $roles->Rank;
			$editorRole = $roles->Editor;
			$adminRole = $roles->Admin;
		}
		$data['Rank'] = $fleetRole;
		$data['RankName'] = Command_model::RANK_NAMES()[$fleetRole];
		$data['Editor'] = $editorRole;
		$data['EditorRoleName'] = Editor_model::ROLE_NAMES()[$editorRole];
		$data['Admin'] = $adminRole;
		$data['AdminRoleName'] = User_model::ADMIN_NAMES()[$adminRole];
		
		$groups =  $this->User_model->get_groups_by_UserID( $UserID );
		$data['groups'] = $groups;
		
		$should_have_discord_identity = Discord_model::should_have_identity( $fleetRole, $editorRole, $adminRole, $groups );
		$data['should_have_discord_identity'] = $should_have_discord_identity;
		if( $should_have_discord_identity )
		{
			$discord_data = array(
				'DiscordID' => NULL,
				'member_data' => array(),
				'roles_data' => array()
			);
			$auth_data = $this->Discord_model->get_auth_data( $UserID );
			if( $auth_data !== FALSE )
			{
				$DiscordID = $auth_data['DiscordID'];
				
				$member_data = $this->Discord_model->get_guild_member_data( $DiscordID, $this->config->item('discord_bot') );
				if( $member_data === FALSE )
				{
					log_message( 'error', 'Portal controller: Removing bad Discord Identity for userID:'.$UserID.'.' );
					$this->Discord_model->delete_auth_data( $UserID );
				}
				else
				{
					$discord_data = array(
						'DiscordID' => $DiscordID,
						'member_data' => $member_data,
						'roles_data' => $this->Discord_model->get_roles()
					);
				}
			}
			
			$data['discord_html'] = $this->load->view( 'discordauth/status', $discord_data, TRUE );
		}
		else
		{
			$data['discord_html'] = '';
		}
		
		$permissions = $this->_get_permissions();
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Portal' ) );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $permissions );
		$this->load->view('portal/portal_content' );
		$this->load->view('portal/index', $data );
		$this->load->view('portal/portal_footer' );
		$this->load->view('common/footer');
		
	}// index()
	
	public function logged_in_json()
	{
		//$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$is_logged_in = $this->_is_logged_in();
		$data['is_logged_in'] = $is_logged_in;
		if( $is_logged_in )
		{
			$data['character_id'] = $this->session->user_session['CharacterID'];
			$data['character_name'] = $this->session->user_session['CharacterName'];
			
			// Refresh permissions, to keep up to date regardless of serving cached pages
			$this->session->permissions = $this->authorization->get_user_permissions( $this->session->user_session['UserID']);
		}
		$data['portal_dropdown_menu'] = $this->load->view( 'portal/portal_dropdown_menu', $this->_get_permissions(), TRUE );
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// logged_in_json()
	
	public function command_team()
	{
		$this->_ensure_logged_in();
		
		$rank_names = Command_model::RANK_NAMES();
		$data['rank_names'] = $rank_names;
		$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Command Team' ) );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $this->_get_permissions() );
		$this->load->view('portal/portal_content' );
		$this->load->view('portal/command_team', $data);
		$this->load->view('portal/portal_footer' );
		$this->load->view('common/footer');
		
	}// command_team()

	public function debugging()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DEBUGGING' );
		
		//$this->db_ro = $this->load->database( 'readonly_limited', TRUE );
		$data['db_platform'] = $this->db_ro->platform();
		$data['db_version'] = $this->db_ro->version();
		$query = $this->db_ro->query( 'SELECT CURRENT_TIMESTAMP as now' );
		$row = $query->row_array();
		$data['db_datetime'] = $row['now'];
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Debugging' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'portal/debugging', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// debugging()

	public function testing()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SET_CHANNEL_TOKENS' );
		
		$data = array(
			'dt_string' => $this->_eve_now_dtz()->format( 'Y-m-d H:i:s' ) . '+00',	// "UTC TZ, in format not supported by date()"
			'dtz_string' => $this->_eve_now_dtz()->format( 'Y-m-d H:i:se' )			// Gains "UTC" suffix instead of "+00"
		);
		/*
		$content = 'test';
		//$this->Discord_model->tell_directorate( $content );
		*/
		
		/*
		$kills = array(
			66825520 => 'edaa8368cac9c3acd0cc15c3c60accf2aafe3750',
			70260815 => 'ae8ebf2692f979436818b946d3f05993d2de90ec',
			70554381 => '79dc1aee58047fcf22e0a0ef8a688d5f533e229a'
		);
		foreach( $kills as $ID => $hash )
		{
			$this->Killmails_model->store_kill( $ID, $hash );
		}
		*/
		
		/*$l = openssl_cipher_iv_length( 'aes-256-ctr' );
		$c = strval( 1416844877 );
		$iv = str_pad( $c, $l, $c, STR_PAD_RIGHT );*/
		//$data['test_string'] = $l.':'.$iv;
		
		
		//$this->db_ro = $this->load->database( 'readonly_limited', TRUE );
		/*$query = $this->db_ro->query( 'SELECT current_user' );
		$data['test_string'] = '<pre>'. print_r( $query->row_array(), TRUE ) .'</pre>';*/
		
		$data['test_string'] = $this->_is_local_request() ? 'Local request' : 'Request from '. $_SERVER['REMOTE_ADDR'];
		
		$permissions = $this->_get_permissions();
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Testing' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $permissions );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'portal/testing', array_merge( $permissions, $data ) );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// testing()
	
	public function meetings( $meeting = NULL )
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_ACCESS_COMMAND_MEETINGS' );
		
		$this->load->helper('file');
		
		$MEETINGS_DIRECTORY = './media/audio/meetings/';
		
		if( $meeting != NULL )
		{
			$meeting = rawurldecode( $meeting );
			// Validate meeting filename and serve contents
			if( !preg_match( '#^[a-zA-Z0-9 -_]+\.mp3$#', $meeting ) )
			{
				self::_not_found();
			}
			$meeting_file = $MEETINGS_DIRECTORY.$meeting;
			if( !file_exists( $meeting_file ) )
			{
				self::_not_found();
			}
			
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			//header( 'Content-Disposition: attachment; filename='.$meeting );
			header( 'Content-Transfer-Encoding: binary' );
			/*header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );*/
			header( 'Content-Length: ' . filesize( $meeting_file ) );
			// Avoid buffering large files in PHP
			if( ob_get_level() )
			{
				ob_end_clean();
			}
			flush();
			readfile( $meeting_file );
			return;
		}
		else
		{
			// Display meetings listing
			
			$data['meetings'] = get_dir_file_info( $MEETINGS_DIRECTORY );
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Command Meetings' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('portal/meetings', $data );
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
	}// meetings()
	
}// Portal
?>