<?php
/**
 * 作品页
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<div class="works-page-module reveal-on-scroll">
  <header class="page-header">
    <h1>作 品</h1>
    <div class="section-divider"></div>
    <p class="page-header-en">SELECTED WORKS</p>
  </header>

  <section id="works-content">
    <div class="works-grid">
      <?php
      /**
       * 读取作品页分类筛选配置（按分类 slug 过滤）。
       *
       * 留空时保持原逻辑：展示全站最新内容。
       */
      $worksCategorySlug = trim((string) ($this->options->worksCategorySlug ?? ''));
      $works = \Widget\Contents\Post\Recent::alloc('pageSize=60');
      $works->to($posts);
      $renderedCount = 0;
      while ($posts->next()):
        if (!themePostHasCategorySlug($posts, $worksCategorySlug)) {
          continue;
        }
        $thumbnail = '';
        if (isset($posts->fields) && is_object($posts->fields) && method_exists($posts->fields, 'offsetExists') && $posts->fields->offsetExists('thumbnail')) {
          $thumbnail = trim((string) $posts->fields['thumbnail']);
        }
        $thumb = $thumbnail !== ''
          ? $thumbnail
          : 'https://picsum.photos/600/400?random=' . (int) $posts->cid;
        $renderedCount++;
        if ($renderedCount > 6) {
          break;
        }
      ?>
      <a href="<?php $posts->permalink(); ?>" class="work-card">
        <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php $posts->title(); ?>" class="work-image">
        <div class="work-content">
          <div class="work-tag"><?php echo themePrimaryCategoryName($posts); ?></div>
          <div class="work-title"><?php $posts->title(); ?></div>
          <div class="work-desc"><?php $posts->excerpt(80); ?></div>
        </div>
      </a>
      <?php endwhile; ?>
      <?php if ($renderedCount === 0): ?>
      <p class="archive-empty">当前筛选分类下暂无作品内容。</p>
      <?php endif; ?>
    </div>

    <div class="more-works">
      <p>更多作品持续创作中...</p>
      <a href="<?php $this->options->siteUrl(); ?>">返回首页 →</a>
    </div>
  </section>
</div>

<?php $this->need('footer.php'); ?>
