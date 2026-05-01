# ANHAOWU · Typecho 主题与配套插件

![主题预览](usr/themes/anhaowu/screenshot.png)

**在线演示**：[https://www.anhaowu.com](https://www.anhaowu.com)

面向个人博客（阅读、摄影、音乐、生活记录等）的 Typecho 主题 **anhaowu**，以及配套插件 **AnhaoPlugin**。

**GitHub 仓库**：[https://github.com/Byclemon/anhaowu-for-typecho](https://github.com/Byclemon/anhaowu-for-typecho)

---

## 本仓库包含什么

本仓库**只收录**主题与插件，下载后把目录对拷到已有 Typecho 站点即可。

建议仓库根目录结构如下：

```text
anhaowu-for-typecho/
├── README.md
├── .gitignore
├── usr/
│   ├── themes/
│   │   └── anhaowu/          # 主题（含 screenshot.png 供后台与文档预览）
│   └── plugins/
│       └── AnhaoPlugin/      # 插件
```

---

## 目录结构说明（部署到站点）

将本仓库中对应目录合并到 Typecho **网站根目录**下（路径与官方约定一致）：

| 本仓库路径 | 服务器上的目标路径 | 说明 |
|------------|-------------------|------|
| `usr/themes/anhaowu/` | `网站根/usr/themes/anhaowu/` | 主题 |
| `usr/plugins/AnhaoPlugin/` | `网站根/usr/plugins/AnhaoPlugin/` | 插件 |

---

## 环境要求

- **Typecho**：建议 1.2 及以上（与当前代码中使用的 Widget / 路由方式兼容；若你使用更旧版本，需在测试环境验证）。
- **PHP**：建议 7.4+（以你服务器实际 Typecho 要求为准）。
- **数据库**：MySQL / MariaDB（与 Typecho 一致）。

---

## 安装步骤

### 1. 安装主题

1. 将 `usr/themes/anhaowu` 整个文件夹上传到服务器的 `usr/themes/` 下。
2. 登录 Typecho 后台 → **控制台 → 外观**，启用 **anhaowu**。
3. 点击 **设置外观**，按需填写各项配置（见下文「主题设置说明」）。

### 2. 安装插件

1. 将 `usr/plugins/AnhaoPlugin` 整个文件夹上传到服务器的 `usr/plugins/` 下。
2. 后台 → **控制台 → 插件**，找到 **AnhaoPlugin**，点击 **启用**。
3. 若你曾修改过插件代码或路由，建议 **禁用后再启用一次**，以重新注册路由。

### 3. 独立页面与模板

在 **管理 → 独立页面** 中新建页面，在 **自定义模板** 中选择对应项（模板文件需以 `page-*.php` 命名且含 `@package custom`）：

| 模板文件 | 用途 |
|----------|------|
| `page.php` | 默认独立页 |
| `page-about.php` | 关于页（支持自定义字段，见主题内 `about-page-demo.md`） |
| `page-works.php` | 作品页（可在主题设置中指定分类 Slug） |
| `page-gallery.php` | 相册页（启用 AnhaoPlugin 后从插件数据读取；未启用时可回退为文章展示） |
| `page-categories.php` | 全部分类索引 |
| `page-tags.php` | 全部标签索引 |

---

## 主题功能概览（anhaowu）

- **布局与动效**：首页 Hero、区块滚动显现、归档页与 404 等页面风格统一。
- **SEO**：`header.php` 输出标题、描述、关键词、canonical、Open Graph、Twitter Card、JSON-LD；逻辑在 `inc/seo.php`。
- **文章扩展字段**：缩略图 `thumbnail`（在文章编辑页填写）。
- **主题设置**（外观设置中）主要包括：
  - 站点 Logo URL（支持图片 URL 或文字）
  - 首页 Hero 主标题、首页 SEO 标题 / 描述 / 关键词
  - 首页 Hero 是否显示社交链接
  - 页脚名言、页脚自定义 HTML（备案等）
  - 微博 / GitHub 链接
  - 是否开启评论
  - **作品页分类 Slug**：填写后作品页只展示该分类文章；留空则展示全站最新若干篇

详细关于页自定义字段示例见：`usr/themes/anhaowu/about-page-demo.md`。

---

## 插件功能概览（AnhaoPlugin）

### 相册管理

- 启用后在后台扩展菜单中出现 **ANHAOWU 管理**（主题图片管理）。
- 数据表：`anhao_gallery`（首次启用时自动创建）。
- 上传目录：`usr/uploads/anhao-gallery/`（请保证 Web 服务器可写）。
- 前台 `page-gallery.php` 在检测到 `AnhaoPlugin_Plugin` 类存在时，会读取插件中的图片数据。



### 插件配置

- **默认分类**：后台新增图片时的默认分类名称（默认示例：`生活点滴`）。

---

## 常见问题

1. **启用插件后报类名相关错误**  
   请使用与本仓库一致的 `AnhaoPlugin` 版本；若 Typecho 版本较旧，需在测试环境核对 `Typecho_Widget` 与 Widget 命名空间兼容性。

2. **相册页空白**  
   确认插件已启用且后台已添加图片；检查 `usr/uploads/anhao-gallery/` 权限。

3. **作品页内容不对**  
   在主题设置中检查 **作品页分类 Slug** 是否与后台分类的 **缩略名（slug）** 完全一致（区分大小写按主题实现为准，当前为不区分大小写匹配）。

---

## 开源与许可

你可以根据实际需要为本项目选择许可证（例如 MIT）。若尚未指定，请在仓库根目录补充 `LICENSE` 文件并在本 README 中写明许可证名称。

---

## 作者与致谢

- 维护者：[Byclemon](https://github.com/Byclemon) · 仓库 [anhaowu-for-typecho](https://github.com/Byclemon/anhaowu-for-typecho)
- 主题与插件服务于个人博客项目 **ANHAOWU**。
- 感谢 [Typecho](https://typecho.org/) 项目。

如有问题或改进建议，欢迎通过 GitHub Issues 交流。
