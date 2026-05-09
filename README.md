# 🖥️ KaiBlog - 个人博客 CMS 系统

一个基于 **ThinkPHP 8 + MySQL + Vue 3 + Element Plus** 的前后端分离个人博客 CMS 系统。

## ✨ 特性

- 📝 文章管理 — 发布、编辑、草稿、置顶、推荐
- 📚 教程知识库 — 多级章节、树形目录、Markdown 编辑
- 👤 用户系统 — 注册、登录、会员中心、收藏、点赞
- 🔐 权限管理 — RBAC 角色权限、菜单权限、按钮权限
- 💬 评论系统 — 多级评论、审核机制
- 🔍 SEO 优化 — Sitemap、JSON-LD、Open Graph、爬虫记录
- ⚙️ 系统配置 — 分组管理、动态配置项、可视化后台
- 🛡️ 安全防护 — JWT 认证、验证码、单点登录

## 🏗️ 技术栈

| 层级 | 技术 |
|------|------|
| 后端 | ThinkPHP 8.0 + MySQL 5.7 |
| 前台 | Vue 3 + Vite + Element Plus |
| 后台 | Vue 3 + Vite + Element Plus |
| 认证 | JWT (30天有效期) |
| 缓存 | File Cache |

## 📁 项目结构

```
├── app/
│   ├── admin/           # 后台管理模块
│   │   ├── controller/  # 后台控制器
│   │   ├── middleware/   # 后台中间件
│   │   └── route/       # 后台路由
│   ├── api/             # 前台 API 模块
│   │   ├── controller/  # API 控制器
│   │   ├── middleware/   # API 中间件
│   │   └── route/       # API 路由
│   └── common/          # 公共模块
│       ├── model/       # 数据模型
│       ├── trait_/      # 公共 Trait
│       └── util/        # 工具类
├── config/              # 配置文件
├── database/            # 数据库迁移
│   └── migrations/      # SQL 迁移脚本
├── font/                # 前端项目
│   ├── blog-web/        # 前台用户端
│   └── blog-admin/      # 后台管理端
├── public/              # 公共入口
│   └── index.php        # 入口文件
└── route/               # 路由定义
```

## 🚀 快速开始

### 环境要求

- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Node.js >= 16

### 安装

```bash
# 克隆项目
git clone https://github.com/your-username/kaiBlog.git
cd kaiBlog

# 安装依赖
composer install

# 配置环境
cp .example.env .env
# 编辑 .env 填写数据库配置

# 导入数据库
# 执行 database/migrations/ 下的 SQL 文件

# 启动后端
php think run

# 启动前端
cd font/blog-web && npm install && npm run dev
cd font/blog-admin && npm install && npm run dev
```

### 默认账号

| 角色 | 用户名 | 密码 |
|------|--------|------|
| 超级管理员 | admin | admin123 |

## 📡 API 接口

详见 [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

### 前台接口

```
GET  /api/articles          # 文章列表
GET  /api/articles/:id      # 文章详情
GET  /api/tutorials         # 教程列表
GET  /api/tutorials/:id     # 教程详情
GET  /api/categories        # 栏目列表
GET  /api/config            # 站点配置
GET  /api/seo/:type/:id     # SEO 信息
GET  /api/captcha           # 获取验证码
POST /api/auth/login        # 用户登录
POST /api/auth/register     # 用户注册
```

### 后台接口

```
POST /admin/auth/login      # 管理员登录
GET  /admin/dashboard       # 控制台数据
GET  /admin/articles        # 文章管理
GET  /admin/tutorials       # 教程管理
GET  /admin/config          # 系统配置
GET  /admin/configGroups    # 配置分组
GET  /admin/crawlers        # 爬虫记录
```

## 🔧 配置说明

系统配置通过后台管理界面可视化管理，支持以下分组：

| 分组 | 说明 |
|------|------|
| 基础配置 | 站点名称、Logo、公告 |
| SEO 配置 | 搜索引擎优化 |
| 联系方式 | 邮箱、QQ、微信、GitHub |
| 赞赏打赏 | 微信/支付宝收款码 |
| 页脚配置 | 版权声明、备案号、友链 |
| 上传配置 | 文件大小、类型限制 |
| 用户配置 | 注册、评论开关 |

## 📦 部署

```bash
# 构建前端
cd font/blog-web && npm run build
cd font/blog-admin && npm run build

# 上传 dist 目录到服务器
# 配置 Nginx 反向代理到 ThinkPHP 入口
```

## 📄 License

[MIT](./LICENSE.txt)
