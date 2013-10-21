<?php
class ApiRequest extends CComponent
{
	
		
	/**
	 * Заголовок, в котором передаётся ключ api
	 */
	const API_KEY_HEADER = 'X-Api-Key';	
	
	const JSON_CONTENT_TYPE = 'application/json';
	
	/**
	 * Параметры запроса
	 * @var type 
	 */
	protected $_params = array();
	
	/**
	 * Декодированные данные post
	 * @var mixed 
	 */
	protected $_postData;
	
	/**
	 * Заголовки запроса
	 * @var type 
	 */
	protected $_headers = array();
	
	/**
	 * Ключ api
	 * @var type 
	 */
	protected $_apiKey = NULL;
	
	/**
	 * Пользователь api
	 * @var type 
	 */
	protected $_apiUser = NULL;

	public function __construct()
	{
		$this->_initParams();
	}
	
	/**
	 * Получить параметры запроса
	 * @return array
	 */
	public function getParams() 
    {
        return $this->_params;
    }
	
	/**
	 * Получить заголовки запроса
	 * @return array
	 */
	public function getHeaders() 
    {
        return $this->_headers;
    }
	
	/**
	 * Получить параметр запроса
	 * @param type $param
	 * @param type $default
	 * @return type
	 */
	public function getParam($param, $default = NULL) 
    {
		if (isset($this->_params[$param]))
		{
			$param = $this->_params[$param];
		}
		else
		{
			 $param = $default;
		}
		return $param;
    }
	
	/**
	 * Получить данные post
	 * @return mixed
	 */
	public function getPostData()
	{
		return $this->_postData;
	}

	/**
	 * Получить пользователя api
	 * @return ArApiUsers
	 */
	public function getApiUser()
	{
		// Ищем пользователя в БД, если передан ключ api
		if (is_null($this->_apiUser) && !is_null($this->_apiKey))
		{
			$apiUser = ArApiUsers::model()->find(
				'api_key = :key', array(':key' => $this->_apiKey)
			);
			if (!is_null($apiUser))
			{
				$this->_apiUser = $apiUser;
			}
		}
		return $this->_apiUser;
	}

	/**
	 * Инициализация параметров
	 */
	protected function _initParams()
	{
		$r = Yii::app()->request;
		// Заголовки
		$this->_headers = getallheaders();
		// Получаем ключ api из запроса
		if (isset($this->_headers[self::API_KEY_HEADER]) && $this->_headers[self::API_KEY_HEADER] != '')
		{
			
			$this->_apiKey = $this->_headers[self::API_KEY_HEADER];
		}
		// Параметры
		$params = array();
		if ($r->isPostRequest)
		{
			$post = file_get_contents('php://input');
			if (isset($this->_headers['Content-Type'])
				&& $this->_headers['Content-Type'] == self::JSON_CONTENT_TYPE
				&& trim($post) != '')
			{
				$this->_postData = CJSON::decode($post);
			}
			else
			{
				parse_str($post, $postParams);
				if (is_array($postParams) && !empty($postParams))
				{
					$this->_params = $postParams;
				}
			}
		}
		else
		{
			if (!empty($_GET))
			{
				$params = $_GET;
			}			
		}
		$this->_params = array_merge($this->_params, $params);
	}
	
}
