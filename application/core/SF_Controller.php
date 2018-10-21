<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Spectre Fleet extended Controller core class.
 *
 * @author Daneel Trevize
 */

class SF_Controller extends CI_Controller {
	
	
	const BAD_URLS = array(
		'news/feed',
		'js/modernizr-2.6.2-respond-1.1.0.min.js',
		'js/html5shiv.js',
		'js/placeholder.js',
		'js/jquery-3.2.1.min.js',
		'wp-admin',
		'site/wp-admin/setup-config.php',
		'wp-login.php',
		'wp-includes/wlwmanifest.xml',
		'wp-content/plugins/cherry-plugin/admin/css/cherry-admin-plugin.css',
		'forum/phpBB3',
		'forum/phpBB3/index.php',
		'forum/phpbb3',
		'forum/phpbb3/index.php',
		'components/com_b2jcontact',
		'nogHead',
		'nogFoot',
		'administrator',
		'administrator.php',
		'admin/login.php',
		'admin.php',
		'adminzone',
		'cms',
		'cms/admin',
		'mscms',
		'manager',
		'manager/html',
		'netcat',
		'channel/wallet.dat',
		'a2billing/common/javascript/misc.js',
		'a2billing/customer/templates/default/footer.tpl'
	);
	
	const REDIRECT_URLS = array(
		'fleets/request_invite' => 'invites/request',
		'doctrine/fittings' => 'doctrine/fits',
		'news' => 'articles/category/News'
	);
	
	
	public function __construct()
	{
		parent::__construct();
		$this->UTC_DTZ = new DateTimeZone( 'UTC' );
	}// __construct()
	
	
	protected function _ensure_logged_in()
	{
		
		if( !self::_is_logged_in() )
		{
			//If no session, redirect to login page
			log_message( 'debug', 'SF_Controller: login triggered from ' . $this->uri->uri_string() );
			$_SESSION['login_return'] = $this->uri->uri_string();
			redirect('login', 'location');
		}
		
		// Refresh user permissions cached in session
		$this->load->library('Authorization');
		$this->session->permissions = $this->authorization->get_user_permissions( $this->session->user_session['UserID'] );
		
	}// _ensure_logged_in()
	
	protected function _is_logged_in()
	{
		return isset( $_SESSION['user_session'] );
	}// _is_logged_in()
	
	protected function _ensure_one_of( $permission_names, $additional_condition = FALSE )
	{
		if( is_string( $additional_condition ) )
		{
			throw new InvalidArgumentException( '$additional_condition should only be a boolean.' );
			
			//Need to be sure we don't return from this function...
			
			$user_session = $this->session->user_session;
			log_message( 'error', 'SF_Controller: invalid function call argument for user:'.$user_session['UserID'].':'.$user_session['Username'].' for URI: ' . $_SERVER['REQUEST_URI'] );
			redirect('portal', 'location');
		}
		
		if( $additional_condition )
		{
			return;	// Sufficient permission
		}
		
		// The additional OR was not true, we may yet determine insufficient permission
		
		$permissions = self::_get_permissions();
		
		if( is_array( $permission_names ) )
		{
			// OR permission_names
			foreach( $permission_names as $permission_name )
			{
				if( array_key_exists( $permission_name, $permissions ) && $permissions[$permission_name] )
				{
					return;	// Sufficient permission
				}
			}
		}
		elseif( is_string( $permission_names ) )
		{
			if( array_key_exists( $permission_names, $permissions ) && $permissions[$permission_names] )
			{
				return;	// Sufficient permission
			}
		}
		
		//If insufficient permission, redirect to profile page.
		$user_session = $this->session->user_session;
		log_message( 'error', 'SF_Controller: permission denied for user:'.$user_session['UserID'].':'.$user_session['Username'].' for URI: ' . $_SERVER['REQUEST_URI'] );
		$this->session->set_flashdata( 'flash_message', 'Insufficient permissions for '. $this->uri->uri_string() );
		redirect('portal', 'location');
		
	}// _ensure_one_of()
	
	protected function _has_permission( $permission_name )
	{
		$permissions = self::_get_permissions();
		if( !array_key_exists( $permission_name, $permissions ) )	// Session predated new permission index...
		{
			return FALSE;	// Ideally we'd reload permissions instead of assuming a lack?
		}
		return $permissions[$permission_name];
	}// _has_permission()
	
	protected function _get_permissions()
	{
		return ( array_key_exists( 'permissions', $_SESSION ) ? $this->session->permissions : array() );
	}// _get_permissions()
	
	protected function _ensure_json_permission( $permission_name )
	{
		// Doesn't refresh user permissions cached in session
		
		if( !self::_is_logged_in() || !self::_has_permission( $permission_name ) )
		{
			// Not logged in or no permission
			$this->output->set_status_header( 403 );
			$this->output->_display();
			exit();
		}
	}// _ensure_json_permission()
	
	protected function _is_local_request()
	{
		return $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'];
	}// _is_local_request()
	
	protected function _ensure_local_request_or( $permission_name )
	{
		if( self::_is_local_request() )
		{
			return;	// Sufficient permission
		}
		if( !self::_is_logged_in() )
		{
			// Not logged in
			$_SESSION['login_return'] = $this->uri->uri_string();
			redirect('login', 'location');
		}
		if( !self::_has_permission( $permission_name ) )
		{
			//If insufficient permission, redirect to profile page.
			$user_session = $this->session->user_session;
			log_message( 'error', 'SF_Controller: permission denied for user:'.$user_session['UserID'].':'.$user_session['Username'].' for URI: ' . $_SERVER['REQUEST_URI'] );
			$this->session->set_flashdata( 'flash_message', 'Insufficient permissions for '. $this->uri->uri_string() );
			redirect('portal', 'location');
		}
	}// _ensure_local_request_or()
	
	
	protected function _eve_now_dtz()
	{
		return new DateTime( 'now', $this->UTC_DTZ );
	}// _eve_now_dtz()
	
	
	protected function _not_found()
	{
		$location = $this->uri->uri_string();
		if( in_array( $location, self::BAD_URLS) )
		{
			//log_message( 'error', 'SF_Controller: 410 triggered from ' . $location );
			$this->output->set_status_header('410');
			$this->output->_display();
			exit();
		}
		if( array_key_exists( $location, self::REDIRECT_URLS ) )
		{
			//log_message( 'error', 'SF_Controller: 301 triggered from ' . $location );
			redirect( self::REDIRECT_URLS[$location] , 'location', 301);
			exit();
		}
		
		log_message( 'error', 'SF_Controller: 404 triggered from ' . $location );
		$this->output->set_status_header('404');
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => '404 Not Found' ) );
		$this->load->view( 'errors/custom_404' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
		$this->output->_display();
		exit();
	}// _not_found()
	
	
	protected static function _is_integer_string( $int_string )
	{
		return ( $int_string !== NULL && ctype_digit( $int_string ) && strlen( $int_string ) <= 9 );	// 9 digits max to avoid integer max
	}// _is_integer_string()
	
}// SF_Controller
?>