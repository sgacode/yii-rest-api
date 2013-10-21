<?php
class CitiesController extends ApiController
{
	
	/**
	 * @var array 
	 */
	public static $cityAttrsMap = array(
		'id' => 'id',
		'name' => 'name',
	);
	
	public function filters()
    {
        return array(
            'getOnly + list',
		);
	}
	
	/**
	 * Получить список городов, в которых есть гостиницы
	 */
	public function actionList()
	{
		// Получаем из БД список городов
		$citiesGw = new GwCities();
		$cities = ApiModule::listToApiObjs(
			$citiesGw->getCitiesWithHotels(),
			self::$cityAttrsMap,
			'ApiCity'
		);
		// Выдаём ответ
		$resp = $this->getApiResponse();
		$resp->setRootElem('cities');
		$resp->setParams($cities);
		$resp->send();
	}
	
}
