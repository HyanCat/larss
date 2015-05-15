<?php

namespace Hyancat\Larss;


class XMLElement extends \SimpleXMLElement
{
	/**
	 * Adds a new child node - and replaces "&" by "&amp;" on the way ...
	 *
	 * @param string $name      Name of the tag
	 * @param string $value     The tag value, if any
	 * @param null   $namespace The tag namespace, if any
	 * @return \XMLElement
	 */
	public function addChild($name, $value = null, $namespace = null)
	{
		return parent::addChild($name, ($value !== null ? str_replace('&', '&amp;', $value) : null), $namespace);
	}

	/**
	 * Adds a new attribute - and replace "&" by "&amp;" on the way ...
	 *
	 * @param string $name      Name of the attribute
	 * @param string $value     The value to set, if any
	 * @param string $namespace The namespace, if any
	 */
	public function addAttribute($name, $value = null, $namespace = null)
	{
		parent::addAttribute($name, ($value !== null ? str_replace('&', '&amp;', $value) : null), $namespace);
	}

	/**
	 * Pretty much like addChild() but wraps the value in CDATA
	 *
	 * @param string $name  tag name
	 * @param string $value tag value
	 * @return void
	 */
	public function addCdataChild($name, $value)
	{
		$child = $this->addChild($name);
		$child->setChildCdataValue($value);
	}

	/**
	 * Sets a cdata value for this child
	 *
	 * @param string $value The value to be enclosed in CDATA
	 * @return void
	 */
	private function setChildCdataValue($value)
	{
		$domNode = dom_import_simplexml($this);
		$domNode->appendChild($domNode->ownerDocument->createCDATASection($value));
	}

}