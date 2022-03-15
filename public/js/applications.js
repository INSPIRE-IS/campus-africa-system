jQuery(document).ready(function ($) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    let table = $("#application_table").DataTable({
        searching: false,
        aaSorting: [],
        stripeClasses: ["odd-row", "even-row"],
    });

    $("#application_table").on("page.dt", function () {
        var info = table.page.info();

        $.ajax({
            type: "post",
            url: "/set-page-session",
            data: {
                page: info.page,
            },
            success: function (data) {
                page = info.page;
            },
        });
    });

    $("#application_table").on("click", "thead th", function () {
        var order = table.order();
        let sortId = order[0][0];
        let sortDirection = order[0][1];

        $.ajax({
            type: "post",
            url: "/set-page-session-order",
            data: {
                sortId: sortId,
                sortDirection: sortDirection,
            },
        });
    });

    $("#searchButton").on("click", function () {
        $("#searchButton").empty().append("Searching...");

        var datatable = $("#application_table").DataTable();

        $.ajax({
            type: "post",
            url: "/applications/filter",
            data: {
                property: $("#property").val(),
                room_type: $("#room_type").val(),
                applicant: $("#applicant").val(),
                student_type: $("#student_type").val(),
                fieldStatus: $("#fieldStatus").val(),
                status: $("#status").val(),
                date: $("#date").val(),
                beds: $("#beds").val(),
                processingStatus: $("#processingStatus").val(),
                studentNo: $("#studentNo").val(),
                idPassport: $("#idPassport").val(),
                bookingRef: $("#bookingRef").val(),
                firstLoad: $("#firstLoad").val(),
            },

            success: function (data) {
                $("#firstLoad").val("0");
                datatable.clear().draw();

                results = JSON.parse(data)[0];

                sortDirection = JSON.parse(data)[1];
                sortId = JSON.parse(data)[2];
                page = JSON.parse(data)[3];

                var i = 0;

                //Set up robot classes
                let rob_rejected = '<div class="button rejected noBoarder"></div>';
                let rob_pending = '<div class="button action noBoarder"></div>';
                let rob_approved = '<div class="button approved noBoarder"></div>';

                results.forEach(function (arrayItem) {
                    if (i % 2 == 0) {
                        color = "dark";
                    } else {
                        color = "light";
                    }

                    //Default our robots to Cancelled
                    var rob_fee = '<div class="button na noBoarder"></div>';
                    var rob_id = '<div class="button na noBoarder"></div>';
                    var rob_card = '<div class="button na noBoarder"></div>';
                    var rob_reg = '<div class="button na noBoarder"></div>';
                    var rob_funds = '<div class="button na noBoarder"></div>';
                    var rob_info = '<div class="button na noBoarder"></div>';
                    var rob_bed = '<div class="button na noBoarder"></div>';
                    var rob_other = '<div class="button na noBoarder"></div>';
                    var rob_approve = '<div class="button na noBoarder"></div>';
                    var rob_lease = '<div class="button na noBoarder"></div>';

                    //For non cancelled rows work out the robots
                    if (arrayItem["rob_60_cancelled"] == 0) {
                        if (arrayItem["rob_10_fee"] == 0) {
                            rob_fee = rob_rejected;
                        } else if (arrayItem["rob_10_fee"] == 1) {
                            rob_fee = rob_pending;
                        } else if (arrayItem["rob_10_fee"] == 2) {
                            rob_fee = rob_approved;
                        }

                        if (arrayItem["rob_15_id"] == 0) {
                            rob_id = rob_rejected;
                        } else if (arrayItem["rob_15_id"] == 1) {
                            rob_id = rob_pending;
                        } else if (arrayItem["rob_15_id"] == 2) {
                            rob_id = rob_approved;
                        }

                        if (arrayItem["rob_20_card"] == 0) {
                            rob_card = rob_rejected;
                        } else if (arrayItem["rob_20_card"] == 1) {
                            rob_card = rob_pending;
                        } else if (arrayItem["rob_20_card"] == 2) {
                            rob_card = rob_approved;
                        }

                        if (arrayItem["rob_25_reg"] == 0) {
                            rob_reg = rob_rejected;
                        } else if (arrayItem["rob_25_reg"] == 1) {
                            rob_reg = rob_pending;
                        } else if (arrayItem["rob_25_reg"] == 2) {
                            rob_reg = rob_approved;
                        }

                        if (arrayItem["rob_30_funds"] == 0) {
                            rob_funds = rob_rejected;
                        } else if (arrayItem["rob_30_funds"] == 1) {
                            rob_funds = rob_pending;
                        } else if (arrayItem["rob_30_funds"] == 2) {
                            rob_funds = rob_approved;
                        }

                        if (arrayItem["rob_35_info"] == 0) {
                            rob_info = rob_rejected;
                        } else if (arrayItem["rob_35_info"] == 1) {
                            rob_info = rob_pending;
                        } else if (arrayItem["rob_35_info"] == 2) {
                            rob_info = rob_approved;
                        }

                        if (arrayItem["rob_40_bed"] == 0) {
                            rob_bed = rob_rejected;
                        } else if (arrayItem["rob_40_bed"] == 1) {
                            rob_bed = rob_pending;
                        } else if (arrayItem["rob_40_bed"] == 2) {
                            rob_bed = rob_approved;
                        }

                        if (arrayItem["rob_45_other"] == 0) {
                            rob_other = rob_rejected;
                        } else if (arrayItem["rob_45_other"] == 1) {
                            rob_other = rob_pending;
                        } else if (arrayItem["rob_45_other"] == 2) {
                            rob_other = rob_approved;
                        }

                        if (arrayItem["rob_50_approve"] == 0) {
                            rob_approve = rob_rejected;
                        } else if (arrayItem["rob_50_approve"] == 1) {
                            rob_approve = rob_pending;
                        } else if (arrayItem["rob_50_approve"] == 2) {
                            rob_approve = rob_approved;
                        }

                        if (arrayItem["rob_55_lease"] == 0) {
                            rob_lease = rob_rejected;
                        } else if (arrayItem["rob_55_lease"] == 1) {
                            rob_lease = rob_pending;
                        } else if (arrayItem["rob_55_lease"] == 2) {
                            rob_lease = rob_approved;
                        }

                        // if (arrayItem["overall_status"] !== "Cancelled" && arrayItem["overall_status"] !== "Rejected") {
                        //     if (arrayItem["bookingStatus"] == "Pending" || arrayItem["bookingStatus"] == "Action") {
                        //         var bookingStat = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                        //     } else if (arrayItem["bookingStatus"] == "Approved" || arrayItem["bookingStatus"] == "Approved Confirmed") {
                        //         var bookingStat = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                        //     } else if (
                        //         arrayItem["bookingStatus"] == "Denied" ||
                        //         arrayItem["bookingStatus"] == "" ||
                        //         arrayItem["bookingStatus"] == "Empty" ||
                        //         arrayItem["bookingStatus"] == "Rejected"
                        //     ) {
                        //         var bookingStat = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        //     } else {
                        //         var bookingStat = '<span class="hidden">D</span><div class="button rejected bigButton"></div>';
                        //     }
                        // } else {
                        //     var bookingStat = '<span class="hidden">D</span><div class="button na bigButton"></div>';
                        // }

                        // if (arrayItem["overall_status"] !== "Cancelled" && arrayItem["overall_status"] !== "Rejected") {
                        //     if (arrayItem["applicationStatus"] === "Pending" || arrayItem["applicationStatus"] === "Action") {
                        //         applicationStatus = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                        //     } else if (arrayItem["applicationStatus"] === "Approved Confirmed") {
                        //         applicationStatus = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                        //     } else if (arrayItem["applicationStatus"] === "Approved") {
                        //         if (arrayItem["bookingStatus"] == "Approved") {
                        //             applicationStatus = '<span class="hidden">C</span><div class="button action bigButton"></div>';
                        //         } else {
                        //             applicationStatus = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        //         }
                        //     } else if (
                        //         arrayItem["applicationStatus"] === "Denied" ||
                        //         arrayItem["applicationStatus"] === null ||
                        //         arrayItem["applicationStatus"] === "Rejected" ||
                        //         arrayItem["applicationStatus"] === "Empty" ||
                        //         arrayItem["applicationStatus"] === "Denied Confimed"
                        //     ) {
                        //         applicationStatus = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        //     } else {
                        //         applicationStatus = '<span class="hidden">B</span><div class="button na bigButton"></div>';
                        //     }
                        // } else {
                        //     applicationStatus = '<span class="hidden">B</span><div class="button na bigButton"></div>';
                        // }

                        // if (arrayItem["leaseStatus"] === "Uploaded") {
                        //     moveIn = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                        // } else if (arrayItem["leaseStatus"] === "Approved") {
                        //     moveIn = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                        // } else if (arrayItem["leaseStatus"] === "na") {
                        //     moveIn = '<span class="hidden">D</span><div class="button na bigButton"></div>';
                        // } else if (arrayItem["leaseStatus"] === "Rejected" || arrayItem["leaseStatus"] === "Incomplete") {
                        //     moveIn = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        // } else if (arrayItem["leaseStatus"] === null && arrayItem["applicationStatus"] === "Approved Confirmed") {
                        //     moveIn = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        // } else {
                        //     moveIn = '<span class="hidden">D</span><div class="button na bigButton"></div>';
                        // }

                        // if (arrayItem["bursaryApproval"] === "Uploaded") {
                        //     bursaryApproval = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                        // } else if (arrayItem["bursaryApproval"] === "Approved") {
                        //     bursaryApproval = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                        // } else if (arrayItem["bursaryApproval"] === "na") {
                        //     bursaryApproval = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                        // } else if (arrayItem["bursaryApproval"] === "Rejected" || arrayItem["bursaryApproval"] === "") {
                        //     bursaryApproval = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                        // } else {
                        //     bursaryApproval = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                        // }

                        // if (arrayItem["bankStatement"] === "Uploaded") {
                        //     bankStatement = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                        // } else if (arrayItem["bankStatement"] === "Approved") {
                        //     bankStatement = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                        // } else if (arrayItem["bankStatement"] === "na") {
                        //     bankStatement = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                        // } else if (arrayItem["bankStatement"] === "Rejected" || arrayItem["bankStatement"] === "") {
                        //     bankStatement = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                        // } else {
                        //     bankStatement = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                        // }
                    }

                    var m = new Date(arrayItem["created_at"]);

                    if (page == null || page == "NaN" || page == "") {
                        page = 0;
                    } else {
                        page = parseFloat(page);
                    }

                    if (sortId == "" || sortId == null || sortDirection == "" || sortDirection == null) {
                        var order = [];
                    } else {
                        var order = [sortId, sortDirection];
                    }

                    var dateString =
                        m.getUTCFullYear() +
                        "-" +
                        ("0" + (m.getUTCMonth() + 1)).slice(-2) +
                        "-" +
                        ("0" + m.getUTCDate()).slice(-2) +
                        "<br />" +
                        ("0" + m.getUTCHours()).slice(-2) +
                        ":" +
                        ("0" + m.getUTCMinutes()).slice(-2) +
                        ":" +
                        ("0" + m.getUTCSeconds()).slice(-2);

                    if (arrayItem["studentType"] == "Cash / Direct") {
                        studentType = "Cash";
                    } else {
                        studentType = arrayItem["studentType"];
                    }

                    if (arrayItem["bed_code"] == null) {
                        bed_code = "";
                    } else {
                        bed_code = arrayItem["bed_code"];
                    }

                    var trDOM = datatable.row
                        .add([
                            "#" + arrayItem["bookingRef"],
                            '<span class="bold">' +
                                arrayItem["userFirstName"] +
                                " " +
                                arrayItem["userLastname"] +
                                "</span> <br/> " +
                                arrayItem["property"] +
                                " | " +
                                arrayItem["roomType"],
                            '<span style="white-space: nowrap;">' + dateString + "</span>",
                            studentType,
                            rob_fee,
                            rob_id,
                            rob_card,
                            rob_reg,
                            rob_funds,
                            rob_info,
                            rob_bed,
                            rob_other,
                            rob_approve,
                            rob_lease,
                        ])
                        .page(page)
                        .order(order)
                        .draw(false)
                        .node();

                    $(trDOM).addClass("table_id_" + arrayItem["userId"]);
                    $(trDOM).find("td").eq(6).addClass("bookingBg");
                    $(trDOM).find("td").eq(9).addClass("bookingBg");
                    $(trDOM).find("td").eq(14).addClass("bookingBg");

                    i++;
                });

                $("#searchButton").empty().append("Search");
            },
        });

        //
    });

    $("#application_table tbody").on("click", "tr", function () {
        var datatable = $("#application_table").DataTable();
        var data2 = datatable.row(this);
        var rowClass = data2["selector"]["rows"].className;
        var rowId = rowClass.split("table_id_")[1];
        var rowId = rowId.replace(" odd-row", "");
        var rowId = rowId.replace(" even-row", "");

        window.location = "/application-profile/" + rowId;
    });

    $("#resetFilter").on("click", function () {
        $('.paginate_button[data-dt-idx="1"]').trigger("click");

        $.ajax({
            type: "post",
            url: "/set-page-session-order",
            data: {
                sortId: "",
                sortDirection: "",
            },
            success: function (data) {
                sortId = "";
                sortDirection = "";
            },
        });

        document.getElementById("property").value = "All";
        document.getElementById("room_type").value = "";
        $("#applicant").val("");
        $("#studentNo").val("");
        $("#idPassport").val("");
        $("#bookingRef").val("");
        document.getElementById("student_type").value = "All";
        document.getElementById("fieldStatus").value = "All";
        $("#date").val("");
        $("#beds").val("");
        $("#processingStatus").val("");
        $("#status").val(null).trigger("change");
        $("#searchButton").click();
    });
});

function btn_view_document_ajax(fileId) {
    $("#myModal").html("<div class='modal-content'>Loading</div>");
    $("#myModal").css("display", "block");

    $("#myModal").load("/documents/view-document/" + fileId);
}
