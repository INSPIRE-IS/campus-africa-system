
jQuery(document).ready(function ($) {

    $("#idNumber").on('blur', function(){
        let idNumber = $("#idNumber").val();

        let idArr = Array.from(idNumber);
        idArr.reverse()
        let newArr = [];

        for (let i = 0; i < idArr.length; i++) {
            if((i % 2) !== 0){
                let newValue = idArr[i]*2
                if(newValue > 9){
                    value1 = (newValue.toString()).substr(0,1)
                    value2 = (newValue.toString()).substr(1, 2)
                    newValue = parseInt(value1) + parseInt(value2)

                }
                newArr.push(newValue)
            }else{
                newArr.push(idArr[i])
            }
        }


        let sum = 0;
        for (let i = 0; i < newArr.length; i++) {
            sum += parseInt(newArr[i]);
        }

        let finalSum = sum/10

        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#idNumber").removeClass("fieldError")
            $("#idNumberError").addClass('hidden')
        }else{
            $("#idNumber").addClass("fieldError")
            $("#idNumberError").removeClass('hidden')
        }

    })

    $("#cell_no").on('blur', function(){

        cell = $("#cell_no").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#cell_no").removeClass("fieldError")
            $("#cellError").addClass('hidden')
        }else{
            $("#cell_no").addClass("fieldError")
            $("#cellError").removeClass('hidden')
        }

    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#email").on('blur', function(){
        if(isEmail($("#email").val())){
            $("#email").removeClass("fieldError")
            $("#emailError").addClass('hidden')
        }else{
            $("#email").addClass("fieldError")
            $("#emailError").removeClass('hidden')
        }
    })

    $("#nationality").on('change', function(){
        if($("#nationality").val() == "South African"){
            $("#passportField").addClass('hidden')
            $("#idField").removeClass('hidden')
            document.getElementById("passportNo").required = false;
            document.getElementById("idNumber").required = true;
        }else{
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')
            document.getElementById("passportNo").required = true;
            document.getElementById("idNumber").required = false;
        }
    })

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]

        var required = document.getElementById(fieldId).required

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
        }
    });

    $("select").blur(function(){
        let fieldId = $(this)[0]["id"]

        var required = document.getElementById(fieldId).required

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
        }
    });
})