import './bootstrap';
import { bindConfirmDialogHandlers } from './confirm-dialog';
import { bindCustomSelectHandlers, initCustomSelects } from './custom-select';
import { initIssuesBoard } from './issues-board';
import { initProjectShow } from './project-show';

document.addEventListener('DOMContentLoaded', () => {
    bindConfirmDialogHandlers();
    bindCustomSelectHandlers();
    initIssuesBoard();
    initProjectShow();
    initCustomSelects();
});
