<?php

namespace Becker\GridBundle\Grid;

class Column {
	
	private $name = '';
	
	private $key = '';
	
	private $options = array();
	
	public function __construct($key, $name = null, $options = array()) {
		$this->setName($name);
		$this->setKey($key);
		$this->setOptions($options);
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	public function getOptions()
	{
		return $this->options;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function setKey($key)
	{
		$this->key = $key;
	}
	
	public function getName()
	{
		return $this->name ?: ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $this->getKey()));
	}
	
	public function getKey()
	{
		return $this->key;
	}
}