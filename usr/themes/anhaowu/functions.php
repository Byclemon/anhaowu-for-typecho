<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Register theme options in Typecho admin panel.
 *
 * @param Typecho_Widget_Helper_Form $form Theme config form instance.
 * @return void
 */
function themeConfig($form) {
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, NULL, _t('站点 Logo URL'), _t('请输入图片 URL'));
    $form->addInput($logoUrl);

    $heroTitle = new Typecho_Widget_Helper_Form_Element_Text('heroTitle', NULL, NULL, _t('首页 Hero 主标题'), _t('可选。留空则使用站点标题。可与导航 Logo 文字不同（例如导航为品牌英文名，此处为中文署名）。'));
    $form->addInput($heroTitle);

    $homeSeoTitle = new Typecho_Widget_Helper_Form_Element_Text(
        'homeSeoTitle',
        NULL,
        NULL,
        _t('首页 SEO 标题'),
        _t('可选。建议包含品牌词 + 核心关键词；留空则自动使用「站点标题 - 站点副标题」。')
    );
    $form->addInput($homeSeoTitle);

    $homeSeoDescription = new Typecho_Widget_Helper_Form_Element_Text(
        'homeSeoDescription',
        NULL,
        NULL,
        _t('首页 SEO 描述'),
        _t('可选。建议 60-120 字，突出站点主题与内容方向。')
    );
    $form->addInput($homeSeoDescription);

    $homeSeoKeywords = new Typecho_Widget_Helper_Form_Element_Text(
        'homeSeoKeywords',
        NULL,
        NULL,
        _t('首页 SEO 关键词'),
        _t('可选。多个关键词用英文逗号分隔。')
    );
    $form->addInput($homeSeoKeywords);

    $heroShowSocial = new Typecho_Widget_Helper_Form_Element_Radio(
        'heroShowSocial',
        array('0' => _t('不显示'), '1' => _t('显示')),
        '0',
        _t('首页 Hero 社交链接'),
        _t('是否在主视觉区域底部显示微博 / GitHub 圆形链接（与极简 demo 一致时可关闭）。')
    );
    $form->addInput($heroShowSocial);
    
    $footerQuote = new Typecho_Widget_Helper_Form_Element_Text('footerQuote', NULL, '每一个优秀的人，都有一段沉默的时光。', _t('页脚名言'), _t('显示在页脚的名言'));
    $form->addInput($footerQuote);

    $footerCustomHtmlHint = <<<'HTML'
<p class="footer-record footer-record-row">
  <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">粤ICP备xxxxxxxx号</a>
  <span class="footer-record-sep" aria-hidden="true">·</span>
  <a href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=xxxxxxxx" target="_blank" rel="noopener">粤公网安备 xxxxxxxx号</a>
</p>
HTML;
    $footerCustomHtml = new Typecho_Widget_Helper_Form_Element_Textarea(
        'footerCustomHtml',
        NULL,
        NULL,
        _t('页脚自定义 HTML'),
        _t('用于 ICP、公安备案等；可并列排版并各自加链接。示例（请替换为真实备案号与公安查询参数）：') . '<pre style="white-space:pre-wrap;word-break:break-all;margin-top:0.5em;font-size:12px;">' . htmlspecialchars($footerCustomHtmlHint, ENT_QUOTES, 'UTF-8') . '</pre>'
    );
    $form->addInput($footerCustomHtml);
    
    $weiboUrl = new Typecho_Widget_Helper_Form_Element_Text('weiboUrl', NULL, NULL, _t('微博地址'), _t('您的微博个人主页地址'));
    $form->addInput($weiboUrl);
    
    $githubUrl = new Typecho_Widget_Helper_Form_Element_Text('githubUrl', NULL, NULL, _t('GitHub 地址'), _t('您的 GitHub 主页地址'));
    $form->addInput($githubUrl);

    $worksCategorySlug = new Typecho_Widget_Helper_Form_Element_Text(
        'worksCategorySlug',
        NULL,
        NULL,
        _t('作品页分类 Slug'),
        _t('可选。填写分类 slug（例如 works）后，作品页仅展示该分类文章；留空则展示全站最新文章。')
    );
    $form->addInput($worksCategorySlug);
    
    $enableComments = new Typecho_Widget_Helper_Form_Element_Radio('enableComments', 
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('评论功能'), _t('是否在文章页面显示评论区'));
    $form->addInput($enableComments);
}

