<?php

if (!function_exists('get_user_status')) {
    function get_user_status($fileType, $user, $student) {

        if($fileType == "proof_of_payment"){
            $typeName = "Booking Fee Proof of Payment";
            $field_data = "";
            $field_doc = "rob_10_fee";
            

        } elseif($fileType == "copyID"){
            $typeName = "Copy of ID Document";
            $field_data = "id_data_approved";
            $field_doc = "rob_15_id";            

        }elseif($fileType == "studentCard"){
            $typeName = "Copy of Student Card";
            $field_data = "student_card_approved";
            $field_doc = "rob_20_card";

        }elseif($fileType == "proofRegistration"){
            $typeName = "Proof of Registration";
            $field_data = "registration_approved";
            $field_doc = "rob_25_reg";

        }elseif($fileType == "bursaryApproval"){
            if($student->studentType == "NSFAS"){
                $typeName = "NSFAS Approval";
            }else{
                $typeName = "Bursary Approval";
            }
            $field_data = "bursary_approved";
            $field_doc = "rob_30_funds";

        }elseif($fileType == "bankStatement"){
            $typeName = "Bank Statements";
            $field_data = "bank_approved";
            $field_doc = "rob_30_funds";                

        }elseif($fileType == "moveIn"){
            $typeName = "Lease";
            $field_data = "";
            $field_doc = "rob_55_lease";
        }else{
            $typeName = strtoupper(str_replace("_", " ", $fileType));
            $field_data = $fileType;
            $field_doc = "rob_45_other";
        }

        $data_approved = 0;
        $doc_approved = 0;

        if (!empty($user)) {
            if (!empty($field_data) && isset($user->{$field_data})) {
                $data_approved = $user->{$field_data};
            }

            if (!empty($field_doc) && isset($user->{$field_doc})) {
                $doc_approved = $user->{$field_doc};
            }
        }

        return [
            'typeName' => $typeName, 

            'field_data' => $field_data, 
            'field_doc' => $field_doc,

            'data_approved' => $data_approved,
            'doc_approved' => $doc_approved
        ];

    }
}