<?php
/**
 * Базовый контроллер модуля api
 */
class ApiController extends BaseController
{

	/**
	 * Получить ApiResponse
	 * @return ApiResponseAbstract
	 */
	public function getApiResponse()
	{
		return $this->module->getApiResponse();
	}
	
	/**
	 * Фильтр, разрешающий только get-запросы
	 * @return boolean
	 */
	public function filterGetOnly($filterChain)
	{
		$req = Yii::app()->request;
		if ($req->isPostRequest || $req->isPutRequest || $req->isDeleteRequest)
		{
			$resp = $this->getApiResponse();
			$resp->setStatus(501);
			$resp->setHeaders(array('Allow' => 'GET'));
			$resp->setErrors(array('Method is not allowed'));
			$resp->send();
		}
		$filterChain->run();
	}
}