/**
 * Register custom fields for posts.
 *
 * @param Typecho_Widget_Helper_Layout $layout Post editor layout.
 * @return void
 */
function themeFields($layout) {
    $thumbnail = new Typecho_Widget_Helper_Form_Element_Text('thumbnail', NULL, NULL, _t('文章缩略图'), _t('用于文章列表显示的缩略图 URL'));
    $layout->addItem($thumbnail);
}

/**
 * 读取独立页自定义字段（安全字符串）。
 *
 * @param Widget_Archive $archive 当前页面归档实例。
 * @param string $name 字段名。
 * @return string
 */
function themePageFieldStr($archive, $name)
{
    if (!is_object($archive) || !isset($archive->fields) || $archive->fields === null) {
        return '';
    }

    $fields = $archive->fields;
    if (!is_object($fields) || !method_exists($fields, 'offsetExists') || !$fields->offsetExists($name)) {
        return '';
    }

    return trim((string) $fields[$name]);
}

/**
 * 将多行「a|b|c」解析为数组行。
 *
 * @param string $raw 原始文本。
 * @param int $columns 每行按 | 分割后期望列数（2 或 3）。
 * @return array<int, array<int, string>>
 */
function themeParsePipeRows($raw, $columns)
{
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $raw) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, $columns));
        if (count($parts) >= $columns) {
            $out[] = $parts;
        }
    }

    return $out;
}

/**
 * Get primary category name as plain text.
 *
 * @param Widget_Archive $archive Current archive widget.
 * @param string $fallback Fallback label when no category exists.
 * @return string
 */
function themePrimaryCategoryName($archive, $fallback = '未分类')
{
    if (!is_object($archive) || !isset($archive->categories) || !is_array($archive->categories)) {
        return $fallback;
    }

    foreach ($archive->categories as $category) {
        if (is_array($category) && !empty($category['name'])) {
            return htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8');
        }
    }

    return $fallback;
}

/**
 * 归档页（分类 / 标签 / 搜索等）页眉英文副标题，与首页区块风格统一。
 *
 * @param Widget_Archive $archive 当前归档组件。
 * @return string 大写英文标签文案。
 */
function themeArchiveSubtitleEn($archive)
{
    if (!is_object($archive) || !method_exists($archive, 'getArchiveType')) {
        return 'ARCHIVE';
    }

    $type = (string) $archive->getArchiveType();
    $map = array(
        'category' => 'CATEGORY',
        'tag' => 'TAG',
        'search' => 'SEARCH',
        'author' => 'AUTHOR',
        'date' => 'DATE ARCHIVE',
    );

    return $map[$type] ?? 'ARCHIVE';
}

/**
 * Resolve logo display mode by configured input.
 *
 * 当配置值看起来像 URL/绝对路径 时按图片渲染，否则按文本渲染。
 *
 * @param mixed $rawLogo Configured logo option value.
 * @param mixed $siteTitle Site title fallback.
 * @return array{mode:string,value:string}
 */
function themeResolveLogoData($rawLogo, $siteTitle)
{
    $raw = trim((string) $rawLogo);
    if ($raw === '') {
        return ['mode' => 'text', 'value' => trim((string) $siteTitle)];
    }

    $isHttpUrl = (bool) preg_match('#^https?://#i', $raw);
    $isRootPath = strpos($raw, '/') === 0;
    $hasImageExt = (bool) preg_match('/\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i', $raw);

    if (($isHttpUrl || $isRootPath) && $hasImageExt) {
        return ['mode' => 'image', 'value' => $raw];
    }

    return ['mode' => 'text', 'value' => $raw];
}

/**
 * 判断文章是否命中指定分类 slug。
 *
 * @param mixed $post 文章归档对象。
 * @param string $slug 分类 slug。
 * @return bool
 */
function themePostHasCategorySlug($post, $slug)
{
    $slug = trim((string) $slug);
    if ($slug === '') {
        return true;
    }
    if (!is_object($post) || !isset($post->categories) || !is_array($post->categories)) {
        return false;
    }

    foreach ($post->categories as $category) {
        if (!is_array($category)) {
            continue;
        }
        $categorySlug = trim((string) ($category['slug'] ?? ''));
        if ($categorySlug !== '' && strcasecmp($categorySlug, $slug) === 0) {
            return true;
        }
    }

    return false;
}

require_once __DIR__ . '/inc/seo.php';
?>