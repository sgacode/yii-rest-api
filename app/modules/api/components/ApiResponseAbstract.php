<?php
abstract class ApiResponseAbstract extends CComponent
{
	
	/**
	 * Успешные http коды
	 * @var type 
	 */
	public static $successCodes = array(200, 201);
	
	/**
	 * Тело запроса
	 * @var string 
	 */
	protected $_body = '';
	
	/**
	 * Параметры запроса
	 * @var array 
	 */
	protected $_params = array();
	
	/**
	 * Ошибки запроса
	 * @var array 
	 */
	protected $_errors = array();
	
	/**
	 * Статус запроса
	 * @var int 
	 */
	protected $_status = 200;
	
	/**
	 * Тип данных тела запроса
	 * @var string 
	 */
	protected $_contentType = '';
	
	/**
	 * Корневой элемент для тела ответа
	 * @var string 
	 */
	protected $_rootElem = 'result';

	/**
	 * Заголовки запроса
	 * @var array 
	 */
	protected $_headers = array(
		'Cache-Control' => 'no-cache, no-store, must-revalidate',
		'Pragma' => 'no-cache',
		'Expires' => '0',
	);

	/**
	 * Данные по основным http-кодам
	 * @var type 
	 */
	public static $codes = array(
		200 => array('title' => 'OK', 'message' => 'OK'),
		201 => array('title' => 'Created', 'message' => 'Created'),
		400 => array('title' => 'Bad Request', 'message' => 'Bad Service Request'),
		401 => array('title' => 'Unauthorized', 'message' => 'Unauthorized Service Request'),
		403 => array('title' => 'Forbidden', 'message' => 'Service Forbidden'),
		404 => array('title' => 'Not Found', 'message' => 'Service Url Not Found'),
		405 => array('title' => 'Method Not Allowed', 'message' => 'Method Not Allowed'),
		500 => array('title' => 'Internal Server Error', 'message' => 'The server encountered an error processing your request.'),
		501 => array('title' => 'Not Implemented', 'message' => 'The service requested method is not implemented.'),
	);
	
	/**
     * Получить детали по запрошенному коду
     * @param int $status
     * @param string $outputType
     * @return mixed 
     */
	public static function getHttpStatusCodeMessage($status, $outputType='title')
	{
		$codes = self::$codes;
		$result = '';
		if (isset($codes[$status]))
		{
			if ($outputType == 'title')
			{
				$result = $codes[$status]['title'];
			}
			else if ($outputType == 'message')
			{
				$result = isset($codes[$status]['message']) ? $codes[$status]['message'] : $codes[$status]['title'];
			}
			else
			{
				$result = $codes[$status];
			}
		}
		return $result;
	}
	
	/**
	 * Отправить запрос пользователю
	 */
	public function send()
	{
		if (in_array($this->getStatus(), self::$successCodes))
		{
			$this->_renderContent();
		}
		else
		{
			$this->_renderErrors();
		}
		$this->_sendHeaders();
		echo $this->_body;
		Yii::app()->end();
	}
	
	/**
	 * Рендеринг параметров в тело запроса с учётом типа
	 */
	abstract protected function _renderContent();
	
	/**
	 * Рендеринг ошибок в тело запроса с учётом типа
	 */
	abstract protected function _renderErrors();
	
	/**
	 * Получить тело запроса
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}
	
	/**
	 * Добавить параметры в запрос
	 * @param array $params
	 */
	public function addParams(array $params)
	{
		$this->_params = array_merge($this->_params, $params);
	}
	
	/**
	 * Установить параметры запроса
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->_params = $params;
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
	 * 
	 * @param array $errors
	 */
	public function addErrors(array $errors)
	{
		$this->_errors = array_merge($this->_errors, $errors);
	}
	
	/**
	 * Установить ошибки запроса
	 * @param array $errors
	 */
	public function setErrors(array $errors)
	{
		$this->_errors = $errors;
	}
	
	/**
	 * Получить ошибки запроса
	 * @return type
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Установить статус запроса
	 * @param string $status
	 */
	public function setStatus($status)
	{
		$this->_status = $status;
		return $this;
	}
    
	/**
	 * Получить статус запроса
	 * @return string
	 */
	public function getStatus()
	{
		return $this->_status;
	}
	
	/**
	 * Получить тип тела запроса
	 * @return string
	 */
	public function getContentType()
	{
		return $this->_contentType;
	}
	
	/**
	 * Установить заголовки
	 * @param array $headers
	 */
	public function setHeaders(array $headers)
	{
		$this->_headers = array_merge($this->_headers, $headers);
	}
	
	/**
	 * Получить заголовки
	 * @return type
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}
	
	/**
	 * Установить родительский элемент тела ответа
	 * @param string $rootElem
	 */
	public function setRootElem($rootElem)
	{
		$this->_rootElem = $rootElem;
	}
	
	/**
	 * Получить родительский элемент тела ответа
	 * @return string
	 */
	public function getRootElem()
	{
		return $this->_rootElem;
	}

	/**
	 * Отправка заголовков пользователю
	 */
	protected function _sendHeaders()
	{
		$statusHeader = 'HTTP/1.1 ' . $this->getStatus() . ' ';
		$statusHeader .= self::getHttpStatusCodeMessage($this->getStatus(),'title');
		$ctHeader = 'Content-type: ' . $this->getContentType();
		$headers = array_merge(array($statusHeader, $ctHeader), $this->_headers);
		foreach ($headers as $header)
		{
			header($header);
		}
	}
	
	
}