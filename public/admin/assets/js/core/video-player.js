/**
 * Admin oynatıcı boot — form, medya önizleme ve sayfa kökleri.
 */
import { boot } from '/js/video-player/player.js';

document.addEventListener('DOMContentLoaded', () => boot());
document.addEventListener('admin:content-loaded', (event) => boot(event.target));
