<?php

namespace Xzxzyzyz\Crawl;

use Illuminate\Support\Facades\Validator;
use DOMDocument;

class RdfFinder
{
    /**
     * @var array
     */
    protected $types = [
        'application/x.atom+xml',
        'application/atom+xml',
        'application/xml',
        'text/xml',
        'application/rss+xml',
        'application/rdf+xml'
    ];

    /**
     * @var mixed
     */
    protected $url;

    /**
     * @var mixed
     */
    protected $rdf = null;

    /**
     * @param  string $url
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate($url)
    {
        $rules = [
            'url' => ['required', 'url'],
        ];

        $messages = [
            "required" => "RSS Feedの取得には:attributeが必要です。",
            "url" => ":attributeが存在しない為、RSS Feedが取得できません。",
        ];

        $attributes = [
            'url' => 'URL'
        ];

        return Validator::make(['url' => $url], $rules, $messages, $attributes);
    }

    /**
     * RSSの取得
     *
     * @param  string $url
     * @return mixed
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
        curl_setopt($curl, CURLOPT_USERAGENT, 'RdfFinder');

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
     * @param  string $html
     * @return void
     */
    protected function parse($html)
    {
        $old_libxml_error = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $links = $doc->getElementsByTagName('link');
        foreach ($links as $link) {
            if (in_array($link->getAttribute('type'), $this->types) && $link->getAttribute('href')) {
                $rdf = $link->getAttribute('href');

                if (strstr($rdf, 'http://') !== false) {
                    $fullUrl = $rdf;
                } else {
                    $urlParts = parse_url($url);
                    $fullUrl = "http://{$urlParts['host']}";
                    if (isset($urlParts['port'])) {
                        $fullUrl .= ":{$urlParts['port']}";
                    }
                    if ($rdf{0} != '/'){
                        if (isset($urlParts['path'])) {
                            $fullUrl .= dirname($urlParts['path']);
                        }
                        if (substr($fullUrl, -1) != '/') {
                            $fullUrl .= '/';
                        }
                    }
                    $fullUrl .= $rdf;
                }

                $this->set($fullUrl);
                break;
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
     * RDFの保持
     *
     * @param  string $rdf
     * @return string
     */
    protected function set($rdf)
    {
        return $this->rdf = $rdf;
    }

    /**
     * 取得済みのRDF
     * @return string
     */
    public function get()
    {
        return $this->rdf;
    }

    /**
     * RDFのリセット
     *
     * @param  string $image
     */
    protected function reset()
    {
        $this->rdf = null;
    }

    /**
     * RDFの取得済み判定
     *
     * @return boolean
     */
    public function exists()
    {
        return !is_null($this->rdf);
    }

}
