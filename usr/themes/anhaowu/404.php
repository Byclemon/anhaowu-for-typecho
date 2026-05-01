<?php
/**
 * AnhaoWu Theme 404
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<div class="not-found-module reveal-on-scroll">
  <header class="page-header">
    <h1>404</h1>
    <div class="section-divider"></div>
    <p class="page-header-en">PAGE NOT FOUND</p>
  </header>

  <main class="not-found-main">
    <div class="not-found-panel">
      <p>您访问的页面不存在</p>
      <a href="<?php $this->options->siteUrl(); ?>" class="nav-btn">返回首页 →</a>
    </div>
  </main>
</div>

<?php $this->need('footer.php'); ?>
