# Video Oynatıcı Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Kütüphane videoları için YouTube benzeri özel oynatıcı; gömme için native iframe; kuyrukta ffmpeg ile 1080/720/480 + poster + sprite.

**Architecture:** `media.video` JSON + `VideoProcessor` + `ProcessVideoJob`. Oynatıcı beyni `public/js/video-player/` (ES module). Ön yüz `<x-player>`, admin `<x-admin::player>`. Poll `GET /media/{id}/player`.

**Tech Stack:** Laravel 13, ffmpeg/ffprobe via Process, vanilla ES modules, ayrı CSS (ön yüz / admin).

**Spec:** `docs/superpowers/specs/2026-09-14-video-player-design.md`

## Global Constraints

- Otomatik test yazılmaz. Doğrulama: pint, ffmpeg, kuyruk, tarayıcı, curl.
- Ön yüz Bootstrap, admin Tailwind; CSS/markup taşınmaz. JS çekirdeği `public/js/video-player/` altında paylaşılır.
- Controller ince. `VideoProcessor` ffmpeg bilir, `MediaService` yalnızca iş basar.
- Kod/DB İngilizce, arayüz Türkçe. Paket yok.
- Yukarı ölçekleme yok. Orijinal diskte kalır. `saveQuietly` + `Activity::record` (processed).

---

### Task 1: Config, migration, Media modeli

**Files:** Create `config/video.php`, migration `video` JSON; Modify `Media.php` (fillable, casts, allPaths, playerPayload, toPayload).

### Task 2: VideoProcessor + Job + Command + MediaService dispatch

**Files:** Create processor, job, `video:process`; Modify `MediaService::store`.

### Task 3: Public JSON + sağlık + activity-log

**Files:** `PlayerController`, `routes/web.php`, `SystemHealth`, `config/health.php`, `config/activity-log.php`.

### Task 4: Shared player JS

**Files:** `public/js/video-player/{player,sprite-preview,prefs,format}.js`

### Task 5: Ön yüz bileşen + CSS + boot; proje detayı

**Files:** `resources/views/components/player.blade.php`, `public/assets/css/video-player.css`, `public/assets/js/video-player.js`, `pages/projects/show.blade.php`

### Task 6: Admin bileşen + CSS + boot; form.video; media-preview

**Files:** admin player Blade/CSS/JS, `form/video.blade.php`, `video-field.js`, `media-preview.js`

### Task 7: CLAUDE.md + migrate + pint + doğrulama

---
