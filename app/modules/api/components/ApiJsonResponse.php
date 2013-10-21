<?php
class ApiJsonResponse extends ApiResponseAbstract
{
	/**
	 * @see ApiResponseAbstract 
	 */
	protected $_contentType = 'application/json';
	
	/**
	 * @see ApiResponseAbstract 
	 */
	protected function _renderContent()
	{
		$this->_body = CJSON::encode($this->_params);
	}
	
	/**
	 * @see ApiResponseAbstract 
	 */
	protected function _renderErrors()
	{
		$errorsData = array(
			'errors' => $this->_errors
		);
		$this->_body = CJSON::encode($errorsData);
	}
}