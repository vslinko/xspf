<?php

namespace Rithis\XSPF;

use DOMElement;
use DOMXPath;
use DOMDocument;

class Track
{
    protected $locations = [];
    protected $identifier;
    protected $title;
    protected $creator;
    protected $annotation;
    protected $info;
    protected $image;
    protected $album;
    protected $trackNum;
    protected $duration;
    protected $links = [];
    protected $meta = [];

    public function addLocation($location)
    {
        $this->locations[] = $location;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setAnnotation($annotation)
    {
        $this->annotation = $annotation;
    }

    public function getAnnotation()
    {
        return $this->annotation;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setAlbum($album)
    {
        $this->album = $album;
    }

    public function getAlbum()
    {
        return $this->album;
    }

    public function setTrackNum($trackNum)
    {
        $this->trackNum = $trackNum;
    }

    public function getTrackNum()
    {
        return $this->trackNum;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function addLink($rel, $content)
    {
        $this->links[] = ['rel' => $rel, 'content' => $content];
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function addMeta($rel, $content)
    {
        $this->meta[] = ['rel' => $rel, 'content' => $content];
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function fillElement(DOMElement $el)
    {
        $one = function ($name) use ($el) {
            $value = $this->{$name};

            if ($value) {
                $el->appendChild(new DOMElement($name, $value, XSPFDocument::XMLNS));
            }
        };
        $many = function ($property, $name, $attr = null) use ($el) {
            foreach ($this->{$property} as $item) {
                if ($attr) {
                    $itemElement = new DOMElement($name, $item['content'], XSPFDocument::XMLNS);
                    $el->appendChild($itemElement);
                    $itemElement->setAttribute($attr, $item[$attr]);
                } else {
                    $el->appendChild(new DOMElement($name, $item, XSPFDocument::XMLNS));
                }
            }
        };

        $many('locations', 'location');
        $one('identifier');
        $one('title');
        $one('creator');
        $one('annotation');
        $one('info');
        $one('image');
        $one('album');
        $one('trackNum');
        $one('duration');
        $many('links', 'link', 'rel');
        $many('meta', 'meta', 'rel');
    }

    public static function fromDOMElement(DOMDocument $doc, DOMElement $el)
    {
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('xspf', XSPFDocument::XMLNS);

        $one = function ($name) use ($el, $xpath) {
            $nodeList = $xpath->query("xspf:$name", $el);

            return $nodeList->length > 0 ? $nodeList->item(0)->nodeValue : null;
        };

        $many = function ($name, $attr = null) use ($el, $xpath) {
            $nodes = [];

            /** @var $el \DOMElement */
            foreach ($xpath->query("xspf:$name", $el) as $el) {
                if ($attr) {
                    $nodes[] = [
                        'content' => $el->nodeValue,
                        $attr => $el->getAttribute($attr),
                    ];
                } else {
                    $nodes[] = $el->nodeValue;
                }
            }

            return $nodes;
        };

        $track = new self();

        $track->setIdentifier($one('identifier'));
        $track->setTitle($one('title'));
        $track->setCreator($one('creator'));
        $track->setAnnotation($one('annotation'));
        $track->setInfo($one('info'));
        $track->setImage($one('image'));
        $track->setAlbum($one('album'));
        $track->setTrackNum($one('trackNum'));
        $track->setDuration($one('duration'));

        foreach ($many('location') as $location) {
            $track->addLocation($location);
        }

        foreach ($many('link', 'rel') as $link) {
            $track->addLink($link['rel'], $link['content']);
        }

        foreach ($many('meta', 'rel') as $meta) {
            $track->addMeta($meta['rel'], $meta['content']);
        }

        return $track;
    }
}
