(function () {
  if (window.GoSoloEditor) {
    return;
  }
  window.GoSoloEditor = true;

  function looksWritten(value) {
    return /<(p|br|strong|em|ul|ol|li|blockquote|a|h2|h3|figure)\b/i.test(value);
  }

  function escapeHtml(value) {
    return value.replace(/[&<>]/g, function (character) {
      if (character === '&') {
        return '&amp;';
      }
      if (character === '<') {
        return '&lt;';
      }
      return '&gt;';
    });
  }

  function plainToHtml(value) {
    var blocks = value.replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim().split(/\n{2,}/);
    var html = '';
    blocks.forEach(function (block) {
      var lines = block.split('\n').map(function (line) {
        return line.trim();
      }).filter(Boolean);
      if (!lines.length) {
        return;
      }
      html += '<p>' + lines.map(escapeHtml).join('<br>') + '</p>';
    });
    return html;
  }

  document.querySelectorAll('[data-editor]').forEach(function (root) {
    var source = root.querySelector('.editor-source');
    var surface = root.querySelector('.editor-surface');
    if (!source || !surface) {
      return;
    }
    var placeholder = root.getAttribute('data-placeholder') || '';
    if (placeholder) {
      surface.setAttribute('data-placeholder', placeholder);
    }
    var articleWorkspace = root.classList.contains('editor-article');
    var imageUrl = root.getAttribute('data-image-url') || '';
    var imageFile = root.querySelector('.editor-image-file');
    var imageError = root.querySelector('[data-image-error]');
    var replaceId = '';
    var savedRange = null;
    var removedMedia = [];
    var sessionLibrary = [];
    var figureSeq = 0;
    var retryFigure = null;
    var draftMedia = null;
    var imageMax = parseInt(root.getAttribute('data-image-max') || '', 10);
    if (!imageMax || imageMax < 1) {
      imageMax = 0;
    }
    var choices = root.querySelector('[data-image-choices]');
    var choiceLibrary = root.querySelector('[data-image-library]');
    var draft = root.querySelector('[data-image-draft]');
    var draftPreview = root.querySelector('[data-image-draft-preview]');
    var draftAlt = root.querySelector('[data-image-draft-alt]');
    var draftCaption = root.querySelector('[data-image-draft-caption]');
    var draftPlace = root.querySelector('[data-image-place]');
    var live = root.querySelector('[data-image-live]');
    var limitNote = root.querySelector('[data-image-limit-note]');
    function markEmpty() {
      var empty = surface.textContent.replace(/\u00a0/g, '').trim() === '';
      surface.classList.toggle('is-empty', empty && placeholder !== '');
    }
    function fitWorkspace() {
      if (!articleWorkspace) {
        return;
      }
      var minHeight = parseFloat(window.getComputedStyle(surface).minHeight) || 0;
      if (surface.style.height && surface.scrollHeight <= minHeight + 2) {
        surface.style.height = '';
        return;
      }
      if (surface.scrollHeight <= surface.clientHeight + 2) {
        return;
      }
      var before = window.scrollY;
      surface.style.height = (surface.offsetHeight + surface.scrollHeight - surface.clientHeight) + 'px';
      if (window.scrollY !== before) {
        window.scrollTo(window.scrollX, before);
      }
    }
    function revealCaret() {
      if (!articleWorkspace || !window.visualViewport) {
        return;
      }
      var selection = window.getSelection();
      if (!selection || !selection.isCollapsed || selection.rangeCount === 0 || !selection.anchorNode || !surface.contains(selection.anchorNode)) {
        return;
      }
      var rect = selection.getRangeAt(0).getBoundingClientRect();
      if (!rect || rect.height === 0 && rect.width === 0 && rect.top === 0) {
        return;
      }
      var viewport = window.visualViewport;
      var tools = root.querySelector('.editor-tools');
      var toolsBottom = tools ? tools.getBoundingClientRect().bottom : viewport.offsetTop;
      var topLimit = Math.max(viewport.offsetTop + 8, toolsBottom + 12);
      var bottomLimit = viewport.offsetTop + viewport.height - 24;
      if (rect.bottom > bottomLimit) {
        window.scrollBy(0, rect.bottom - bottomLimit);
      } else if (rect.top < topLimit) {
        window.scrollBy(0, rect.top - topLimit);
      }
    }
    function blockName() {
      return String(document.queryCommandValue('formatBlock') || '').toLowerCase().replace(/[<>]/g, '');
    }
    function applyBlock(tag) {
      document.execCommand('formatBlock', false, tag);
      if (blockName() !== tag) {
        document.execCommand('formatBlock', false, '<' + tag + '>');
      }
    }
    function selectionInside(tagName) {
      var selection = window.getSelection();
      if (!selection || !selection.anchorNode || !surface.contains(selection.anchorNode)) {
        return false;
      }
      var node = selection.anchorNode.nodeType === 3 ? selection.anchorNode.parentNode : selection.anchorNode;
      while (node && node !== surface) {
        if (node.nodeName && node.nodeName.toLowerCase() === tagName) {
          return true;
        }
        node = node.parentNode;
      }
      return false;
    }
    if (looksWritten(source.value)) {
      surface.innerHTML = source.value;
    } else if (source.value.trim() !== '') {
      surface.innerHTML = plainToHtml(source.value);
    }
    function labelText(name, fallback) {
      return root.getAttribute(name) || fallback;
    }
    function showImageError(message) {
      if (!imageError) {
        return;
      }
      if (!message) {
        imageError.hidden = true;
        imageError.textContent = '';
        return;
      }
      imageError.hidden = false;
      imageError.textContent = message;
    }
    function mediaCatalog() {
      var node = document.getElementById('reading-media');
      if (!node) {
        return {};
      }
      try {
        return JSON.parse(node.textContent || '{}') || {};
      } catch (error) {
        return {};
      }
    }
    function rememberRange() {
      var selection = window.getSelection();
      if (!selection || selection.rangeCount === 0 || !selection.anchorNode || !surface.contains(selection.anchorNode)) {
        savedRange = null;
        return;
      }
      savedRange = selection.getRangeAt(0).cloneRange();
    }
    function noteRemoved(id) {
      if (!id || removedMedia.indexOf(id) !== -1) {
        return;
      }
      removedMedia.push(id);
      var form = root.closest('form');
      if (!form) {
        return;
      }
      var field = form.querySelector('input[name="removed_media"]');
      if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = 'removed_media';
        form.appendChild(field);
      }
      field.value = removedMedia.join(',');
    }
    function announce(message) {
      if (!live) {
        return;
      }
      live.textContent = '';
      window.setTimeout(function () {
        live.textContent = message;
      }, 30);
    }
    function isSkippable(node) {
      if (!node || node.nodeType !== 1) {
        return true;
      }
      if (node.matches('figure')) {
        return false;
      }
      return (node.textContent || '').replace(/\u00a0/g, '').trim() === '';
    }
    function meaningfulSibling(node, direction) {
      var sibling = direction < 0 ? node.previousElementSibling : node.nextElementSibling;
      while (sibling && isSkippable(sibling)) {
        sibling = direction < 0 ? sibling.previousElementSibling : sibling.nextElementSibling;
      }
      return sibling;
    }
    function realFigures() {
      return Array.prototype.filter.call(surface.querySelectorAll('figure[data-media]'), function (figure) {
        return /^[1-9][0-9]{0,9}$/.test(figure.getAttribute('data-media') || '');
      });
    }
    function proseText() {
      var text = '';
      Array.prototype.forEach.call(surface.children, function (child) {
        if (child.matches && child.matches('figure')) {
          return;
        }
        text += ' ' + (child.textContent || '');
      });
      return text.replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
    }
    function canMove(figure, direction) {
      var sibling = meaningfulSibling(figure, direction);
      if (!sibling || (sibling.matches && sibling.matches('figure'))) {
        return false;
      }
      var beyond = meaningfulSibling(sibling, direction);
      return !(beyond && beyond.matches && beyond.matches('figure'));
    }
    function refreshMoveButtons(figure) {
      var up = figure.querySelector('[data-image-up]');
      var down = figure.querySelector('[data-image-down]');
      if (up) {
        up.disabled = !canMove(figure, -1);
      }
      if (down) {
        down.disabled = !canMove(figure, 1);
      }
    }
    function refreshMoves() {
      realFigures().forEach(refreshMoveButtons);
    }
    function refreshLimit() {
      if (!imageUrl) {
        return;
      }
      var count = realFigures().length;
      var full = imageMax > 0 && count >= imageMax;
      var button = root.querySelector('[data-cmd="image"]');
      var sentence = labelText('data-image-limit', '');
      if (limitNote) {
        limitNote.textContent = sentence;
        limitNote.hidden = !full;
      }
      if (button) {
        button.disabled = full;
        if (full) {
          button.setAttribute('aria-describedby', 'reading-image-limit');
          if (sentence) {
            button.title = sentence;
          }
        } else {
          button.removeAttribute('aria-describedby');
          button.title = button.getAttribute('aria-label') || '';
        }
      }
    }
    function libraryItems() {
      var used = {};
      realFigures().forEach(function (figure) {
        used[figure.getAttribute('data-media')] = true;
      });
      var merged = [];
      var seen = {};
      var server = [];
      var node = document.getElementById('reading-library');
      if (node) {
        try {
          server = JSON.parse(node.textContent || '[]') || [];
        } catch (error) {
          server = [];
        }
      }
      server.concat(sessionLibrary).forEach(function (item) {
        var id = String(item.id || '');
        if (!/^[1-9][0-9]{0,9}$/.test(id) || used[id] || seen[id]) {
          return;
        }
        seen[id] = true;
        merged.push(item);
      });
      return merged;
    }
    function rememberLibrary(media) {
      var id = String(media.id || '');
      sessionLibrary = sessionLibrary.filter(function (item) {
        return String(item.id) !== id;
      });
      sessionLibrary.push({ id: media.id, url: media.url || '', alt: media.alt || '' });
    }
    function discardFigure(figure) {
      var ask = labelText('data-image-remove-ask', 'Remove this supporting image from the article?');
      if (!window.confirm(ask)) {
        return false;
      }
      var id = figure.getAttribute('data-media') || '';
      noteRemoved(id);
      var next = figure.nextElementSibling;
      figure.remove();
      if (next && next.matches && next.matches('p')) {
        var range = document.createRange();
        range.setStart(next, 0);
        range.collapse(true);
        var selection = window.getSelection();
        if (selection) {
          selection.removeAllRanges();
          selection.addRange(range);
        }
        surface.focus();
      } else {
        surface.focus();
      }
      markEmpty();
      fitWorkspace();
      refreshMoves();
      refreshLimit();
      surface.dispatchEvent(new Event('input', { bubbles: true }));
      return true;
    }
    function moveFigure(figure, direction) {
      if (!canMove(figure, direction)) {
        announce(labelText('data-image-gap', ''));
        figure.focus();
        return;
      }
      var sibling = meaningfulSibling(figure, direction);
      if (direction < 0) {
        sibling.before(figure);
        announce(labelText('data-image-moved-up', 'Supporting image moved up.'));
      } else {
        sibling.after(figure);
        announce(labelText('data-image-moved-down', 'Supporting image moved down.'));
      }
      refreshMoves();
      figure.focus();
      fitWorkspace();
      surface.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function buildFigure(media) {
      figureSeq += 1;
      var figure = document.createElement('figure');
      figure.className = 'writing-figure';
      figure.setAttribute('data-media', String(media.id));
      figure.setAttribute('contenteditable', 'false');
      figure.tabIndex = 0;
      figure.setAttribute('aria-label', labelText('data-image-label', 'Supporting Image'));
      var kicker = document.createElement('p');
      kicker.className = 'writing-figure-label';
      kicker.textContent = labelText('data-image-label', 'Supporting Image');
      figure.appendChild(kicker);
      if (media.url) {
        var image = document.createElement('img');
        image.src = media.url;
        image.alt = media.alt || '';
        figure.appendChild(image);
      } else {
        var missing = document.createElement('p');
        missing.className = 'writing-figure-missing';
        missing.textContent = labelText('data-image-missing', 'This image is no longer on the shelf.');
        figure.appendChild(missing);
      }
      var captionPreview = document.createElement('p');
      captionPreview.className = 'writing-figure-caption';
      captionPreview.textContent = media.caption || '';
      captionPreview.hidden = !media.caption;
      figure.appendChild(captionPreview);
      var status = document.createElement('p');
      status.className = 'writing-figure-status';
      status.setAttribute('data-image-status', '');
      figure.appendChild(status);
      var tools = document.createElement('div');
      tools.className = 'writing-figure-tools';
      var helpId = 'reading-image-alt-help-' + figureSeq;
      var altLabel = document.createElement('label');
      var altName = document.createElement('span');
      altName.textContent = labelText('data-image-alt-label', 'Description');
      var altInput = document.createElement('input');
      altInput.type = 'text';
      altInput.maxLength = 255;
      altInput.setAttribute('data-image-alt', '');
      altInput.value = media.alt || '';
      altInput.setAttribute('aria-describedby', helpId);
      var altHelp = document.createElement('span');
      altHelp.id = helpId;
      altHelp.className = 'soft';
      altHelp.textContent = labelText('data-image-alt-help', 'Describe what matters in the image for someone who cannot see it.');
      altLabel.appendChild(altName);
      altLabel.appendChild(altInput);
      altLabel.appendChild(altHelp);
      var captionLabel = document.createElement('label');
      var captionName = document.createElement('span');
      captionName.textContent = labelText('data-image-caption-label', 'Caption');
      var captionInput = document.createElement('input');
      captionInput.type = 'text';
      captionInput.maxLength = 300;
      captionInput.setAttribute('data-image-caption', '');
      captionInput.value = media.caption || '';
      captionLabel.appendChild(captionName);
      captionLabel.appendChild(captionInput);
      var actions = document.createElement('div');
      actions.className = 'writing-figure-actions';
      var up = document.createElement('button');
      up.type = 'button';
      up.setAttribute('data-image-up', '');
      up.textContent = labelText('data-image-up', 'Move up');
      var down = document.createElement('button');
      down.type = 'button';
      down.setAttribute('data-image-down', '');
      down.textContent = labelText('data-image-down', 'Move down');
      var replace = document.createElement('button');
      replace.type = 'button';
      replace.setAttribute('data-image-replace', '');
      replace.textContent = labelText('data-image-replace-label', 'Replace image');
      var remove = document.createElement('button');
      remove.type = 'button';
      remove.setAttribute('data-image-remove', '');
      remove.textContent = labelText('data-image-remove-label', 'Remove image');
      actions.appendChild(up);
      actions.appendChild(down);
      actions.appendChild(replace);
      actions.appendChild(remove);
      tools.appendChild(altLabel);
      tools.appendChild(captionLabel);
      tools.appendChild(actions);
      figure.appendChild(tools);
      function refreshStatus() {
        var ready = altInput.value.trim() !== '';
        status.textContent = ready
          ? labelText('data-image-alt-ready', 'Description added.')
          : labelText('data-image-alt-missing', 'A description is still needed.');
        var imageNode = figure.querySelector('img');
        if (imageNode) {
          imageNode.alt = altInput.value.trim();
        }
        var caption = captionInput.value.trim();
        captionPreview.textContent = caption;
        captionPreview.hidden = caption === '';
      }
      altInput.addEventListener('input', refreshStatus);
      captionInput.addEventListener('input', refreshStatus);
      up.addEventListener('click', function (event) {
        event.preventDefault();
        moveFigure(figure, -1);
      });
      down.addEventListener('click', function (event) {
        event.preventDefault();
        moveFigure(figure, 1);
      });
      refreshStatus();
      return figure;
    }
    function buildPlaceholder() {
      var figure = document.createElement('figure');
      figure.className = 'writing-figure writing-figure-pending';
      figure.setAttribute('contenteditable', 'false');
      figure.tabIndex = 0;
      figure.setAttribute('aria-label', labelText('data-image-label', 'Supporting Image'));
      var note = document.createElement('p');
      note.className = 'writing-figure-missing';
      note.textContent = labelText('data-image-failed', 'The image could not be added.');
      var actions = document.createElement('div');
      actions.className = 'writing-figure-actions';
      var retry = document.createElement('button');
      retry.type = 'button';
      retry.setAttribute('data-image-retry', '');
      retry.textContent = labelText('data-image-retry', 'Retry');
      var remove = document.createElement('button');
      remove.type = 'button';
      remove.setAttribute('data-image-remove', '');
      remove.textContent = labelText('data-image-remove-label', 'Remove image');
      actions.appendChild(retry);
      actions.appendChild(remove);
      figure.appendChild(note);
      figure.appendChild(actions);
      retry.addEventListener('click', function (event) {
        event.preventDefault();
        retryFigure = figure;
        replaceId = '';
        if (imageFile) {
          imageFile.click();
        }
      });
      return figure;
    }
    function figureFromNode(figure) {
      var id = figure.getAttribute('data-media') || '';
      var catalog = mediaCatalog();
      var known = catalog[id] || {};
      var captionNode = figure.querySelector('figcaption');
      return buildFigure({
        id: id,
        url: known.url || '',
        alt: figure.getAttribute('data-alt') || known.alt || '',
        caption: captionNode ? captionNode.textContent.trim() : ''
      });
    }
    function upgradeFigures() {
      if (!imageUrl) {
        return;
      }
      surface.querySelectorAll('figure[data-media]').forEach(function (figure) {
        if (figure.querySelector('[data-image-alt]')) {
          return;
        }
        figure.replaceWith(figureFromNode(figure));
      });
    }
    function blockFromRange(range) {
      var block = range ? range.startContainer : null;
      if (block && block.nodeType === 3) {
        block = block.parentNode;
      }
      if (block && block.closest) {
        var host = block.closest('figure');
        if (host && surface.contains(host)) {
          block = host;
        }
      }
      while (block && block.parentNode !== surface) {
        block = block.parentNode;
      }
      return block && block.parentNode === surface ? block : null;
    }
    function slotTouches(reference, placeBefore) {
      if (!reference) {
        var last = surface.lastElementChild;
        while (last && isSkippable(last)) {
          last = last.previousElementSibling;
        }
        return !!(last && last.matches && last.matches('figure'));
      }
      if (reference.matches && reference.matches('figure')) {
        return true;
      }
      var sibling = meaningfulSibling(reference, placeBefore ? -1 : 1);
      return !!(sibling && sibling.matches && sibling.matches('figure'));
    }
    function finishPlacement(figure) {
      if (!figure.nextElementSibling) {
        var after = document.createElement('p');
        after.appendChild(document.createElement('br'));
        figure.after(after);
      }
      refreshMoves();
      refreshLimit();
      markEmpty();
      fitWorkspace();
      figure.focus();
      surface.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function placeFigure(figure) {
      if (imageMax > 0 && realFigures().length >= imageMax) {
        showImageError(labelText('data-image-limit', ''));
        refreshLimit();
        return false;
      }
      if (proseText() === '') {
        showImageError(labelText('data-image-words', ''));
        return false;
      }
      var selection = window.getSelection();
      var range = null;
      if (selection && selection.rangeCount && selection.anchorNode && surface.contains(selection.anchorNode)) {
        range = selection.getRangeAt(0);
      } else if (savedRange && savedRange.startContainer && surface.contains(savedRange.startContainer)) {
        range = savedRange;
      }
      var block = blockFromRange(range);
      if (!block) {
        if (slotTouches(null, false)) {
          showImageError(labelText('data-image-gap', ''));
          return false;
        }
        surface.appendChild(figure);
        finishPlacement(figure);
        return true;
      }
      if (block.matches && block.matches('figure')) {
        var nextText = meaningfulSibling(block, 1);
        var prevText = meaningfulSibling(block, -1);
        if (nextText && !(nextText.matches && nextText.matches('figure')) && !slotTouches(nextText, false)) {
          nextText.after(figure);
        } else if (prevText && !(prevText.matches && prevText.matches('figure')) && !slotTouches(prevText, true)) {
          prevText.before(figure);
        } else {
          showImageError(labelText('data-image-gap', ''));
          return false;
        }
        finishPlacement(figure);
        return true;
      }
      var atStart = range && range.collapsed && range.startOffset === 0 && (range.startContainer === block || range.startContainer.parentNode === block);
      if (slotTouches(block, atStart)) {
        atStart = !atStart;
        if (slotTouches(block, atStart)) {
          showImageError(labelText('data-image-gap', ''));
          return false;
        }
      }
      if (atStart) {
        block.before(figure);
      } else {
        block.after(figure);
      }
      finishPlacement(figure);
      return true;
    }
    function serializeArticle() {
      if (!imageUrl) {
        return surface.innerHTML;
      }
      var copy = surface.cloneNode(true);
      copy.querySelectorAll('figure').forEach(function (figure) {
        if (!figure.hasAttribute('data-media')) {
          figure.remove();
        }
      });
      copy.querySelectorAll('figure[data-media]').forEach(function (figure) {
        var id = figure.getAttribute('data-media') || '';
        if (!/^[1-9][0-9]{0,9}$/.test(id)) {
          figure.remove();
          return;
        }
        var altInput = figure.querySelector('[data-image-alt]');
        var captionInput = figure.querySelector('[data-image-caption]');
        var alt = altInput ? altInput.value.trim() : (figure.getAttribute('data-alt') || '');
        var caption = captionInput ? captionInput.value.trim() : '';
        var clean = document.createElement('figure');
        clean.setAttribute('data-media', id);
        clean.setAttribute('data-alt', alt);
        if (caption) {
          var cap = document.createElement('figcaption');
          cap.textContent = caption;
          clean.appendChild(cap);
        }
        figure.replaceWith(clean);
      });
      return copy.innerHTML;
    }
    root.serializeArticle = serializeArticle;
    function openDraft(media) {
      draftMedia = media;
      if (!draft || !draftAlt || !draftCaption || !draftPlace) {
        placeFigure(buildFigure({ id: media.id, url: media.url, alt: media.alt || '', caption: '' }));
        return;
      }
      if (draftPreview) {
        if (media.url) {
          draftPreview.src = media.url;
          draftPreview.hidden = false;
        } else {
          draftPreview.removeAttribute('src');
          draftPreview.hidden = true;
        }
      }
      draftAlt.value = media.alt || '';
      draftCaption.value = '';
      draftPlace.disabled = draftAlt.value.trim() === '';
      if (choices) {
        choices.hidden = true;
      }
      draft.hidden = false;
      draftAlt.focus();
    }
    function closeDraft() {
      if (draft) {
        draft.hidden = true;
      }
      draftMedia = null;
    }
    function uploadImage(file) {
      showImageError('');
      if (!file) {
        return;
      }
      if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
        showImageError(labelText('data-image-failed', 'The image could not be added.'));
        return;
      }
      if (file.size > 2000000) {
        showImageError(labelText('data-image-size', 'That picture needs to be under 2 MB.'));
        if (imageFile) {
          imageFile.value = '';
        }
        return;
      }
      var data = new FormData();
      var form = root.closest('form');
      var token = form ? form.querySelector('input[name="csrf"]') : null;
      if (token) {
        data.append('csrf', token.value);
      }
      data.append('inline_image', file);
      data.append('article_id', root.getAttribute('data-article-id') || '0');
      if (replaceId) {
        data.append('id', replaceId);
        var current = surface.querySelector('figure[data-media="' + replaceId + '"] [data-image-alt]');
        if (current) {
          data.append('alt', current.value.trim());
        }
      }
      var pendingReplace = replaceId;
      var pendingRetry = retryFigure;
      replaceId = '';
      retryFigure = null;
      var imageButton = root.querySelector('[data-cmd="image"]');
      if (imageButton) {
        imageButton.setAttribute('aria-busy', 'true');
      }
      fetch(imageUrl, { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (response) {
          return response.json().then(function (payload) {
            if (!response.ok) {
              throw new Error(payload && payload.error ? payload.error : '');
            }
            return payload;
          });
        })
        .then(function (payload) {
          if (pendingReplace) {
            var existing = surface.querySelector('figure[data-media="' + pendingReplace + '"]');
            if (existing) {
              var captionInput = existing.querySelector('[data-image-caption]');
              var altInput = existing.querySelector('[data-image-alt]');
              var fresh = buildFigure({
                id: payload.id,
                url: payload.url,
                alt: (altInput ? altInput.value : payload.alt) || '',
                caption: captionInput ? captionInput.value : ''
              });
              existing.replaceWith(fresh);
              refreshMoves();
              markEmpty();
              fitWorkspace();
              fresh.focus();
              surface.dispatchEvent(new Event('input', { bubbles: true }));
              return;
            }
          }
          rememberLibrary({ id: payload.id, url: payload.url, alt: payload.alt || '' });
          if (pendingRetry && pendingRetry.parentNode) {
            pendingRetry.remove();
          }
          openDraft({ id: payload.id, url: payload.url, alt: payload.alt || '' });
        })
        .catch(function (error) {
          var message = error && error.message ? error.message : '';
          var interrupted = !message || message.indexOf('Unexpected') === 0 || message.indexOf('JSON') !== -1 || message === 'Failed to fetch';
          if (interrupted) {
            message = labelText('data-image-failed', 'The image could not be added.');
          }
          showImageError(message);
          if (interrupted && !pendingReplace) {
            if (pendingRetry && pendingRetry.parentNode) {
              pendingRetry.focus();
            } else if (!surface.querySelector('.writing-figure-pending')) {
              var placeholder = buildPlaceholder();
              if (placeFigure(placeholder) === false) {
                showImageError(message);
              }
            }
          }
        })
        .finally(function () {
          if (imageButton) {
            imageButton.removeAttribute('aria-busy');
          }
          if (imageFile) {
            imageFile.value = '';
          }
        });
    }
    function openChoices() {
      var items = libraryItems();
      if (!items.length || !choices || !choiceLibrary) {
        if (imageFile) {
          imageFile.click();
        }
        return;
      }
      choiceLibrary.replaceChildren();
      items.forEach(function (item) {
        var choice = document.createElement('button');
        choice.type = 'button';
        choice.className = 'editor-image-choice';
        choice.textContent = labelText('data-image-use', 'Use this image');
        if (item.url) {
          var thumb = document.createElement('img');
          thumb.src = item.url;
          thumb.alt = '';
          choice.insertBefore(thumb, choice.firstChild);
        }
        choice.addEventListener('click', function () {
          choices.hidden = true;
          openDraft(item);
        });
        choiceLibrary.appendChild(choice);
      });
      choices.hidden = false;
      var first = choiceLibrary.querySelector('button');
      if (first) {
        first.focus();
      }
    }
    markEmpty();
    fitWorkspace();
    upgradeFigures();
    refreshMoves();
    refreshLimit();
    if (draftAlt && draftPlace) {
      draftAlt.addEventListener('input', function () {
        draftPlace.disabled = draftAlt.value.trim() === '';
      });
    }
    if (draftPlace) {
      draftPlace.addEventListener('click', function () {
        if (!draftMedia || !draftAlt || draftAlt.value.trim() === '') {
          return;
        }
        var figure = buildFigure({
          id: draftMedia.id,
          url: draftMedia.url,
          alt: draftAlt.value.trim(),
          caption: draftCaption ? draftCaption.value.trim() : ''
        });
        if (!placeFigure(figure)) {
          return;
        }
        closeDraft();
        showImageError('');
      });
    }
    var draftCancel = root.querySelector('[data-image-draft-cancel]');
    if (draftCancel) {
      draftCancel.addEventListener('click', function () {
        closeDraft();
        var imageButton = root.querySelector('[data-cmd="image"]');
        if (imageButton) {
          imageButton.focus();
        }
      });
    }
    var uploadNew = root.querySelector('[data-image-upload]');
    if (uploadNew) {
      uploadNew.addEventListener('click', function () {
        if (choices) {
          choices.hidden = true;
        }
        if (imageFile) {
          imageFile.click();
        }
      });
    }
    var choicesCancel = root.querySelector('[data-image-choices-cancel]');
    if (choicesCancel) {
      choicesCancel.addEventListener('click', function () {
        if (choices) {
          choices.hidden = true;
        }
        var imageButton = root.querySelector('[data-cmd="image"]');
        if (imageButton) {
          imageButton.focus();
        }
      });
    }
    if (imageUrl) {
      surface.addEventListener('paste', function (event) {
        var clipboard = event.clipboardData;
        if (!clipboard) {
          return;
        }
        var html = clipboard.getData('text/html') || '';
        if (!/<img\b|<figure\b/i.test(html)) {
          return;
        }
        event.preventDefault();
        document.execCommand('insertText', false, clipboard.getData('text/plain') || '');
      });
      surface.addEventListener('dragover', function (event) {
        if (event.dataTransfer && Array.prototype.indexOf.call(event.dataTransfer.types || [], 'Files') !== -1) {
          event.preventDefault();
        }
      });
      surface.addEventListener('drop', function (event) {
        if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
          event.preventDefault();
        }
      });
    }
    if (imageFile) {
      imageFile.addEventListener('change', function () {
        var file = imageFile.files && imageFile.files[0];
        uploadImage(file);
      });
    }
    surface.addEventListener('click', function (event) {
      var remove = event.target.closest ? event.target.closest('[data-image-remove]') : null;
      if (remove && surface.contains(remove)) {
        event.preventDefault();
        var figure = remove.closest('figure');
        if (figure) {
          discardFigure(figure);
        }
        return;
      }
      var replace = event.target.closest ? event.target.closest('[data-image-replace]') : null;
      if (replace && surface.contains(replace) && imageFile) {
        event.preventDefault();
        var host = replace.closest('figure');
        replaceId = host ? host.getAttribute('data-media') || '' : '';
        rememberRange();
        imageFile.click();
      }
    });
    surface.addEventListener('input', function () {
      markEmpty();
      fitWorkspace();
      revealCaret();
    });
    surface.addEventListener('keyup', revealCaret);
    if (articleWorkspace && window.visualViewport) {
      window.visualViewport.addEventListener('resize', revealCaret);
      window.addEventListener('resize', fitWorkspace);
    }
    surface.addEventListener('keydown', function (event) {
      if (imageUrl && event.key === 'Backspace' && !event.ctrlKey && !event.metaKey && !event.altKey) {
        var selection = window.getSelection();
        if (selection && selection.isCollapsed && selection.anchorNode && surface.contains(selection.anchorNode) && selection.anchorOffset === 0) {
          var block = selection.anchorNode.nodeType === 3 ? selection.anchorNode.parentNode : selection.anchorNode;
          while (block && block.parentNode !== surface) {
            block = block.parentNode;
          }
          var previous = block ? block.previousElementSibling : null;
          if (previous && previous.matches && previous.matches('figure[data-media]')) {
            event.preventDefault();
            discardFigure(previous);
            return;
          }
        }
      }
      if (!(event.ctrlKey || event.metaKey) || event.altKey) {
        return;
      }
      var key = event.key.toLowerCase();
      if (key === 'b') {
        event.preventDefault();
        document.execCommand('bold', false, null);
      } else if (key === 'i') {
        event.preventDefault();
        document.execCommand('italic', false, null);
      } else {
        return;
      }
      markEmpty();
      fitWorkspace();
      refreshToolbar();
      revealCaret();
    });
    function refreshToolbar() {
      if (!articleWorkspace) {
        return;
      }
      var block = blockName();
      root.querySelectorAll('[data-cmd]').forEach(function (button) {
        var command = button.getAttribute('data-cmd');
        var active = false;
        if (command === 'bold' || command === 'italic' || command === 'insertUnorderedList' || command === 'insertOrderedList') {
          active = document.queryCommandState(command);
        } else if (command === 'quote') {
          active = block === 'blockquote';
        } else if (command === 'heading') {
          active = block === 'h2';
        } else if (command === 'subheading') {
          active = block === 'h3';
        } else if (command === 'link') {
          active = selectionInside('a');
        } else if (command === 'image') {
          return;
        }
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }
    document.addEventListener('selectionchange', function () {
      var selection = window.getSelection();
      if (!selection || !selection.anchorNode || !surface.contains(selection.anchorNode)) {
        return;
      }
      if (draft && !draft.hidden) {
        rememberRange();
      }
      refreshToolbar();
      revealCaret();
    });
    root.classList.add('is-ready');
    root.querySelectorAll('[data-cmd]').forEach(function (button) {
      button.addEventListener('mousedown', function (event) {
        event.preventDefault();
      });
      button.addEventListener('click', function (event) {
        event.preventDefault();
        var command = button.getAttribute('data-cmd');
        if (command === 'image') {
          if (button.disabled || (draft && !draft.hidden) || (choices && !choices.hidden)) {
            return;
          }
          replaceId = '';
          retryFigure = null;
          rememberRange();
          openChoices();
          return;
        }
        surface.focus();
        if (command === 'link') {
          var ask = root.getAttribute('data-link-ask') || 'Where should this link go? Leave this empty to remove the link.';
          var address = window.prompt(ask);
          if (address === null) {
            return;
          }
          if (address.trim() === '') {
            document.execCommand('unlink', false, null);
          } else {
            document.execCommand('createLink', false, address);
          }
          markEmpty();
          fitWorkspace();
          refreshToolbar();
          revealCaret();
          return;
        }
        if (command === 'quote') {
          applyBlock('blockquote');
        } else if (command === 'heading') {
          applyBlock('h2');
        } else if (command === 'subheading') {
          applyBlock('h3');
        } else {
          document.execCommand(command, false, null);
        }
        markEmpty();
        fitWorkspace();
        refreshToolbar();
        revealCaret();
      });
    });
    var form = root.closest('form');
    if (form) {
      form.addEventListener('submit', function (event) {
        if (imageUrl) {
          var pending = surface.querySelector('.writing-figure-pending');
          if (pending) {
            event.preventDefault();
            showImageError(labelText('data-image-failed', 'The image could not be added.'));
            pending.focus();
            return;
          }
          var blank = null;
          surface.querySelectorAll('figure[data-media] [data-image-alt]').forEach(function (input) {
            if (!blank && input.value.trim() === '') {
              blank = input;
            }
          });
          if (blank) {
            event.preventDefault();
            showImageError(labelText('data-image-needs', 'Each image needs a short description of what it shows.'));
            var host = blank.closest('figure');
            if (host) {
              host.focus();
            }
            blank.focus();
            return;
          }
          var issue = '';
          var figures = realFigures();
          if (figures.length) {
            var seenWords = proseText() !== '';
            var previousFigure = false;
            var stacked = false;
            Array.prototype.forEach.call(surface.children, function (child) {
              if (!(child.matches && child.matches('figure[data-media]'))) {
                if (!isSkippable(child)) {
                  previousFigure = false;
                }
                return;
              }
              if (previousFigure) {
                stacked = true;
              }
              previousFigure = true;
            });
            if (imageMax > 0 && figures.length > imageMax) {
              issue = labelText('data-image-limit', '');
            } else if (stacked) {
              issue = labelText('data-image-gap', '');
            } else if (!seenWords) {
              issue = labelText('data-image-words', '');
            }
          }
          if (issue) {
            event.preventDefault();
            showImageError(issue);
            return;
          }
        }
        source.value = imageUrl ? serializeArticle() : surface.innerHTML;
        if (!articleWorkspace) {
          return;
        }
        if (form.getAttribute('data-saving') === '1') {
          event.preventDefault();
          return;
        }
        form.setAttribute('data-saving', '1');
        var submitter = event.submitter;
        if (submitter && submitter.name === 'commit') {
          var hidden = form.querySelector('input[type="hidden"][name="commit"]');
          if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'commit';
            form.appendChild(hidden);
          }
          hidden.value = submitter.value;
        }
        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
          button.disabled = true;
          button.setAttribute('aria-busy', 'true');
        });
        var status = form.querySelector('[data-saving-status]');
        if (status) {
          status.hidden = false;
          status.textContent = status.getAttribute('data-saving-status') || '';
        }
      });
    }
  });

  document.querySelectorAll('[data-reading-preview]').forEach(function (wrap) {
    var form = wrap.querySelector('form');
    var surface = wrap.querySelector('.editor-surface');
    var preview = wrap.querySelector('.reading-preview');
    var writeButton = wrap.querySelector('[data-reading-mode="write"]');
    var previewButton = wrap.querySelector('[data-reading-mode="preview"]');
    if (!form || !surface || !preview || !writeButton || !previewButton) {
      return;
    }
    var imageInput = form.querySelector('input[name="image"]');
    var blobUrl = '';
    function revokeBlob() {
      if (blobUrl) {
        URL.revokeObjectURL(blobUrl);
        blobUrl = '';
      }
    }
    function chosenImage() {
      var file = imageInput && imageInput.files && imageInput.files[0];
      if (!file || !/^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
        return wrap.getAttribute('data-existing-image') || '';
      }
      if (!blobUrl) {
        blobUrl = URL.createObjectURL(file);
      }
      return blobUrl;
    }
    function stripRisky(element) {
      Array.prototype.slice.call(element.attributes || []).forEach(function (attr) {
        if (attr.name.indexOf('on') === 0 || attr.name === 'style') {
          element.removeAttribute(attr.name);
        }
      });
    }
    function cleanCopy(node) {
      var copy = node.cloneNode(true);
      if (!copy.querySelectorAll) {
        return copy;
      }
      if (/^(script|style|iframe|object|embed|form)$/i.test(copy.nodeName)) {
        return document.createTextNode('');
      }
      stripRisky(copy);
      copy.querySelectorAll('script, style, iframe, object, embed, form').forEach(function (element) {
        element.remove();
      });
      copy.querySelectorAll('*').forEach(stripRisky);
      return copy;
    }
    function showPreview() {
      var title = form.querySelector('input[name="title"]');
      var standfirst = form.querySelector('input[name="standfirst"]');
      var category = form.querySelector('select[name="category_id"]');
      var titleNode = preview.querySelector('[data-preview="title"]');
      var standNode = preview.querySelector('[data-preview="standfirst"]');
      var categoryNode = preview.querySelector('[data-preview="category"]');
      var imageNode = preview.querySelector('[data-preview="image"]');
      var bodyNode = preview.querySelector('[data-preview="body"]');
      titleNode.textContent = title ? title.value.trim() : '';
      var lede = standfirst ? standfirst.value.trim() : '';
      standNode.textContent = lede;
      standNode.hidden = lede === '';
      var categoryText = '';
      if (category && category.selectedIndex >= 0) {
        categoryText = category.options[category.selectedIndex].text;
      }
      categoryNode.textContent = categoryText;
      var src = chosenImage();
      if (src) {
        imageNode.src = src;
        imageNode.hidden = false;
      } else {
        imageNode.removeAttribute('src');
        imageNode.hidden = true;
      }
      bodyNode.replaceChildren();
      var endpoint = wrap.getAttribute('data-preview-url');
      if (!endpoint) {
        return;
      }
      var data = new FormData();
      var token = form.querySelector('input[name="csrf"]');
      if (token) {
        data.append('csrf', token.value);
      }
      var editorRoot = surface.closest('[data-editor]');
      data.append('body', editorRoot && editorRoot.serializeArticle ? editorRoot.serializeArticle() : surface.innerHTML);
      data.append('article_id', editorRoot ? (editorRoot.getAttribute('data-article-id') || '0') : '0');
      preview.setAttribute('aria-busy', 'true');
      fetch(endpoint, { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'text/html' } })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('preview');
          }
          return response.text();
        })
        .then(function (html) {
          bodyNode.innerHTML = html;
        })
        .catch(function () {
          Array.prototype.forEach.call(surface.childNodes, function (node) {
            if (node.nodeType === 1 && /^(script|style|iframe|object|embed|form)$/i.test(node.nodeName)) {
              return;
            }
            bodyNode.appendChild(cleanCopy(node));
          });
        })
        .finally(function () {
          preview.removeAttribute('aria-busy');
        });
    }
    function setMode(mode) {
      var previewing = mode === 'preview';
      wrap.classList.toggle('is-previewing', previewing);
      preview.hidden = !previewing;
      writeButton.setAttribute('aria-pressed', previewing ? 'false' : 'true');
      previewButton.setAttribute('aria-pressed', previewing ? 'true' : 'false');
      if (previewing) {
        showPreview();
      }
    }
    writeButton.addEventListener('click', function () {
      setMode('write');
    });
    previewButton.addEventListener('click', function () {
      setMode('preview');
    });
    if (imageInput) {
      imageInput.addEventListener('change', function () {
        revokeBlob();
        if (wrap.classList.contains('is-previewing')) {
          showPreview();
        }
      });
    }
    window.addEventListener('pagehide', revokeBlob);
    var dirty = false;
    function markDirty() {
      dirty = true;
    }
    form.addEventListener('input', markDirty);
    surface.addEventListener('input', markDirty);
    window.addEventListener('beforeunload', function (event) {
      if (!dirty) {
        return;
      }
      event.preventDefault();
      event.returnValue = '';
    });
    form.addEventListener('submit', function () {
      dirty = false;
    });
  });
}());
