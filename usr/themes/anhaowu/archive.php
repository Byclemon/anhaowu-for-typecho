<?php
/**
 * AnhaoWu Theme Archive（分类 / 标签 / 搜索 / 作者 / 日期等）
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

/**
 * 当前归档上下文标识（用于分类页/标签页差异化文案）
 *
 * @var bool $isCategoryArchive
 * @var bool $isTagArchive
 */
$isCategoryArchive = $this->is('category');
$isTagArchive = $this->is('tag');
$archiveTypeLabel = $isCategoryArchive ? '分类归档' : ($isTagArchive ? '标签归档' : '归档');
$archiveDescription = trim((string) $this->description);
?>

<div class="archive-page-module reveal-on-scroll">
  <header class="page-header">
    <p class="archive-badge"><?php echo htmlspecialchars($archiveTypeLabel, ENT_QUOTES, 'UTF-8'); ?></p>
    <h1><?php $this->archiveTitle(array(
        'category' => '%s',
        'search' => '%s',
        'tag' => '%s',
        'author' => '%s',
        'date' => '%s',
    ), '', ''); ?></h1>
    <div class="section-divider"></div>
    <p class="page-header-en"><?php echo htmlspecialchars(themeArchiveSubtitleEn($this), ENT_QUOTES, 'UTF-8'); ?></p>
    <?php if ($archiveDescription !== ''): ?>
    <p class="archive-intro"><?php echo htmlspecialchars($archiveDescription, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php $archiveTotal = (int) $this->getTotal(); ?>
    <?php if ($archiveTotal > 0): ?>
    <p class="archive-count">共 <?php echo $archiveTotal; ?> 篇</p>
    <?php endif; ?>
  </header>

  <main class="archive-main">
    <?php if ($archiveTotal < 1): ?>
    <div class="archive-empty">
      <p>这里还没有文章。</p>
      <a href="<?php $this->options->siteUrl(); ?>" class="nav-btn">返回首页 →</a>
    </div>
    <?php else: ?>
    <div class="essays-list">
      <?php while ($this->next()): ?>
      <a href="<?php $this->permalink(); ?>" class="essay-item">
        <div class="essay-item-left">
          <div class="essay-date"><?php $this->date('Y.m.d'); ?></div>
          <h3 class="essay-title"><?php $this->title(); ?></h3>
          <p class="essay-excerpt"><?php $this->excerpt(100); ?></p>
        </div>
        <span class="essay-tag">
          <?php echo themePrimaryCategoryName($this); ?>
        </span>
      </a>
      <?php endwhile; ?>
    </div>

    <?php $totalPages = (int) ceil($this->getTotal() / $this->parameter->pageSize); ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($this->getCurrentPage() > 1): ?>
        <?php $this->pageLink('← 上一页', 'prev'); ?>
      <?php endif; ?>
      <span class="current"><?php echo $this->getCurrentPage(); ?> / <?php echo $totalPages; ?></span>
      <?php if ($this->getCurrentPage() < $totalPages): ?>
        <?php $this->pageLink('下一页 →', 'next'); ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </main>
</div>

<?php $this->need('footer.php'); ?>
