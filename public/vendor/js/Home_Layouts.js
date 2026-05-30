(function () {
    const loader = document.getElementById('page-loader');
    const body = document.body;

    if (!loader) {
        return;
    }

    body.classList.add('is-loading');

    const hideLoader = function () {
        loader.classList.add('page-loader-hidden');

        window.setTimeout(function () {
            loader.remove();
            body.classList.remove('is-loading');
        }, 500);
    };

    const minDisplayMs = 800;
    const startTime = Date.now();

    window.addEventListener('load', function () {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(minDisplayMs - elapsed, 0);

        window.setTimeout(hideLoader, remaining);
    });
})();

(function () {
    const goTopBtn = document.getElementById('chapter-go-top-btn');

    if (!goTopBtn) {
        return;
    }

    const toggleGoTopBtn = function () {
        if (window.scrollY > 400) {
            goTopBtn.classList.add('is-visible');
        } else {
            goTopBtn.classList.remove('is-visible');
        }
    };

    goTopBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', toggleGoTopBtn, { passive: true });
    toggleGoTopBtn();
})();

(function () {
    const STORAGE_PREFIX = 'adult_confirmed_';

    function isAdultConfirmed(slug) {
        try {
            return sessionStorage.getItem(STORAGE_PREFIX + slug) === '1';
        } catch (error) {
            return false;
        }
    }

    function confirmAdult(slug) {
        try {
            sessionStorage.setItem(STORAGE_PREFIX + slug, '1');
        } catch (error) {
            // ignore storage errors
        }
    }

    function showAdultAlert(onConfirm) {
        if (typeof swal !== 'function') {
            if (window.confirm('This series contains adult content (18+). Do you want to continue?')) {
                onConfirm();
            }

            return;
        }

        swal({
            title: 'Adult Content Warning',
            text: 'This series contains mature content for adults only. You must be 18 or older to continue reading.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Go Back',
                    visible: true,
                    value: null,
                },
                confirm: {
                    text: 'I am 18+, Continue',
                    value: true,
                },
            },
            dangerMode: true,
        }).then(function (confirmed) {
            if (confirmed) {
                onConfirm();
            }
        });
    }

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[data-adult-content]');

        if (!link) {
            return;
        }

        const slug = link.getAttribute('data-adult-slug');
        const url = link.getAttribute('href');

        if (!slug || !url || isAdultConfirmed(slug)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        showAdultAlert(function () {
            confirmAdult(slug);
            window.location.href = url;
        });
    });

    const gate = document.getElementById('adult-content-gate');

    if (!gate) {
        return;
    }

    const slug = gate.getAttribute('data-adult-slug');

    if (!slug) {
        gate.remove();

        return;
    }

    if (isAdultConfirmed(slug)) {
        gate.remove();

        return;
    }

    gate.hidden = false;
    document.body.classList.add('adult-gate-active');

    const confirmBtn = gate.querySelector('[data-adult-confirm]');

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            confirmAdult(slug);
            gate.remove();
            document.body.classList.remove('adult-gate-active');
        });
    }
})();

(function () {
    const copyButtons = document.querySelectorAll('[data-copy-share-url]');

    if (!copyButtons.length) {
        return;
    }

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);

            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }

    function showCopiedFeedback(button) {
        const label = button.querySelector('.manhwa-share-btn-copy-text');

        if (!label) {
            return;
        }

        const originalText = label.textContent;
        button.classList.add('is-copied');
        label.textContent = 'Copied!';

        window.setTimeout(function () {
            button.classList.remove('is-copied');
            label.textContent = originalText;
        }, 2000);
    }

    copyButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const url = button.getAttribute('data-copy-share-url');

            if (!url) {
                return;
            }

            try {
                await copyText(url);
                showCopiedFeedback(button);
            } catch (error) {
                if (typeof swal === 'function') {
                    swal('Could not copy link', 'Please copy the URL from your browser address bar.', 'error');
                } else {
                    window.alert('Could not copy link. Please copy the URL from your browser address bar.');
                }
            }
        });
    });
})();

