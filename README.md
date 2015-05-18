# larss
RSS builder for Laravel 5

## Installation

1. Pull down the package with composer:

		composer require hyancat/larss

2. Add the service provider to your providers array in `app/config/app.php` file.

		'Hyancat\Larss\LarssServiceProvider'

3. At last, add the alias to the alias array in `app/config/app.php` file.

		'RSS'		=> 'Hyancat\Larss\LarssFacade',

## Usage

### Usage with Cache:

```php

$rss = \RSS::make();
if (! $rss->caching(10)) {

	// make channel.
	$rss->channel([
		'title'       => 'title',
		'description' => 'description',
		'link'        => 'http://www.xxx.yyy',
	])->withImage([
		'url'   => 'http://www.xxx.yyy/logo.png',
	  	'title' => 'title',
	  	'link'  => 'http://www.xxx.yyy',
	]);

	// gen posts data ......
	foreach ($posts as $post) {
		$rss->item([
			'title'             => $post->title,
			'description|cdata' => $post->body,
			'link'              => $post->url,
			// ......
		]);
	}
}

// If you want to save the rss data to file.
$rss->save('rss.xml');

// Or just make a response to the http request.
return \Response::make($rss->render(), 200, ['Content-Type' => 'text/xml']);

```

### Usage without Cache:

```php

// make with channel.
$rss = \RSS::make()->channel([
	'title'       => 'title',
	'description' => 'description',
	'link'        => 'http://www.xxx.yyy',
	// ......
])->withImage([
	'url'   => 'http://www.xxx.yyy/logo.png',
	'title' => 'title',
	'link'  => 'http://www.xxx.yyy',
]);

// gen posts data ......
foreach ($posts as $post) {
	$rss->item([
		'title'             => $post->title,
		'description|cdata' => $post->body,
		'link'              => $post->url,
		// ......
	]);
}

return ...;

```
