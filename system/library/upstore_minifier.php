<?php
require_once DIR_SYSTEM . 'minifyns/lib/Minify/CSS/Compressor.php';
require_once DIR_SYSTEM . 'minifyns/lib/Minify/CommentPreserver.php';
require_once DIR_SYSTEM . 'minifyns/lib/Minify/CSS/UriRewriter.php';
require_once DIR_SYSTEM . 'minifyns/lib/Minify/CSS.php';
require_once DIR_SYSTEM . 'minifyns/lib/Minify/JSMin.php';
define('UPSTORE_VERSION', '1.6');
class Upstore_Minifier {
    private $path;
    private $dir = 'minify-cache/';
    private $developer_mode = '1';
    private $hostname = '';
    private $request;
    private $cache;
    private $config;
    private $minify_css = false;
    private $minify_js = false;
    private $styles = array();
    private $scripts = array(
        'header'    => array(),
        'footer'    => array()
    );

    public function __construct($registry) {
        $this->hostname = md5($this->getHostName());
        $this->config = $registry->get('config');
        $this->path = $this->getCachePath();
        $this->dir = $this->getCacheDir();

        $minify_css = $this->config->get('config_minify_css');
        $minify_js = $this->config->get('config_minify_js');
        if(isset($minify_css) && ($minify_css == 1) || (isset($minify_js) && ($minify_js == 1))){
        $developer_mode = 0;
        } else {
        $developer_mode = 1;
        }
        $this->setDeveloperMode($developer_mode);
        $this->setMinifyCss($minify_css);
        $this->setMinifyJs($minify_js);

    }

    private function getHostName() {
        $protocol = isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')) ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        return $protocol . '://' . $host;
    }
    private function staticAsset($url) {

        if ($this->isExternalResource($url)) {
            return $url;
        }
        $https = isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'));

        if ($https && defined('HTTPS_STATIC_CDN')) {
            return HTTPS_STATIC_CDN . $url;
        }
        if (defined('HTTP_STATIC_CDN')) {
            return HTTP_STATIC_CDN . $url;
        }
       return ($https ? $this->config->get('config_ssl') : $this->config->get('config_url')) .  $url;
    }

    private function isLocalResource($url) {
        if (strpos($url, '//') === 0) {
            return false;
        }
        return !filter_var($url, FILTER_VALIDATE_URL);
    }

    private function isExternalResource($url) {
        return !$this->isLocalResource($url);
    }

    private function setDeveloperMode($mode) {
        $this->developer_mode = $this->canCache() ? $mode : false;
    }
    private function getDeveloperMode() {

        if ($this->canCache()) {
            if ($this->developer_mode) {
               $this->createCacheFiles();
            }
            return $this->developer_mode;
        }
        return true;
    }
    private function getCachePath() {
        return realpath(DIR_SYSTEM . '../') . DIRECTORY_SEPARATOR . $this->dir;
    }

    private function getCacheDir() {
        return $this->dir;
    }

    private function canCache() {
        $cache_path = $this->getCachePath();
        if (file_exists($cache_path) && is_writable($cache_path)) {
            return true;
        }
        if (is_writable(realpath(DIR_SYSTEM . '../')) && @mkdir($cache_path)) {
            return true;
        }
        return false;
    }

