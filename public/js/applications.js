jQuery(document).ready(function ($) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#application_table").DataTable({
        searching: false,
        aaSorting: [],
        stripeClasses: ["odd-row", "even-row"],
    });

    $("#searchButton").on("click", function () {
        $("#searchButton").empty().append("Searching...");

        var datatable = $("#application_table").DataTable();
        datatable.clear().draw();

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
                studentNo: $("#studentNo").val(),
                idPassport: $("#idPassport").val(),
                bookingRef: $("#bookingRef").val(),
            },

            success: function (data) {
                results = JSON.parse(data);

                var i = 0;

                results.forEach(function (arrayItem) {
                    if (i % 2 == 0) {
                        color = "dark";
                    } else {
                        color = "light";
                    }

                    if (arrayItem["overall_status"] !== "Cancelled" && arrayItem["overall_status"] !== "Rejected") {
                        if (arrayItem["bookingStatus"] == "Pending" || arrayItem["bookingStatus"] == "Action") {
                            var bookingStat = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                        } else if (arrayItem["bookingStatus"] == "Approved") {
                            var bookingStat = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                        } else if (
                            arrayItem["bookingStatus"] == "Denied" ||
                            arrayItem["bookingStatus"] == "" ||
                            arrayItem["bookingStatus"] == "Empty" ||
                            arrayItem["bookingStatus"] == "Rejected"
                        ) {
                            var bookingStat = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        } else {
                            var bookingStat = '<span class="hidden">D</span><div class="button rejected bigButton"></div>';
                        }
                    } else {
                        var bookingStat = '<span class="hidden">D</span><div class="button na bigButton"></div>';
                    }

                    if (arrayItem["overall_status"] !== "Cancelled" && arrayItem["overall_status"] !== "Rejected") {
                        if (
                            arrayItem["applicationStatus"] === "Pending" ||
                            arrayItem["applicationStatus"] === "Action" ||
                            arrayItem["applicationStatus"] === "Approved"
                        ) {
                            applicationStatus = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                        } else if (arrayItem["applicationStatus"] === "Approved Confirmed") {
                            applicationStatus = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                        } else if (
                            arrayItem["applicationStatus"] === "Denied" ||
                            arrayItem["applicationStatus"] === null ||
                            arrayItem["applicationStatus"] === "Rejected" ||
                            arrayItem["applicationStatus"] === "Empty" ||
                            arrayItem["applicationStatus"] === "Denied Confimed"
                        ) {
                            applicationStatus = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                        }
                    } else {
                        applicationStatus = '<span class="hidden">B</span><div class="button na bigButton"></div>';
                    }

                    if (arrayItem["proof_of_payment"] === "Uploaded") {
                        pop = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["proof_of_payment"] === "Approved") {
                        pop = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["proof_of_payment"] === "na") {
                        pop = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["proof_of_payment"] === "Rejected" || arrayItem["proof_of_payment"] === "") {
                        pop = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        pop = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    if (arrayItem["copyID"] === "Uploaded") {
                        copyId = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["copyID"] === "Approved") {
                        copyId = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["copyID"] === "na") {
                        copyId = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["copyID"] === "Rejected" || arrayItem["copyID"] === "") {
                        copyId = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        copyId = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    if (arrayItem["studentCard"] === "Uploaded") {
                        studentCard = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["studentCard"] === "Approved") {
                        studentCard = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["studentCard"] === "na") {
                        studentCard = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["studentCard"] === "Rejected" || arrayItem["studentCard"] === "") {
                        studentCard = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        studentCard = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    if (arrayItem["proofRegistration"] === "Uploaded") {
                        proofRegistration = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["proofRegistration"] === "Approved") {
                        proofRegistration = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["proofRegistration"] === "na") {
                        proofRegistration = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["proofRegistration"] === "Rejected" || arrayItem["proofRegistration"] == "") {
                        proofRegistration = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        proofRegistration = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    if (arrayItem["moveIn"] === "Uploaded") {
                        moveIn = '<span class="hidden">A</span><div class="button action bigButton"></div>';
                    } else if (arrayItem["moveIn"] === "Approved") {
                        moveIn = '<span class="hidden">C</span><div class="button approved bigButton"></div>';
                    } else if (arrayItem["moveIn"] === "na") {
                        moveIn = '<span class="hidden">D</span><div class="button na bigButton"></div>';
                    } else if (arrayItem["moveIn"] === "Rejected" || arrayItem["moveIn"] === "") {
                        moveIn = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                    } else {
                        moveIn = '<span class="hidden">B</span><div class="button rejected bigButton"></div>';
                    }

                    if (arrayItem["bursaryApproval"] === "Uploaded") {
                        bursaryApproval = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["bursaryApproval"] === "Approved") {
                        bursaryApproval = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["bursaryApproval"] === "na") {
                        bursaryApproval = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["bursaryApproval"] === "Rejected" || arrayItem["bursaryApproval"] === "") {
                        bursaryApproval = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        bursaryApproval = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    if (arrayItem["bankStatement"] === "Uploaded") {
                        bankStatement = '<span class="hidden">A</span><div class="button action noBoarder"></div>';
                    } else if (arrayItem["bankStatement"] === "Approved") {
                        bankStatement = '<span class="hidden">C</span><div class="button approved noBoarder"></div>';
                    } else if (arrayItem["bankStatement"] === "na") {
                        bankStatement = '<span class="hidden">D</span><div class="button na noBoarder"></div>';
                    } else if (arrayItem["bankStatement"] === "Rejected" || arrayItem["bankStatement"] === "") {
                        bankStatement = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    } else {
                        bankStatement = '<span class="hidden">B</span><div class="button rejected noBoarder"></div>';
                    }

                    var m = new Date(arrayItem["created_at"]);

                    var dateString =
                        m.getUTCFullYear() +
                        "-" +
                        ("0" + (m.getUTCMonth() + 1)).slice(-2) +
                        "-" +
                        ("0" + m.getUTCDate()).slice(-2) +
                        " " +
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
                            bed_code,
                            dateString,
                            studentType,
                            arrayItem["overall_status"],
                            bookingStat,
                            pop,
                            copyId,
                            applicationStatus,
                            studentCard,
                            proofRegistration,
                            bursaryApproval,
                            bankStatement,
                            moveIn,
                        ])
                        .draw()
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
        document.getElementById("property").value = "All";
        document.getElementById("room_type").value = "";
        $("#applicant").val("");
        $("#studentNo").val("");
        $("#idPassport").val("");
        $("#bookingRef").val("");
        document.getElementById("student_type").value = "All";
        document.getElementById("fieldStatus").value = "All";
        $("#date").val("");
        $("#status").val(null).trigger("change");
        $("#searchButton").click();
    });
});
