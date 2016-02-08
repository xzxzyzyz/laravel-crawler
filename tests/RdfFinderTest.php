<?php

use Xzxzyzyz\Crawl\RdfFinder;

class RdfFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindRdf()
    {
        $finder = new RdfFinder;
        $rss = $finder->read('http://xzxzyzyz.com/blog/');
        $this->assertNotNull($rss);
    }

    public function testFindRdfFails()
    {
        $finder = new RdfFinder;
        $rss = $finder->read('http://google.com/');
        $this->assertNull($rss);
    }
}