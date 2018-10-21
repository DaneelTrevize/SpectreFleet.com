<?php
class Rss extends SF_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Articles_model');
		$this->load->helper('file');
		$this->base_url = $this->config->item('base_url');
	}// __construct()
	
	public function index()
	{
		// Create RSS Feed from Database "articles"
		$posts = $this->Articles_model->get_articles();
		
		// Load the "Feed" library
		$this->load->library('feed');

		// Create new instance
		$feed = new Feed();

		// Set feed information.
		$feed->title = 'Spectre Fleet';
		$feed->description = 'News, Blogs, and Events from Eve\'s Largest Public Group';
		$feed->link = $this->base_url;
		$feed->lang = 'en';
		if( count($posts) >= 1 )
		{
			$feed->pubdate = $posts[0]['DatePublished']; // DatePublished on most recent article
		}
		else
			$feed->pubdate = date('Y-m-d H:i:s');
		{
			
		}

		// add posts to the feed
		foreach ($posts as $post)
		{
			// Find file's size for the Enclosure tag.
			$file_info = get_file_info('./media/image'.$post['ArticlePhoto']);
			$type = get_mime_by_extension('./media/image'.$post['ArticlePhoto']);
			$enclosure = array(
				'url'=>$this->base_url.'media/image'.$post['ArticlePhoto'],
				'type'=>$type,
				'length'=>$file_info['size']
			);
			
			// Set article's title, author, url, pubdate and description
			$feed->add($post['ArticleName'], $post['CharacterName'], $this->base_url.'articles/'.$post['ArticleID'], $post['DatePublished'], $post['ArticleDescription'], $post['ArticleContent'], $enclosure);
		}

		// Render Feed
		$feed->render('rss');
		
	}// index()
	
}// Rss
?>