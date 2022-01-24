@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>


<div class="container">
    <br/>
    <h1>Import</h1>

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
        <h1>Import Lists</h1>

        <br/><br/>

        <table id="upload_table" class="display">
        <thead>
            <tr>
                <th>Import ID</th>
                <th>Total Approved</th>
                <th>Total Skipped</th>
                <th>Total Not Found</th>
                <th>File Name</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        @foreach($uploads as $upload)
            <tr>
                <td>
                    {{$upload->id}}
                </td>
                <td>
                    {{$upload->approved}}
                </td>
                <td>
                    {{$upload->skipped}}
                </td>
                <td>
                    {{$upload->not_found}}
                </td>
                <td>
                    {{$upload->file_name}}
                </td>
                <td>
                    {{$upload->users->userFirstName}} {{$upload->users->userLastname}}
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

        <br/>
        <h1>Students not found in Import</h1>

        <br/><br/>

        <table id="student_upload_table" class="display">
        <thead>
            <tr>
                <th>Import ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Student Number</th>
                <th>ID Number</th>
            </tr>
        </thead>
        @foreach($students as $student)
            <tr>
                <td>
                    {{$student->importId}}
                </td>
                <td>
                    {{$student->student_first_name}}
                </td>
                <td>
                    {{$student->student_last_name}}
                </td>
                <td>
                    {{$student->student_email}}
                </td>
                <td>
                    {{$student->student_number}}
                </td>
                <td>
                    {{$student->student_id_number}}
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

                                    var trDOM = datatable1.row.add( [
                                        arrayItem['id'],
                                        arrayItem['approved'],
                                        arrayItem['skipped'],
                                        arrayItem['not_found'],
                                        arrayItem['file_name'],
                                        arrayItem['users']['userFirstName']+" "+arrayItem['users']['userLastname'],
                                        dateString,
                                    ] ).draw().node();
                                })

                                results2.forEach(function (arrayItem) {
                                    var trDOM = datatable2.row.add( [
                                        arrayItem['importId'],
                                        arrayItem['student_first_name'],
                                        arrayItem['student_last_name'],
                                        arrayItem['student_email'],
                                        arrayItem['student_number'],
                                        arrayItem['student_id_number'],
                                    ] ).draw().node();
                                })
                            },
                        });
                        };
                    });
                }
            });
    });

    $(document).ready( function () {
        $('#upload_table').DataTable({
            "columnDefs": [
                {"className": "dt-center", "targets": "_all"}
            ],
        });
    } );

    $(document).ready( function () {
        $('#student_upload_table').DataTable();
    } );
</script>


@endsection