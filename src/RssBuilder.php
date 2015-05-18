<?php
/**
 * RssBuilder.php
 * ruogu-community
 *
 * Created by HyanCat on 15/5/16.
 * Copyright (C) 2015 HyanCat. All rights reserved.
 */


namespace Hyancat\Larss;


class RssBuilder
{
	protected $encoding = 'UTF-8';
	protected $xmlVersion = '1.0';

	protected $rssVersion = '2.0';

	protected $document;
	protected $rssNode;
	protected $channelNode;

	public function __construct($rssVersion = '2.0', $encoding = 'UTF-8')
	{
		$this->rssVersion = $rssVersion;
		$this->encoding   = $encoding;

		$this->document = new \DOMDocument($this->xmlVersion, $this->encoding);
	}

	/**
	 * create a new builder and build it.
	 * @param string $rssVersion
	 * @param string $encoding
	 * @return \Hyancat\Larss\RssBuilder
	 */
	public static function create($rssVersion = '2.0', $encoding = 'UTF-8')
	{
		$builder = new self($rssVersion, $encoding);

		return $builder->build();
	}

	/**
	 * Build the basic rss data.
	 * @return $this
	 */
	public function build()
	{
		$this->rssNode = $this->buildRssNode();
		$this->document->appendChild($this->rssNode);

		return $this;
	}

	/**
	 * To build more data in the basic <rss/> node.
	 * @param $nodeName
	 * @param $property
	 * @return $this
	 * @throws \Exception
	 */
	public function with($nodeName, $property)
	{
		switch ($nodeName) {
			case 'channel':
				$this->channelNode = $this->buildChannel($property);
				$this->rssNode->appendChild($this->channelNode);
				break;
			case 'items' :
				if (is_null($this->channelNode)) {
					throw new \Exception('Channel Element Not Defined.');
				}
				$this->buildItemsInChannel($property, $this->channelNode);
				break;
			default:
				break;
		}

		return $this;
	}

	/**
	 * Save the rss into file.
	 * @param $filename
	 * The path of file to save rss.
	 * @return int The number of bytes written or false if an error occurred.
	 */
	public function save($filename)
	{
		return $this->document->save($filename);
	}

	public function __toString()
	{
		return $this->document->saveXML();
	}


	/**
	 * Build the <rss/> node
	 * @return \DOMElement
	 */
	protected function buildRssNode()
	{
		$rssNode = $this->document->createElement('rss');

		$rssNode->setAttribute('version', $this->rssVersion);
		$rssNode->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
		$rssNode->setAttribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
		$rssNode->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$rssNode->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
		$rssNode->setAttribute('xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/');
		$rssNode->setAttribute('xmlns:slash', 'http://purl.org/rss/1.0/modules/slash/');

		return $rssNode;
	}

	/**
	 * Build the <channel/> node.
	 * @param $channel
	 * The properties in the channel.
	 * @return \DOMElement
	 */
	protected function buildChannel($channel)
	{
		$channelNode = $this->document->createElement('channel');

		foreach ($channel as $channelName => $channelValue) {
			if (is_string($channelValue)) {
				$channelPropertyNode = new \DOMElement($channelName, $channelValue);
			}
			elseif (is_array($channelValue)) {
				$channelPropertyNode = $channelNode->ownerDocument->createElement($channelName);
				foreach ($channelValue as $subKey => $subValue) {
					$subNode = new \DOMElement($subKey, $subValue);
					$channelPropertyNode->appendChild($subNode);
				}
			}
			else {
				continue;
			}
			$channelNode->appendChild($channelPropertyNode);
		}

		return $channelNode;
	}

	/**
	 * Build the <item/> nodes.
	 * @param $items
	 * @param $channelNode
	 */
	protected function buildItemsInChannel($items, $channelNode)
	{
		foreach ($items as $item) {
			$itemNode = $this->document->createElement('item');
			foreach ($item as $itemKey => $itemValue) {
				$options = explode('|', $itemKey);
				if (in_array('cdata', $options)) {
					$section = $itemNode->ownerDocument->createElement($options[0]);
					$section->appendChild(new \DOMCdataSection($itemValue));
					$itemNode->appendChild($section);
				}
				elseif (strpos($options[0], ':') !== false) {
					$itemNode->appendChild(new \DOMElement($options[0], $itemValue, 'http://purl.org/dc/elements/1.1/'));
				}
				else {
					$itemNode->appendChild(new \DOMElement($options[0], $itemValue));
				}
			}
			$channelNode->appendChild($itemNode);
		}
	}

}