(function () {
    const bookmarkButtons = document.querySelectorAll('[data-bookmark-toggle]');

    if (!bookmarkButtons.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function updateBookmarkButton(button, bookmarked) {
        const icon = button.querySelector('i');
        const label = button.querySelector('.manhwa-bookmark-btn-text');
        const title = button.getAttribute('data-bookmark-title') || 'this series';

        button.classList.toggle('is-bookmarked', bookmarked);
        button.setAttribute('aria-pressed', bookmarked ? 'true' : 'false');
        button.setAttribute(
            'aria-label',
            bookmarked ? 'Remove ' + title + ' from bookmarks' : 'Bookmark ' + title
        );

        if (icon) {
            icon.classList.toggle('fa-solid', bookmarked);
            icon.classList.toggle('fa-regular', !bookmarked);
        }

        if (label) {
            label.textContent = bookmarked ? 'Bookmarked' : 'Bookmark';
        }
    }

    bookmarkButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const url = button.getAttribute('data-bookmark-url');

            if (!url || button.classList.contains('is-loading')) {
                return;
            }

            button.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();
                updateBookmarkButton(button, Boolean(data.bookmarked));
            } catch (error) {
                if (typeof swal === 'function') {
                    swal('Bookmark failed', 'Could not update your bookmark. Please try again.', 'error');
                } else {
                    window.alert('Could not update your bookmark. Please try again.');
                }
            } finally {
                button.classList.remove('is-loading');
            }
        });
    });
})();

(function () {
    const likeButtons = document.querySelectorAll('[data-like-toggle]');

    if (!likeButtons.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function formatCount(count) {
        return Number(count).toLocaleString();
    }

    function likeScopeRoot(button) {
        return (
            button.closest('[data-like-scope]')
            || button.closest('.manhwa-detail-section')
            || button.closest('.chapter-read-section')
            || document
        );
    }

    function updateLikeCounts(button, count) {
        const scope = likeScopeRoot(button);
        const section =
            scope.closest('.manhwa-detail-section')
            || scope.closest('.chapter-read-section')
            || scope;

        scope.querySelectorAll('[data-like-count]').forEach(function (element) {
            element.textContent = formatCount(count);
        });

        section.querySelectorAll('[data-like-count-display]').forEach(function (element) {
            element.textContent = formatCount(count);
        });
    }

    function updateLikeButton(button, liked) {
        const icon = button.querySelector('i');
        const label = button.querySelector('.engagement-like-btn-text');
        const likeLabel = button.getAttribute('data-like-label') || 'Like';

        button.classList.toggle('is-liked', liked);
        button.setAttribute('aria-pressed', liked ? 'true' : 'false');
        button.setAttribute('aria-label', liked ? 'Unlike' : 'Like');

        if (icon) {
            icon.classList.toggle('fa-solid', liked);
            icon.classList.toggle('fa-regular', !liked);
        }

        if (label) {
            label.textContent = liked ? 'Liked' : likeLabel;
        }
    }

    likeButtons.forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();

            const url = button.getAttribute('data-like-url');

            if (!url || button.classList.contains('is-loading')) {
                return;
            }

            button.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        page_url: window.location.href,
                    }),
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();
                updateLikeButton(button, Boolean(data.liked));

                if (typeof data.likes_count !== 'undefined') {
                    updateLikeCounts(button, data.likes_count);
                }
            } catch (error) {
                if (typeof swal === 'function') {
                    swal('Like failed', 'Could not update your like. Please try again.', 'error');
                } else {
                    window.alert('Could not update your like. Please try again.');
                }
            } finally {
                button.classList.remove('is-loading');
            }
        });
    });
})();

