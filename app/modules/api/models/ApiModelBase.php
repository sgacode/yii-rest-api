<?php
abstract class ApiModelBase extends CModel
{
	
	/**
	 * Название объекта класса
	 * @var array 
	 */
	protected $_objType = 'api_object';
	
	/**
	 * Атрибуты, значение которых должно возвращаться как int
	 * @var array 
	 */
	protected $_intAttrs = array();


	/**
	 * Обязательны атрибуты
	 * @var array 
	 */
	protected $_requiredAttrs = array();

	/**
	 * Переопределяем функцию получения атрибутов - проводим необходимую обработку значений
	 * @param mixed $names
	 * @return array
	 */
	public function getAttributes($names=NULL)
	{
		$attrs = parent::getAttributes($names);
		foreach ($attrs as $attrKey => $attr)
		{
			if (is_array($attr) && !empty($attr) && $attr[0] instanceof ApiModelBase)
			{
				$attrArr = array();
				foreach ($attr as $item)
				{
					if ($item instanceof ApiModelBase)
					{
						$attrArr[] = $item->getAttributes();
					}
				}
				$attrs[$attrKey] = $attrArr;
			}
			elseif ($attr instanceof ApiModelBase)
			{
				$attrs[$attrKey] = $attr->getAttributes();
			}
			elseif (in_array($attrKey, $this->_intAttrs))
			{
				$attrs[$attrKey] = (int) $attr;
			}
		}
		return $attrs;
	}
	
	public function rules()
    {
		$safeAttrs = array_diff(array_keys($this->getAttributes()), $this->_requiredAttrs);
		return array(
			array($safeAttrs, 'safe'),
			array(
				$this->_requiredAttrs,
				'required',
				'message' => ApiModule::t('{attribute} является обязательным параметром')
			),
		);
	}
	
	public function attributeLabels()
	{
		foreach ($this->attributeNames() as $attr)
		{
			$labels[$attr] = $attr;
		}
		return $labels;
	}
	
	/**
	 * Получить название типа объекта
	 * @return string
	 */
	public function getObjType()
	{
		return $this->_objType;
	}
}