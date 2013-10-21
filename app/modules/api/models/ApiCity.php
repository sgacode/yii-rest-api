<?php
class ApiCity extends ApiModelBase
{
	public $id;
	
	public $name;
	
	/**
	 * @see ApiModelBase
	 */
	protected $_objType = 'city';
	
	public function attributeNames()
	{
		return array(
			'id',
			'name',
		);
	}
}
