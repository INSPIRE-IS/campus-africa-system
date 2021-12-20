@extends('layouts.admin')

@section('content')

<div class="container">
    <br/>
    <h1>Imports</h1>

    <br/><br/>

    <div class="row">
            <div class="column" style="width:100%">
                <div class="field">      
                    <div class="file-upload-wrapper">
                        <div id='mydropzone' name="mydropzone" class="dropzone needsclick dz-clickable"></div>
                    </div>
                </div>
            </div>
        </div>

        <br/><br/>
</div>


<script type="text/javascript">
    Dropzone.autoDiscover = false;
    $(document).ready(function(){
        var myDropzone = new Dropzone("div#mydropzone", 
            { 
                url: "/import/data", 
                timeout: 1200000,
                init: function() {
                    this.on("sending", function(file, xhr, formData) {
                        formData.append("_token", "{{ csrf_token() }}")
                    });
                    this.on("complete", function (file) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                            // location.reload();
                        };
                    });
                }
            });
    });
</script>

@endsection