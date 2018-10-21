<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Dynamic Search library. Redacted
 *
 * @author Daneel Trevize
 */

class Dynamic_search
{
	
	const DEFAULT_PAGE_SIZE = 10;
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->model('Eve_SDE_model');
	}// __construct()
	
	public static function validate_search_fields( $search_fields, $permitted_fields )
	{
		
	}// validate_search_fields()
	
	public static function validate_orderType( $orderType, $permitted_orderTypes )
	{
		
	}// validate_orderType()
	
	public static function validate_orderSort( $orderSort )
	{
		
	}// validate_orderSort()
	
	public static function validate_page( $page )
	{
		
	}// validate_page()
	
	public static function validate_page_size( $pageSize, $permitted_pageSizes )
	{
		
	}// validate_page_size()
	
	public function subquery_typeName_to_typeID( $typeCategory, $fieldName, $fieldID, &$validated_search_fields )
	{
		
	}// subquery_typeName_to_typeID()
	
	public function build_search_conditions( $db, $validated_search_fields )
	{
		
	}// build_search_conditions()
	
	public static function regenerate_search_string( $search_fields, $validated_search_fields, $orderType, $orderSort, $pageSize )
	{
		
	}// regenerate_search_string()
	
}// Dynamic_search
?>