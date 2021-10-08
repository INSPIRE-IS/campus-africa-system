@extends('layouts.dash')

@section('content')


<div class="container LeasePage">
    
    
    <div class="singleUploadSection" id="moveInCard">
        <h3 class="uploadName">Move In Form</h3>
        <div class="uploadFileSection">
            <label for="moveIn" id="moveInUpload">
                <i class="fas fa-file-upload" ></i>
            </label>
            <input type="file" id="moveIn" hidden/>
            <label id="moveInSucess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
            
        </div>
    </div>

    <div class="buttonSection">
        <a href="/sign-lease"><button type="button" class="download">GO BACK</button></a>
        <a href="/complete-reservation"><button type="button" class="sign" disabled>COMPLETE RESERVATION</button></a>
    </div>

    <br/><br/>
</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $("#moveIn").on('change', function(){
        $("#moveInUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('moveIn').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "moveIn");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $("#moveInUpload").empty().append('<i class="fas fa-file successful"></i>')
                    $("#moveInSucess").removeClass('hidden')
                    $("#moveInCard").addClass('greenBorder')
                    $(".sign").removeAttr('disabled')
                }
            },
        });
    });
</script>

    

@endsection