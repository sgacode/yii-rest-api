<?php

$apiV1Prefix = '/v1/';

return array(
	'components' => array(
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
					'categories'=> 'apierr.*',
					'logPath' => ROOT . '/../data/logs',
					'logFile' => 'errors.log',
					'maxFileSize' => 2048,
				),
			)
		),
		// Обработка ошибок
		'errorHandler' => array(
			'errorAction' => 'api/common/error',
		),
		// Url
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
			'rules' => array(
				$apiV1Prefix . '<controller:\w+>/<action:\w+>' => array('api/<controller>/<action>'),
			),
		),
		// CacheManager
		'cacheManager' => array(
			'class' => 'CacheManager',
		),
	)
);
