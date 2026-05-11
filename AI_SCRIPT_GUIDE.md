# AI 定时任务脚本说明

> 本文档说明如何运行 AI 内容生成的定时任务脚本。
> 项目根目录：`/www/wwwroot/kaiii.top/`（ThinkPHP 项目目录）

---

## 一、可用脚本

项目已内置 3 个 ThinkPHP 命令脚本，按顺序执行：

### 1. `ai:generate` — 生成任务
从关键词库创建待处理任务。

```bash
php think ai:generate
```

**作用：** 扫描所有状态为"启用"的关键词，检查还需要生成多少篇，创建对应的 `pending` 任务。

### 2. `ai:consume` — 执行任务
调用 AI 接口生成文章内容。

```bash
php think ai:consume
```

**作用：** 取出一批 `pending` 任务，逐个调用 AI 渠道生成内容。生成完成后任务状态变为 `completed`。每次最多处理 5 个任务。

### 3. `ai:publish` — 发布文章
把已生成的文章发布到博客前台。

```bash
php think ai:publish
```

**作用：** 取出一批 `completed` 任务，发布为正式文章（状态设为已发布）。每次最多处理 3 个任务。

---

## 二、完整流程（手动跑一次）

```bash
# 1. 进到项目目录
cd /www/wwwroot/kaiii.top

# 2. 根据关键词创建任务
php think ai:generate

# 3. 执行任务（调用 AI 生成内容）
php think ai:consume

# 4. 把生成好的内容发布为文章
php think ai:publish
```

执行完之后，去后台 → 文章管理就能看到新生成的文章。

---

## 三、设置定时自动执行（服务器 crontab）

登录服务器，编辑 crontab：

```bash
crontab -e
```

添加以下规则：

```cron
# 每小时生成一次任务（从关键词创建）
0 * * * * cd /www/wwwroot/kaiii.top && php think ai:generate >> runtime/ai_generate.log 2>&1

# 每 30 分钟执行一次 AI 生成
*/30 * * * * cd /www/wwwroot/kaiii.top && php think ai:consume >> runtime/ai_consume.log 2>&1

# 每 30 分钟发布一次已完成任务
*/30 * * * * cd /www/wwwroot/kaiii.top && php think ai:publish >> runtime/ai_publish.log 2>&1
```

> **建议频率：**
> - `ai:generate`：每小时一次就够了
> - `ai:consume` + `ai:publish`：每 30 分钟一次（取决于 AI 接口的响应速度）

---

## 四、通过后台设置定时规则

后台"AI 定时调度"页面可以配置定时规则（数据存在 `ai_schedules` 表），但服务器上**仍然需要一条 crontab 作为触发入口**：

```cron
# 每分钟检查一次调度表，看有没有到时间的任务要执行
* * * * * cd /www/wwwroot/kaiii.top && php think ai:consume >> runtime/ai_consume.log 2>&1
```

后台的调度规则控制的是"什么时候创建/执行/发布任务"，服务器 crontab 是"最低频的触发心跳"。推荐直接用上面的三条 crontab 规则，简单可靠。

---

## 五、查看执行日志

```bash
# AI 生成日志
tail -f runtime/ai_consume.log

# 发布日志
tail -f runtime/ai_publish.log

# 系统操作日志（后台 AI 日志页面也能看到）
# 访问：后台 → AI 生成 → 日志
```

---

## 六、常见问题

**Q: 跑 `php think ai:consume` 报错说没有可用渠道？**
A: 先去后台 → AI 渠道管理，添加一个可用的 AI 渠道（支持 DeepSeek、通义千问、文心一言、OpenAI、Claude）。

**Q: 任务一直显示 `pending` 不执行？**
A: 手动运行 `php think ai:consume` 看输出，会打印具体错误信息。

**Q: 生成的内容在哪里查看？**
A: 后台 → 文章管理，状态为"已发布"的就是 AI 生成的文章。或者直接在博客前台查看。
