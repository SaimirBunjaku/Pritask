import './bootstrap';
import { bindCustomSelectHandlers, initCustomSelects } from './custom-select';
import { initIssuesBoard } from './issues-board';

document.addEventListener('DOMContentLoaded', () => {
    bindCustomSelectHandlers();
    initIssuesBoard();
    initCustomSelects();
});
