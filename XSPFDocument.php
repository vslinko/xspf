<?php

namespace Rithis\XSPF;

use DOMDocument,
    DOMElement,
    DOMXPath;

use Countable;

class XSPFDocument extends DOMDocument implements Countable
{
    const XMLNS = 'http://xspf.org/ns/0/';

    /**
     * @var \DOMNode
     */
    private $trackListNode;

    /**
     * @var array
     */
    protected $index;

    public function __construct()
    {
        parent::loadXML('<?xml version="1.0" encoding="UTF-8"?><playlist version="1" xmlns="http://xspf.org/ns/0/"><trackList></trackList></playlist>');

        $this->refreshNodes();
    }

    public function load($filename, $options = null)
    {
        $result = parent::load($filename, $options);

        $this->forcedValidation();
        $this->refreshNodes();

        return $result;
    }

    public function loadXML($source, $options = null)
    {
        $result = parent::loadXML($source, $options);

        $this->forcedValidation();
        $this->refreshNodes();

        return $result;
    }

    public function addTrack(Track $track)
    {
        $trackElement = new DOMElement('track', '', self::XMLNS);
        $this->trackListNode->appendChild($trackElement);
        $track->fillElement($trackElement);

        $this->index[] = [$trackElement, $track];
    }

    public function removeTrack(Track $track)
    {
        foreach ($this->index as $key => $item) {
            list($trackElement, $indexedTrack) = $item;

            if ($track === $indexedTrack) {
                $this->trackListNode->removeChild($trackElement);
                unset($this->index[$key]);
                return;
            }
        }
    }

    public function hasTrack(Track $track)
    {
        foreach ($this->index as $item) {
            list(, $indexedTrack) = $item;

            if ($track === $indexedTrack) {
                return true;
            }
        }

        return false;
    }

    public function getTracks()
    {
        $tracks = [];

        foreach ($this->index as $item) {
            list(, $track) = $item;
            $tracks[] = $track;
        }

        return $tracks;
    }

    public function count()
    {
        return count($this->index);
    }

    private function refreshNodes()
    {
        $xpath = new DOMXPath($this);
        $xpath->registerNamespace('xspf', self::XMLNS);

        $this->trackListNode = $xpath->query('/xspf:playlist/xspf:trackList')->item(0);
        $this->index = [];

        foreach ($xpath->query('xspf:track', $this->trackListNode) as $trackElement) {
            $this->index[] = [$trackElement, Track::fromDOMElement($this, $trackElement)];
        }
    }

    private function forcedValidation()
    {
        if (!@$this->relaxNGValidate(__DIR__ . '/xspf-1_0.7.rng')) {
            throw new InvalidXSPFException();
        }
    }

    public function loadHTML($source, $options = null)
    {
        self::forbiddenMethod();
    }

    public function loadHTMLFile($filename, $options = null)
    {
        self::forbiddenMethod();
    }

    public function saveHTML()
    {
        self::forbiddenMethod();
    }

    public function saveHTMLFile($filename)
    {
        self::forbiddenMethod();
    }

    private static function forbiddenMethod()
    {
        throw new \BadMethodCallException("Couldn't process XSPF playlist as HTML");
    }
}
