/**
 * AnhaoPlugin 后台管理：弹窗、视图切换、AJAX 提交与列表局部更新。
 *
 * 依赖页面注入的 window.AG_ADMIN.replaceStateUrl（无跳转 POST 后修正地址栏，可为空）。
 */
(function () {
    var modalCreate = document.getElementById('ag-modal-create');
    var modalEdit = document.getElementById('ag-modal-edit');
    var card = document.getElementById('ag-gallery-card');
    var VIEW_KEY = 'anhao_plugin_view';
    var flashHideTimer = null;

    function openModal(el) {
        if (!el) return;
        el.classList.add('is-open');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(el) {
        if (!el) return;
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function closeAllModals() {
        closeModal(modalCreate);
        closeModal(modalEdit);
    }

    /**
     * Toggle custom category input by select value.
     *
     * @param {'create'|'edit'} scope Form scope.
     * @param {string} value Selected value.
     */
    function toggleCategoryCustom(scope, value) {
        var wrap = document.querySelector('[data-category-custom-wrap="' + scope + '"]');
        var input = document.querySelector('[data-category-custom="' + scope + '"]');
        if (!wrap || !input) return;
        var show = value === '__custom__';
        wrap.classList.toggle('ag-hidden', !show);
        if (!show) {
            input.value = '';
        }
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /**
     * 在顶部展示操作提示（AJAX 与内联消息共用容器）。
     *
     * @param {string} text 文案。
     * @param {string} type notice | error。
     */
    function agShowMessage(text, type) {
        var el = document.getElementById('ag-flash-mount');
        if (!el) return;
        if (flashHideTimer) {
            clearTimeout(flashHideTimer);
            flashHideTimer = null;
        }
        if (!text) {
            el.innerHTML = '';
            return;
        }
        var cls = type === 'error' ? 'error' : 'notice';
        el.innerHTML = '<div class="message ' + cls + '"><ul><li>' + escapeHtml(text) + '</li></ul></div>';
        flashHideTimer = setTimeout(function () {
            el.innerHTML = '';
        }, 3000);
    }

    /**
     * 用接口返回的 HTML 更新表格、网格与统计区。
     *
     * @param {Object} data JSON 载荷。
     */
    function agPatchList(data) {
        if (!data || !data.success) return;
        var tbody = document.querySelector('[data-view-pane="table"] tbody');
        var gridPane = document.querySelector('[data-view-pane="grid"]');
        var meta = document.querySelector('.ag-meta');
        if (tbody && data.table_tbody != null) tbody.innerHTML = data.table_tbody;
        if (gridPane && data.grid_inner != null) gridPane.innerHTML = data.grid_inner;
        if (meta && data.meta_inner != null) meta.innerHTML = data.meta_inner;
    }

    /**
     * 以 fetch 提交表单并局部更新列表。
     *
     * @param {HTMLFormElement} form 表单节点。
     */
    function agSubmitFormViaAjax(form) {
        var scopedSelect = form.querySelector('[data-category-select]');
        var scopedCustom = form.querySelector('[data-category-custom]');
        var pendingCategory = '';
        if (scopedSelect && scopedSelect.value === '__custom__' && scopedCustom) {
            pendingCategory = (scopedCustom.value || '').trim();
        }
        var fd = new FormData(form);
        fd.set('ag_ajax', '1');
        var url = form.getAttribute('action') || window.location.href.split('#')[0];
        var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        fetch(url, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) {
                if (!r.ok) throw new Error('http');
                return r.json();
            })
            .then(function (data) {
                if (submitBtn) submitBtn.disabled = false;
                if (!data) return;
                agShowMessage(data.message || '', data.noticeType || 'notice');
                if (data.success) {
                    if (pendingCategory !== '') {
                        document.querySelectorAll('[data-category-select]').forEach(function (sel) {
                            var exists = Array.prototype.some.call(sel.options, function (opt) { return opt.value === pendingCategory; });
                            if (!exists) {
                                var opt = document.createElement('option');
                                opt.value = pendingCategory;
                                opt.textContent = pendingCategory;
                                sel.insertBefore(opt, sel.querySelector('option[value="__custom__"]'));
                            }
                        });
                    }
                    agPatchList(data);
                    closeAllModals();
                    form.reset();
                    toggleCategoryCustom('create', (document.querySelector('[data-category-select="create"]') || {}).value || '');
                    toggleCategoryCustom('edit', (document.querySelector('[data-category-select="edit"]') || {}).value || '');
                }
            })
            .catch(function () {
                if (submitBtn) submitBtn.disabled = false;
                agShowMessage('网络错误或服务器无响应，请重试', 'error');
            });
    }

    document.querySelectorAll('.ag-btn-open-create').forEach(function (btn) {
        btn.addEventListener('click', function () { openModal(modalCreate); });
    });

    document.body.addEventListener('click', function (e) {
        var target = e.target;
        if (!(target instanceof Element) && target && target.parentElement) {
            target = target.parentElement;
        }
        if (!(target instanceof Element)) return;
        var btn = target.closest('.ag-open-edit');
        if (!btn || !card || !card.contains(btn)) return;
        var raw = btn.getAttribute('data-photo');
        if (!raw) return;
        var p;
        try { p = JSON.parse(raw); } catch (err) { return; }
        document.getElementById('ag-edit-id').value = p.id || '';
        document.getElementById('ag-edit-title').value = p.title || '';
        document.getElementById('ag-edit-image-url').value = p.image_url || '';
        var editSelect = document.getElementById('ag-edit-category');
        var editCustom = document.getElementById('ag-edit-category-custom');
        if (editSelect) {
            var selectedCategory = p.category || '';
            var hasOption = Array.prototype.some.call(editSelect.options, function (opt) {
                return opt.value === selectedCategory;
            });
            if (selectedCategory !== '' && !hasOption) {
                editSelect.value = '__custom__';
                if (editCustom) {
                    editCustom.value = selectedCategory;
                }
            } else {
                editSelect.value = selectedCategory;
                if (editCustom) {
                    editCustom.value = '';
                }
            }
            toggleCategoryCustom('edit', editSelect.value);
        }
        document.getElementById('ag-edit-description').value = p.description || '';
        document.getElementById('ag-edit-taken-at').value = p.taken_at || '';
        document.getElementById('ag-edit-sort-order').value = p.sort_order != null ? p.sort_order : 0;
        var fileInput = document.querySelector('#ag-form-edit input[name="image_file"]');
        if (fileInput) fileInput.value = '';
        openModal(modalEdit);
    });

    document.querySelectorAll('.ag-modal-dismiss').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(btn.closest('.ag-modal-backdrop'));
        });
    });

    [modalCreate, modalEdit].forEach(function (backdrop) {
        if (!backdrop) return;
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeModal(backdrop);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    document.querySelectorAll('.ag-switch-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-view-target');
            try { localStorage.setItem(VIEW_KEY, target || 'table'); } catch (err) {}
            document.querySelectorAll('.ag-switch-btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            document.querySelectorAll('[data-view-pane]').forEach(function (pane) {
                if (pane.getAttribute('data-view-pane') === target) {
                    pane.classList.add('is-active');
                } else {
                    pane.classList.remove('is-active');
                }
            });
        });
    });

    document.querySelectorAll('[data-category-select]').forEach(function (select) {
        select.addEventListener('change', function () {
            var scope = select.getAttribute('data-category-select');
            toggleCategoryCustom(scope, select.value);
        });
    });

    /**
     * 恢复上次表格/网格视图，避免局部更新后回到默认表格。
     */
    function applySavedView() {
        try {
            var v = localStorage.getItem(VIEW_KEY);
            if (v !== 'grid' && v !== 'table') return;
            var btn = document.querySelector('.ag-switch-btn[data-view-target="' + v + '"]');
            if (!btn) return;
            document.querySelectorAll('.ag-switch-btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            document.querySelectorAll('[data-view-pane]').forEach(function (pane) {
                if (pane.getAttribute('data-view-pane') === v) {
                    pane.classList.add('is-active');
                } else {
                    pane.classList.remove('is-active');
                }
            });
        } catch (e) {}
    }

    document.body.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!(form instanceof HTMLFormElement)) return;
        var inCreate = modalCreate && modalCreate.contains(form);
        var inEdit = modalEdit && modalEdit.contains(form);
        var inCardDelete = card && card.contains(form) && form.classList.contains('ag-delete-form');
        if (!inCreate && !inEdit && !inCardDelete) return;
        ev.preventDefault();
        if (form.classList.contains('ag-delete-form')) {
            if (!confirm('确定删除这张图片吗？')) return;
        }
        agSubmitFormViaAjax(form);
    });

    applySavedView();
    toggleCategoryCustom('create', (document.querySelector('[data-category-select="create"]') || {}).value || '');
    toggleCategoryCustom('edit', (document.querySelector('[data-category-select="edit"]') || {}).value || '');
    if (document.querySelector('#ag-flash-mount .message')) {
        flashHideTimer = setTimeout(function () {
            var mount = document.getElementById('ag-flash-mount');
            if (mount) {
                mount.innerHTML = '';
            }
        }, 3000);
    }

    var rs = window.AG_ADMIN && window.AG_ADMIN.replaceStateUrl;
    if (rs) {
        try { history.replaceState(null, '', rs); } catch (e) {}
    }
})();
