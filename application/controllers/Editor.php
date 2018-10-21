<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Editor extends SF_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'form_validation' );
		$this->load->model( 'User_model' );
		$this->load->model( 'Articles_model' );
		$this->load->helper( 'file' );
		$this->load->model( 'Editor_model' );
		$this->load->model( 'Discord_model' );
	}// __construct()
	
	public function create_article()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SUBMIT_ARTICLES' );
		
		$UserID = $this->session->user_session['UserID'];
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ArticleName', 'article name', 'required|max_length[255]');
		$this->form_validation->set_rules('ArticleDescription', 'description', 'required|max_length[255]');
		$this->form_validation->set_rules('ArticleCategory', 'category', 'required');
		$this->form_validation->set_rules('ArticleContent', 'content', 'required|max_length[65535]');
		$this->form_validation->set_rules('ArticlePhoto', 'photo', 'required');
		
		if( $this->form_validation->run() == TRUE )
		{
			$ArticleName = htmlentities( $this->input->post('ArticleName'), ENT_QUOTES);
			$ArticleDescription = htmlentities( $this->input->post('ArticleDescription'), ENT_QUOTES);
			$ArticleCategory = htmlentities( $this->input->post('ArticleCategory'), ENT_QUOTES);
			$ArticleContent = strip_tags( $this->input->post('ArticleContent'), Articles_model::CONTENT_TAGS );
			$ArticlePhoto = $this->input->post('ArticlePhoto');
			
			$submissionID = $this->Editor_model->create_article( $ArticleName, $ArticleDescription, $ArticleCategory, $ArticleContent, $ArticlePhoto, $UserID );
			if( $submissionID != FALSE )	// Not actually the ID atm, insert_id() bug
			{
				$this->session->set_flashdata( 'flash_message', "Submission created." );
				redirect('editor/review_submissions', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem creating the submission.' );
				log_message( 'error', 'Editor controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while creating a submission.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			// Form validation failed, reload with content.
			
			if( isset( $_POST['ArticleCategory'] ) )
			{
				$data['ArticleCategory'] = $_POST['ArticleCategory'];		// Override displaying default with latest submitted
			}
			if( isset( $_POST['ArticlePhoto'] ) )
			{
				$data['ArticlePhoto'] = $_POST['ArticlePhoto'];			// Override displaying default with latest submitted
			}
			
			$data['categories'] = Articles_model::CATEGORIES();
			
			$data['photos'] = get_filenames('./media/image/uploads/');		// Hardcoded path, assumptions of content in view. Should be ...filenames(BASEPATH.'media/...?
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Create Article' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'editor/create', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'CKEDITOR' => TRUE ) );
		}
		
	}// create_article()
	
	public function edit_article( $SubmissionID = NULL )	// Handles edits to submissions prior to publication. But only submissions, not published articles...?
	{
		$this->_ensure_logged_in();
		
		if( $SubmissionID == NULL || !ctype_digit( $SubmissionID ) )
		{
			//	Malicious population of SubmissionID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while editing.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while editing.' );
			redirect('portal', 'location');
		}
		
		$this->_ensure_one_of( array('CAN_SUBMIT_ARTICLES', 'CAN_EDIT_OTHERS_SUBMISSIONS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ArticleName', 'article name', 'required|max_length[255]');
		$this->form_validation->set_rules('ArticleDescription', 'description', 'required|max_length[255]');
		$this->form_validation->set_rules('ArticleCategory', 'category', 'required');
		$this->form_validation->set_rules('ArticleContent', 'content', 'required|max_length[65535]');
		$this->form_validation->set_rules('ArticlePhoto', 'photo', 'required');
		$this->form_validation->set_rules('SubmissionID', 'submission', 'required|is_natural');	// Check it's a valid submissionID and owned by this session's user if CAN_SUBMIT_ARTICLES but not CAN_EDIT_OTHERS_SUBMISSIONS.
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			$this->_ensure_one_of( 'CAN_EDIT_OTHERS_SUBMISSIONS', self::confirm_submission_owner( $SubmissionID ) );
			
			$ArticleName = htmlentities( $this->input->post('ArticleName'), ENT_QUOTES);
			$ArticleDescription = htmlentities( $this->input->post('ArticleDescription'), ENT_QUOTES);
			$ArticleCategory = htmlentities( $this->input->post('ArticleCategory'), ENT_QUOTES);
			$ArticleContent = strip_tags( $this->input->post('ArticleContent'), Articles_model::CONTENT_TAGS );
			$ArticlePhoto = $this->input->post('ArticlePhoto');
			
			if( $this->Editor_model->edit_article( $SubmissionID, $ArticleName, $ArticleDescription, $ArticleCategory, $ArticleContent, $ArticlePhoto ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' was edited successfully.' );
				redirect('editor/review_submissions', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem editing Submission ID: '.$SubmissionID.'.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while editing SubmissionID'.$SubmissionID.'.' );
				redirect('editor/review_submissions', 'location');
			}
			
		}
		else
		{
			// Form validation failed, reload with content.
			//$SubmissionID = $this->input->post('SubmissionID');	// Take the ID from the URI/GET instead
			$submission = $this->Editor_model->get_submission( $SubmissionID );
			if( $submission != FALSE )
			{
				$user = $this->User_model->get_user_data_by_ID( $submission['UserID'] );
				$submission['Username'] = $user->Username;
				
				if( isset( $_POST['ArticleCategory'] ) )
				{
					$submission['ArticleCategory'] = $_POST['ArticleCategory'];		// Override displaying stored with latest submitted
				}
				if( isset( $_POST['ArticlePhoto'] ) )
				{
					$submission['ArticlePhoto'] = $_POST['ArticlePhoto'];			// Override displaying stored with latest submitted
				}
				
				$data['submission'] = $submission;
				
				$data['categories'] = Articles_model::CATEGORIES();
				
				$data['photos'] = get_filenames('./media/image/uploads/');		// Hardcoded path, assumptions of content in view. Should be ...filenames(BASEPATH.'media/...?
				
				$this->load->view( 'common/header', array( 'UNVEIL' => TRUE, 'PAGE_TITLE' => 'Edit Article' ) );
				$this->load->view( 'portal/portal_header' );
				$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
				$this->load->view( 'portal/portal_content' );
				$this->load->view( 'editor/edit', $data );
				$this->load->view( 'portal/portal_footer' );
				$this->load->view( 'common/footer', array( 'CKEDITOR' => TRUE ) );
			}
			else
			{
				//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
				$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while editing.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while editing.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		
	}// edit_article()
	
	public function submit_submission()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SUBMIT_ARTICLES' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			if( self::confirm_submission_owner( $SubmissionID ) )
			{
				if( $this->Editor_model->submit_submission( $SubmissionID ) )
				{
					$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' was submitted.' );
					redirect('editor/review_submissions', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem submitting Submission ID: '.$SubmissionID.'.' );
					log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while submitting SubmissionID'.$SubmissionID.'.' );
					redirect('editor/review_submissions', 'location');
				}
			}
			else
			{
				// If no permission, redirect to submissions.		Malicious population of SubmissionID field?
				$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while submitting.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID:'.$SubmissionID.' supplied while submitting.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while submitting.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while submitting.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// submit_submission()
	
	public function promote_submission()	// Really should be submit_article/put_for_review, submit_article should be create_article/submission?
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_SUBMIT_ARTICLES', 'CAN_EDIT_OTHERS_SUBMISSIONS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			$this->_ensure_one_of( 'CAN_EDIT_OTHERS_SUBMISSIONS', self::confirm_submission_owner( $SubmissionID ) );
			
			if( $this->Editor_model->promote_submission( $SubmissionID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' was put to review.' );
				redirect('editor/review_submissions', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem promoting Submission ID: '.$SubmissionID.'.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while promoting SubmissionID'.$SubmissionID.'.' );
				redirect('editor/review_submissions', 'location');
			}
			
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while promoting.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while promoting.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// promote_submission()
	
	public function retract_submission()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SUBMIT_ARTICLES' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			if( self::confirm_submission_owner( $SubmissionID ) )
			{
				if( $this->Editor_model->retract_submission( $SubmissionID ) )
				{
					$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' was retracted.' );
					redirect('editor/review_submissions', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem retracting Submission ID: '.$SubmissionID.'.' );
					log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while retracting SubmissionID'.$SubmissionID.'.' );
					redirect('editor/review_submissions', 'location');
				}
			}
			else
			{
				// If no permission, redirect to submissions.		Malicious population of SubmissionID field?
				$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while retracting.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID:'.$SubmissionID.' supplied while retracting.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while retracting.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while retracting.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// retract_submission()
	
	public function review_submissions()
	{
		$this->_ensure_logged_in();
		
		if( $this->_has_permission('CAN_SUBMIT_ARTICLES') || $this->_has_permission('CAN_EDIT_OTHERS_SUBMISSIONS') || $this->_has_permission('CAN_PUBLISH_ARTICLES') )
		{
			$UserID = $this->session->user_session['UserID'];
			if( $this->_has_permission('CAN_SUBMIT_ARTICLES') )
			{
				$data['draft_submissions'] = $this->Editor_model->get_draft_submissions( $UserID );
			}
			
			if( $this->_has_permission('CAN_SUBMIT_ARTICLES') || $this->_has_permission('CAN_EDIT_OTHERS_SUBMISSIONS') )
			{
				if( $this->_has_permission('CAN_EDIT_OTHERS_SUBMISSIONS') )
				{
					$submitted_submissions = $this->Editor_model->get_submitted_submissions( NULL );
				}
				else
				{
					// Filter to just the owned submissions
					$submitted_submissions = $this->Editor_model->get_submitted_submissions( $UserID );
				}
				foreach( $submitted_submissions as &$submission)
				{
					$submission['can_retract'] = ( $submission['UserID'] == $UserID );
				}
				$data['submitted_submissions'] = $submitted_submissions;
			}
			
			if( $this->_has_permission('CAN_PUBLISH_ARTICLES') )
			{
				$data['publishable_submissions'] = $this->Editor_model->get_publishable_submissions();
			}
			else
			{
				// Filter to just the owned submissions
				$data['publishable_submissions'] = $this->Editor_model->get_publishable_submissions( $UserID );
			}
			
			$permissions = $this->_get_permissions();
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Review Submissions' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $permissions );
			$this->load->view('portal/portal_content' );
			$this->load->view('editor/review_submissions', array_merge($permissions, $data));
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		else
		{
			// If no permission, redirect to portal page.
			redirect('portal', 'location');
		}
		
	}// review_submissions()
	
	public function preview_article( $SubmissionID = NULL )	// Insufficient arg validation before ownership check?
	{
		$this->_ensure_logged_in();
		
		if( $SubmissionID == NULL || !ctype_digit( $SubmissionID ) )
		{
			//	Malicious population of SubmissionID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while previewing.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID:'.$SubmissionID.' supplied while previewing.' );
			redirect('portal', 'location');
		}
		
		$this->_ensure_one_of( 'CAN_PUBLISH_ARTICLES', self::confirm_submission_owner( $SubmissionID ) );
	
		$submission = $this->Editor_model->get_submission( $SubmissionID );
		if( $submission != FALSE )
		{
			$user = $this->User_model->get_user_data_by_ID( $submission['UserID'] );
			$submission['Username'] = $user->Username;
			
			$data['submission'] = $submission;
			
			$this->load->view( 'common/header', array( 'UNVEIL' => TRUE, 'PAGE_TITLE' => 'Preview Article' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('editor/preview', $data);
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while previewing.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID:'.$SubmissionID.' supplied while previewing.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// preview_article()
	
	public function publish_article()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PUBLISH_ARTICLES' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			$articleID = $this->Editor_model->publish_submission( $SubmissionID );
			if( $articleID != FALSE )	// Not actually the ID atm, insert_id() bug
			{
				$this->session->set_flashdata( 'flash_message', "Article created from Submission ID: $SubmissionID." );
				redirect('editor/review_submissions', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem publishing Submission ID: '.$SubmissionID.'.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while publishing SubmissionID'.$SubmissionID.'.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while publishing.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while publishing.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// publish_article()
	
	public function reject_submission()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PUBLISH_ARTICLES' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			if( $this->Editor_model->reject_submission( $SubmissionID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' was rejected.' );
				redirect('editor/review_submissions', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem rejecting Submission ID: '.$SubmissionID.'.' );
				log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while rejecting SubmissionID'.$SubmissionID.'.' );
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			//	No submission found.	Malicious population of SubmissionID field? Or concurrent change of status?
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while rejecting.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while rejecting.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// reject_submission()
	
	public function delete_submission()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SUBMIT_ARTICLES' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SubmissionID', 'submission ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SubmissionID = $this->input->post('SubmissionID');
			
			if( self::confirm_submission_owner( $SubmissionID ) )
			{
				if( $this->Editor_model->delete_submission( $SubmissionID ) )
				{
					$this->session->set_flashdata( 'flash_message', 'Submission ID: '.$SubmissionID.' deleted.' );
					redirect('editor/review_submissions', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem deleting Submission ID: '.$SubmissionID.'.' );
					log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while deleting SubmissionID'.$SubmissionID.'.' );
					redirect('editor/review_submissions', 'location');
				}
			}
			else
			{
				// If no permission, redirect to submissions.		Malicious population of SubmissionID field?
				redirect('editor/review_submissions', 'location');
			}
		}
		else
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid Submission ID supplied while deleting.' );
			log_message( 'error', 'Editor controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Submission ID supplied while deleting.' );
			redirect('editor/review_submissions', 'location');
		}
		
	}// delete_submission()
	
	private function confirm_submission_owner( $SubmissionID )
	{
		$UserID = $this->session->user_session['UserID']; 
		$submission = $this->Editor_model->get_submission( $SubmissionID );
		if( $submission == FALSE )
		{
			return FALSE;
		}
		return ($submission['UserID'] == $UserID);
	}// confirm_submission_owner()
	
	
	public function change_role()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_EDITOR_ROLES' );
		
		$this->form_validation->set_rules('UserID', 'user ID', 'callback__check_pairedUserID');
		$this->form_validation->set_rules('Username', 'user name', 'callback__check_pairedUsername');
		$this->form_validation->set_rules('Role', 'role', 'required|is_natural|callback__check_role');
		
		$role_names = Editor_model::ROLE_NAMES();
		$data['role_names'] = $role_names;
		
		if( $this->form_validation->run() == TRUE )
		{
			$UserID = $this->input->post('UserID');
			$Username = $this->input->post('Username');
			if( $UserID !== '' )
			{
				$user = $this->User_model->get_user_data_by_ID( $UserID );
				$Username = $user->Username;
			}
			else
			{	
				$user = $this->User_model->get_user_data_by_name( $Username );
				$UserID = $user->UserID;
			}
			
			$role = $this->input->post('Role');
			
			$rolename = $role_names[$role];
			
			
			if( $this->User_model->update_editor_role( $UserID, $role, $this->session->user_session['UserID'] ) )
			{
				$content = "$Username's role was changed to '$rolename'.";
				$result = $this->Discord_model->tell_command( $content );
				if( $result['response'] == FALSE )
				{
					log_message( 'error', "Editor controller: failure to tell_command( $content )." );
				}
				
				$this->session->set_flashdata( 'flash_message', "$Username's role was changed to '$rolename'." );
				redirect('editor/change_role', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', "$Username's role was unable to be changed." );
				redirect('editor/change_role', 'location');
			}
		}
		else
		{
			if( isset( $_POST['UserID'] ) )			// else Malicious removal of select field?
			{
				$data['UserID'] = $_POST['UserID'];
			}
			if( isset( $_POST['Username'] ) )			// else Malicious removal of select field?
			{
				$data['Username'] = $_POST['Username'];
			}
			if( isset( $_POST['Role'] ) )			// else Malicious removal of select field?
			{
				$data['Role'] = $_POST['Role'];
			}
			
			$data['sorted_editors'] = $this->Editor_model->get_sorted_editors();
			// Field validation failed. Reload registration page with errors.
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Change Editorial Role' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('editor/change_role', $data);
			$this->load->view('portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'SELECT2' => TRUE ) );
		}
		
	}// change_role()
	
	function _check_pairedUserID( $UserID )
	{
		if( $UserID === '' && $this->input->post('Username') === '' )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'A User has not been selected while the user name field is also empty.');
			return FALSE;
		}
		if( $UserID !== '' && $this->input->post('Username') !== '' )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'A User has been selected while the user name field is not empty.');
			return FALSE;
		}
		if( $UserID !== '' && !ctype_digit($UserID) )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'The user ID field is invalid.');
			return FALSE;
		}
		return TRUE;
	}// _check_pairedUserID()
	
	function _check_pairedUsername( $Username )
	{
		if( $Username === '' && $this->input->post('UserID') === '' )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name field is empty while a User has also not been selected.');
			return FALSE;
		}
		if( $Username !== '' && $this->input->post('UserID') !== '' )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name field is not empty while a User has been selected.');
			return FALSE;
		}
		if( $Username !== '' && $this->User_model->get_user_data_by_name( $Username ) === FALSE )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name was not found.');
			return FALSE;
		}
		return TRUE;
	}// _check_pairedUsername()
	
	function _check_role( $role )
	{
		if( !array_key_exists( $role, Editor_model::ROLE_NAMES() ) )	// Needs to include Member for demotions
		{
			$this->form_validation->set_message('_check_role', 'Invalid role supplied.');
			return FALSE;
		}
		return TRUE;
	}// _check_role()
	
}// Editor
?>