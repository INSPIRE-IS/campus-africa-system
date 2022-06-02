@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="https://cdn.tiny.cloud/1/u3wbb2rax98pnwm5w8cxrjja5oxoqztklpt49ug97d5n009f/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<div class="container">
    <br/>
    <table id="mailer_table" class="display">
        <thead>
            <tr>
                <th>Mailer</th>
                <th>Status</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>



        @foreach($MailerList as $mailer)

            <!-- onClick="activeMailer('{{$mailer->id}}')" -->
            <!-- onClick="inactiveMailer('{{$mailer->id}}')" -->

            <tr>
                <td>{{$mailer->mailer_name}}</td>
                <td>@if($mailer->status == "0") <button type="button" class="inactiveBtn"  id="mailer_{{$mailer->id}}"> Inactive </button>@else <button type="button" class="activeBtn"  id="mailer_{{$mailer->id}}">Active</button> @endif</td>
                <td><button type="button" id="tableBtn" onClick="openModal('{{$mailer->mailer_name}}', '{{$mailer->id}}')" >View</button></td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <br/>
</div>

<div id="MailModalView" class="modal">
    <div class="modal-content">
        <span class="close closeModalView">&times;</span>
        <h2 id="modalHeader"></h2>
        <input type="hidden" id="editMailerId" value="" />
        <br/>
        <button type="button" class="saveTxtMail" onClick="saveMail(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
        <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSectionModal" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaMail" name="mailerText" style="background-color:transparent">
                        </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $(document).ready( function () {
        $('#mailer_table').DataTable();
    } );

    // function activeMailer(mailerId){
    //     $("#mailer_"+mailerId).empty().append("...");
    //     $.ajax({
    //         type: "post",
    //         url: "/activate_mailer",
    //         data: {
    //             mailerId: mailerId, 
    //         },

    //         success: function (data) {
    //             console.log(data)
    //             $("#mailer_"+data).empty().append("Active");
    //             $("#mailer_"+data).removeClass("inactiveBtn")
    //             $("#mailer_"+data).addClass("activeBtn");
    //         },
    //     });
    // }

    // function inactiveMailer(mailerId){
    //     $("#mailer_"+mailerId).empty().append("...");
    //     $.ajax({
    //         type: "post",
    //         url: "/inactivate_mailer",
    //         data: {
    //             mailerId: mailerId, 
    //         },

    //         success: function (data) {
    //             console.log(data)
    //             $("#mailer_"+data).empty().append("Inactive");
    //             $("#mailer_"+data).removeClass("activeBtn")
    //             $("#mailer_"+data).addClass("inactiveBtn");
    //         },
    //     });
    // }

    function openModal(title, mailerID){

        $("#modalHeader").empty().append(title);
        $("#editMailerId").val('').val(mailerID);

        let Mailers = <?php echo json_encode($MailerList); ?>;

        $.ajax({
            type: "get",
            url: "/get-mail-list",
            success: function (data) {
                let Mailers = JSON.parse(data);

                Mailers.forEach(function(item){
                    if(item.id == mailerID){
                        if(item.color == '1'){
                            $("#mailTextSectionModal").addClass('greenBack')
                            $("#mailTextSectionModal").removeClass('yellowBack')
                            $("#mailTextSectionModal").removeClass('redBack')
                        }else if(item.color == '2'){
                            $("#mailTextSectionModal").addClass('yellowBack')
                            $("#mailTextSectionModal").removeClass('greenBack')
                            $("#mailTextSectionModal").removeClass('redBack')
                        }else if(item.color == '3'){
                            $("#mailTextSectionModal").addClass('redBack')
                            $("#mailTextSectionModal").removeClass('yellowBack')
                            $("#mailTextSectionModal").removeClass('greenBack')
                        }
                        tinymce.get("mytextareaMail").setContent(item.message);
                    }
                })

                var modal = document.getElementById("MailModalView");
                modal.style.display = "block";
            },
        });

        

    }


    var close = document.getElementsByClassName("closeModalView")[0];

    close.onclick = function() {
        var modal = document.getElementById("MailModalView");
        modal.style.display = "none";
    }

    tinymce.init({
        selector: '#mytextareaMail',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    });

    function saveMail(event){
        $(".saveTxtMail").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMail",
            data: {
                text: tinymce.get("mytextareaMail").getContent(), 
                mailerID: $("#editMailerId").val(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMail").empty().append("Save Text")
            },
        });
    } 

</script>


@endsection