@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>


<div class="container">
    <br/>
    <h1>Upload File</h1>

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

        <br/>
        <h1>Upload History</h1>

        <br/><br/>
        <i class="fas fa-circle-notch fa-spin hidden" id="importsLoading" style="font-size:35px; margin-left:45%"></i>

        <table id="upload_table" class="display">
        <thead>
            <tr>
                <th>Status</th>
                <th>File Name</th>
                <th>Total Records</th>
                <th>Matched</th>
                <th>Unmatched</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        @foreach($uploads as $upload)
            <tr>
                <td>
                    @if($upload->status == "1") Current @else Replaced @endif
                </td>
                <td>
                    @if($upload->file_name) {{$upload->file_name}} @endif
                </td>
                <td>
                    {{$upload->total_records}}
                </td>
                <td>
                    {{$upload->matched}}
                </td>
                <td>
                    {{$upload->unmatched}}
                </td>
                <td>
                    @if($upload->users && $upload->users->userFirstName) {{$upload->users->userFirstName}} {{$upload->users->userLastname}} @endif
                </td>
                <td>
                    {{$upload->created_at}}
                </td>
            </tr>
        @endforeach
        <tbody>
            
        </tbody>
    </table>

    <br/><br/>
    <input type="hidden" id="id_to_download" value="<?php if($active && $active->id){echo $active->id;} ?>"/>
    <button type="button" class="detailsChange" id="downloadStudents">Download Matched / Unmatched</button>
    <div class="hidden"><a href="/NSFAS Approval Export.xlsx" download><button type="button" id="downloadBtn">Download</button></a></div>

        <br/>
        <h1>Unmatched Students in Current Upload</h1>

        <br/><br/>

        <i class="fas fa-circle-notch fa-spin hidden" id="studentLoading" style="font-size:35px; margin-left:45%"></i>
        <table id="student_upload_table" class="display">
        <thead>
            <tr>
                <th>Student Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>ID Number</th>
                <th>Institution</th>
            </tr>
        </thead>
        @foreach($students as $student)
            <tr>
                <td>
                    {{$student->student_number}}
                </td>
                <td>
                    {{$student->student_first_name}}
                </td>
                <td>
                    {{$student->student_last_name}}
                </td>
                <td>
                    {{$student->student_id_number}}
                </td>
                <td>
                    {{$student->institution}}
                </td>
            </tr>
        @endforeach
        <tbody>
            
        </tbody>
    </table>
</div>


<script type="text/javascript">
    Dropzone.autoDiscover = false;
    $(document).ready(function(){
        var myDropzone = new Dropzone("div#mydropzone", 
            { 
                url: "/import/nsfas-students", 
                timeout: 1200000,
                init: function() {
                    this.on("sending", function(file, xhr, formData) {
                        formData.append("_token", "{{ csrf_token() }}")
                    });
                    this.on("complete", function (file) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {

                            $("#student_upload_table").addClass('hidden')
                            $("#studentLoading").removeClass('hidden')
                            $("#upload_table").addClass('hidden')
                            $("#importsLoading").removeClass('hidden')

                            $.ajax({
                            type: "get",
                            url: "/import/newData",

                            success: function (data) {

                                var datatable1 = $( "#upload_table" ).DataTable();
                                datatable1.clear();

                                var datatable2 = $( "#student_upload_table" ).DataTable();
                                datatable2.clear();

                                results1 = data[0]
                                results2 = data[1]

                                results1.forEach(function (arrayItem) {
                                    var m = new Date(arrayItem["created_at"]);

                                var dateString =
                                    m.getUTCFullYear() + "-" +
                                    ("0" + (m.getMonth()+1)).slice(-2) + "-" +
                                    ("0" + m.getDate()).slice(-2) + " " +
                                    ("0" + m.getHours()).slice(-2) + ":" +
                                    ("0" + m.getMinutes()).slice(-2) + ":" +
                                    ("0" + m.getSeconds()).slice(-2);

                                    if(arrayItem['status'] == "1"){
                                        var status = "Current"
                                    }else{
                                        var status = "Replaced"
                                    }

                                    if(arrayItem['users'] && arrayItem['users']['userFirstName'] && arrayItem['users']['userLastname']){
                                        var name = arrayItem['users']['userFirstName']+" "+arrayItem['users']['userLastname'];
                                    }else{
                                        var name = "";
                                    }

                                    var trDOM = datatable1.row.add( [
                                        status,
                                        arrayItem['file_name'],
                                        arrayItem['total_records'],
                                        arrayItem['matched'],
                                        arrayItem['unmatched'],
                                        name,
                                        dateString,
                                    ] ).draw().node();
                                })

                                results2.forEach(function (arrayItem) {
                                    var trDOM = datatable2.row.add( [
                                        arrayItem['student_number'],
                                        arrayItem['student_first_name'],
                                        arrayItem['student_last_name'],
                                        arrayItem['student_id_number'],
                                        arrayItem['institution'],
                                    ] ).draw().node();
                                })

                                $("#student_upload_table").removeClass('hidden')
                                $("#studentLoading").addClass('hidden')
                                $("#upload_table").removeClass('hidden')
                                $("#importsLoading").addClass('hidden')
                            },
                        });
                        };
                    });
                }
            });
    });

    $(document).ready( function () {
        var table = $('#upload_table').DataTable({
            "columnDefs": [
                {"className": "dt-center", "targets": "_all"}
            ],
            "order": [[ 6, "desc" ]],
        });

        $('#upload_table tbody').on('click', 'tr', function () {
            $("#student_upload_table").addClass('hidden')
            $("#studentLoading").removeClass('hidden')
            var data = table.row( this ).data();
            console.log(data[6])
            $.ajax({
                type: "post",
                url: "/get-upload-info",
                data: {
                    timestamp: data[6], 
                },

                success: function (data) {

                    if(data[0] == undefined){
                        
                        var datatable2 = $( "#student_upload_table" ).DataTable();
                        datatable2.clear().draw(); 

                        $("#student_upload_table").removeClass('hidden')
                        $("#studentLoading").addClass('hidden')

                    }else{
                        $("#id_to_download").val('').val(data[0].importId);
                    

                        var datatable2 = $( "#student_upload_table" ).DataTable();
                        datatable2.clear(); 
                        
                        data.forEach(function (arrayItem) {
                            var trDOM = datatable2.row.add( [
                                arrayItem['student_number'],
                                arrayItem['student_first_name'],
                                arrayItem['student_last_name'],
                                arrayItem['student_id_number'],
                                arrayItem['institution'],
                            ] ).draw().node();
                        })

                        $("#student_upload_table").removeClass('hidden')
                        $("#studentLoading").addClass('hidden')
                    }

                    
                },
            });
        } );
    } );

    $(document).ready( function () {
        $('#student_upload_table').DataTable();
    } );

    $("#downloadStudents").on('click', function(){
        
        $.ajax({
            type: "get",
            url: "/download/matched-unmatched/"+$("#id_to_download").val(),

            success: function (data) {
                $("#downloadBtn").click();
            },
        });
    })
</script>


@endsection