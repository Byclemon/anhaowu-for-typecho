<?php
/**
 * 默认独立页
 *
 * 
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<div class="page-static-module reveal-on-scroll">
  <header class="page-header">
    <h1><?php $this->title(); ?></h1>
    <div class="section-divider"></div>
    <p class="page-header-meta"><?php $this->date('Y年m月d日'); ?></p>
  </header>

  <main class="page-body">
    <div class="page-content">
      <?php $this->content(); ?>
    </div>
  </main>
</div>

<?php $this->need('footer.php'); ?>
