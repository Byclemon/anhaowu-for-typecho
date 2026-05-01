<?php
/**
 * 关于页
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$tagline = themePageFieldStr($this, 'aboutTagline');
$photoRaw = themePageFieldStr($this, 'aboutPhoto');
$photoUrl = $photoRaw !== '' ? htmlspecialchars($photoRaw, ENT_QUOTES, 'UTF-8') : '';
$lead = themePageFieldStr($this, 'aboutLead');
$detailRows = themeParsePipeRows(themePageFieldStr($this, 'aboutDetails'), 2);
$statRows = themeParsePipeRows(themePageFieldStr($this, 'aboutStats'), 2);
$skillRows = themeParsePipeRows(themePageFieldStr($this, 'aboutSkills'), 3);
$timeRows = themeParsePipeRows(themePageFieldStr($this, 'aboutTimeline'), 3);
$contactTitle = themePageFieldStr($this, 'aboutContactTitle') ?: '与我联系';
$contactIntro = themePageFieldStr($this, 'aboutContactIntro') ?: '文字是桥，我在桥的另一端等你';
$aboutEmail = themePageFieldStr($this, 'aboutEmail');
$hasPhoto = $photoUrl !== '';

$this->need('header.php');
?>

<div class="about-full-module reveal-on-scroll">
  <header class="page-header">
    <h1><?php $this->title(); ?></h1>
    <div class="section-divider"></div>
    <?php if ($tagline !== ''): ?>
    <p class="page-header-meta"><?php echo htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else: ?>
    <p class="page-header-meta"><?php $this->date('Y年m月d日'); ?></p>
    <?php endif; ?>
  </header>

  <section id="about-content">
    <div class="about-layout<?php echo $hasPhoto ? '' : ' about-layout--no-photo'; ?>">
      <?php if ($hasPhoto): ?>
      <div class="about-photo">
        <img src="<?php echo $photoUrl; ?>" alt="<?php $this->title(); ?>">
      </div>
      <?php endif; ?>
      <div class="about-text">
        <?php if ($lead !== ''): ?>
        <h3><?php echo htmlspecialchars($lead, ENT_QUOTES, 'UTF-8'); ?></h3>
        <?php endif; ?>
        <div class="page-content about-prose">
          <?php $this->content(); ?>
        </div>
        <?php if (!empty($detailRows)): ?>
        <div class="about-detail">
          <?php foreach ($detailRows as $row): ?>
          <div class="detail-item">
            <span class="label"><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="value"><?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($statRows)): ?>
    <div class="stats-grid">
      <?php foreach ($statRows as $row): ?>
      <?php
        $numOnly = preg_replace('/[^\d]/', '', $row[0]);
        $hasNumeric = $numOnly !== '';
      ?>
      <div class="stat-item about-animate-item">
        <?php if ($hasNumeric): ?>
        <div class="stat-number about-count-up" data-target="<?php echo (int) $numOnly; ?>">0</div>
        <?php else: ?>
        <div class="stat-number"><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="stat-label"><?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($skillRows)): ?>
    <section id="skills" class="skills-section">
      <div class="section-header">
        <h2>专 长</h2>
        <div class="section-divider"></div>
        <p class="subtitle">SKILLS &amp; EXPERTISE</p>
      </div>
      <div class="skills-grid">
        <?php foreach ($skillRows as $row): ?>
        <div class="skill-card about-animate-item">
          <div class="skill-icon"><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></div>
          <h4><?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?></h4>
          <p><?php echo htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($timeRows)): ?>
    <section id="timeline" class="timeline-section">
      <div class="section-header">
        <h2>历 程</h2>
        <div class="section-divider"></div>
        <p class="subtitle">LIFE JOURNEY</p>
      </div>
      <ul class="timeline-list">
        <?php foreach ($timeRows as $row): ?>
        <li class="timeline-item about-animate-item">
          <div class="timeline-dot"></div>
          <div class="timeline-year"><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="timeline-title"><?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="timeline-desc"><?php echo htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'); ?></div>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <section class="contact-section" id="contact">
      <h2><?php echo htmlspecialchars($contactTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
      <p><?php echo htmlspecialchars($contactIntro, ENT_QUOTES, 'UTF-8'); ?></p>
      <div class="contact-links">
        <?php if ($aboutEmail !== ''): ?>
        <a href="mailto:<?php echo htmlspecialchars($aboutEmail, ENT_QUOTES, 'UTF-8'); ?>">✉ 邮箱</a>
        <?php endif; ?>
        <?php if ($this->options->weiboUrl): ?>
        <a href="<?php $this->options->weiboUrl(); ?>" target="_blank" rel="noopener">◎ 微博</a>
        <?php endif; ?>
        <?php if ($this->options->githubUrl): ?>
        <a href="<?php $this->options->githubUrl(); ?>" target="_blank" rel="noopener">◇ GitHub</a>
        <?php endif; ?>
      </div>
    </section>
  </section>
</div>

<script>
(function () {
  const opts = { threshold: 0.12, rootMargin: '0px 0px -40px 0px' };
  const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry, i) {
      if (!entry.isIntersecting) return;
      setTimeout(function () {
        entry.target.classList.add('visible');
      }, i * 90);
      io.unobserve(entry.target);
    });
  }, opts);
  document.querySelectorAll('.about-animate-item').forEach(function (el) { io.observe(el); });

  document.querySelectorAll('.about-count-up').forEach(function (el) {
    var target = parseInt(el.getAttribute('data-target'), 10);
    if (!target || target < 0) return;
    var done = false;
    var numIo = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting || done) return;
        done = true;
        var duration = 1800;
        var steps = 50;
        var inc = target / steps;
        var cur = 0;
        var t = setInterval(function () {
          cur += inc;
          if (cur >= target) {
            el.textContent = String(target);
            clearInterval(t);
          } else {
            el.textContent = String(Math.floor(cur));
          }
        }, duration / steps);
        numIo.unobserve(el);
      });
    }, { threshold: 0.35 });
    numIo.observe(el);
  });
})();
</script>

<?php $this->need('footer.php'); ?>
