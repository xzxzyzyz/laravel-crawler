<?php

namespace Xzxzyzyz\Crawl;

use SimplePie;

class Crawler
{
    /**
     * @var SimplePie
     */
    public $reader;

    /**
     * @var mixed
     */
    public $error = null;

    /**
     * @var \RedpotionAdmin\Services\Crawl\ScrapingThumbnail
     */
    protected $thumbnail;

    /**
     * 取得したFeedの一覧
     *
     * @var mixed
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $options = [
        'image' => true
    ];

    /**
     * @param \RedpotionAdmin\Services\Crawl\ScrapingThumbnail $thumbnail
     */
    public function __construct(ScrapingThumbnail $thumbnail)
    {
        $this->thumbnail = $thumbnail;

        $this->reader = new SimplePie();
        $this->reader->enable_cache(false);
        $this->reader->set_timeout(10);
    }

    /**
     * 与えられたRSSのURLから情報を取得する
     *
     * @param string $url
     */
    public function set($url)
    {
        $this->reader->force_feed(true);
        $this->reader->set_feed_url($url);

        $this->reader->init();
        $this->items = $this->reader->get_items();
        $this->error = $this->reader->error;

        return $this;
    }

    /**
     * 取得済みのFeedを整形して返却する
     *
     * @return array
     */
    public function fetch($options = [])
    {
        $_options = array_merge($this->options, $options);

        return array_map(function($item) use($_options) {
                    mb_language("Japanese");
                    $description = strip_tags($item->get_description());
                    $description = mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8');
                    $description = mb_convert_encoding($description, 'UTF-8' , 'HTML-ENTITIES');
                    $description = preg_replace('/\n|\r|\r\n/','',$description);

                    return [
                        'title' => $item->get_title(),
                        'description' => str_limit($description, 250),
                        'link' => $item->get_link(),
                        'dc_date' => $item->get_date('Y-m-d H:i:s'),
                        'image' => $_options['image']? $this->thumbnail->read($item->get_link()): null
                    ];
                },
                array_filter($this->items, function($item) {
                    // PR記事と未来の記事は除去
                    return (!$this->isPR($item->get_link()) &&
                            $item->get_date('Y-m-d H:i:s') <= date('Y-m-d H:i:s'));
                }));
    }

    /**
     * 取得したFeedの一覧を返却する
     *
     * @return array
     */
    public function get() {
        return $this->items;
    }

    public function fails()
    {
        return !is_null($this->error);
    }

    /**
     * 広告の判定
     *
     * @param  string  $url
     * @return boolean
     */
    protected function isPR($url) {
        return preg_match('/.*rss\.rssad\.jp.*/', $url);
    }
}
