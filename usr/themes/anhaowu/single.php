<?php
/**
 * AnhaoWu Theme Single Post
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<main class="single-main">
  <div class="single-page-module reveal-on-scroll">
  <article>
    <div class="article-header">
      <div class="article-tag"><?php echo themePrimaryCategoryName($this); ?></div>
      <h1 class="article-title"><?php $this->title(); ?></h1>
      <div class="article-date"><?php $this->date('Y年m月d日'); ?></div>
      <div class="article-divider"></div>
    </div>
    
    <div class="article-content">
      <?php $this->content(); ?>
    </div>
    
    <div class="article-footer">
      <?php $this->thePrev('%s', '', array('title' => '← 上一篇', 'tagClass' => 'nav-btn')); ?>
      <?php $this->theNext('%s', '', array('title' => '下一篇 →', 'tagClass' => 'nav-btn next')); ?>
    </div>
  </article>
  </div>

  <?php if($this->options->enableComments == '1'): ?>
  <div class="comments-section">
    <h3 class="comments-title">留 言</h3>
    <div class="comments-divider"></div>
    <?php $this->comments()->to($comments); ?>
    <?php if ($comments->have()): ?>
    <div class="comments-list">
      <?php while ($comments->next()): ?>
      <div class="comment-item">
        <div class="comment-avatar">
          <?php $comments->gravatar(48); ?>
        </div>
        <div class="comment-content">
          <div class="comment-header">
            <span class="comment-author"><?php $comments->author(); ?></span>
            <span class="comment-time"><?php $comments->date('Y.m.d H:i'); ?></span>
          </div>
          <p class="comment-text"><?php $comments->content(); ?></p>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>
    
    <?php $comments->cancelReply(); ?>
    <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form" class="comment-form">
      <div class="form-group">
        <input type="text" name="author" id="author" class="form-input" placeholder="您的名字" required value="<?php $this->remember('author'); ?>">
      </div>
      <div class="form-group">
        <input type="email" name="mail" id="mail" class="form-input" placeholder="电子邮箱（不会公开）" required value="<?php $this->remember('mail'); ?>">
      </div>
      <div class="form-group">
        <input type="url" name="url" id="url" class="form-input" placeholder="个人网站（选填）" value="<?php $this->remember('url'); ?>">
      </div>
      <div class="form-group">
        <textarea name="text" id="textarea" class="form-textarea" placeholder="写下您的留言..." required><?php $this->remember('text'); ?></textarea>
      </div>
      <div class="form-group">
        <button type="submit" class="submit-btn"><?php _e('提交留言'); ?></button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</main>

<?php $this->need('footer.php'); ?>
