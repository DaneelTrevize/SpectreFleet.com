<?php
class Activity extends SF_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Channel_model');
		$this->load->model('Activity_model');
		$this->load->model('Killmails_model');
		$this->load->model('User_model');
		$this->load->model('Command_model');
		$this->load->model('Doctrine_model');
	}// __construct()
	
	
	public function index()
	{
		self::fleets();
	}// index()
	
	public function fleets()
	{
		$data['currentEVEtime'] = self::get_current_EVEtime();
		
		$future_fleets_data['fleets'] = $this->Activity_model->get_future_fleets( NULL, TRUE );
		$future_fleets_data['single_FC'] = FALSE;
		$future_fleets_data['HIGHLIGHT_NEXT_FLEET'] = TRUE;
		$future_fleets_data['currentEVEtime'] = $this->_eve_now_dtz();
		$future_html = $this->load->view( 'activity/scheduled_fleets_table', $future_fleets_data, TRUE );
		$data['future_html'] = $future_html;
		
		$data['bulletins_html'] = '';
		$motd = $this->Channel_model->get_latest_MOTD();
		if( $motd === FALSE )
		{
			log_message( 'error', 'Activity controller: problem calling Channel_model->get_latest_MOTD().' );
		}
		else
		{
			$data['bulletins_html'] = $motd['data']['bulletins_html'];
		}
		
		$active_fleets_data['active_fleets'] = $this->Activity_model->get_latest_active_fleets();
		$active_html = $this->load->view( 'activity/current_active_table', $active_fleets_data, TRUE );
		$data['active_html'] = $active_html;
		
		$past_fleets_data['fleets'] = $this->Activity_model->get_past_interlinked_fleets();
		$past_interlinked_html = $this->load->view( 'activity/past_interlinked_fleets_table', $past_fleets_data, TRUE );
		$data['past_interlinked_html'] = $past_interlinked_html;
		
		$top_isk_kills = $this->Killmails_model->get_top_isk_killmails();
		foreach( $top_isk_kills as $period => $enhanced_kills )
		{
			$kills_data = array(
				'kills' => array(),
				'enhanced_kills' => $enhanced_kills
			);
			$kills_html = $this->load->view( 'activity/kills_table', $kills_data, TRUE );
			$data[$period.'_kills_html'] = $kills_html;
		}
		
		
		$parsedHTML = $this->load->view( 'activity/fleets_summary', $data, TRUE );
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header' );
		$this->output->append_output( $parsedHTML );
		$this->load->view( 'common/footer' );
	}// fleets()
	
	private function get_current_EVEtime()
	{
		return $this->_eve_now_dtz()->format( Channel_model::DATETIME_DISPLAY_FORMAT );
	}// get_current_EVEtime()
	
	
	public function FCs()
	{
		$ranks_data['rank_names'] = Command_model::RANK_NAMES();
		$ranks_data['ranks_changes'] = $this->User_model->get_recent_public_rank_changes();
		$data['ranks_html'] = $this->load->view( 'activity/ranks_table', $ranks_data, TRUE );
		
		$doctrines_data['doctrines_changes'] = $this->Doctrine_model->get_recent_public_changes();
		$doctrines_data['single_FC'] = FALSE;
		$data['doctrines_html'] = $this->load->view( 'activity/doctrines_table', $doctrines_data, TRUE );
		
		
		$parsedHTML = $this->load->view( 'activity/FCs_summary', $data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent FC Activities' ) );
		$this->output->append_output( $parsedHTML );
		$this->load->view( 'common/footer' );
	}// FCs()
	
	public function recent_fleets()
	{
		$fleets_data['recent_favoured_scheduled_locations'] = $this->Activity_model->get_recent_favoured_scheduled_locations( NULL, 10 );
		$data['recent_favoured_scheduled_locations_html'] = $this->load->view( 'activity/favourite_locations_table', $fleets_data, TRUE );
		$fleets_data['recent_favoured_scheduled_doctrines'] = $this->Activity_model->get_recent_favoured_scheduled_doctrines( NULL, 10 );
		$data['recent_favoured_scheduled_doctrines_html'] = $this->load->view( 'activity/favourite_doctrines_table', $fleets_data, TRUE );
		
		$fleets_data['recent_favoured_scheduled_timezones'] = $this->Activity_model->get_recent_favoured_scheduled_timezones();
		$data['recent_favoured_scheduled_timezones_html'] = $this->load->view( 'activity/favourite_timezones_table', $fleets_data, TRUE );
		
		$past_fleets_data['fleets'] = $this->Activity_model->get_past_interlinked_fleets();
		$past_interlinked_html = $this->load->view( 'activity/past_interlinked_fleets_table', $past_fleets_data, TRUE );
		$data['past_interlinked_html'] = $past_interlinked_html;
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Fleets History' ) );
		$this->load->view( 'activity/recent_fleets', $data );
		$this->load->view( 'common/footer' );
	}// recent_fleets()
	
	public function recent_scheduled_fleets()
	{
		$fleets_data['fleets'] = $this->Activity_model->get_recent_scheduled_fleets();
		$fleets_data['single_FC'] = FALSE;
		$fleets_html = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		$data['header'] = 'Recently Past Scheduled Fleets';
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Scheduled Fleets' ) );
		$this->load->view( 'activity/scheduled_fleets', $data );
		$this->load->view( 'common/footer' );
	}// recent_scheduled_fleets()
	
	public function cancelled_scheduled_fleets()
	{
		$fleets_data['fleets'] = $this->Activity_model->get_cancelled_scheduled_fleets();
		$fleets_data['single_FC'] = FALSE;
		$fleets_html = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		$data['header'] = 'Cancelled Past Scheduled Fleets';
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Cancelled Fleets' ) );
		$this->load->view( 'activity/scheduled_fleets', $data );
		$this->load->view( 'common/footer' );
	}// cancelled_scheduled_fleets()
	
	public function future_fleets()
	{
		$fleets_data['fleets'] = $this->Activity_model->get_future_fleets();
		$fleets_data['single_FC'] = FALSE;
		$fleets_data['HIGHLIGHT_NEXT_FLEET'] = TRUE;
		$fleets_data['currentEVEtime'] = $this->_eve_now_dtz();
		$fleets_html = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		$data['header'] = 'Future Scheduled Fleets';
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Future Scheduled Fleets' ) );
		$this->load->view( 'activity/scheduled_fleets', $data );
		$this->load->view( 'common/footer' );
	}// future_fleets()
	
	public function cancelled_future_fleets()
	{
		$fleets_data['fleets'] = $this->Activity_model->get_cancelled_future_fleets();
		$fleets_data['single_FC'] = FALSE;
		$fleets_html = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		$data['header'] = 'Cancelled Future Scheduled Fleets';
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Cancelled Future Fleets' ) );
		$this->load->view( 'activity/scheduled_fleets', $data );
		$this->load->view( 'common/footer' );
	}// cancelled_future_fleets()
	
	
	public function recent_active_fleets()
	{
		$fleets_data['fleets'] = $this->Activity_model->get_recent_active_fleets();
		$fleets_data['single_FC'] = FALSE;
		$fleets_html = $this->load->view( 'activity/active_table2', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Active Fleets' ) );
		$this->load->view( 'activity/active_fleets', $data );
		$this->load->view( 'common/footer' );
	}// recent_active_fleets()
	
	
	public function recent_kills()
	{
		$kills_data['kills'] = $this->Killmails_model->get_recent_killmails();
		$kills_html = $this->load->view( 'activity/kills_table2', $kills_data, TRUE );
		$data['kills_html'] = $kills_html;
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Highlighted Kills' ) );
		$this->load->view( 'activity/kills', $data );
		$this->load->view( 'common/footer' );
	}// recent_kills()
	
	public function FC( $FC_ID = NULL )
	{
		if( $FC_ID == NULL )
		{
			$FC_ID = $this->input->post( 'FC_ID' );
			
			if( $FC_ID != NULL )
			{
				redirect( 'activity/FC/'.$FC_ID, 'location' );
			}
			
			$data['rank_names'] = Command_model::RANK_NAMES();
			$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Search FC Profiles' ) );
			$this->load->view( 'activity/fc_search', $data );
			$this->load->view( 'common/footer', array( 'SELECT2' => TRUE ) );
			return;
		}
		
		if( !self::_is_integer_string( $FC_ID ) )
		{
			self::_not_found();
		}
		
		if( !$this->User_model->was_user_an_FC( $FC_ID ) )
		{
			self::_not_found();
		}
		
		$FC_data = $this->User_model->get_user_data_by_ID( $FC_ID );
		if( $FC_data === FALSE )
		{
			self::_not_found();
		}
		
		$DateRankChange = $this->User_model->get_latest_rank_change( $FC_ID );
		
		/*
		$activity = $this->Activity_model->get_recent_activity( $FC_ID );
		if( $activity === FALSE )
		{
			self::_not_found();
		}*/
		$data['FC_ID'] = $FC_ID;
		$data['CharacterName'] = $FC_data->CharacterName;
		$data['CharacterID'] = $FC_data->CharacterID;
		$data['DateRegistered'] = $FC_data->DateRegistered;
		$Rank = $FC_data->Rank;
		$ex_FC = ( $Rank == Command_model::RANK_MEMBER );
		//$data['Rank'] = $Rank;
		$data['ex_FC'] = $ex_FC;
		$data['RankName'] = Command_model::RANK_NAMES()[$Rank];
		$data['DateRankChange'] = $DateRankChange;
		
		$fleets_data['recent_favoured_scheduled_locations'] = $this->Activity_model->get_recent_favoured_scheduled_locations( $FC_ID, 3 );
		$data['recent_favoured_scheduled_locations_html'] = $this->load->view( 'activity/favourite_locations_table', $fleets_data, TRUE );
		
		$fleets_data['recent_favoured_scheduled_doctrines'] = $this->Activity_model->get_recent_favoured_scheduled_doctrines( $FC_ID, 3 );
		$data['recent_favoured_scheduled_doctrines_html'] = $this->load->view( 'activity/favourite_doctrines_table', $fleets_data, TRUE );
		
		$fleets_data['recent_favoured_scheduled_timezones'] = $this->Activity_model->get_recent_favoured_scheduled_timezones( $FC_ID );
		$data['recent_favoured_scheduled_timezones_html'] = $this->load->view( 'activity/favourite_timezones_table', $fleets_data, TRUE );
		
		$fleets_data['single_FC'] = TRUE;
		
		$fleets_data['fleets'] = $this->Activity_model->get_recent_scheduled_fleets( $FC_ID );
		$data['recent_scheduled_fleets_html'] = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		
		$fleets_data['fleets'] = $this->Activity_model->get_cancelled_scheduled_fleets( $FC_ID );
		$data['cancelled_scheduled_fleets_html'] = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		if( $ex_FC )
		{
			$data['future_fleets_html'] = '';
		}
		else
		{
			$fleets_data['fleets'] = $this->Activity_model->get_future_fleets( $FC_ID );
			$data['future_fleets_html'] = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		}
		
		$fleets_data['fleets'] = $this->Activity_model->get_cancelled_future_fleets( $FC_ID );
		$data['cancelled_future_fleets_html'] = $this->load->view( 'activity/scheduled_fleets_table', $fleets_data, TRUE );
		/*
		$fleets_data['fleets'] = $this->Activity_model->get_recent_active_fleets( $FC_ID );
		$data['active_fleets_html'] = $this->load->view( 'activity/active_table2', $fleets_data, TRUE );
		*/
		$doctrines_data['doctrines_changes'] = $this->Doctrine_model->get_recent_public_changes( $FC_ID );
		$doctrines_data['single_FC'] = TRUE;
		$data['doctrines_html'] = $this->load->view( 'activity/doctrines_table', $doctrines_data, TRUE );
		
		if( $ex_FC )
		{
			$this->output->cache( 1440 );	// 1 day in minutes
		}
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => $data['CharacterName'] . '\'s Profile',
			'PAGE_AUTHOR' => $data['CharacterName'] )
		);
		$this->load->view( 'activity/view_FC', $data );
		$this->load->view( 'common/footer' );
		
	}// FC()
	
	
	public function fleet( $date_string = NULL, $time_string = NULL )
	{
		if( empty( $date_string ) || empty( $time_string ) )
		{
			self::_not_found();
		}
		
		$datetime = DateTime::createFromFormat( 'Y-m-d H:i', $date_string.' '.$time_string );
		if( $datetime == FALSE )
		{
			self::_not_found();
		}
		
		$fleet_data = $this->Activity_model->get_scheduled_fleet( $datetime->format( Activity_model::DATETIME_DB_FORMAT ) );
		if( $fleet_data == FALSE )
		{
			log_message( 'error', 'Activity controller::fleet() no fleet found for '. $datetime->format( Activity_model::DATETIME_DB_FORMAT ) );
			self::_not_found();
		}
		
		$data = $fleet_data;
		
		$data['latestDetected'] = $this->Activity_model->get_latest_MOTD_merge();
		$data['currentEVEtime'] = $this->_eve_now_dtz();
		
		$data['fleet'] = NULL;
		$doctrineID = $fleet_data['doctrineID'];
		if( $doctrineID != NULL )
		{
			$fleet = $this->Doctrine_model->get_fleet_info( $doctrineID );
			if( $fleet != FALSE )
				{
				
				$fitIDs = $this->Doctrine_model->get_fleet_fitIDs( $doctrineID );
				$shipIDs = [];
				foreach($fitIDs as $fit)
				{
					$fitInfo = $this->Doctrine_model->get_fit_info( $fit['fitID'] );
					if( $fitInfo !== FALSE )
					{
						$shipIDs[] = $fitInfo['shipID'];
					}
				}
				$fleet['shipIDs'] = $shipIDs;
			
				$data['fleet'] = $fleet;
			}
		}
		
		$data['kills_html'] = '';
		if( $fleet_data['af_firstDetected'] != NULL )
		{
			$kills = $this->Killmails_model->get_killmails_for_fleet( $fleet_data['af_firstDetected'], $fleet_data['XUPNumber'] );
			$data['af_lastDetected'] = $kills[0]->lastDetected;
			if( $kills[0]->ID != NULL )
			{
				$kills_data['kills'] = $kills;
				$kills_html = $this->load->view( 'activity/kills_table2', $kills_data, TRUE );
				$data['kills_html'] = $kills_html;
			}
		}
		
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => 'Fleet Details',
			'PAGE_AUTHOR' => $data['CharacterName'] )
		);
		$this->load->view( 'activity/view_scheduled_fleet', $data );
		$this->load->view( 'common/footer' );
		
	}// fleet()
	
	public function active( $date_string = NULL, $time_string = NULL, $XUPNumber = NULL )
	{
		if( empty( $date_string ) || empty( $time_string ) || empty( $XUPNumber ) )
		{
			self::_not_found();
		}
		
		$firstDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $date_string.' '.$time_string );
		if( $firstDetected_datetime == FALSE )
		{
			self::_not_found();
		}
		if( !ctype_digit( $XUPNumber ) )
		{
			self::_not_found();
		}
		
		$firstDetected = $firstDetected_datetime->format( Activity_model::DATETIME_DB_FORMAT );
		$fleet_data = $this->Activity_model->get_active_fleet( $firstDetected, $XUPNumber );
		if( $fleet_data == FALSE )
		{
			self::_not_found();
		}
		
		// It's probably best to redirect the user to the scheduled fleet's page if there is one
		if( $fleet_data['fleetTime'] != NULL )
		{
			$fleetTime_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet_data['fleetTime'] );
			$scheduled_fleet_URL = '/activity/fleet/'. $fleetTime_datetime->format( 'Y-m-d/H:i' );
			redirect( $scheduled_fleet_URL, 'location' );
		}
		
		$data = $fleet_data;
		
		$data['firstDetected_datetime'] = $firstDetected_datetime;
		$lastDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $data['lastDetected'] );
		$data['lastDetected_datetime'] = $lastDetected_datetime;
		
		$latestDetected = $this->Activity_model->get_latest_MOTD_merge();
		$data['latestDetected'] = $latestDetected;
		$latestDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $latestDetected );
		$data['latestDetected_datetime'] = $latestDetected_datetime;
		
		$kills = $this->Killmails_model->get_killmails_for_fleet( $firstDetected, $XUPNumber );
		$data['kills_html'] = '';
		if( $kills[0]->ID != NULL )
		{
			$kills_data['kills'] = $kills;
			$kills_html = $this->load->view( 'activity/kills_table2', $kills_data, TRUE );
			$data['kills_html'] = $kills_html;
		}
		
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => 'Fleet Details',
			'PAGE_AUTHOR' => $data['CharacterName'] )
		);
		$this->load->view( 'activity/view_active_fleet', $data );
		$this->load->view( 'common/footer' );
	}// active()
	
}// Activity
?>