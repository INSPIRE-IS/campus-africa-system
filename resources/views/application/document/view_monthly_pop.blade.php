<div class="modal-content" style="width:99%; max-height:95vh; overflow:auto;">
    <span class="close" aria-label="Close">&times;</span>

    <div style="padding:0 12px 16px;">
        <h2>Monthly Proof of Payment</h2>

        <p>
            <strong>Period:</strong> {{ $meta['period'] ?? 'Unknown' }} |
            <strong>Due:</strong> {{ $meta['due_date'] ?? 'Unknown' }}
            @if (array_key_exists('amount', $meta) && $meta['amount'] !== null)
                | <strong>Amount:</strong> R {{ number_format((float) $meta['amount'], 2) }}
            @endif
        </p>

        @if (!$file || !$documentUrl)
            <p>No Monthly POP has been uploaded for this period.</p>
            <p><small>{{ $action->action }}</small></p>
        @else
            <p>
                <strong>{{ $file->fileName ?: 'Monthly Proof of Payment' }}</strong><br>
                <small>
                    Application year: {{ $file->year }} |
                    Status: {{ $file->status ?: 'Requested' }} |
                    Uploaded: {{ $file->updated_at->format('d|m|Y H:i') }}
                </small>
            </p>

            <p>
                <a href="{{ $documentUrl }}" download="{{ $file->fileName ?: 'monthly-proof-of-payment' }}">
                    <button type="button">Download</button>
                </a>
            </p>

            @if (strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'pdf')
                <embed src="{{ $documentUrl }}" type="application/pdf" width="100%" height="800px">
            @elseif (in_array(strtolower(pathinfo(parse_url($documentUrl, PHP_URL_PATH), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
                <img src="{{ $documentUrl }}" alt="Monthly Proof of Payment" style="display:block; max-width:100%; height:auto;">
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
