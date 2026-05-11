document.addEventListener('DOMContentLoaded', function() {
    var page = document.querySelector('.fi-page');
    var dropzone = document.getElementById('fe-dropzone');
    var dragCounter = 0;

    // === DRAG & DROP UPLOAD ===
    if (page && dropzone) {
        page.addEventListener('dragenter', function(e) {
            e.preventDefault();
            dragCounter++;
            if (e.dataTransfer.types.includes('Files')) {
                dropzone.style.display = 'block';
            }
        });

        page.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dragCounter--;
            if (dragCounter <= 0) {
                dropzone.style.display = 'none';
                dragCounter = 0;
            }
        });

        page.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        page.addEventListener('drop', function(e) {
            e.preventDefault();
            dragCounter = 0;
            dropzone.style.display = 'none';

            if (e.dataTransfer.files.length > 0) {
                var fileInput = page.querySelector('input[type="file"]');
                if (fileInput) {
                    var dt = new DataTransfer();
                    for (var i = 0; i < e.dataTransfer.files.length; i++) {
                        dt.items.add(e.dataTransfer.files[i]);
                    }
                    fileInput.files = dt.files;
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    }

    // === UPLOAD PROGRESS BAR ===
    // Livewire 3 dispatches these events on the file input element
    // as it streams each file to the temporary-upload endpoint.
    var progressBox   = document.getElementById('fe-upload-progress');
    var progressBar   = document.getElementById('fe-upload-progress-bar');
    var progressPct   = document.getElementById('fe-upload-progress-pct');
    var progressLabel = document.getElementById('fe-upload-progress-label');

    function attachUploadListeners(input) {
        if (!input || input.__feUploadWired) return;
        input.__feUploadWired = true;

        input.addEventListener('livewire-upload-start', function(e) {
            if (!progressBox) return;
            progressBox.style.display = 'block';
            progressBar.style.width = '0%';
            progressPct.textContent = '0%';
            // input.files is set by the change event before upload-start fires
            var n = input.files ? input.files.length : 1;
            progressLabel.textContent = n > 1
                ? 'Uploading ' + n + ' files…'
                : 'Uploading ' + (input.files && input.files[0] ? input.files[0].name : 'file') + '…';
        });

        input.addEventListener('livewire-upload-progress', function(e) {
            if (!progressBox) return;
            var p = (e.detail && typeof e.detail.progress === 'number') ? e.detail.progress : 0;
            progressBar.style.width = p + '%';
            progressPct.textContent = Math.round(p) + '%';
        });

        function finish(success, message) {
            if (!progressBox) return;
            progressBar.style.width = '100%';
            progressPct.textContent = success ? 'Done' : 'Error';
            if (message) progressLabel.textContent = message;
            // Fade out shortly after completion
            setTimeout(function() {
                progressBox.style.display = 'none';
                progressBar.style.width = '0%';
                progressPct.textContent = '0%';
                progressLabel.textContent = 'Uploading…';
            }, success ? 1200 : 4000);
        }

        input.addEventListener('livewire-upload-finish', function() { finish(true); });
        input.addEventListener('livewire-upload-cancel', function() { finish(false, 'Upload canceled'); });
        input.addEventListener('livewire-upload-error',  function() { finish(false, 'Upload failed — check Laravel log'); });
    }

    // Initial wire-up
    var initial = page ? page.querySelector('input[type="file"]') : null;
    attachUploadListeners(initial);

    // Livewire may re-render the input; observe for replacement
    if (page) {
        new MutationObserver(function() {
            attachUploadListeners(page.querySelector('input[type="file"]'));
        }).observe(page, { childList: true, subtree: true });
    }
});
