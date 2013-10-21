<?php
class CommonController extends ApiController
{
	
	/**
	 * Ошибки
	 */
	public function actionError()
	{
		$e = Yii::app()->errorHandler->error;
		$r = $this->getApiResponse();
		$r->setStatus($e['code']);
		$msg = '';
		if (isset(ApiResponseAbstract::$codes[$e['code']]))
		{
			$msg = ApiResponseAbstract::$codes[$e['code']]['title'];
		}
		$r->setErrors(array($msg));
		$r->send();
	}
}