<?php
/**
 * ANHAOWU 主题专属功能插件。
 *
 * 提供主题图片管理面板，并为前台模板输出图片数据。
 *
 * @package AnhaoPlugin
 * @author Codex
 * @version 1.0.0
 * @link https://example.com
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

require_once __DIR__ . '/SeoAction.php';

/**
 * ANHAOWU 主题专属插件主类。
 */
class AnhaoPlugin_Plugin implements Typecho_Plugin_Interface
{
    /**
     * Activate plugin and initialize resources.
     *
     * @return string
     */
    public static function activate()
    {
        self::createPhotoTable();
        Helper::addPanel(1, 'AnhaoPlugin/manage.php', 'ANHAOWU 管理', '主题图片管理', 'administrator');
        Helper::addRoute('anhao_plugin_sitemap', '/sitemap.xml', 'AnhaoPlugin_SeoAction', 'sitemap');
        Helper::addRoute('anhao_plugin_robots', '/robots.txt', 'AnhaoPlugin_SeoAction', 'robots');
        Helper::addRoute('anhao_plugin_sitemap_index', '/index.php/sitemap.xml', 'AnhaoPlugin_SeoAction', 'sitemap');
        Helper::addRoute('anhao_plugin_robots_index', '/index.php/robots.txt', 'AnhaoPlugin_SeoAction', 'robots');
        Helper::addRoute('anhao_plugin_sitemap_action', '/action/anhaoplugin/sitemap.xml', 'AnhaoPlugin_SeoAction', 'sitemap');
        Helper::addRoute('anhao_plugin_robots_action', '/action/anhaoplugin/robots.txt', 'AnhaoPlugin_SeoAction', 'robots');

        return _t('AnhaoPlugin 插件启用成功');
    }

    /**
     * Deactivate plugin and unregister panel.
     *
     * @return string
     */
    public static function deactivate()
    {
        Helper::removePanel(1, 'AnhaoPlugin/manage.php');
        Helper::removeRoute('anhao_plugin_sitemap');
        Helper::removeRoute('anhao_plugin_robots');
        Helper::removeRoute('anhao_plugin_sitemap_index');
        Helper::removeRoute('anhao_plugin_robots_index');
        Helper::removeRoute('anhao_plugin_sitemap_action');
        Helper::removeRoute('anhao_plugin_robots_action');

        return _t('AnhaoPlugin 插件已禁用');
    }

    /**
     * Build plugin config form.
     *
     * @param Typecho_Widget_Helper_Form $form Config form.
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $defaultCategory = new Typecho_Widget_Helper_Form_Element_Text(
            'defaultCategory',
            null,
            '生活点滴',
            _t('默认分类'),
            _t('后台新增图片时默认使用的分类名称')
        );
        $form->addInput($defaultCategory);
    }

    /**
     * Build personal config form.
     *
     * @param Typecho_Widget_Helper_Form $form Personal config form.
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * Return gallery photos for frontend rendering.
     *
     * @param int $limit Max rows.
     * @return array<int, array<string, mixed>>
     */
    public static function getPhotos($limit = 100)
    {
        $db = Typecho_Db::get();
        $safeLimit = max(1, min((int) $limit, 500));

        try {
            $select = $db->select()
                ->from('table.anhao_gallery')
                ->order('sort_order', Typecho_Db::SORT_ASC)
                ->order('created_at', Typecho_Db::SORT_DESC)
                ->limit($safeLimit);
            $rows = $db->fetchAll($select);
        } catch (Exception $e) {
            return [];
        }

        if (!isset($rows) || !is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /**
     * Create gallery data table if not exists.
     *
     * @return void
     */
    private static function createPhotoTable()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $tableName = $prefix . 'anhao_gallery';

        try {
            $db->fetchRow($db->select()->from('table.anhao_gallery')->limit(1));
            return;
        } catch (Exception $e) {
            // Table does not exist, continue creating it.
        }

        $sql = "CREATE TABLE `{$tableName}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(150) NOT NULL,
            `image_url` varchar(255) NOT NULL,
            `category` varchar(80) DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `taken_at` int(10) unsigned DEFAULT 0,
            `sort_order` int(10) unsigned DEFAULT 0,
            `created_at` int(10) unsigned NOT NULL,
            `updated_at` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_sort_order` (`sort_order`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->query($sql);
    }

}

