<?php

use Xzxzyzyz\Crawl\ScrapingThumbnail;
use Xzxzyzyz\Crawl\Providers\Ameba;
use Xzxzyzyz\Crawl\Providers\Fc2;
use Xzxzyzyz\Crawl\Providers\Livedoor;

class ScrapingThumbnailTest extends \PHPUnit_Framework_TestCase
{
    protected $thumbnail;

    public function setUp()
    {
        parent::setUp();

        $ameba = new Ameba;
        $fc2 = new Fc2;
        $livedoor = new Livedoor;
        $this->thumbnail = new ScrapingThumbnail($ameba, $fc2, $livedoor);
    }

    public function testScrapingThumbnail()
    {
        $this->thumbnail->read('https://github.com/xzxzyzyz/laravel-crawler');
        $this->assertNotNull($this->thumbnail->get());
    }

    public function testScrapingThumbnailFails()
    {
        $this->thumbnail->read('https:/google.com/');
        $this->assertNull($this->thumbnail->get());
    }
}