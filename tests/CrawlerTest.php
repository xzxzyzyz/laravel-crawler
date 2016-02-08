<?php

use Xzxzyzyz\Crawl\Crawler;
use Xzxzyzyz\Crawl\ScrapingThumbnail;
use Xzxzyzyz\Crawl\Providers\Ameba;
use Xzxzyzyz\Crawl\Providers\Fc2;
use Xzxzyzyz\Crawl\Providers\Livedoor;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    protected $crawler;

    public function setUp()
    {
        parent::setUp();

        $ameba = new Ameba;
        $fc2 = new Fc2;
        $livedoor = new Livedoor;
        $thumbnail = new ScrapingThumbnail($ameba, $fc2, $livedoor);

        $this->crawler = New Crawler($thumbnail);
    }

    public function testCrawler()
    {
        $crawl = $this->crawler->set('http://xzxzyzyz.com/blog/feed');
        $this->assertFalse($crawl->fails());
    }

    public function testCrawlerFails()
    {
        $crawl = $this->crawler->set('http://google.com');
        $this->assertTrue($crawl->fails());
    }
}