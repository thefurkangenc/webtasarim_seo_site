/** Menü yöneticisi sayfası — tüm iş core/menu-builder.js'te. */

import { MenuBuilder } from '../../core/menu-builder.js';

const workspace = JSON.parse(document.querySelector('[data-menu-workspace]').textContent);

new MenuBuilder(document.getElementById('main-content') ?? document.body, workspace);
