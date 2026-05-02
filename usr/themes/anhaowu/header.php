<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $seoMeta = themeBuildSeoMeta($this); ?>
<title><?php echo htmlspecialchars($seoMeta['title'], ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8'); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($seoMeta['keywords'], ENT_QUOTES, 'UTF-8'); ?>">
<meta name="robots" content="<?php echo htmlspecialchars($seoMeta['robots'], ENT_QUOTES, 'UTF-8'); ?>">
<link rel="canonical" href="<?php echo htmlspecialchars($seoMeta['canonical'], ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:type" content="<?php echo ($this->is('single') || $this->is('page')) ? 'article' : 'website'; ?>">
<meta property="og:locale" content="zh_CN">
<meta property="og:site_name" content="<?php echo htmlspecialchars($seoMeta['siteName'], ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($seoMeta['title'], ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($seoMeta['canonical'], ENT_QUOTES, 'UTF-8'); ?>">
<?php if ($seoMeta['ogImage'] !== ''): ?>
<meta property="og:image" content="<?php echo htmlspecialchars($seoMeta['ogImage'], ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?php echo $seoMeta['ogImage'] !== '' ? 'summary_large_image' : 'summary'; ?>">
<meta name="twitter:title" content="<?php echo htmlspecialchars($seoMeta['title'], ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8'); ?>">
<?php if ($seoMeta['ogImage'] !== ''): ?>
<meta name="twitter:image" content="<?php echo htmlspecialchars($seoMeta['ogImage'], ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>
<script type="application/ld+json"><?php echo json_encode($seoMeta['jsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght=400;600;700&family=ZCOOL+XiaoWei&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>">
<?php $this->header(); ?>
</head>
<body>
<?php $logoData = themeResolveLogoData($this->options->logoUrl, $this->options->title); ?>

<nav id="nav" class="site-nav">
  <a href="<?php $this->options->siteUrl(); ?>" class="nav-logo">
    <?php if ($logoData['mode'] === 'image'): ?>
      <img src="<?php echo htmlspecialchars($logoData['value'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php $this->options->title(); ?>" style="height: 32px; width: auto; display: block;">
    <?php else: ?>
      <?php echo htmlspecialchars($logoData['value'], ENT_QUOTES, 'UTF-8'); ?>
    <?php endif; ?>
  </a>
  <button type="button" class="nav-toggle" id="nav-toggle" aria-controls="nav-panel" aria-expanded="false" aria-label="打开导航菜单">
    <span class="nav-toggle-bar" aria-hidden="true"></span>
    <span class="nav-toggle-bar" aria-hidden="true"></span>
    <span class="nav-toggle-bar" aria-hidden="true"></span>
  </button>
  <div class="nav-backdrop" id="nav-backdrop" aria-hidden="true"></div>
  <div class="nav-panel" id="nav-panel">
    <ul class="nav-links">
      <li><a href="<?php $this->options->siteUrl(); ?>"<?php if($this->is('index')) echo ' class="active"'; ?>>首页</a></li>
      <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
      <?php while($pages->next()): ?>
        <li><a href="<?php $pages->permalink(); ?>"<?php if($this->is('page', $pages->slug)) echo ' class="active"'; ?>><?php $pages->title(); ?></a></li>
      <?php endwhile; ?>
    </ul>
  </div>
</nav>