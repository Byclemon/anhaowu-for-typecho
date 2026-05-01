<?php
/**
 * 相册页
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

/**
 * Convert category text to safe slug.
 *
 * @param string $category Raw category value.
 * @return string
 */
function anhaoGalleryCategorySlug($category)
{
  $slug = strtolower(trim((string) $category));
  $slug = preg_replace('/[^a-z0-9\-_]+/i', '-', $slug);
  $slug = trim((string) $slug, '-');

  return $slug !== '' ? $slug : 'uncategorized';
}

$galleryItems = array();
$galleryCategories = array();

if (class_exists('AnhaoPlugin_Plugin')) {
  $galleryItems = AnhaoPlugin_Plugin::getPhotos(200);
}

if (!empty($galleryItems)) {
  foreach ($galleryItems as $item) {
    $catName = !empty($item['category']) ? (string) $item['category'] : '生活点滴';
    $catSlug = anhaoGalleryCategorySlug($catName);
    if (!isset($galleryCategories[$catSlug])) {
      $galleryCategories[$catSlug] = $catName;
    }
  }
}
?>

<div class="gallery-page-module reveal-on-scroll">
<header class="page-header">
  <h1>相 册</h1>
  <div class="section-divider"></div>
  <p class="page-header-en">MOMENTS IN LIGHT</p>
</header>

<div class="gallery-container">
  <div class="gallery-filters">
    <button class="filter-btn active" data-filter="all">全部</button>
    <?php foreach ($galleryCategories as $catSlug => $catName): ?>
      <button class="filter-btn" data-filter="<?php echo htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($catName, ENT_QUOTES, 'UTF-8'); ?></button>
    <?php endforeach; ?>
  </div>

  <div class="gallery-grid">
    <?php if (!empty($galleryItems)): ?>
      <?php foreach ($galleryItems as $index => $item): ?>
      <?php
        $catName = !empty($item['category']) ? (string) $item['category'] : '生活点滴';
        $catSlug = anhaoGalleryCategorySlug($catName);
        $takenAt = !empty($item['taken_at']) ? date('Y年m月d日', (int) $item['taken_at']) : '';
        $imageUrl = !empty($item['image_url']) ? (string) $item['image_url'] : '';
        $title = !empty($item['title']) ? (string) $item['title'] : '未命名图片';
      ?>
      <div class="gallery-item <?php echo ($index === 0) ? 'featured' : ''; ?> <?php echo ($index === 3) ? 'wide' : ''; ?>" data-category="<?php echo htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8'); ?>">
        <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="gallery-overlay">
          <p><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></p>
          <span><?php echo htmlspecialchars($takenAt, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php 
      $gallery = \Widget\Contents\Post\Recent::alloc('pageSize=12');
      $gallery->to($posts);
      while($posts->next()): 
        $category = $posts->categories;
        $catSlug = '';
        if($category) {
          foreach($category as $cat) {
            $catSlug = $cat['slug'];
            break;
          }
        }
      ?>
      <div class="gallery-item <?php echo ($posts->sequence == 1) ? 'featured' : ''; ?> <?php echo ($posts->sequence == 4) ? 'wide' : ''; ?>" data-category="<?php echo htmlspecialchars($catSlug ?: 'life', ENT_QUOTES, 'UTF-8'); ?>">
        <img src="<?php echo htmlspecialchars($posts->fields->thumbnail ?: 'https://picsum.photos/400/300?random=' . $posts->cid, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php $posts->title(); ?>">
        <div class="gallery-overlay">
          <p><?php $posts->title(); ?></p>
          <span><?php $posts->date('Y年m月d日'); ?></span>
        </div>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <div class="photo-stats">
    <?php
      $totalCount = !empty($galleryItems) ? count($galleryItems) : (isset($gallery) ? (int) $gallery->getTotal() : 0);
    ?>
    <p>共 <span class="stats-count"><?php echo $totalCount; ?></span> 张照片</p>
  </div>
</div>
</div>

<div class="modal-overlay" id="modal">
  <img src="" alt="" id="modal-image">
  <div class="modal-info">
    <p id="modal-title"></p>
    <span id="modal-date"></span>
  </div>
</div>

<script>
  const galleryItems = document.querySelectorAll('.gallery-item');
  const modal = document.getElementById('modal');
  const modalImg = document.getElementById('modal-image');
  const modalTitle = document.getElementById('modal-title');
  const modalDate = document.getElementById('modal-date');

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      const overlay = item.querySelector('.gallery-overlay');
      modalImg.src = img.src;
      modalTitle.textContent = overlay.querySelector('p').textContent;
      modalDate.textContent = overlay.querySelector('span').textContent;
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  modal.addEventListener('click', () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  });

  const filterBtns = document.querySelectorAll('.filter-btn');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      galleryItems.forEach(item => {
        if(filter === 'all' || item.dataset.category === filter) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
</script>

<?php $this->need('footer.php'); ?>
