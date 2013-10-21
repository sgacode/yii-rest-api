<?php
class ApiXmlResponse extends ApiResponseAbstract
{
	
	const XML_HEADER = '<?xml version="1.0" encoding="utf-8"?>';
	
	const ERR_ROTT_ELEM = 'errors';
	
	/**
	 * @see ApiResponseAbstract 
	 */
	protected $_contentType = 'text/xml';
	
	/**
	 * @see ApiResponseAbstract 
	 */
	protected function _renderContent()
	{
		$xmlElem = new SimpleXMLElement(self::XML_HEADER . '<' . $this->_rootElem . '/>');
		$this->_paramsToXml($this->_params, $xmlElem);
		$this->_body = $xmlElem->asXML();
	}
	
	/**
	 * @see ApiResponseAbstract 
	 */
	protected function _renderErrors()
	{
		$xmlElem = new SimpleXMLElement(self::XML_HEADER . '<' . self::ERR_ROTT_ELEM . '/>');
		$this->_paramsToXml($this->_errors, $xmlElem, 'error');
		$this->_body = $xmlElem->asXML();
	}
	
	/**
	 * Конвертация массива в объект SimpleXMLElement
	 * @param array $data
	 * @param SimpleXMLElement $xmlObj
	 */
	protected function _paramsToXml($data, &$xmlObj, $nodeName = NULL)
	{
		foreach ($data as $key => $value)
		{
			$curNodeName = $nodeName;
			if ($value instanceof ApiModelBase)
			{
				$curNodeName = $value->objType;
				$value = $value->getAttributes();	
			}
			if (is_array($value))
			{
				if (!is_numeric($key))
				{
					$subnode = $xmlObj->addChild($key);
				}
				else
				{
					if (is_null($curNodeName))
					{
						$curNodeName = 'item' . $key;
					}
					$subnode = $xmlObj->addChild($curNodeName);	
				}
				$this->_paramsToXml($value, $subnode, $nodeName);
			}
			else
			{
				if (!is_numeric($key))
				{
					$curNodeName = $key;
				}
				else
				{
					$curNodeName = 'item' . $key;
				}
				$xmlObj->addChild($curNodeName, CHtml::encode($value));
			}
		}
	}
}