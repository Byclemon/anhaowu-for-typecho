<?php
/**
 * 相册管理后台页面
 *
 * @package AnhaoPlugin
 */

if (!defined('__TYPECHO_ADMIN__')) {
    include '../../../admin/common.php';
}

if (!$user->pass('administrator', true)) {
    $response->goBack();
}

require_once __DIR__ . '/ManagePartials.php';

/**
 * Sanitize text input safely.
 *
 * @param mixed $value Raw text.
 * @return string
 */
function anhaoGallerySanitizeText($value)
{
    return trim(strip_tags((string) $value));
}

/**
 * Parse date string into timestamp.
 *
 * @param string $value Date text.
 * @return int
 */
function anhaoGalleryParseDate($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return 0;
    }

    $time = strtotime($value);
    return $time === false ? 0 : (int) $time;
}

/**
 * Handle uploaded image and return its public URL.
 *
 * @param array|null $file Uploaded file info from $_FILES.
 * @return string
 * @throws Exception
 */
function anhaoGalleryHandleUpload($file)
{
    if (!is_array($file) || !isset($file['error'])) {
        return '';
    }

    if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('图片上传失败，请重试');
    }

    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception('未检测到有效上传文件');
    }

    $maxSize = 10 * 1024 * 1024;
    if (!empty($file['size']) && (int) $file['size'] > $maxSize) {
        throw new Exception('图片大小不能超过 10MB');
    }

    $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExt, true)) {
        throw new Exception('仅支持 jpg/jpeg/png/gif/webp 格式');
    }

    $uploadDir = __TYPECHO_ROOT_DIR__ . '/usr/uploads/anhao-gallery';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception('创建上传目录失败');
    }

    $filename = date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 8) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('保存上传文件失败');
    }

    global $options;
    $baseUrl = rtrim((string) $options->siteUrl, '/');
    return $baseUrl . '/usr/uploads/anhao-gallery/' . $filename;
}

/**
 * Normalize $_FILES input to a flat file list.
 *
 * @param array|null $files Raw $_FILES item.
 * @return array<int, array<string, mixed>>
 */
function anhaoGalleryNormalizeUploadedFiles($files)
{
    if (!is_array($files) || !isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        $normalized[] = [
            'name' => $files['name'][$i] ?? '',
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0
        ];
    }

    return $normalized;
}

/**
 * Handle one or more uploaded images and return saved URLs.
 *
 * @param array|null $files Raw $_FILES item.
 * @return array<int, array<string, string>>
 * @throws Exception
 */
function anhaoGalleryHandleUploads($files)
{
    $items = anhaoGalleryNormalizeUploadedFiles($files);
    $saved = [];

    foreach ($items as $item) {
        $url = anhaoGalleryHandleUpload($item);
        if ($url !== '') {
            $saved[] = [
                'url' => $url,
                'original_name' => (string) ($item['name'] ?? '')
            ];
        }
    }

    return $saved;
}

/**
 * Delete local file from plugin upload directory by URL.
 *
 * @param string $imageUrl Public image URL.
 * @return bool
 */
function anhaoGalleryDeleteLocalFileByUrl($imageUrl)
{
    $imageUrl = trim((string) $imageUrl);
    if ($imageUrl === '') {
        return false;
    }

    global $options;
    $siteUrl = rtrim((string) $options->siteUrl, '/');
    $prefix = $siteUrl . '/usr/uploads/anhao-gallery/';
    if (strpos($imageUrl, $prefix) !== 0) {
        return false;
    }

    $relative = substr($imageUrl, strlen($prefix));
    $relative = ltrim((string) $relative, '/');
    if ($relative === '' || strpos($relative, '..') !== false) {
        return false;
    }

    $fullPath = __TYPECHO_ROOT_DIR__ . '/usr/uploads/anhao-gallery/' . $relative;
    if (!is_file($fullPath)) {
        return false;
    }

    return @unlink($fullPath);
}

