@include('application.document.partials.modal_theme')

<div class="modal-content document-modal-theme" style="width:99%; max-height:95vh; overflow:auto;">
    <span class="close" aria-label="Close">&times;</span>

    <div class="panes">
        <div class="left_pane">
            <h2 id="modalHeader">Monthly Proof of Payment</h2>
            <p class="document-review-note">This is a view-only operational document.</p>
            <p>
                <strong>Period:</strong> {{ $meta['period'] ?? 'Unknown' }}<br>
                <strong>Due:</strong> {{ $meta['due_date'] ?? 'Unknown' }}
                @if (array_key_exists('amount', $meta) && $meta['amount'] !== null)
                    <br><strong>Amount:</strong> R {{ number_format((float) $meta['amount'], 2) }}
                @endif
            </p>

            @if (!$file || !$documentUrl)
                <p>No Monthly POP has been uploaded for this period.</p>
                <p><small>{{ $action->action }}</small></p>
            @else
                <p>
                    <strong>Status:</strong> {{ $file->status ?: 'Requested' }}<br>
                    <strong>Application year:</strong> {{ $file->year }}<br>
                    <strong>Uploaded:</strong> {{ $file->updated_at->format('d|m|Y H:i') }}
                </p>
            @endif
        </div>

        <div class="right_pane">
            @if ($file && $documentUrl)
                <a class="document-download" href="{{ $documentUrl }}"
                    download="{{ $file->fileName ?: 'monthly-proof-of-payment' }}">Download</a>

                @if (strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'pdf')
                    <embed class="document-preview" src="{{ $documentUrl }}" type="application/pdf">
                @elseif (in_array(strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
                    <img class="document-preview-image" src="{{ $documentUrl }}" alt="Monthly Proof of Payment">
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
