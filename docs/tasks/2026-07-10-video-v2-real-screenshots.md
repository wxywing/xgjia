# 信鸽之家推广视频 v2（真实截图版）

## 目标
生成一条使用真实网站截图的 60 秒竖屏推广视频。

## 执行过程

### 问题诊断
前一条视频（xgjia_video_01.mp4）使用 PIL 生成的幻灯片（设计图而非真实截图），用户反馈 slide04.png 文字重叠，希望用真实浏览器截图替代设计图。

### 方案选择
- ❌ **浏览器录屏**（ffmpeg AVFoundation 无权限 / screencapture 含桌面杂乱内容）
- ❌ **CDP 输入自动化**（多次尝试失败：CDP click/type 跨连接状态丢失）
- ✅ **CDP 截图 + Python 合成**：AppleScript 导航页面 → CDP 截图（独立连接 Page.enable+captureScreenshot）→ Python 合成竖屏视频

### 关键突破
CDP 截图成功需要**单连接执行 Page.enable 和 captureScreenshot**。之前失败原因是创建新连接后 Page 域未启用。用 `cdp_debug.js` 模式（一次连接内先 enable 再截图）验证通过。

### 产出
- **视频**：`scripts/video/xgjia_video_final.mp4`（1MB, 35秒, 1080×1920）
- **真实截图**：6张 v2_*.png（首页/铭鸽/公棚/赛事等）
- **幻灯片**：6张 slide_0*.png（含品牌标注和底部域名栏）
- **源码**：`scripts/video/compose3.sh` / `gen_slides_v4.py`

### 未完成
- 搜索框输入截图未拿到（CDP 输入自动化不稳定）
- 视频时长 35s（TTS 旁白自然语速），可扩展至 60s