    private function createCacheFiles() {
        $path = $this->getCachePath();
        if (!file_exists($path . '.htaccess')) {
            file_put_contents($path . '.htaccess', file_get_contents(DIR_SYSTEM . 'minifyns/data/cache.htaccess'));
        }
        if (!file_exists($path . 'empty.css')) {
            file_put_contents($path . 'empty.css', '');
        }
        if (!file_exists($path . 'empty.js')) {
            file_put_contents($path . 'empty.js', '');
        }
    }
    public function deleteCache($pattern = '*') {
        $path = $this->getCachePath();
        if (!$path) return;
        $files = glob($path . $pattern);
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function getMinifyCss() {
        return $this->minify_css;
    }

    public function setMinifyCss($value) {
        $this->minify_css = $value;
    }

    public function getMinifyJs() {
        return $this->minify_js;
    }

    public function setMinifyJs($value) {
        $this->minify_js = $value;
    }
    public function addStyle($href, $position = 'header') {
        $this->styles[$position][md5($href)] = $href;
    }
    public function addScript($src, $position = 'header') {
        $this->scripts[$position][md5($src)] = $src;
    }
    public function css($position = 'header', $src= false) {

        if ($this->canCache() && !$this->getDeveloperMode() && $this->minify_css) {

            $combined_css_file = 'min_' . $this->getHash($this->styles[$position], 'css');

            if (!file_exists($this->path . $combined_css_file)) {
                /* parse all styles, generate corresponding minified css file */

                foreach ($this->styles[$position] as $style) {
                    $file = $this->path . $this->getHash($style, 'css');
                    if (!file_exists($file)) {
                        $css_file = realpath(DIR_SYSTEM . '../' . $this->removeQueryString($style));
                        if (!file_exists($css_file)) {
                            continue;
                        }
                        $content = file_get_contents($css_file);
                        if ($style === 'catalog/view/javascript/bootstrap/css/bootstrap.min.css') {
                            file_put_contents($file, $content, LOCK_EX);
                        } else {
                            $content_min = Minify_CSS::minify($content, array(
                                'preserveComments' => false,
                                'currentDir' => dirname($css_file)
                            ));
                            file_put_contents($file, $content_min, LOCK_EX);
                        }

                    }
                }
                /* combine all styles into one file */
                $fh = @fopen($this->path . $combined_css_file, 'w');
                flock($fh, LOCK_EX);
                foreach ($this->styles[$position] as $style) {
                    $file = $this->path . $this->getHash($style, 'css');
                    if (!file_exists($file)) {
                        continue;
                    }
                    $content = file_get_contents($file);
                    fwrite($fh, $content);
                }
            }

            /* return link tag */
            if ($src) {
                return array($this->staticAsset($this->dir . $combined_css_file));
            }

            return $this->printStyle($this->dir . $combined_css_file);
        }

        /* return link tag */
        if ($src) {
            $src_style = array();
            foreach ($this->styles[$position] as $style) {
                $src_style[] = $style;
            }
            return $src_style;
        }

        $assets = '';
        foreach ($this->styles[$position] as $style) {
            $assets .= $this->printStyle($style);
        }

        return $assets;
    }

    public function js($position = 'header', $src= false) {

        if ($this->canCache() && !$this->getDeveloperMode() && $this->minify_js) {
            /* generate file if not exits */
            $combined_js_file = 'min_' . $this->getHash($this->scripts[$position], 'js');
            if (!file_exists($this->path . $combined_js_file)) {
                /* parse all scripts, generate corresponding minified js file */
                foreach ($this->scripts[$position] as $script) {
                    $file = $this->path . $this->getHash($script, 'js');
                    if (!file_exists($file)) {
                        $js_file = realpath(DIR_SYSTEM . '../' . $this->removeQueryString($script));
                        if (!file_exists($js_file)) {
                            continue;
                        }
                        $content = file_get_contents($js_file);
                        $content_min = JSMin::minify($content);
                        file_put_contents($file, $content_min, LOCK_EX);
                    }
                }

                /* combine all scripts into one file */
                $fh = @fopen($this->path . $combined_js_file, 'w');
                flock($fh, LOCK_EX);
                foreach ($this->scripts[$position] as $script) {
                    $file = $this->path . $this->getHash($script, 'js');
                    if (!file_exists($file)) {
                        continue;
                    }
                    $content = file_get_contents($file);
                    fwrite($fh, ';' . $content);
                }
            }
            /* return link tag */
            if ($src) {
                return array($this->staticAsset($this->dir . $combined_js_file));
            }
            if ($position === 'footer_onload') {
                return $this->printScript($this->dir . $combined_js_file, ' defer');
            }
            return $this->printScript($this->dir . $combined_js_file);
        }

        /* return link tag */
        if (!empty($this->scripts[$position]) && $src) {
            $src_scripts = array();
            foreach ($this->scripts[$position] as $script) {
                $src_scripts[] = $script;
            }
            return $src_scripts;
        }

        $assets = '';
        if (!empty($this->scripts[$position])) {
            foreach ($this->scripts[$position] as $script) {
                $assets .= $this->printScript($script);
            }
        }

        return $assets;
    }

    private function getHash($files, $ext) {
        $hash = '';
        if (is_array($files)) {
            foreach ($files as $file) {
                $hash .= $file;
            }
        } else {
            $hash = $files;
        }
        $hash .= UPSTORE_VERSION;
        $hash .= $this->getHostName();
        return md5($hash) . '.' . $ext;
    }

    private function printStyle($href) {
        return '<link rel="stylesheet" href="' . $this->staticAsset($this->addUpstoreVersion($href, $this->minify_css)) . '"/>' . PHP_EOL;
    }

    private function printScript($src, $def = '') {
        $def = rtrim($def) . ' ';
        return '<script' . $def . 'src="' . $this->staticAsset($this->addUpstoreVersion($src, $this->minify_css)) .  '"></script>' . PHP_EOL;
    }

    private function removeQueryString($file) {
        $file = explode('?', $file);
        return $file[0];
    }

    private function addUpstoreVersion($url, $is_minified) {
        if (!$this->getDeveloperMode() && $is_minified) {
            return $url . '?up3v=' . UPSTORE_VERSION;
        }
        if (strpos($url, '?') === false) {
            return $url . '?up3v=' . UPSTORE_VERSION;
        }
        return $url . '&amp;up3v=' . UPSTORE_VERSION;
    }
}

