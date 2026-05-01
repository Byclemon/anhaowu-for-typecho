<?php
/**
 * 分类总览
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

$categoriesWidget = \Widget\Metas\Category\Rows::alloc();
$categoriesWidget->to($categories);
?>

<div class="page-static-module reveal-on-scroll">
  <header class="page-header">
    <h1><?php $this->title(); ?></h1>
    <div class="section-divider"></div>
    <p class="page-header-en">CATEGORIES</p>
  </header>

  <main class="page-body">
    <?php if ($categories->have()): ?>
    <div class="meta-index-grid">
      <?php while ($categories->next()): ?>
      <a class="meta-index-card" href="<?php $categories->permalink(); ?>">
        <span class="meta-index-name"><?php $categories->name(); ?></span>
        <span class="meta-index-count"><?php echo (int) $categories->count; ?> 篇</span>
      </a>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="meta-index-empty">暂无分类。</p>
    <?php endif; ?>
  </main>
</div>

<?php $this->need('footer.php'); ?>
