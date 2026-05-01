<?php
/**
 * sitemap.xml 与 robots.txt 输出处理器。
 *
 * @package AnhaoPlugin
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 站点地图与 robots 输出 Action。
 */
class AnhaoPlugin_SeoAction extends Typecho_Widget
{
    /**
     * 输出 sitemap.xml。
     *
     * @return void
     */
    public function sitemap()
    {
        $this->cleanAllOutputBuffers();
        if (function_exists('header')) {
            header('Content-Type: application/xml; charset=UTF-8');
        }

        $siteUrl = rtrim($this->resolveSiteUrl(), '/') . '/';
        $items = [];
        $items[] = [
            'loc' => $siteUrl,
            'lastmod' => date('c'),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $postWidget = $this->createWidget(
            '\\Widget\\Contents\\Post\\Recent',
            'Widget_Contents_Post_Recent',
            'pageSize=10000'
        );
        $postWidget->to($posts);
        while ($posts->next()) {
            $items[] = [
                'loc' => (string) $posts->permalink,
                'lastmod' => !empty($posts->modified) ? date('c', (int) $posts->modified) : date('c', (int) $posts->created),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $pageWidget = $this->createWidget(
            '\\Widget\\Contents\\Page\\Rows',
            'Widget_Contents_Page_Rows',
            'pageSize=1000'
        );
        $pageWidget->to($pages);
        while ($pages->next()) {
            $items[] = [
                'loc' => (string) $pages->permalink,
                'lastmod' => !empty($pages->modified) ? date('c', (int) $pages->modified) : date('c', (int) $pages->created),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $categoryWidget = $this->createWidget(
            '\\Widget\\Metas\\Category\\Rows',
            'Widget_Metas_Category_Rows'
        );
        $categoryWidget->to($categories);
        while ($categories->next()) {
            $items[] = [
                'loc' => (string) $categories->permalink,
                'lastmod' => date('c'),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        $tagWidget = $this->createWidget(
            '\\Widget\\Metas\\Tag\\Cloud',
            'Widget_Metas_Tag_Cloud',
            'ignoreZeroCount=1&limit=5000'
        );
        $tagWidget->to($tags);
        while ($tags->next()) {
            $items[] = [
                'loc' => (string) $tags->permalink,
                'lastmod' => date('c'),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $seen = [];
        foreach ($items as $item) {
            $loc = trim((string) ($item['loc'] ?? ''));
            if (!$this->isValidSitemapUrl($loc) || isset($seen[$loc])) {
                continue;
            }
            $seen[$loc] = true;
            echo "  <url>\n";
            echo "    <loc>" . $this->xmlEsc($loc) . "</loc>\n";
            echo "    <lastmod>" . $this->xmlEsc((string) ($item['lastmod'] ?? '')) . "</lastmod>\n";
            echo "    <changefreq>" . $this->xmlEsc((string) ($item['changefreq'] ?? '')) . "</changefreq>\n";
            echo "    <priority>" . $this->xmlEsc((string) ($item['priority'] ?? '')) . "</priority>\n";
            echo "  </url>\n";
        }
        echo "</urlset>";
        exit;
    }

    /**
     * 输出 robots.txt。
     *
     * @return void
     */
    public function robots()
    {
        $this->cleanAllOutputBuffers();
        if (function_exists('header')) {
            header('Content-Type: text/plain; charset=UTF-8');
        }

        $siteUrl = rtrim($this->resolveSiteUrl(), '/');
        $sitemapUrl = $siteUrl !== '' ? $siteUrl . '/sitemap.xml' : '/sitemap.xml';

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /install.php\n";
        echo "Disallow: /var/\n";
        echo "Sitemap: " . $sitemapUrl . "\n";
        exit;
    }

    /**
     * 清理所有输出缓冲，避免 XML/TXT 被污染。
     *
     * @return void
     */
    private function cleanAllOutputBuffers()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * 转义 XML 文本。
     *
     * @param string $value 原始文本。
     * @return string
     */
    private function xmlEsc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * 判断是否为可用于 sitemap 的绝对 URL。
     *
     * @param string $url 待校验 URL。
     * @return bool
     */
    private function isValidSitemapUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return false;
        }
        if (!preg_match('#^https?://#i', $url)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 安全获取站点 URL（兼容不同 Typecho 版本）。
     *
     * @return string
     */
    private function resolveSiteUrl()
    {
        if (isset($this->options) && is_object($this->options) && isset($this->options->siteUrl)) {
            return (string) $this->options->siteUrl;
        }

        if (class_exists('\\Widget\\Options')) {
            $options = \Widget\Options::alloc();
            if (is_object($options) && isset($options->siteUrl)) {
                return (string) $options->siteUrl;
            }
        }

        if (class_exists('Widget_Options')) {
            $options = Typecho_Widget::widget('Widget_Options');
            if (is_object($options) && isset($options->siteUrl)) {
                return (string) $options->siteUrl;
            }
        }

        return '';
    }

    /**
     * 兼容不同 Typecho 版本创建 Widget 实例。
     *
     * @param string $modernClass 新版命名空间类名。
     * @param string $legacyClass 旧版类名。
     * @param string|null $params 初始化参数。
     * @return mixed
     * @throws Exception
     */
    private function createWidget($modernClass, $legacyClass, $params = null)
    {
        if (class_exists($modernClass) && method_exists($modernClass, 'alloc')) {
            return $params === null ? $modernClass::alloc() : $modernClass::alloc($params);
        }

        if (class_exists($legacyClass)) {
            return $params === null ? Typecho_Widget::widget($legacyClass) : Typecho_Widget::widget($legacyClass, $params);
        }

        throw new Exception('无法创建 Widget：' . $legacyClass);
    }
}

