@include('application.document.partials.modal_theme')

<div class="modal-content document-modal-theme" style="width:99%; max-height:95vh; overflow:auto;">
    <span class="close" aria-label="Close">&times;</span>

    <div class="panes">
        <div class="left_pane">
            <h2 id="modalHeader">Archived Signed Lease</h2>
            <p class="document-review-note">This is a read-only historical document.</p>

            @if (!$file || !$documentUrl)
                <p>No archived lease file was found for this action.</p>
                <p><small>{{ $action->action }}</small></p>
            @else
                <small>
                    Application year: {{ $file->year }} |
                    Status: {{ $file->status ?: 'Unknown' }} |
                    Archived: {{ $file->deleted_at->format('d|m|Y H:i') }}
                </small>
            @endif
        </div>

        <div class="right_pane">
            @if ($file && $documentUrl)
                <a class="document-download" href="{{ $documentUrl }}"
                    download="{{ $file->fileName ?: 'signed-lease' }}">Download</a>

                @if (strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'pdf')
                    <embed class="document-preview" src="{{ $documentUrl }}" type="application/pdf">
                @elseif (in_array(strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
                    <img class="document-preview-image" src="{{ $documentUrl }}" alt="Archived signed lease">
                @else
                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener">Open document</a>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    $('#myModal .close').on('click', function() {
        $('#myModal').css('display', 'none').empty();
    });
</script>
