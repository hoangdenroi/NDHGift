import './bootstrap';
import './elements/turbo-echo-stream-tag';
import './libs';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();