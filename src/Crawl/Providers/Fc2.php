<?php

namespace Xzxzyzyz\Crawl\Providers;

class Fc2 implements ProviderInterface
{
    /**
     * URLからプロバイダを判定する
     *
     * @param  string  $url
     * @return boolean
     */
    public function is($url) {
        return preg_match('/.*blog.*\.fc2\.com.*/', $url);
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
        if ($dom->hasAttribute('width') &&
            $dom->hasAttribute('height') &&
            $dom->hasAttribute('alt') &&
            !$dom->hasAttribute('title')
        ) {
            $src = $dom->getAttribute('src');
            if (preg_match('/^http(s)?:\/\/blog-imgs-.*\.fc2\.com.*/', $src)) {
                return str_replace('-origin', '', $src);
            }
        }

        return false;
    }
}