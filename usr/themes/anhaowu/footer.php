<footer>
 <?php
  /** @var string $footerHtml 后台配置的页脚 HTML（备案等），仅管理员可编辑 */
  $footerHtml = isset($this->options->footerCustomHtml) ? trim((string) $this->options->footerCustomHtml) : '';
  $footerInlineHtml = '';
  if ($footerHtml !== '') {
    // 若后台填写的是 <p>...</p>，拆掉外层后再放入行内位置，避免嵌套段落标签
    $footerInlineHtml = (string) preg_replace('/^\s*<p[^>]*>(.*)<\/p>\s*$/is', '$1', $footerHtml);
  }
  ?>
  <p class="footer-meta">
    © <?php echo date('Y'); ?> <?php $this->options->title(); ?>
    <?php if ($footerInlineHtml !== ''): ?>
      <span class="footer-meta-sep" aria-hidden="true">·</span>
      <span class="footer-inline-records"><?php echo $footerInlineHtml; ?></span>
    <?php endif; ?>
  </p>
</footer>

<script>
  const nav = document.getElementById('nav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 80);
  });

  const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        const delay = entry.target.classList.contains('reveal-on-scroll') ? 0 : i * 110;
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, delay);
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
    observer.observe(el);
  });
</script>

<?php $this->footer(); ?>
</body>
</html>