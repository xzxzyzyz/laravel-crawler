<?php

namespace Xzxzyzyz\Crawl\Providers;

class Livedoor implements ProviderInterface
{
    /**
     * URLからプロバイダを判定する
     *
     * @param  string  $url
     * @return boolean
     */
    public function is($url) {
        return preg_match('/.*livedoor\.jp.*/', $url);
    }

    /**
     * DOMから有効なサムネイルを取得する
     * 有効な場合は画像のURLを返却する
     * 無効な場合は false を返却する
     *
     * @param  DOMDocument $dom
     * @return mixed
     */
    public function get($dom) {
        if ($dom->hasAttribute('class') &&
            strpos($dom->getAttribute('class'), 'pict') === 0
        ) {
            return $dom->getAttribute('src');
        }

        return false;
    }
}