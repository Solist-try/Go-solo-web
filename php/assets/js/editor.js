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
    if (looksWritten(source.value)) {
      surface.innerHTML = source.value;
    } else if (source.value.trim() !== '') {
      surface.innerHTML = plainToHtml(source.value);
    }
    root.classList.add('is-ready');
    root.querySelectorAll('[data-cmd]').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        surface.focus();
        var command = button.getAttribute('data-cmd');
        if (command === 'link') {
          var address = window.prompt('Where should this link go?');
          if (!address) {
            return;
          }
          document.execCommand('createLink', false, address);
          return;
        }
        if (command === 'quote') {
          document.execCommand('formatBlock', false, 'blockquote');
          return;
        }
        if (command === 'heading') {
          document.execCommand('formatBlock', false, 'h2');
          return;
        }
        if (command === 'subheading') {
          document.execCommand('formatBlock', false, 'h3');
          return;
        }
        document.execCommand(command, false, null);
      });
    });
    var form = root.closest('form');
    if (!form) {
      return;
    }
    form.addEventListener('submit', function () {
      source.value = surface.innerHTML;
    });
  });
}());
