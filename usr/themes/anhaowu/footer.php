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
    if (nav) {
      nav.classList.toggle('scrolled', window.scrollY > 80);
    }
  });

  /**
   * 小屏导航：抽屉菜单开关、遮罩关闭、跳转后关闭、Escape 关闭。
   */
  (function () {
    const rootNav = document.getElementById('nav');
    const toggle = document.getElementById('nav-toggle');
    const backdrop = document.getElementById('nav-backdrop');
    const panel = document.getElementById('nav-panel');
    if (!rootNav || !toggle || !panel) {
      return;
    }

    function setNavOpen(open) {
      rootNav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? '关闭导航菜单' : '打开导航菜单');
      document.body.classList.toggle('nav-menu-open', open);
      if (backdrop) {
        backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
      }
    }

    toggle.addEventListener('click', function () {
      setNavOpen(!rootNav.classList.contains('is-open'));
    });

    if (backdrop) {
      backdrop.addEventListener('click', function () {
        setNavOpen(false);
      });
    }

    panel.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setNavOpen(false);
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        setNavOpen(false);
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 900 && rootNav.classList.contains('is-open')) {
        setNavOpen(false);
      }
    });
  })();

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