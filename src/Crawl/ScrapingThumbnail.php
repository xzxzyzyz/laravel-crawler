<?php

namespace Xzxzyzyz\Crawl;

use Xzxzyzyz\Crawl\Providers\Ameba;
use Xzxzyzyz\Crawl\Providers\Fc2;
use Xzxzyzyz\Crawl\Providers\Livedoor;
use DOMDocument;

class ScrapingThumbnail
{
    /**
     * @var mixed
     */
    protected $image = null;

    /**
     * @var mixed
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @param \RedpotionAdmin\Services\Crawl\Providers\Ameba    $ameba
     * @param \RedpotionAdmin\Services\Crawl\Providers\Fc2      $fc2
     * @param \RedpotionAdmin\Services\Crawl\Providers\Livedoor $livedoor
     */
    public function __construct(Ameba $ameba, Fc2 $fc2, Livedoor $livedoor)
    {
        $this->providers[] = $ameba;
        $this->providers[] = $fc2;
        $this->providers[] = $livedoor;
    }

    /**
     * サムネイルの取得
     *
     * @param  string $url
     * @return string | null
     */
    public function read($url)
    {
        $this->reset();
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'ScrapingThumbnail');

        $response = curl_exec($curl);

        curl_close($curl);

        if (!empty($response)) {
            $this->setUrl($url);
            $this->parse($response);
        }

        return $this->get();
    }

    /**
     * HTMLのパース
     *
     * @param  html document $html
     * @return void
     */
    protected function parse($html)
    {
        $old_libxml_error = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $meta_tags = $doc->getElementsByTagName('meta');
        foreach ($meta_tags as $tag) {
            // Twitter
            if ($tag->hasAttribute('property') && strpos($tag->getAttribute('property'), 'twitter:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 8), '-', '_');

                if ($key == 'image') {
                    $this->set($tag->getAttribute('content'));
                    break;
                }
            }
            // Facebook
            if ($tag->hasAttribute('property') && strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');

                if ($key == 'image') {
                    $this->set($tag->getAttribute('content'));
                    break;
                }
            }
        }

        // headerのmeta情報から取得できなかった場合に、bodyのimg要素から取得
        if (!$this->exists()) {
            $img_tags = $doc->getElementsByTagName('img');
            foreach ($img_tags as $tag) {
                if ($image = $this->getFromProvider($tag)) {
                    $this->set($image);
                    break;
                }
            }
        }
    }

    /**
     * 対象URLを保持
     *
     * @param   string $url
     * @return  string
     */
    protected function setUrl($url)
    {
        return $this->url = $url;
    }

    /**
     * 対象URLを取得
     *
     * @return string
     */
    protected function getUrl()
    {
        return $this->url;
    }

    /**
     * 登録されたプロバイダ別にサムネイルの取得
     *
     * @param  DOMDocument $dom
     * @return string | false
     */
    protected function getFromProvider($dom)
    {
        foreach ($this->providers as $provider) {
            if ($provider->is($this->getUrl())) {
                return $provider->get($dom);
            }
        }

        return false;
    }

    /**
     * サムネイルの保持
     *
     * @param  string $image
     * @return string
     */
    protected function set($image)
    {
        return $this->image = $image;
    }

    /**
     * 取得済みのサムネイル
     * @return string
     */
    protected function get()
    {
        return $this->image;
    }

    /**
     * サムネイルのリセット
     *
     * @param  string $image
     */
    protected function reset()
    {
        $this->image = null;
    }

    /**
     * サムネイルの取得済み判定
     *
     * @return boolean
     */
    public function exists()
    {
        return !is_null($this->image);
    }

}
