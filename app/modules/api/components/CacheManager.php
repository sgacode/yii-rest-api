<?php
class CacheManager extends CComponent
{
	
	/**
	 * Префикс для меток кэша
	 */
	const CACHE_PREFIX = 'api';
	
	/**
	 * Время жизни кэша по умолчанию
	 */
	const CACHE_LIFETIME = 86400;
	
	public function init()
	{
		
	}

	/**
	 * Получить значение из кэша
	 * @param string $id
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($id, $default = NULL)
	{
		$cache = Yii::app()->cache;
		if (is_null($cache))
		{
			return $default;
		}
		$data = $cache->get($this->_makeCacheId($id));
		if (is_null($data))
		{
			return $default;
		}
		return $data;
	}
	
	/**
	 * Установить кэш
	 * @param string $id
	 * @param mixed $data
	 * @param int $expire
	 * @return mixed
	 */
	public function set($id, $data, $expire = self::CACHE_LIFETIME)
	{
		$cacheId = $this->_makeCacheId($id);
		$cache = Yii::app()->cache;
		if (is_null($cache))
		{
			return;
		}
		return $cache->set($cacheId, $data, $expire);
	}
	
	/**
	 * Генерация метки кэша
	 * @param string $id
	 * @return string
	 */
	protected function _makeCacheId($id)
	{
		return self::CACHE_PREFIX . '_' . $id;
	}
}
