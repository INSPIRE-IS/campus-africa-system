@include('application.document.partials.modal_theme')

<div class="modal-content document-modal-theme" style="width:99%; max-height:95vh; overflow:auto;">
    <span class="close" aria-label="Close">&times;</span>

    <div class="panes">
        <div class="left_pane">
            <h2 id="modalHeader">ADDITIONAL | {{ strtoupper($document->title) }}</h2>
            <small>
                Uploaded on {{ $document->created_at->format('d|m|Y') }} at {{ $document->created_at->format('H:i') }}
            </small>

            <p class="document-review-note">
                This supporting document does not block or change application approval.
            </p>

            @if ($document->reviewed_at)
                <p>
                    <strong>Reviewed:</strong> {{ $document->reviewed_at->format('d|m|Y H:i') }}
                    @if ($document->reviewer)
                        by {{ trim($document->reviewer->userFirstName . ' ' . $document->reviewer->userLastname) }}
                    @endif
                </p>
            @endif

            @if ($document->status === 'Rejected' && $document->rejection_reason)
                <p class="document-status-rejected">
                    <strong>Rejection reason:</strong> {{ $document->rejection_reason }}
                </p>
            @endif

            @if ($canReview)
                <input type="text" value="{{ Auth::user()->userFirstName }}" readonly aria-label="Reviewer name">
                <div style="margin-top:12px;">
                    <label for="supplementary_rejection_reason"><strong>Rejection reason</strong></label>
                    <textarea id="supplementary_rejection_reason" class="document-review-reason" maxlength="1000" rows="3"
                        placeholder="Type field for rejection reason"></textarea>
                </div>
                <div class="flex" style="margin:16px 0;" id="buttonSection">
                    <button type="button" class="approve button"
                        onclick="reviewSupplementaryDocument('Approved')">Approve</button>
                    <button type="button" class="rejected button"
                        onclick="reviewSupplementaryDocument('Rejected')">Reject</button>
                </div>
            @endif

            <hr style="margin:35px 0;">
            <h3>Info</h3>
            <p><strong>Name:</strong> {{ $student->userFirstName }} {{ $student->userLastname }}</p>
            <p><strong>Application year:</strong> {{ $document->year }}</p>
            <p><strong>Status:</strong> {{ $document->status }}</p>
        </div>

        <div class="right_pane">
            <a class="document-download" href="{{ $documentUrl }}" download="{{ $document->original_file_name }}">Download</a>

            @if (strtolower(pathinfo($document->original_file_name, PATHINFO_EXTENSION)) === 'pdf')
                <embed class="document-preview" src="{{ $documentUrl }}" type="application/pdf">
            @elseif (in_array(strtolower(pathinfo($document->original_file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
                <img class="document-preview-image" src="{{ $documentUrl }}" alt="{{ $document->title }}">
            @else
                <a href="{{ $documentUrl }}" target="_blank" rel="noopener">Open document</a>
            @endif
        </div>
    </div>
</div>

<script>
    $('#myModal .close').on('click', function() {
        $('#myModal').css('display', 'none').empty();
    });

    function reviewSupplementaryDocument(status) {
        var reason = $('#supplementary_rejection_reason').val().trim();

        if (status === 'Rejected' && !reason) {
            Swal.fire('Rejection reason required', 'Enter a reason before rejecting this document.', 'warning');
            return;
        }

        $.ajax({
            type: 'POST',
            url: @json(route('supplementary-documents.review', $document->id)),
            data: {
                _token: @json(csrf_token()),
                status: status,
                rejection_reason: reason
            },
            success: function(response) {
                Swal.fire('Document reviewed', response.message, 'success').then(function() {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'The document could not be reviewed.';
                Swal.fire('Review failed', message, 'error');
            }
        });
    }
</script>
