# DOMDocument wrapper for XSPF playlist

## Installation

Run in your project root:

```composer require rithis/xspf:@dev```

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$playlist = new Rithis\XSPF\XSPFDocument();
$playlist->load("some_playlist.xspf");

$track = new Rithis\XSPF\Track();
$track->setCreator('Creator');
$track->setTitle('Example');
$playlist->addTrack($track);

/** @var $track \Rithis\XSPF\Track */
foreach ($playlist->getTracks() as $track) {
    echo sprintf("%s - %s\n", $track->getCreator(), $track->getTitle());
}
```
