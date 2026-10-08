(function () {
  if (window.GoSoloEditor) {
    return;
  }
  window.GoSoloEditor = true;

  function looksWritten(value) {
    return /<(p|br|strong|em|ul|ol|li|blockquote|a)\b/i.test(value);
  }

  document.querySelectorAll('[data-editor]').forEach(function (root) {
    var source = root.querySelector('.editor-source');
    var surface = root.querySelector('.editor-surface');
    if (!source || !surface) {
      return;
    }
    if (looksWritten(source.value)) {
      surface.innerHTML = source.value;
    } else {
      surface.textContent = source.value;
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
