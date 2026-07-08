<?php

if (! function_exists('purify_html')) {
    /**
     * Purifies HTML to prevent XSS attacks while allowing safe tags.
     *
     * @param string|null $html The dirty HTML string
     *
     * @return string The purified HTML string
     */
    function purify_html(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        static $purifier = null;

        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            
            // Set encoding
            $config->set('Core.Encoding', 'UTF-8');
            
            // Allow basic WYSIWYG formatting
            $config->set('HTML.Allowed', 'p,b,strong,i,em,s,strike,ul,ol,li,br,h1,h2,h3,h4,h5,h6,blockquote,pre,code,a[href|title|target]');
            
            // Prevent target="_blank" vulnerability by automatically adding rel="noreferrer noopener"
            $config->set('HTML.TargetBlank', true);
            
            // Ensure proper caching directory (CodeIgniter's writable/cache)
            $cachePath = WRITEPATH . 'cache/purifier';
            if (! is_dir($cachePath)) {
                mkdir($cachePath, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cachePath);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}
