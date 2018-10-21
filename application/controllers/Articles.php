<?php
class Articles extends SF_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model( 'Articles_model' );
	}// __construct()

	public function index()		// The landing page, presents the Featured articles in larger Billboard carousel
	{
		$data['category'] = 'All Articles';
		$data['Page'] = 0;
		
		$data['articles'] = $this->Articles_model->get_articles();
		$data['featured'] = $this->Articles_model->get_featured();
		$data['hottest'] = $this->Articles_model->get_hot();
		
		$whats_new_data['whats_new'] = $this->Articles_model->get_whats_new();
		$data['whats_new_html'] = $this->load->view( 'articles/whats_new', $whats_new_data, TRUE );
		
		$this->load->view( 'common/header', array(
			'UNVEIL' => TRUE,
			'CAROUSEL' => TRUE,
			'PAGE_TITLE' => 'Articles' )
		);
		$this->load->view('articles/billboard', $data);		// Difference of index() from just /page/0
		$data['featured_side_html'] = '';
		$this->load->view('articles/index', $data);
		$this->load->view( 'common/footer' );
	}// index()

	public function view( $ArticleID )	// View a specific article
	{
		if( !ctype_digit( $ArticleID ) || $ArticleID < 0 )
		{
			self::_not_found();
		}
		
		$data['article'] = $this->Articles_model->view_article( $ArticleID );
		
		if( empty($data['article']) )
		{
			self::_not_found();
		}

		$this->load->view( 'common/header', array(
			'UNVEIL' => TRUE,
			'PAGE_TITLE' => $data['article']['ArticleName'],
			'PAGE_AUTHOR' => $data['article']['Username'],
			'PAGE_DESC' => $data['article']['ArticleDescription'] )
		);
		
		$this->load->view('articles/view', $data);
		$this->load->view( 'common/footer' );
	}// view()
	
	public function category( $ArticleCategory = NULL, $Page = 0 )	// Display a page of a category, or All Articles pseudo-category
	{
		if( !in_array( $ArticleCategory, Articles_model::CATEGORIES() ) || ($Page !== 0 && !ctype_digit( $Page )) )
		{
			$ArticleCategory = NULL;	// Invalid category or page. List all. Or should we redirect to /articles top level, or an error page?
			$Page = 0;
		}
		
		$data['category'] = $ArticleCategory;
		$data['Page'] = $Page;
		
		$data['articles'] = $this->Articles_model->get_articles( $ArticleCategory, $Page );
		if( $ArticleCategory == NULL )
		{
			$data['category'] = 'All Articles';
		}
		
		$whats_new_data['whats_new'] = $this->Articles_model->get_whats_new();
		$data['whats_new_html'] = $this->load->view( 'articles/whats_new', $whats_new_data, TRUE );
		$featured_data['featured'] = $this->Articles_model->get_featured();
		$data['featured_side_html'] = $this->load->view( 'articles/featured_side', $featured_data, TRUE );
		$data['hottest'] = $this->Articles_model->get_hot();
		
		$this->load->view( 'common/header', array(
			'UNVEIL' => TRUE,
			'CAROUSEL' => TRUE,
			'PAGE_TITLE' => $data['category'] )
		);
		$this->load->view('articles/index', $data);
		$this->load->view('common/footer');
	}// category()
	
	public function page( $Page = 0 )	// Display a page of the All Articles pseudo-category.
	{
		if( !ctype_digit( $Page ) || $Page < 0 )
		{
			self::_not_found();
		}
		
		$data['category'] = 'All Articles';
		$data['Page'] = $Page;
		
		$data['articles'] = $this->Articles_model->get_articles( NULL, $Page );
		
		$whats_new_data['whats_new'] = $this->Articles_model->get_whats_new();
		$data['whats_new_html'] = $this->load->view( 'articles/whats_new', $whats_new_data, TRUE );
		$featured_data['featured'] = $this->Articles_model->get_featured();
		$data['featured_side_html'] = $this->load->view( 'articles/featured_side', $featured_data, TRUE );
		$data['hottest'] = $this->Articles_model->get_hot();
		
		$this->load->view( 'common/header', array(
			'UNVEIL' => TRUE,
			'CAROUSEL' => TRUE,
			'PAGE_TITLE' => 'Articles' )
		);
		$this->load->view('articles/index', $data);
		$this->load->view('common/footer');
	}// page()
	
}// Articles
?>