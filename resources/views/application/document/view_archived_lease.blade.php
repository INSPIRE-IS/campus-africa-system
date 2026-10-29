<div class="modal-content" style="width:99%; max-height:95vh; overflow:auto;">
    <span class="close" aria-label="Close">&times;</span>

    <div style="padding:0 12px 16px;">
        <h2>Archived signed lease</h2>

        @if (!$file || !$documentUrl)
            <p>No archived lease file was found for this action.</p>
            <p><small>{{ $action->action }}</small></p>
        @else
            <p>
                <strong>{{ $file->fileName ?: 'Signed lease' }}</strong><br>
                <small>
                    Application year: {{ $file->year }} |
                    Status: {{ $file->status ?: 'Unknown' }} |
                    Archived: {{ $file->deleted_at->format('d|m|Y H:i') }}
                </small>
            </p>

            <p>
                <a href="{{ $documentUrl }}" download="{{ $file->fileName ?: 'signed-lease' }}">
                    <button type="button">Download</button>
                </a>
            </p>

            @if (strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'pdf')
                <embed src="{{ $documentUrl }}" type="application/pdf" width="100%" height="800px">
            @elseif (in_array(strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
                <img src="{{ $documentUrl }}" alt="Archived signed lease" style="display:block; max-width:100%; height:auto;">
            @else
                <a href="{{ $documentUrl }}" target="_blank" rel="noopener">Open document</a>
            @endif
        @endif
    </div>
</div>

<script>
    $('#myModal .close').on('click', function() {
        $('#myModal').css('display', 'none').empty();
    });
</script>
