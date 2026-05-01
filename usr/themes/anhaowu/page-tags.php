<?php
/**
 * 标签总览
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

$tagsWidget = \Widget\Metas\Tag\Cloud::alloc('ignoreZeroCount=1&limit=500&sort=name&desc=0');
$tagsWidget->to($tags);
?>

<div class="page-static-module reveal-on-scroll">
  <header class="page-header">
    <h1><?php $this->title(); ?></h1>
    <div class="section-divider"></div>
    <p class="page-header-en">TAGS</p>
  </header>

  <main class="page-body">
    <?php if ($tags->have()): ?>
    <div class="meta-index-grid meta-index-grid--tags">
      <?php while ($tags->next()): ?>
      <a class="meta-index-card" href="<?php $tags->permalink(); ?>">
        <span class="meta-index-name"><?php $tags->name(); ?></span>
        <span class="meta-index-count"><?php echo (int) $tags->count; ?> 篇</span>
      </a>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="meta-index-empty">暂无标签。</p>
    <?php endif; ?>
  </main>
</div>

<?php $this->need('footer.php'); ?>
