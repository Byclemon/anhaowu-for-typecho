<?php
/**
 * 相册管理页：列表 HTML 片段生成（供整页与 AJAX 复用）。
 *
 * @package AnhaoPlugin
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 从图片行数据汇总分类数量。
 *
 * @param array<int, array<string, mixed>> $photos 数据库行。
 * @return array<string, int> 分类名 => 数量。
 */
function anhaoGalleryBuildCategoryStats(array $photos)
{
    $categoryStats = [];
    foreach ($photos as $photo) {
        $categoryName = trim((string) ($photo['category'] ?? ''));
        if ($categoryName === '') {
            $categoryName = '未分类';
        }
        if (!isset($categoryStats[$categoryName])) {
            $categoryStats[$categoryName] = 0;
        }
        $categoryStats[$categoryName]++;
    }
    return $categoryStats;
}

/**
 * 生成编辑按钮用的 data-photo 属性值（已 htmlspecialchars）。
 *
 * @param array<string, mixed> $photo 单行数据。
 * @return string
 */
function anhaoGalleryEditPayloadAttr(array $photo)
{
    $payload = json_encode([
        'id' => (int) $photo['id'],
        'title' => (string) $photo['title'],
        'image_url' => (string) $photo['image_url'],
        'category' => (string) ($photo['category'] ?? ''),
        'description' => (string) ($photo['description'] ?? ''),
        'taken_at' => !empty($photo['taken_at']) ? date('Y-m-d', (int) $photo['taken_at']) : '',
        'sort_order' => (int) $photo['sort_order'],
    ], JSON_UNESCAPED_UNICODE);

    return htmlspecialchars((string) $payload, ENT_QUOTES, 'UTF-8');
}

/**
 * 渲染统计 chips 区域内部 HTML。
 *
 * @param array<int, array<string, mixed>> $photos 图片列表。
 * @return string
 */
function anhaoGalleryRenderMetaInnerHtml(array $photos)
{
    $categoryStats = anhaoGalleryBuildCategoryStats($photos);
    ob_start();
    ?>
    <span class="ag-chip">总图片 <strong><?php echo count($photos); ?></strong></span>
    <?php foreach ($categoryStats as $categoryName => $count): ?>
        <span class="ag-chip"><?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?> <strong><?php echo (int) $count; ?></strong></span>
    <?php endforeach;
    return (string) ob_get_clean();
}

/**
 * 渲染表格 tbody 内部 HTML。
 *
 * @param array<int, array<string, mixed>> $photos 图片列表。
 * @param string $agPanelValue 隐藏域 panel，可为空。
 * @return string
 */
function anhaoGalleryRenderTableTbodyHtml(array $photos, $agPanelValue)
{
    $agPanelValue = (string) $agPanelValue;
    ob_start();
    if (empty($photos)): ?>
        <tr><td colspan="7" style="text-align:center;">暂无图片，请点击右上角「新增图片」。</td></tr>
    <?php else:
        foreach ($photos as $photo):
            $editAttr = anhaoGalleryEditPayloadAttr($photo);
            ?>
            <tr>
                <td><?php echo (int) $photo['id']; ?></td>
                <td><?php echo htmlspecialchars((string) $photo['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><code class="ag-url"><?php echo htmlspecialchars((string) $photo['image_url'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td><?php echo htmlspecialchars((string) ($photo['category'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo (int) $photo['sort_order']; ?></td>
                <td><?php echo !empty($photo['taken_at']) ? date('Y-m-d', (int) $photo['taken_at']) : '-'; ?></td>
                <td class="ag-actions">
                    <button type="button" class="btn btn-s ag-open-edit" data-photo="<?php echo $editAttr; ?>">编辑</button>
                    <form method="post" class="ag-delete-form" style="margin-top: 8px;">
                        <?php if ($agPanelValue !== ''): ?>
                            <input type="hidden" name="panel" value="<?php echo htmlspecialchars($agPanelValue, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <input type="hidden" name="gallery_action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int) $photo['id']; ?>">
                        <button class="btn" type="submit">删除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach;
    endif;
    return (string) ob_get_clean();
}

/**
 * 渲染网格视图容器内部 HTML（空态或 .ag-grid）。
 *
 * @param array<int, array<string, mixed>> $photos 图片列表。
 * @param string $agPanelValue 隐藏域 panel，可为空。
 * @return string
 */
function anhaoGalleryRenderGridInnerHtml(array $photos, $agPanelValue)
{
    $agPanelValue = (string) $agPanelValue;
    ob_start();
    if (empty($photos)): ?>
        <p class="ag-muted" style="margin-top: 8px;">暂无图片，请点击右上角「新增图片」。</p>
    <?php else: ?>
        <div class="ag-grid">
            <?php foreach ($photos as $photo):
                $editAttr = anhaoGalleryEditPayloadAttr($photo);
                ?>
                <div class="ag-item">
                    <img class="ag-thumb" src="<?php echo htmlspecialchars((string) $photo['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $photo['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="ag-item-body">
                        <h4 class="ag-item-title"><?php echo htmlspecialchars((string) $photo['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p class="ag-item-info">分类：<?php echo htmlspecialchars((string) (($photo['category'] ?? '') ?: '未分类'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="ag-item-info">排序：<?php echo (int) $photo['sort_order']; ?>，时间：<?php echo !empty($photo['taken_at']) ? date('Y-m-d', (int) $photo['taken_at']) : '-'; ?></p>
                        <div class="ag-item-actions">
                            <button type="button" class="btn btn-s ag-open-edit" data-photo="<?php echo $editAttr; ?>">编辑</button>
                            <form method="post" class="ag-delete-form" style="display:inline;">
                                <?php if ($agPanelValue !== ''): ?>
                                    <input type="hidden" name="panel" value="<?php echo htmlspecialchars($agPanelValue, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="gallery_action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $photo['id']; ?>">
                                <button class="btn" type="submit">删除</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif;
    return (string) ob_get_clean();
}
