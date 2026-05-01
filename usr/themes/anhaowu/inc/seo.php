<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 安全读取 Typecho options 字段值（兼容属性、数组、回显方法）。
 *
 * @param mixed $options Typecho options 对象。
 * @param string $name 字段名。
 * @param string $fallback 默认值。
 * @return string
 */
function themeOptionValue($options, $name, $fallback = '')
{
    if (!is_object($options)) {
        return $fallback;
    }

    $value = '';
    if (isset($options->$name)) {
        $value = (string) $options->$name;
    } elseif ($options instanceof ArrayAccess && isset($options[$name])) {
        $value = (string) $options[$name];
    } elseif (is_callable([$options, $name])) {
        ob_start();
        $options->$name();
        $value = trim((string) ob_get_clean());
    }

    $value = trim($value);
    return $value !== '' ? $value : $fallback;
}

/**
 * 构建页面级 SEO 元信息。
 *
 * @param Widget_Archive $archive 当前归档组件。
 * @return array{
 *   siteName:string,
 *   title:string,
 *   description:string,
 *   keywords:string,
 *   canonical:string,
 *   robots:string,
 *   ogImage:string,
 *   jsonLd:array<string,mixed>
 * }
 */
function themeBuildSeoMeta($archive)
{
    $options = (isset($archive->options) && is_object($archive->options)) ? $archive->options : null;
    $globalOptions = class_exists('\Widget\Options') ? \Widget\Options::alloc() : null;
    $siteTitle = themeOptionValue($options, 'title', themeOptionValue($globalOptions, 'title', ''));
    $siteDesc = themeOptionValue($options, 'description', themeOptionValue($globalOptions, 'description', ''));
    if ($siteTitle === '') {
        $siteTitle = 'My Site';
    }
    $archiveType = method_exists($archive, 'getArchiveType') ? (string) $archive->getArchiveType() : '';

    $title = $siteTitle;
    $description = $siteDesc;
    $keywords = $siteTitle;
    $canonical = themeOptionValue($options, 'siteUrl', themeOptionValue($globalOptions, 'siteUrl', ''));
    $robots = 'index,follow,max-image-preview:large';
    $ogImage = '';

    if ($archive->is('index')) {
        $heroTitle = themeOptionValue($options, 'heroTitle', '');
        $homeSeoTitle = themeOptionValue($options, 'homeSeoTitle', '');
        $homeSeoDescription = themeOptionValue($options, 'homeSeoDescription', '');
        $homeSeoKeywords = themeOptionValue($options, 'homeSeoKeywords', '');

        if ($homeSeoTitle !== '') {
            $title = $homeSeoTitle;
        } elseif ($siteDesc !== '') {
            $title = $siteTitle . ' - ' . $siteDesc;
        } elseif ($heroTitle !== '') {
            $title = $heroTitle . ' - ' . $siteTitle;
        } else {
            $title = $siteTitle;
        }

        $description = $homeSeoDescription !== '' ? $homeSeoDescription : ($siteDesc !== '' ? $siteDesc : '个人网站首页');
        if ($homeSeoKeywords !== '') {
            $keywords = $homeSeoKeywords;
        }
    } elseif ($archive->is('single') || $archive->is('page')) {
        $postTitle = trim((string) ($archive->title ?? ''));
        if ($postTitle !== '') {
            $title = $postTitle . ' - ' . $siteTitle;
        }

        $descRaw = trim((string) ($archive->description ?? ''));
        if ($descRaw === '') {
            $descRaw = trim((string) ($archive->text ?? ''));
        }
        $description = trim((string) preg_replace('/\s+/u', ' ', strip_tags($descRaw)));
        if ($description === '') {
            $description = $siteDesc;
        }
        if (mb_strlen($description, 'UTF-8') > 160) {
            $description = mb_substr($description, 0, 157, 'UTF-8') . '...';
        }

        $keywords = trim((string) ($archive->keywords ?? ''));
        if ($keywords === '') {
            $keywords = $siteTitle;
        }

        $canonical = trim((string) ($archive->permalink ?? $canonical));
        if (
            isset($archive->fields)
            && is_object($archive->fields)
            && method_exists($archive->fields, 'offsetExists')
            && $archive->fields->offsetExists('thumbnail')
        ) {
            $ogImage = trim((string) $archive->fields['thumbnail']);
        }
    } elseif ($archiveType !== '') {
        $archiveName = trim((string) ($archive->archiveTitle ?? ''));
        $archiveLabel = themeArchiveSubtitleEn($archive);
        $title = ($archiveName !== '' ? $archiveName . ' - ' : '') . $archiveLabel . ' - ' . $siteTitle;
        $description = $archiveName !== ''
            ? $archiveLabel . '：' . $archiveName . '，共 ' . (int) $archive->getTotal() . ' 篇内容。'
            : ($siteDesc !== '' ? $siteDesc : $archiveLabel);
        $keywords = ($archiveName !== '' ? $archiveName . ',' : '') . $archiveLabel . ',' . $siteTitle;
        $canonical = trim((string) ($archive->archiveUrl ?? $canonical));
    }

    if ($archive->is('search') || $archive->is('404')) {
        $robots = 'noindex,follow';
    }

    if ($canonical === '') {
        $canonical = themeOptionValue($options, 'siteUrl', themeOptionValue($globalOptions, 'siteUrl', ''));
    }
    if ($description === '') {
        $description = $siteDesc !== '' ? $siteDesc : $siteTitle;
    }
    if ($keywords === '') {
        $keywords = $siteTitle;
    }

    if (method_exists($archive, 'getCurrentPage')) {
        $currentPage = (int) $archive->getCurrentPage();
        if ($currentPage > 1) {
            $title .= ' - 第' . $currentPage . '页';
        }
    }

    $jsonLd = array(
        '@context' => 'https://schema.org',
        '@type' => ($archive->is('single') || $archive->is('page')) ? 'Article' : 'WebPage',
        'name' => trim((string) preg_replace('/\s+/u', ' ', strip_tags($title))),
        'description' => $description,
        'url' => $canonical,
    );
    if ($ogImage !== '') {
        $jsonLd['image'] = $ogImage;
    }
    if ($archive->is('single') || $archive->is('page')) {
        $authorName = $siteTitle;
        if (isset($archive->author) && is_object($archive->author) && isset($archive->author->screenName)) {
            $authorName = trim((string) $archive->author->screenName);
        }
        $jsonLd['headline'] = trim((string) ($archive->title ?? $siteTitle));
        if (!empty($archive->created)) {
            $jsonLd['datePublished'] = date('c', (int) $archive->created);
        }
        if (!empty($archive->modified)) {
            $jsonLd['dateModified'] = date('c', (int) $archive->modified);
        }
        $jsonLd['author'] = array(
            '@type' => 'Person',
            'name' => $authorName !== '' ? $authorName : $siteTitle,
        );
        $jsonLd['publisher'] = array(
            '@type' => 'Organization',
            'name' => $siteTitle,
        );
    }

    return array(
        'siteName' => $siteTitle,
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'canonical' => $canonical,
        'robots' => $robots,
        'ogImage' => $ogImage,
        'jsonLd' => $jsonLd,
    );
}

