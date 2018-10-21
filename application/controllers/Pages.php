<?php
class Pages extends SF_Controller {
	
	public function view( $page = '404' )
	{
		if( $page == '404' || !file_exists( APPPATH.'/views/pages/'.$page.'.php' ) )
		{
			self::_not_found();
		}
		
		$data = array();
		
		if( $page == 'commanders' )
		{
			$this->load->model( 'User_model' );
			$data['staff'] = $this->User_model->get_staff();
			$this->load->model( 'Command_model' );
			$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
		}
		
		$this->output->cache( 1440 );	// 1 day in minutes
		$this->load->view( 'common/header' );
		$this->load->view( 'pages/'.$page, $data );
		$this->load->view( 'common/footer' );
	}// view()
	
}// Pages
?>