/**
 * Build category name set for server-side validation.
 *
 * @param Typecho_Db $db Database instance.
 * @return array<int, string>
 */
function anhaoGalleryFetchKnownCategories(Typecho_Db $db)
{
    $names = [];
    $rows = $db->fetchAll($db->select('category')->from('table.anhao_gallery')->order('category', Typecho_Db::SORT_ASC));
    foreach ($rows as $row) {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '' && isset($row['category'])) {
            $name = trim((string) $row['category']);
        }
        if ($name !== '') {
            $names[] = $name;
        }
    }
    return array_values(array_unique($names));
}

/**
 * Resolve final category from selected dropdown and custom input.
 *
 * @param string $selected Selected option value.
 * @param string $custom Custom category input value.
 * @return string
 */
function anhaoGalleryResolveSelectedCategory($selected, $custom = '')
{
    $custom = anhaoGallerySanitizeText($custom);
    if ($custom !== '') {
        return $custom;
    }
    return anhaoGallerySanitizeText($selected);
}

$db = Typecho_Db::get();
$request = Typecho_Request::getInstance();
$noticeMessage = anhaoGallerySanitizeText($request->get('ag_notice'));
$noticeType = anhaoGallerySanitizeText($request->get('ag_type')) === 'error' ? 'error' : 'notice';
/** @var string 当前后台面板的 panel 参数，POST 时写入隐藏域以免丢失 */
$agPanelValue = trim((string) $request->get('panel', ''));
/** @var bool 本请求是否已处理相册 POST（用于无跳转模式下的地址栏修正） */
$agPostHandled = false;
/** @var string history.replaceState 使用的纯 GET 地址（仅含 panel） */
$agReplaceStateUrl = '';