(function () {
    const ratingBlocks = document.querySelectorAll('[data-manhwa-rating]');

    if (!ratingBlocks.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function formatAverage(value) {
        const number = Number(value);

        if (!Number.isFinite(number) || number <= 0) {
            return '—';
        }

        return number.toFixed(1);
    }

    function formatCount(count) {
        return Number(count).toLocaleString();
    }

    function ratingScopeRoot(block) {
        return (
            block.closest('.manhwa-detail-section')
            || block.closest('.manhwa-detail-main')
            || block
        );
    }

    function updateRatingSummary(block, average, count) {
        const scope = ratingScopeRoot(block);

        scope.querySelectorAll('[data-rating-average]').forEach(function (element) {
            element.textContent = formatAverage(average);
        });

        scope.querySelectorAll('[data-rating-average-display]').forEach(function (element) {
            element.textContent = formatAverage(average);
        });

        scope.querySelectorAll('[data-rating-count]').forEach(function (element) {
            element.textContent = formatCount(count);
        });

        scope.querySelectorAll('[data-rating-count-display]').forEach(function (element) {
            element.textContent = formatCount(count);
        });

        scope.querySelectorAll('[data-rating-count-label]').forEach(function (element) {
            const total = Number(count);
            element.textContent = total === 1 ? 'rating' : 'ratings';
        });
    }

    function paintStars(block, score) {
        const stars = block.querySelectorAll('[data-rating-star]');

        stars.forEach(function (starButton) {
            const starValue = Number(starButton.getAttribute('data-rating-star'));
            const active = starValue <= score;
            const icon = starButton.querySelector('i');

            starButton.setAttribute('aria-checked', active ? 'true' : 'false');

            if (icon) {
                icon.classList.toggle('fa-solid', active);
                icon.classList.toggle('fa-regular', !active);
            }
        });
    }

    ratingBlocks.forEach(function (block) {
        const url = block.getAttribute('data-rating-url');
        const initialScore = Number(block.getAttribute('data-user-score'));

        if (Number.isFinite(initialScore) && initialScore > 0) {
            paintStars(block, initialScore);
        }

        block.querySelectorAll('[data-rating-star]').forEach(function (starButton) {
            starButton.addEventListener('mouseenter', function () {
                if (block.classList.contains('is-loading')) {
                    return;
                }

                paintStars(block, Number(starButton.getAttribute('data-rating-star')));
            });

            starButton.addEventListener('click', async function (event) {
                event.preventDefault();

                if (!url || block.classList.contains('is-loading')) {
                    return;
                }

                const score = Number(starButton.getAttribute('data-rating-star'));

                block.classList.add('is-loading');

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            score: score,
                            page_url: window.location.href,
                        }),
                    });

                    if (response.status === 401 || response.status === 419) {
                        window.location.href = '/login';

                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    const data = await response.json();
                    block.setAttribute('data-user-score', String(data.score));
                    paintStars(block, data.score);
                    updateRatingSummary(block, data.average, data.ratings_count);
                } catch (error) {
                    if (typeof swal === 'function') {
                        swal('Rating failed', 'Could not save your rating. Please try again.', 'error');
                    } else {
                        window.alert('Could not save your rating. Please try again.');
                    }
                } finally {
                    block.classList.remove('is-loading');
                }
            });
        });

        block.addEventListener('mouseleave', function () {
            if (block.classList.contains('is-loading')) {
                return;
            }

            const savedScore = Number(block.getAttribute('data-user-score'));
            paintStars(block, Number.isFinite(savedScore) ? savedScore : 0);
        });
    });
})();

