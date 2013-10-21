<?php

class ApiModule extends CWebModule
{
	/**
	 *  Категория перевода для модуля по умолчанию 
	 */

	const DEF_TRANSLATE_CAT = 'ApiModule.main';

	/**
	 * Язык по умолчанию
	 */
	const DEF_LANG = 'ru';
	
	/**
	 * Категория для логов
	 */
	const API_ERR_LOG_CAT = 'apierr';

	/**
	 * Параметр в запросе, отвечающий за тип тела ответа
	 */
	const RESPONSE_TYPE_PARAM = 'rtype';
	
	/**
	 * Тип тела ответа по умолчанию
	 */
	const DEF_RESPONSE_TYPE = 'json';
	
	/**
	 * Параметр в запросе, отвечающий за язык
	 */
	const LANG_PARAM = 'lang';
	
	/**
	 * Стандартное сообщение об ошибке API
	 */
	const API_ERROR_TEXT = 'Api internal error';
	
	/**
	 * Доступные языки
	 * @var array 
	 */
	public static $avLangs = array('ru', 'en');
	
	/**
	 * Preload components
	 * @var array  
	 */
	public $preload = array('log');
	
	/**
	 * Соответствие типа запроса соответствующему классу
	 * @var array 
	 */
	protected static $_responseClasses = array(
		'json' => 'ApiJsonResponse',
		'xml' => 'ApiXmlResponse',
	);

	/**
	 * Объект ответ API
	 * @var ApiResponseAbstract 
	 */
	protected $_apiResponse;
	
	/**
	 * Объект запрос API
	 * @var ApiRequest 
	 */
	protected $_apiRequest;

	/**
	 * Используемый шаблон
	 * @var string 
	 */
	public $layout = NULL;

	public function init()
	{
		// Инициализация компонентов
		$this->_initComponents();
		// Обработчик ошибок
		Yii::app()->attachEventHandler('onError', array($this, 'handleError'));
		Yii::app()->attachEventHandler('onException', array($this, 'handleError'));
		// Инициализация различных объектов
		$this->_initRequest();
		$this->_initResponse();
		$this->_initCustom();
		// Контроль доступа к api
		$this->_accessControl();
	}

	/**
	 * Перевод сообщения
	 * @see YiiBase::t()
	 */
	public static function t($message, array $params = array(), string $source = NULL, string $language = NULL, $category = NULL)
	{
		if (is_null($category))
		{
			$category = self::DEF_TRANSLATE_CAT;
		}
		return Yii::t($category, $message, $params, $source, $language);
	}
	
	/**
	 * Преобразование массива данных в объект api на основе карты сопоставления индексов массива свойствам объекта
	 * @param array $arr
	 * @param array $attrsMap
	 * @param string $objClass
	 * @return ApiModelBase
	 */
	public static function arrayToApiObj($arr, array $attrsMap, $objClass)
	{
		$apiObj = new $objClass();
		foreach ($attrsMap as $objProp => $arrIndex)
		{
			if (!isset($arr[$arrIndex]))
			{
				continue;
			}
			$apiObj->$objProp = $arr[$arrIndex];
		}
		return $apiObj;
	}
	
	/**
	 * arrayToApiObj для массива
	 * @param array $arr
	 * @param array $attrsMap
	 * @param string $objClass
	 * @return ApiModelBase
	 */
	public static function listToApiObjs($list, array $attrsMap, $objsClass)
	{
		$objs = array();
		foreach($list as $arr)
		{
			$objs[] = self::arrayToApiObj($arr, $attrsMap, $objsClass);
		}
		return $objs;
	}

	/**
	 * Получить запрос API
	 * @return ApiRequest
	 */
	public function getApiRequest()
	{
		return $this->_apiRequest;
	}
	
	/**
	 * Получить ответ API
	 * @return ApiResponse
	 */
	public function getApiResponse()
	{
		return $this->_apiResponse;
	}
	
	
	/**
	 * Получить польхзователя API
	 * @return ApiRequest
	 */
	public function getApiUser()
	{
		$user = NULL;
		if (!is_null($this->_apiRequest))
		{
			$user = $this->_apiRequest->getApiUser();
		}
		return $user;
	}
	
	/**
	 * Обработчик ошибок и исключений
	 * @param CEvent $event
	 */
	public function handleError(CEvent $event)
	{
		$logCat = self::API_ERR_LOG_CAT;
		$logFunc = function($msg, $msgCat) use ($logCat)
		{
			Yii::log($msg, 'error', $logCat . '.' . $msgCat);
		};
		if (is_null($this->_apiResponse))
		{
			$this->_initResponse();
		}
		$r = $this->_apiResponse;
		$r->setStatus(500);
		$r->setErrors(array(self::API_ERROR_TEXT));
		// Если было брошено исключение
		if ($event instanceof CExceptionEvent)
		{
			$e = $event->exception;
			// Если был выполнен некорретный запрос
			if (isset($e->statusCode) && in_array($e->statusCode, array(400, 404)))
			{
				$r->setStatus($e->statusCode);
				$msg = $e->getMessage();
				if (isset(ApiResponseAbstract::$codes[$e->statusCode]))
				{
					$msg = ApiResponseAbstract::$codes[$e->statusCode]['title'];
				}
				$r->setErrors(array($msg));
			}
			// Пишем в лог только системные ошибки
			else
			{
				$logFunc((string) $e, 'exception');
			}
		}
		// Если была ошибка
		elseif ($event instanceof CErrorEvent)
		{
			$msg = $event->message . ' in ' . $event->file . ' on line ' . $event->line;
			$logFunc($msg, 'php_error');
		}
		$event->handled = TRUE;
		$r->send();
	}

	/**
	 * Инициализация объекта запроса
	 */
	protected function _initRequest()
	{
		$this->_apiRequest = new ApiRequest();
	}
	
	
	/**
	 * Инициализация объекта ответа
	 */
	protected function _initResponse()
	{
		if (!is_null($this->_apiResponse))
		{
			return;
		}
		$respType = Yii::app()->request->getParam(self::RESPONSE_TYPE_PARAM);
		if (!in_array($respType, array_keys(self::$_responseClasses)))
		{
			$respType = self::DEF_RESPONSE_TYPE;
		}
		$this->_apiResponse = new self::$_responseClasses[$respType]();
	}
	
	/**
	 * Инициализация прочих параметров
	 */
	protected function _initCustom()
	{
		// Язык
		$paramLang = $this->_apiRequest->getParam(self::LANG_PARAM);
		Yii::app()->setLanguage($paramLang);
	}
	
	/**
	 * Контроль доступа к api 
	 */
	protected function _accessControl()
	{
		$apiUser = $this->_apiRequest->getApiUser();
		if (is_null($apiUser))
		{
			$this->_apiResponse->setStatus(401);
			$this->_apiResponse->setErrors(array('Incorrect api key'));
			$this->_apiResponse->send();
		}
	}

}
