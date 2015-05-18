<?php

namespace Hyancat\Larss;


use Illuminate\Support\Facades\Cache;

class Larss
{
	protected $rssVersion = '2.0';
	protected $encoding = 'UTF-8';

	protected $cacheDuration = 0;
	protected $cacheKey;

	protected $channel = [];
	protected $items = [];
	protected $limit = 0;

	/**
	 * Make a new Larss objetc.
	 * @param string $rssVersion
	 * @param string $encoding
	 * @return $this
	 * @internal param $version
	 */
	public function make($rssVersion = '2.0', $encoding = 'UTF-8')
	{
		$this->rssVersion = $rssVersion;
		$this->encoding   = $encoding;

		return $this;
	}

	public function caching($duration, $key = 'laravel-larss')
	{
		$this->cacheKey      = $key;
		$this->cacheDuration = $duration;

		if (! $this->shouldCache()) {
			if ($this->isCached()) {
				Cache::forget($this->cacheKey);
			}

			return false;
		}

		return $this->isCached();
	}

	/**
	 * @param $properties
	 *        --required: [title] & [link] & [description]
	 *        --optional: [language | copyright | managingEditor | webMaster | pubDate | lastBuildDate | category | generator | docs | cloud | ttl | image | rating | textInput | skipHours | skipDays]
	 * @return $this
	 * @throws \Exception
	 */
	public function channel($properties)
	{
		if (! array_key_exists('title', $properties) || ! array_key_exists('description', $properties) || ! array_key_exists('link', $properties)) {
			throw new \Exception('Properties required missing : title, description or link!');
		}
		$this->channel = $properties;

		return $this;
	}

	public function withImage($image)
	{
		$this->channel['image'] = $image;

		return $this;
	}

	/**
	 * @param $properties
	 *        --optional: [title | link | description | author | category | comments | enclosure | guid | pubDate | source]
	 * @return $this
	 * @throws \Exception
	 */
	public function item($properties)
	{
		if (empty($properties)) {
			throw new \Exception('Properties missing!');
		}
		$this->items[] = $properties;

		return $this;
	}

	/**
	 * Limit the count of the output.
	 * @param $limit
	 * @return $this
	 */
	public function limit($limit)
	{
		$this->limit = is_int($limit) && $limit > 0 ? $limit : 0;

		return $this;
	}

	/**
	 * render for page.
	 * @param $cacheDuration
	 * @param $cacheKey
	 * @return $this
	 * @throws \Exception
	 */
	public function render($cacheDuration = null, $cacheKey = null)
	{
		$this->cacheDuration = $cacheDuration ?: $this->cacheDuration;
		$this->cacheKey      = $cacheKey ?: $this->cacheKey;

		// get data from cache.
		if ($this->shouldCache() && $this->isCached()) {
			$value = Cache::get($this->cacheKey);

			return $value;
		}

		// build rss data.
		$items = $this->limit > 0 ? array_slice($this->items, 0, $this->limit) : $this->items;

		$rss = RssBuilder::create($this->rssVersion, $this->encoding)->with('channel', $this->channel)->with('items', $items);;
		if ($this->shouldCache()) {
			Cache::put($this->cacheKey, strval($rss), $this->cacheDuration);
		}

		return $rss;
	}

	/**
	 * Save the rss into file.
	 * @param $filename
	 * @return int The number of bytes written or false if an error occurred.
	 * @throws \Exception
	 */
	public function save($filename)
	{
		return RssBuilder::create($this->rssVersion, $this->encoding)->with('channel', $this->channel)->with('items', $this->items)->save($filename);
	}

	protected function shouldCache()
	{
		return is_int($this->cacheDuration) && $this->cacheDuration > 0;
	}

	protected function isCached()
	{
		return Cache::has($this->cacheKey);
	}
}