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
      if (surface.scrollHeight > surface.clientHeight + 2) {
        surface.style.height = (surface.offsetHeight + surface.scrollHeight - surface.clientHeight) + 'px';
      }
    }
    function revealCaret() {
      if (!articleWorkspace || !window.visualViewport) {
        return;
      }
      var selection = window.getSelection();
      if (!selection || selection.rangeCount === 0 || !surface.contains(selection.anchorNode)) {
        return;
      }
      var rect = selection.getRangeAt(0).getBoundingClientRect();
      if (!rect) {
        return;
      }
      var viewport = window.visualViewport;
      var topLimit = viewport.offsetTop + 68;
      var bottomLimit = viewport.offsetTop + viewport.height - 24;
      if (rect.bottom > bottomLimit) {
        window.scrollBy(0, rect.bottom - bottomLimit);
      } else if (rect.height > 0 && rect.top < topLimit) {
        window.scrollBy(0, rect.top - topLimit);
      }
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
      window.addEventListener('resize', function () {
        surface.style.height = '';
        fitWorkspace();
      });
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
      }
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
          return;
        }
        if (command === 'quote') {
          document.execCommand('formatBlock', false, 'blockquote');
          markEmpty();
          fitWorkspace();
          return;
        }
        if (command === 'heading') {
          document.execCommand('formatBlock', false, 'h2');
          markEmpty();
          fitWorkspace();
          return;
        }
        if (command === 'subheading') {
          document.execCommand('formatBlock', false, 'h3');
          markEmpty();
          fitWorkspace();
          return;
        }
        document.execCommand(command, false, null);
        markEmpty();
        fitWorkspace();
      });
    });
    var form = root.closest('form');
    if (form) {
      form.addEventListener('submit', function () {
        source.value = surface.innerHTML;
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
      Array.prototype.forEach.call(surface.childNodes, function (node) {
        if (node.nodeType === 1 && /^(script|style|iframe|object|embed|form)$/i.test(node.nodeName)) {
          return;
        }
        bodyNode.appendChild(cleanCopy(node));
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
  });
}());
