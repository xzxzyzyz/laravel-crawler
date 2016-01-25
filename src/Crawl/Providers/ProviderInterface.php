<?php

namespace Xzxzyzyz\Crawl\Providers;

interface ProviderInterface
{
    /**
     * URLからプロバイダを判定する
     *
     * @param  string  $url
     * @return boolean
     */
    public function is($url);

    /**
     * DOMから有効なサムネイルを取得する
     * 有効な場合は画像のURLを返却する
     * 無効な場合は false を返却する
     *
     * @param  [type] $dom [description]
     * @return mixed
     */
    public function get($dom);
}