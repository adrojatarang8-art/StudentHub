'use strict';
// Extra convenience only. PHP performs all security and validation checks.
document.querySelectorAll('.p12-delete-form').forEach(form => {
  form.addEventListener('submit', event => {
    const name = form.dataset.event || 'this event';
    if (!window.confirm(`Delete "${name}" permanently? This cannot be undone.`)) {
      event.preventDefault();
    }
  });
});
const posterInput = document.querySelector('#poster');
const preview = document.querySelector('#p12-preview');
const previewImage = document.querySelector('#p12-preview-img');
let previousObjectUrl = null;
if (posterInput && preview && previewImage) {
  posterInput.addEventListener('change', () => {
    if (previousObjectUrl) URL.revokeObjectURL(previousObjectUrl);
    previousObjectUrl = null;
    const file = posterInput.files && posterInput.files[0];
    if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      preview.hidden = true;
      return;
    }
    previousObjectUrl = URL.createObjectURL(file);
    previewImage.src = previousObjectUrl;
    preview.hidden = false;
  });
}
