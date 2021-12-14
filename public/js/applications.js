
jQuery(document).ready(function ($) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready( function () {
            $('#application_table').DataTable({searching: false, "aaSorting": [], "stripeClasses": [ 'odd-row', 'even-row' ]});
        } );

        $("#searchButton").on('click', function(){
                $("#searchButton").empty().append("Searching...")

                $.ajax({
                    type: "post",
                    url: "/applications/filter",
                    data: {
                        property: $("#property").val(), 
                        room_type: $("#room_type").val(), 
                        applicant: $("#applicant").val(), 
                        student_type: $("#student_type").val(),
                        bookingStatus: $("#bookingStatus").val(),
                        applicationStatus: $("#applicationStatus").val(), 
                        date: $("#date").val(), 
                    },

                    success: function (data) {
                        results = JSON.parse(data)

                        var i = 0;
                        var datatable = $( "#application_table" ).DataTable();
                        datatable.clear();

                        if(results.length == "0"){
                            var datatable = $( "#application_table" ).DataTable();
                            datatable.clear().draw();
                        }

                        results.forEach(function (arrayItem) {
                            console.log(i)
                            let files = arrayItem['files'];

                            if( i % 2 == 0){
                                color = "dark";
                            }else{
                                color = "light";
                            }

                            if(arrayItem["bookingStatus"] == "Pending" || arrayItem["bookingStatus"] == "Action"){
                                var bookingStat = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                            }else if(arrayItem["bookingStatus"] == "Approved"){
                                var bookingStat = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                            }else if(arrayItem["bookingStatus"] == "Denied" || arrayItem["bookingStatus"] == "" || arrayItem["bookingStatus"] == "Empty" || arrayItem["bookingStatus"] == "Rejected"){
                                var bookingStat = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                            }else{
                                var bookingStat = '<span class="hidden">D</span><div class="button rejected bigButton"></div>';
                            }

                            if(arrayItem["applicationStatus"] === "Pending" || arrayItem["applicationStatus"] === "Action"){
                                applicationStatus = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                            }else if(arrayItem["applicationStatus"] === "Approved" || arrayItem["applicationStatus"] === "Approved Confirmed"){
                                applicationStatus = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                            }else if(arrayItem["applicationStatus"] === "Denied" || arrayItem["applicationStatus"] === null || arrayItem["applicationStatus"] === "Rejected" || arrayItem["applicationStatus"] === "Empty"){
                                applicationStatus = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                            }

                            files.forEach(function (file) {
                                if(file['field'] == "proof_of_payment"){
                                    if(file["status"] === "Uploaded"){
                                        pop = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        pop = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] === ""){
                                        pop = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>'
                                    }else if(file["status"] === "na"){
                                        pop = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }
                                }

                                if(file["field"] === "copyID"){
                                    if(file["status"] === "Uploaded"){
                                        copyId = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        copyId = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] === ""){
                                        copyId = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                                    }else if(file["status"] === "na"){
                                        copyId = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }
                                }

                                if(file["field"] === "studentCard" ){
                                    if(file["status"] === "Uploaded"){
                                        studentCard = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        studentCard= '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] === ""){
                                        studentCard = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                                    }else if(file["status"] === "na"){
                                        studentCard = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }        
                                }

                                if(file["field"] === "proofRegistration" ){
                                    if(file["status"] === "Uploaded"){
                                        proofRegistration = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        proofRegistration = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] == ""){
                                        proofRegistration = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                                    }else if(file["status"] === "na"){
                                        proofRegistration = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }
                                }

                                if(file["field"] === "bursaryApproval"){
                                    if(file["status"] === "Uploaded"){
                                        bursaryApproval = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        bursaryApproval = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] === ""){
                                        bursaryApproval = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                                    }else if(file["status"] === "na"){
                                        bursaryApproval = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }
                                }

                                if(file["field"] === "bankStatement"){
                                        
                                    if(file["status"] === "Uploaded"){
                                        bankStatement = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                                    }else if(file["status"] === "Approved"){
                                        bankStatement = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                                    }else if(file["status"] === "Rejected" || file["status"] === ""){
                                        bankStatement = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                                    }else if(file["status"] === "na"){
                                        bankStatement = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                    }
                                        
                                }else{
                                    bankStatement = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                                }
                                            
                            });

                            var datatable = $( "#application_table" ).DataTable();

                            var m = new Date(arrayItem["created_at"]);

                            var dateString =
                                m.getUTCFullYear() + "-" +
                                ("0" + (m.getUTCMonth()+1)).slice(-2) + "-" +
                                ("0" + m.getUTCDate()).slice(-2) + " " +
                                ("0" + m.getUTCHours()).slice(-2) + ":" +
                                ("0" + m.getUTCMinutes()).slice(-2) + ":" +
                                ("0" + m.getUTCSeconds()).slice(-2);

                            var trDOM = datatable.row.add( [
                                '#'+arrayItem["bookingRef"],
                                '<span class="bold">'+arrayItem["userFirstName"]+' '+arrayItem["userLastname"]+'</span> <br/> '+arrayItem["property"]+' | '+arrayItem["roomType"]+' | '+dateString,
                                arrayItem["studentType"],
                                bookingStat,
                                pop,
                                copyId,
                                applicationStatus,
                                studentCard,
                                proofRegistration,
                                bursaryApproval,
                                bankStatement,
                            ] ).draw().node();


                            $( trDOM ).addClass('table_id_'+arrayItem["id"]);
                            $( trDOM ).find('td').eq(3).addClass('bookingBg');
                            $( trDOM ).find('td').eq(6).addClass('bookingBg');





                            i++;
                        });

                        $("#searchButton").empty().append("Search")
                    },
                });

                // 
            })

    $('#application_table tbody').on('click', 'tr', function () {
        var datatable = $( "#application_table" ).DataTable();
        var data2 = datatable.row( this );
        var rowClass = data2['selector']['rows'].className;
        var rowId = (rowClass.split("table_id_"))[1];
        var rowId = rowId.replace(" odd-row", "");
        var rowId = rowId.replace(" even-row", "");

        window.location = "/application-profile/"+rowId;
    } );



    




})