(function () {
    const commentSections = document.querySelectorAll('[data-comments-section]');

    if (!commentSections.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function showCommentFeedback(section, message, isError) {
        const success = section.querySelector('[data-comments-success]');
        const error = section.querySelector('[data-comment-error]');

        if (isError && error) {
            error.textContent = message;
            error.hidden = false;

            if (success) {
                success.hidden = true;
            }

            return;
        }

        if (error) {
            error.hidden = true;
        }

        if (success) {
            success.textContent = message;
            success.hidden = false;

            window.setTimeout(function () {
                success.hidden = true;
            }, 4000);
        }
    }

    function updateCommentsCount(section, count) {
        const counter = section.querySelector('[data-comments-count]');

        if (counter) {
            counter.textContent = '(' + Number(count).toLocaleString() + ')';
        }
    }

    function ensureEmptyState(list) {
        if (!list || list.querySelector('[data-comment-id]')) {
            return;
        }

        if (!list.querySelector('[data-comments-empty]')) {
            list.insertAdjacentHTML(
                'beforeend',
                '<li class="comments-empty" data-comments-empty>No comments yet. Be the first to share your thoughts.</li>'
            );
        }
    }

    function insertCommentHtml(section, list, data) {
        if (!data.html) {
            return;
        }

        if (data.is_reply && data.parent_id) {
            const repliesList = section.querySelector(
                '[data-comment-replies="' + data.parent_id + '"]'
            );

            if (repliesList) {
                repliesList.insertAdjacentHTML('beforeend', data.html);
            }

            return;
        }

        const emptyState = section.querySelector('[data-comments-empty]');

        if (emptyState) {
            emptyState.remove();
        }

        if (list) {
            list.insertAdjacentHTML('afterbegin', data.html);
        }
    }

    function updateReactionBar(bar, data) {
        if (!bar) {
            return;
        }

        bar.setAttribute('data-user-reaction', data.user_reaction || '');

        bar.querySelectorAll('[data-comment-react]').forEach(function (button) {
            const type = button.getAttribute('data-reaction-type');
            const count = data.reaction_counts && data.reaction_counts[type]
                ? data.reaction_counts[type]
                : 0;
            const countEl = button.querySelector('[data-reaction-count]');

            button.classList.toggle('is-active', data.user_reaction === type);
            button.setAttribute('aria-pressed', data.user_reaction === type ? 'true' : 'false');

            if (countEl) {
                countEl.textContent = count > 0 ? String(count) : '';
            }
        });
    }

    function openReplyPanel(item) {
        const panel = item.querySelector('[data-comment-reply-panel]');

        if (panel) {
            panel.hidden = false;
        }
    }

    function closeReplyPanel(item) {
        const panel = item.querySelector('[data-comment-reply-panel]');
        const textarea = item.querySelector('[data-comment-reply-body]');
        const error = item.querySelector('[data-comment-reply-error]');

        if (panel) {
            panel.hidden = true;
        }

        if (textarea) {
            textarea.value = '';
        }

        if (error) {
            error.hidden = true;
        }
    }

    function openCommentEdit(item) {
        const display = item.querySelector('[data-comment-body-display]');
        const panel = item.querySelector('[data-comment-edit-panel]');
        const editError = item.querySelector('[data-comment-edit-error]');

        if (display) {
            display.hidden = true;
        }

        if (panel) {
            panel.hidden = false;
        }

        if (editError) {
            editError.hidden = true;
        }
    }

    function closeCommentEdit(item) {
        const display = item.querySelector('[data-comment-body-display]');
        const panel = item.querySelector('[data-comment-edit-panel]');
        const textarea = item.querySelector('[data-comment-edit-body]');
        const editError = item.querySelector('[data-comment-edit-error]');

        if (display) {
            display.hidden = false;
        }

        if (panel) {
            panel.hidden = true;
        }

        if (textarea && display) {
            textarea.value = display.textContent.trim();
        }

        if (editError) {
            editError.hidden = true;
        }
    }

    async function parseJsonResponse(response) {
        return response.json().catch(function () {
            return {};
        });
    }

    function validationMessage(data, fallback) {
        if (data.errors && data.errors.body) {
            return data.errors.body[0];
        }

        return fallback;
    }

    document.querySelectorAll('[data-comment-form]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const section = form.closest('[data-comments-section]');

            if (!section || form.classList.contains('is-loading')) {
                return;
            }

            const textarea = form.querySelector('[data-comment-body]');
            const submitButton = form.querySelector('[data-comment-submit]');
            const list = section.querySelector('[data-comments-list]');
            const body = textarea ? textarea.value.trim() : '';

            if (!body) {
                showCommentFeedback(section, 'Please enter a comment.', true);

                return;
            }

            form.classList.add('is-loading');

            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        body: body,
                        page_url: window.location.href,
                    }),
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                const data = await parseJsonResponse(response);

                if (!response.ok) {
                    showCommentFeedback(
                        section,
                        validationMessage(data, 'Could not post your comment. Please try again.'),
                        true
                    );

                    return;
                }

                insertCommentHtml(section, list, data);

                if (textarea) {
                    textarea.value = '';
                }

                updateCommentsCount(section, data.comments_count);
                showCommentFeedback(section, data.message || 'Your comment was posted.', false);
            } catch (error) {
                showCommentFeedback(section, 'Could not post your comment. Please try again.', true);
            } finally {
                form.classList.remove('is-loading');

                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    });

    document.addEventListener('click', async function (event) {
        const editButton = event.target.closest('[data-comment-edit]');
        const cancelButton = event.target.closest('[data-comment-cancel]');
        const saveButton = event.target.closest('[data-comment-save]');
        const deleteButton = event.target.closest('[data-comment-delete]');
        const replyButton = event.target.closest('[data-comment-reply]');
        const replyCancelButton = event.target.closest('[data-comment-reply-cancel]');
        const replySubmitButton = event.target.closest('[data-comment-reply-submit]');
        const reactButton = event.target.closest('[data-comment-react]');

        if (replyButton) {
            const item = replyButton.closest('.comments-item:not(.comments-item-reply)');

            if (item) {
                openReplyPanel(item);
            }

            return;
        }

        if (replyCancelButton) {
            const item = replyCancelButton.closest('.comments-item:not(.comments-item-reply)');

            if (item) {
                closeReplyPanel(item);
            }

            return;
        }

        if (replySubmitButton) {
            event.preventDefault();

            const item = replySubmitButton.closest('.comments-item:not(.comments-item-reply)');
            const section = item ? item.closest('[data-comments-section]') : null;
            const url = replySubmitButton.getAttribute('data-comment-reply-url');
            const parentId = replySubmitButton.getAttribute('data-parent-id');
            const textarea = item ? item.querySelector('[data-comment-reply-body]') : null;
            const error = item ? item.querySelector('[data-comment-reply-error]') : null;
            const body = textarea ? textarea.value.trim() : '';

            if (!item || !section || !url || !parentId || item.classList.contains('is-loading')) {
                return;
            }

            if (!body) {
                if (error) {
                    error.textContent = 'Please enter a reply.';
                    error.hidden = false;
                }

                return;
            }

            item.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        body: body,
                        parent_id: Number(parentId),
                        page_url: window.location.href,
                    }),
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                const data = await parseJsonResponse(response);

                if (!response.ok) {
                    if (error) {
                        error.textContent = validationMessage(
                            data,
                            'Could not post your reply. Please try again.'
                        );
                        error.hidden = false;
                    }

                    return;
                }

                insertCommentHtml(section, null, data);
                closeReplyPanel(item);
                updateCommentsCount(section, data.comments_count);
                showCommentFeedback(section, data.message || 'Your reply was posted.', false);
            } catch (replyError) {
                if (error) {
                    error.textContent = 'Could not post your reply. Please try again.';
                    error.hidden = false;
                }
            } finally {
                item.classList.remove('is-loading');
            }

            return;
        }

        if (reactButton && !reactButton.disabled) {
            event.preventDefault();

            const bar = reactButton.closest('[data-comment-reactions]');
            const item = reactButton.closest('[data-comment-id]');
            const section = item ? item.closest('[data-comments-section]') : null;
            const url = bar ? bar.getAttribute('data-comment-react-url') : null;
            const type = reactButton.getAttribute('data-reaction-type');

            if (!bar || !url || !type || bar.classList.contains('is-loading')) {
                return;
            }

            bar.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ type: type }),
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                const data = await parseJsonResponse(response);

                if (!response.ok) {
                    if (section) {
                        showCommentFeedback(
                            section,
                            data.message || 'Could not update your reaction. Please try again.',
                            true
                        );
                    }

                    return;
                }

                updateReactionBar(bar, data);
            } catch (reactError) {
                if (section) {
                    showCommentFeedback(section, 'Could not update your reaction. Please try again.', true);
                }
            } finally {
                bar.classList.remove('is-loading');
            }

            return;
        }

        if (editButton) {
            const item = editButton.closest('[data-comment-id]');

            if (item) {
                openCommentEdit(item);
            }

            return;
        }

        if (cancelButton) {
            const item = cancelButton.closest('[data-comment-id]');

            if (item) {
                closeCommentEdit(item);
            }

            return;
        }

        if (saveButton) {
            event.preventDefault();

            const item = saveButton.closest('[data-comment-id]');
            const section = item ? item.closest('[data-comments-section]') : null;
            const url = item ? item.getAttribute('data-comment-update-url') : null;
            const textarea = item ? item.querySelector('[data-comment-edit-body]') : null;
            const editError = item ? item.querySelector('[data-comment-edit-error]') : null;
            const body = textarea ? textarea.value.trim() : '';

            if (!item || !section || !url || item.classList.contains('is-loading')) {
                return;
            }

            if (!body) {
                if (editError) {
                    editError.textContent = 'Please enter a comment.';
                    editError.hidden = false;
                }

                return;
            }

            item.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ body: body }),
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                const data = await parseJsonResponse(response);

                if (!response.ok) {
                    if (editError) {
                        editError.textContent = validationMessage(
                            data,
                            'Could not update your comment. Please try again.'
                        );
                        editError.hidden = false;
                    }

                    return;
                }

                if (data.html) {
                    item.outerHTML = data.html;
                }

                updateCommentsCount(section, data.comments_count);
                showCommentFeedback(section, data.message || 'Your comment was updated.', false);
            } catch (error) {
                if (editError) {
                    editError.textContent = 'Could not update your comment. Please try again.';
                    editError.hidden = false;
                }
            } finally {
                item.classList.remove('is-loading');
            }

            return;
        }

        if (deleteButton) {
            event.preventDefault();

            const item = deleteButton.closest('[data-comment-id]');
            const section = item ? item.closest('[data-comments-section]') : null;
            const url = item ? item.getAttribute('data-comment-delete-url') : null;

            if (!item || !section || !url || item.classList.contains('is-loading')) {
                return;
            }

            const confirmed = typeof swal === 'function'
                ? await swal({
                    title: 'Delete comment?',
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    buttons: {
                        cancel: 'Cancel',
                        confirm: { text: 'Delete', value: true },
                    },
                    dangerMode: true,
                })
                : window.confirm('Delete this comment? This cannot be undone.');

            if (!confirmed) {
                return;
            }

            item.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';

                    return;
                }

                const data = await parseJsonResponse(response);

                if (!response.ok) {
                    showCommentFeedback(
                        section,
                        data.message || 'Could not delete this comment. Please try again.',
                        true
                    );

                    return;
                }

                item.remove();
                updateCommentsCount(section, data.comments_count);
                ensureEmptyState(section.querySelector('[data-comments-list]'));
                showCommentFeedback(section, data.message || 'Comment deleted.', false);
            } catch (error) {
                showCommentFeedback(section, 'Could not delete this comment. Please try again.', true);
            } finally {
                if (item.isConnected) {
                    item.classList.remove('is-loading');
                }
            }
        }
    });
})();

(function () {
    const readSection = document.querySelector('.chapter-read-section');

    if (!readSection) {
        return;
    }

    const blockedKeys = new Set(['s', 'u', 'p', 'c', 'a']);

    readSection.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });

    readSection.addEventListener('dragstart', function (event) {
        event.preventDefault();
    });

    readSection.addEventListener('copy', function (event) {
        event.preventDefault();
    });

    readSection.addEventListener('cut', function (event) {
        event.preventDefault();
    });

    readSection.addEventListener('mousedown', function (event) {
        if (event.button === 1) {
            event.preventDefault();
        }
    });

    document.addEventListener('keydown', function (event) {
        const selection = window.getSelection();
        const anchor = selection ? selection.anchorNode : null;
        const isInsideReader = readSection.contains(anchor) || readSection.matches(':hover');

        if (!isInsideReader) {
            return;
        }

        const key = event.key.toLowerCase();

        if (!event.ctrlKey && !event.metaKey) {
            return;
        }

        if (blockedKeys.has(key)) {
            event.preventDefault();
        }
    });
})();
