<?php
/**
 * ANHAOWU（安好屋）— Typecho 个人博客主题
 *
 * 极简阅读向布局，含首页 Hero、随笔区块、归档与独立页模板；支持 SEO 元信息、页脚自定义 HTML、作品页按分类筛选等。
 * 相册展示可配合同名配套插件 AnhaoPlugin 使用。
 *
 * @package anhaowu
 * @author Byclemon
 * @version 1.0.0
 * @link https://www.anhaowu.com
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<?php
/** @var string $heroHeadline 首页大标题：优先主题配置，否则站点名 */
$heroHeadline = isset($this->options->heroTitle) ? trim((string) $this->options->heroTitle) : '';
/** @var bool $heroSocialOn 是否在 Hero 展示社交链接 */
$heroSocialOn = isset($this->options->heroShowSocial) && (string) $this->options->heroShowSocial === '1';
?>
<div class="hero">
  <div class="hero-content">
    <div class="hero-pen">✦</div>
    <h1><?php
      if ($heroHeadline !== '') {
          echo htmlspecialchars($heroHeadline, ENT_QUOTES, 'UTF-8');
      } else {
          $this->options->title();
      }
    ?></h1>
    <div class="hero-line"></div>
    <p class="hero-sub"><?php $this->options->description(); ?></p>

    <?php if ($heroSocialOn && ($this->options->weiboUrl || $this->options->githubUrl)): ?>
    <div class="social-links">
      <?php if ($this->options->weiboUrl): ?>
      <a href="<?php $this->options->weiboUrl(); ?>" target="_blank" rel="noopener" aria-label="微博">◎</a>
      <?php endif; ?>
      <?php if ($this->options->githubUrl): ?>
      <a href="<?php $this->options->githubUrl(); ?>" target="_blank" rel="noopener" aria-label="GitHub">◇</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  
  <div class="scroll-hint">
    <span>向下探索</span>
    <div class="scroll-line"></div>
  </div>
</div>

<section id="essays">
  <?php /** 整段「随笔」模块：标题 + 列表 + 分页一并做滚动入场，与 demo 整块上浮一致 */ ?>
  <div class="home-essays-module reveal-on-scroll">
    <div class="section-header">
      <h2>随 笔</h2>
      <div class="section-divider"></div>
      <p class="subtitle">THOUGHTS &amp; ESSAYS</p>
    </div>

    <div class="essays-grid">
      <?php while($this->next()): ?>
      <a href="<?php $this->permalink(); ?>" class="essay-card">
        <div class="essay-date"><?php $this->date('Y.m.d'); ?></div>
        <div class="essay-content">
          <h3><?php $this->title(); ?></h3>
          <p><?php $this->excerpt(120); ?></p>
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
        <?php $this->pageLink('下一页 →','next'); ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php $this->need('footer.php'); ?>
