(function () {
  if (window.GoSoloEditor) {
    return;
  }
  window.GoSoloEditor = true;

  function looksWritten(value) {
    return /<(p|br|strong|em|ul|ol|li|blockquote|a|h2|h3)\b/i.test(value);
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
    markEmpty();
    fitWorkspace();
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
        surface.focus();
        var command = button.getAttribute('data-cmd');
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
        source.value = surface.innerHTML;
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
      data.append('body', surface.innerHTML);
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