if ($request->isPost()) {
    $action = anhaoGallerySanitizeText($request->get('gallery_action'));
    $agWantAjax = anhaoGallerySanitizeText($request->get('ag_ajax')) === '1';
    $validActions = ['create', 'delete', 'update'];

    if ($agWantAjax && !in_array($action, $validActions, true)) {
        if (is_object($response) && method_exists($response, 'throwJson')) {
            $response->throwJson([
                'success' => false,
                'message' => '无效的操作',
                'noticeType' => 'error',
            ]);
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => '无效的操作',
            'noticeType' => 'error',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $now = time();
    try {
        if ($action === 'create') {
            $title = anhaoGallerySanitizeText($request->get('title'));
            $imageUrl = trim((string) $request->get('image_url'));
            $uploadedItems = anhaoGalleryHandleUploads(isset($_FILES['image_file']) ? $_FILES['image_file'] : null);
            $category = anhaoGalleryResolveSelectedCategory($request->get('category'), $request->get('category_custom'));
            $description = anhaoGallerySanitizeText($request->get('description'));
            $takenAt = anhaoGalleryParseDate($request->get('taken_at'));
            $sortOrder = max(0, (int) $request->get('sort_order'));

            if (empty($uploadedItems) && $imageUrl === '') {
                throw new Exception('请填写图片 URL 或上传至少一张图片');
            }

            if (!empty($uploadedItems)) {
                $addedCount = 0;
                $multi = count($uploadedItems) > 1;
                foreach ($uploadedItems as $index => $item) {
                    $autoTitle = pathinfo($item['original_name'], PATHINFO_FILENAME);
                    $autoTitle = $autoTitle !== '' ? anhaoGallerySanitizeText($autoTitle) : ('图片 ' . ($index + 1));
                    if ($title !== '') {
                        $itemTitle = $multi ? ($title . ' ' . ($index + 1)) : $title;
                    } else {
                        $itemTitle = $autoTitle;
                    }

                    $db->query($db->insert('table.anhao_gallery')->rows([
                        'title' => $itemTitle,
                        'image_url' => $item['url'],
                        'category' => $category,
                        'description' => $description,
                        'taken_at' => $takenAt,
                        'sort_order' => $sortOrder + $index,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]));
                    $addedCount++;
                }
                $noticeMessage = '批量上传成功，共添加 ' . $addedCount . ' 张图片';
            } else {
                if ($title === '') {
                    throw new Exception('使用图片 URL 创建时，标题不能为空');
                }

                $db->query($db->insert('table.anhao_gallery')->rows([
                    'title' => $title,
                    'image_url' => $imageUrl,
                    'category' => $category,
                    'description' => $description,
                    'taken_at' => $takenAt,
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now
                ]));
                $noticeMessage = '图片已添加';
            }
            $noticeType = 'notice';
            $agPostHandled = true;
        } elseif ($action === 'delete') {
            $id = max(0, (int) $request->get('id'));
            if ($id <= 0) {
                throw new Exception('无效的图片 ID');
            }

            $row = $db->fetchRow($db->select()->from('table.anhao_gallery')->where('id = ?', $id)->limit(1));
            if (is_array($row) && !empty($row['image_url'])) {
                anhaoGalleryDeleteLocalFileByUrl((string) $row['image_url']);
            }

            $db->query($db->delete('table.anhao_gallery')->where('id = ?', $id));
            $noticeMessage = '图片记录已删除，并已尝试删除本地文件';
            $noticeType = 'notice';
            $agPostHandled = true;
        } elseif ($action === 'update') {
            $id = max(0, (int) $request->get('id'));
            $title = anhaoGallerySanitizeText($request->get('title'));
            $imageUrl = trim((string) $request->get('image_url'));
            $uploadedItems = anhaoGalleryHandleUploads(isset($_FILES['image_file']) ? $_FILES['image_file'] : null);
            if (!empty($uploadedItems)) {
                $imageUrl = $uploadedItems[0]['url'];
            }
            $category = anhaoGalleryResolveSelectedCategory($request->get('category'), $request->get('category_custom'));
            $description = anhaoGallerySanitizeText($request->get('description'));
            $takenAt = anhaoGalleryParseDate($request->get('taken_at'));
            $sortOrder = max(0, (int) $request->get('sort_order'));

            if ($id <= 0 || $title === '' || $imageUrl === '') {
                throw new Exception('编辑参数不完整');
            }

            $db->query($db->update('table.anhao_gallery')->rows([
                'title' => $title,
                'image_url' => $imageUrl,
                'category' => $category,
                'description' => $description,
                'taken_at' => $takenAt,
                'sort_order' => $sortOrder,
                'updated_at' => $now
            ])->where('id = ?', $id));
            $noticeMessage = '图片已更新';
            $noticeType = 'notice';
            $agPostHandled = true;
        }
    } catch (Exception $e) {
        $noticeType = 'error';
        $noticeMessage = $e->getMessage();
        $agPostHandled = true;
    }

    if ($agWantAjax) {
        $payload = [
            'success' => $noticeType !== 'error',
            'message' => $noticeMessage,
            'noticeType' => $noticeType,
        ];
        if ($noticeType !== 'error') {
            $ajaxPhotos = $db->fetchAll(
                $db->select()
                    ->from('table.anhao_gallery')
                    ->order('sort_order', Typecho_Db::SORT_ASC)
                    ->order('created_at', Typecho_Db::SORT_DESC)
            );
            $payload['table_tbody'] = anhaoGalleryRenderTableTbodyHtml($ajaxPhotos, $agPanelValue);
            $payload['grid_inner'] = anhaoGalleryRenderGridInnerHtml($ajaxPhotos, $agPanelValue);
            $payload['meta_inner'] = anhaoGalleryRenderMetaInnerHtml($ajaxPhotos);
            $payload['total'] = count($ajaxPhotos);
        }
        if (is_object($response) && method_exists($response, 'throwJson')) {
            $response->throwJson($payload);
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($agPostHandled) {
    $panelForUrl = $agPanelValue !== '' ? $agPanelValue : 'AnhaoPlugin/manage.php';
    $q = http_build_query(['panel' => $panelForUrl], '', '&', PHP_QUERY_RFC3986);
    if (isset($options) && is_object($options) && method_exists($options, 'adminUrl')) {
        $agReplaceStateUrl = (string) $options->adminUrl('extending.php?' . $q, true);
    } else {
        $agReplaceStateUrl = '/admin/extending.php?' . $q;
    }
}

$photos = $db->fetchAll(
    $db->select()
        ->from('table.anhao_gallery')
        ->order('sort_order', Typecho_Db::SORT_ASC)
        ->order('created_at', Typecho_Db::SORT_DESC)
);
$categoryOptions = anhaoGalleryFetchKnownCategories($db);

include 'header.php';
include 'menu.php';
?>
<style>
.ag-card { background:#fff; border:1px solid #e8eaef; border-radius:10px; margin-bottom:20px; overflow:hidden; box-shadow:0 1px 2px rgba(30,35,90,0.04); }
.ag-flash-mount { margin-bottom:12px; }
.ag-card-header { padding:14px 18px; font-size:16px; font-weight:600; color:#2f3b4a; background:#f8fafc; border-bottom:1px solid #edf1f5; display:flex; justify-content:space-between; align-items:center; }
.ag-card-body { padding:16px 18px; }
.ag-muted { margin:0; color:#78879a; font-size:13px; }
.ag-meta { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
.ag-chip { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; border:1px solid #d9e2ec; color:#5a6b7f; background:#f8fbff; font-size:12px; }
.ag-chip strong { color:#2f3b4a; font-weight:600; }
.ag-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
.ag-view-switch { display:inline-flex; border:1px solid #d8e0ea; border-radius:7px; overflow:hidden; }
.ag-switch-btn { border:0; background:#fff; color:#607286; padding:7px 14px; font-size:13px; cursor:pointer; }
.ag-switch-btn + .ag-switch-btn { border-left:1px solid #d8e0ea; }
.ag-switch-btn.is-active { background:#3f6ea8; color:#fff; }
.ag-header-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.ag-view { display:none; }
.ag-view.is-active { display:block; }
.ag-modal-backdrop { display:none; position:fixed; inset:0; z-index:10050; background:rgba(30,40,55,0.45); align-items:center; justify-content:center; padding:20px; overflow:auto; }
.ag-modal-backdrop.is-open { display:flex; }
.ag-modal { background:#fff; border-radius:12px; box-shadow:0 12px 40px rgba(0,0,0,0.18); width:100%; max-width:520px; max-height:calc(100vh - 40px); overflow:auto; position:relative; }
.ag-modal-header { padding:16px 20px; border-bottom:1px solid #edf1f5; display:flex; justify-content:space-between; align-items:center; }
.ag-modal-header h3 { margin:0; font-size:17px; font-weight:600; color:#2f3b4a; }
.ag-modal-close { border:0; background:transparent; font-size:22px; line-height:1; color:#8a9aad; cursor:pointer; padding:4px 8px; }
.ag-modal-close:hover { color:#2f3b4a; }
.ag-modal-body { padding:18px 20px 22px; }
.ag-modal-body .ag-form-row { margin-bottom:12px; }
.ag-modal-body .ag-form-grid { margin-bottom:0; }
.ag-url { max-width:340px; display:inline-block; white-space:normal; word-break:break-all; color:#5c6b7e; font-size:12px; }
.ag-actions details summary { cursor:pointer; color:#3f6ea8; font-weight:500; }
.ag-actions form { margin-top:8px; }
.ag-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:14px; }
.ag-item { border:1px solid #e5ebf2; border-radius:10px; overflow:hidden; background:#fff; }
.ag-thumb { display:block; width:100%; aspect-ratio:4/3; object-fit:cover; background:#f0f3f8; }
.ag-item-body { padding:10px 12px; }
.ag-item-title { font-size:14px; font-weight:600; color:#2f3b4a; margin:0 0 6px; }
.ag-item-info { font-size:12px; color:#708197; margin:0 0 4px; line-height:1.5; }
.ag-item-actions { display:flex; gap:8px; margin-top:10px; }
.ag-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.ag-form-row { margin-bottom:10px; }
.ag-inline { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.ag-inline .text { min-width:130px; }
.ag-submit { margin-top:8px; }
.ag-hidden { display:none; }
@media (max-width: 900px) { .ag-form-grid { grid-template-columns:1fr; } }
</style>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12 typecho-list">
                <div id="ag-flash-mount" class="ag-flash-mount">
                <?php if ($noticeMessage !== ''): ?>
                    <div class="message <?php echo $noticeType === 'error' ? 'error' : 'notice'; ?>">
                        <ul><li><?php echo htmlspecialchars($noticeMessage, ENT_QUOTES, 'UTF-8'); ?></li></ul>
                    </div>
                <?php endif; ?>
                </div>

                <div class="ag-card" id="ag-gallery-card">
                    <div class="ag-card-header">
                        <span>图片列表</span>
                        <div class="ag-header-actions">
                            <div class="ag-view-switch">
                                <button type="button" class="ag-switch-btn is-active" data-view-target="table">表格视图</button>
                                <button type="button" class="ag-switch-btn" data-view-target="grid">网格视图</button>
                            </div>
                            <button type="button" class="btn primary ag-btn-open-create">新增图片</button>
                        </div>
                    </div>
                    <div class="ag-card-body">
                <p class="ag-muted">删除记录时会自动尝试删除本地上传文件。</p>
                <div class="ag-meta">
                    <?php echo anhaoGalleryRenderMetaInnerHtml($photos); ?>
                </div>
                <div class="ag-toolbar"></div>

                <div class="typecho-table-wrap ag-view is-active" data-view-pane="table">
                    <table class="typecho-list-table">
                        <colgroup>
                            <col width="6%">
                            <col width="16%">
                            <col width="26%">
                            <col width="14%">
                            <col width="10%">
                            <col width="14%">
                            <col width="14%">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>标题</th>
                            <th>图片 URL</th>
                            <th>分类</th>
                            <th>排序</th>
                            <th>拍摄时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php echo anhaoGalleryRenderTableTbodyHtml($photos, $agPanelValue); ?>
                        </tbody>
                    </table>
                </div>

                <div class="ag-view" data-view-pane="grid">
                    <?php echo anhaoGalleryRenderGridInnerHtml($photos, $agPanelValue); ?>
                </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ag-modal-create" class="ag-modal-backdrop" aria-hidden="true" role="presentation">
    <div class="ag-modal" role="dialog" aria-labelledby="ag-modal-create-title">
        <div class="ag-modal-header">
            <h3 id="ag-modal-create-title">新增图片</h3>
            <button type="button" class="ag-modal-close ag-modal-dismiss" aria-label="关闭">×</button>
        </div>
        <div class="ag-modal-body">
            <p class="ag-muted" style="margin-bottom:12px;">支持批量上传；列表通过 AJAX 更新，无需整页刷新。</p>
            <form method="post" enctype="multipart/form-data" autocomplete="off">
                <?php if ($agPanelValue !== ''): ?>
                    <input type="hidden" name="panel" value="<?php echo htmlspecialchars($agPanelValue, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <input type="hidden" name="gallery_action" value="create">
                <div class="ag-form-grid">
                    <p class="ag-form-row"><input class="w-100 text" type="text" name="title" placeholder="标题（批量上传时可作为前缀）"></p>
                    <p class="ag-form-row">
                        <select class="w-100 text" name="category" data-category-select="create">
                            <option value="">未分类</option>
                            <?php foreach ($categoryOptions as $categoryOption): ?>
                                <option value="<?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                            <option value="__custom__">+ 新建分类</option>
                        </select>
                    </p>
                </div>
                <p class="ag-form-row ag-hidden" data-category-custom-wrap="create"><input class="w-100 text" type="text" name="category_custom" data-category-custom="create" placeholder="输入新分类名称"></p>
                <p class="ag-form-row"><input class="w-100 text" type="url" name="image_url" placeholder="图片 URL（与上传二选一）"></p>
                <p class="ag-form-row"><input class="w-100 text" type="file" name="image_file[]" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" multiple></p>
                <p class="ag-muted" style="margin-bottom:10px;">多图时 URL 可留空；若同时填写 URL 与上传，优先使用上传。</p>
                <p class="ag-form-row"><input class="w-100 text" type="text" name="description" placeholder="描述"></p>
                <p class="ag-inline">
                    <input class="text" type="date" name="taken_at">
                    <input class="text" type="number" name="sort_order" min="0" value="0" placeholder="排序值">
                </p>
                <p class="ag-submit"><button class="btn primary" type="submit">添加图片</button></p>
            </form>
        </div>
    </div>
</div>

<div id="ag-modal-edit" class="ag-modal-backdrop" aria-hidden="true" role="presentation">
    <div class="ag-modal" role="dialog" aria-labelledby="ag-modal-edit-title">
        <div class="ag-modal-header">
            <h3 id="ag-modal-edit-title">编辑图片</h3>
            <button type="button" class="ag-modal-close ag-modal-dismiss" aria-label="关闭">×</button>
        </div>
        <div class="ag-modal-body">
            <form id="ag-form-edit" method="post" enctype="multipart/form-data" autocomplete="off">
                <?php if ($agPanelValue !== ''): ?>
                    <input type="hidden" name="panel" value="<?php echo htmlspecialchars($agPanelValue, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <input type="hidden" name="gallery_action" value="update">
                <input type="hidden" name="id" id="ag-edit-id" value="">
                <p class="ag-form-row"><input class="w-100 text" type="text" name="title" id="ag-edit-title" placeholder="标题" required></p>
                <p class="ag-form-row"><input class="w-100 text" type="url" name="image_url" id="ag-edit-image-url" placeholder="图片 URL" required></p>
                <p class="ag-form-row"><input class="w-100 text" type="file" name="image_file" accept=".jpg,.jpeg,.png,.gif,.webp,image/*"></p>
                <p class="ag-muted" style="margin-bottom:10px;">更换图片时选择文件即可；留空则保持当前地址。</p>
                <p class="ag-form-row">
                    <select class="w-100 text" name="category" id="ag-edit-category" data-category-select="edit">
                        <option value="">未分类</option>
                        <?php foreach ($categoryOptions as $categoryOption): ?>
                            <option value="<?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                        <option value="__custom__">+ 新建分类</option>
                    </select>
                </p>
                <p class="ag-form-row ag-hidden" data-category-custom-wrap="edit"><input class="w-100 text" type="text" name="category_custom" id="ag-edit-category-custom" data-category-custom="edit" placeholder="输入新分类名称"></p>
                <p class="ag-form-row"><input class="w-100 text" type="text" name="description" id="ag-edit-description" placeholder="描述"></p>
                <p class="ag-inline">
                    <input class="text" type="date" name="taken_at" id="ag-edit-taken-at">
                    <input class="text" type="number" name="sort_order" id="ag-edit-sort-order" min="0" value="0" placeholder="排序值">
                </p>
                <p class="ag-submit"><button class="btn primary" type="submit">保存</button></p>
            </form>
        </div>
    </div>
</div>

<script>
window.AG_ADMIN = <?php echo json_encode([
    'replaceStateUrl' => ($agPostHandled && $agReplaceStateUrl !== '') ? $agReplaceStateUrl : '',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="<?php echo htmlspecialchars(\Typecho\Common::url('AnhaoPlugin/anhao-plugin-admin.js', $options->pluginUrl), ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php
include 'footer.php';
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
