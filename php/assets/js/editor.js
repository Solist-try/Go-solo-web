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
    function discardFigure(figure) {
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
      }
      markEmpty();
      fitWorkspace();
      surface.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function buildFigure(media) {
      var figure = document.createElement('figure');
      figure.className = 'writing-figure';
      figure.setAttribute('data-media', String(media.id));
      figure.setAttribute('contenteditable', 'false');
      if (media.url) {
        var image = document.createElement('img');
        image.src = media.url;
        image.alt = '';
        image.setAttribute('aria-hidden', 'true');
        figure.appendChild(image);
      } else {
        var missing = document.createElement('p');
        missing.className = 'writing-figure-missing';
        missing.textContent = labelText('data-image-missing', 'This image is no longer on the shelf.');
        figure.appendChild(missing);
      }
      var altLabel = document.createElement('label');
      var altName = document.createElement('span');
      altName.textContent = labelText('data-image-alt-label', 'Description');
      var altInput = document.createElement('input');
      altInput.type = 'text';
      altInput.maxLength = 255;
      altInput.required = true;
      altInput.setAttribute('data-image-alt', '');
      altInput.value = media.alt || '';
      altInput.setAttribute('aria-describedby', 'reading-image-alt-help-' + media.id);
      var altHelp = document.createElement('span');
      altHelp.id = 'reading-image-alt-help-' + media.id;
      altHelp.className = 'soft';
      altHelp.textContent = labelText('data-image-alt-help', 'A short description for someone who cannot see the image.');
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
      var replace = document.createElement('button');
      replace.type = 'button';
      replace.setAttribute('data-image-replace', '');
      replace.textContent = labelText('data-image-replace-label', 'Replace image');
      var remove = document.createElement('button');
      remove.type = 'button';
      remove.setAttribute('data-image-remove', '');
      remove.textContent = labelText('data-image-remove-label', 'Remove image');
      actions.appendChild(replace);
      actions.appendChild(remove);
      figure.appendChild(altLabel);
      figure.appendChild(captionLabel);
      figure.appendChild(actions);
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
    function placeFigure(figure) {
      var selection = window.getSelection();
      var range = savedRange;
      if (!range || !range.startContainer || !surface.contains(range.startContainer)) {
        range = null;
      }
      if (!range && selection && selection.rangeCount && selection.anchorNode && surface.contains(selection.anchorNode)) {
        range = selection.getRangeAt(0);
      }
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
      if (block && block.parentNode === surface) {
        var atStart = range && range.collapsed && range.startOffset === 0 && (range.startContainer === block || range.startContainer.parentNode === block);
        if (atStart) {
          block.before(figure);
        } else {
          block.after(figure);
        }
      } else {
        surface.appendChild(figure);
      }
      var after = document.createElement('p');
      after.appendChild(document.createElement('br'));
      figure.after(after);
      var caret = document.createRange();
      caret.setStart(after, 0);
      caret.collapse(true);
      if (selection) {
        selection.removeAllRanges();
        selection.addRange(caret);
      }
      surface.focus();
      var altInput = figure.querySelector('[data-image-alt]');
      if (altInput) {
        altInput.focus();
      }
      markEmpty();
      fitWorkspace();
      surface.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function serializeArticle() {
      if (!imageUrl) {
        return surface.innerHTML;
      }
      var copy = surface.cloneNode(true);
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
    function uploadImage(file) {
      showImageError('');
      if (!file) {
        return;
      }
      if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
        showImageError(labelText('data-image-failed', 'The image could not be added.'));
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
      replaceId = '';
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
              markEmpty();
              fitWorkspace();
              surface.dispatchEvent(new Event('input', { bubbles: true }));
              return;
            }
          }
          placeFigure(buildFigure({ id: payload.id, url: payload.url, alt: payload.alt || '', caption: '' }));
        })
        .catch(function (error) {
          var message = error && error.message ? error.message : '';
          if (!message || message.indexOf('Unexpected') === 0 || message.indexOf('JSON') !== -1) {
            message = labelText('data-image-failed', 'The image could not be added.');
          }
          showImageError(message);
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
    markEmpty();
    fitWorkspace();
    upgradeFigures();
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
          if (!imageFile) {
            return;
          }
          replaceId = '';
          rememberRange();
          imageFile.click();
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
          var blank = null;
          surface.querySelectorAll('[data-image-alt]').forEach(function (input) {
            if (!blank && input.value.trim() === '') {
              blank = input;
            }
          });
          if (blank) {
            event.preventDefault();
            showImageError(labelText('data-image-needs', 'Each image needs a short description of what it shows.'));
            blank.focus();
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
