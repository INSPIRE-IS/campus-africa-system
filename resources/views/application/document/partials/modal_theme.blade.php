<style>
    .document-modal-theme .panes {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }

    .document-modal-theme .left_pane {
        width: 30%;
        padding-right: 20px;
    }

    .document-modal-theme .right_pane {
        flex: 1 1 70%;
        min-width: 0;
    }

    .document-modal-theme .document-review-note {
        margin: 18px 0;
        padding: 10px 12px;
        border-left: 4px solid #ffbb3c;
        background: #fff8e8;
        color: #464749;
    }

    .document-modal-theme .document-review-reason {
        width: 100%;
        min-height: 72px;
        padding: 10px;
        border: 0;
        border-radius: 7px;
        background: #f0f0f0;
        resize: vertical;
    }

    .document-modal-theme .document-status-rejected {
        color: #b00020;
    }

    .document-modal-theme .document-download {
        display: inline-block;
        margin-bottom: 10px;
        padding: 8px 14px;
        border-radius: 7px;
        background: #464749;
        color: #fff;
        text-decoration: none;
    }

    .document-modal-theme .document-preview {
        display: block;
        width: 100%;
        height: 800px;
        border: 0;
    }

    .document-modal-theme .document-preview-image {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 0 auto;
    }

    @media (max-width: 767px) {
        .document-modal-theme .panes {
            display: block;
        }

        .document-modal-theme .left_pane {
            width: 100%;
            padding-right: 0;
        }

        .document-modal-theme .right_pane {
            width: 100%;
        }

        .document-modal-theme .document-preview {
            height: 70vh;
        }
    }
</style>
