<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use App\Models\MailerList;
use App\Models\Booking;
use App\Models\User;
use Mail;
use App\Models\ActionList;

class MailReminder extends Controller
{
    public function mail_reminder(Request $request)
    {
        $BookingFeeReminder = Lookups::where('group_id', '12')->first();
        $oustandingReminder = Lookups::where('group_id', '13')->first();
        $this_year = Lookups::where('group_id', '4')->where('is_active_year', '1')->first();
        $this_year = $this_year->lookup_name;

        $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
        $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

        $mailerForFeeMessage = MailerList::where("mailer_name", "Reminder Mail Fee")->first();

        //check is reminder is online
        if ($BookingFeeReminder->lookup_name == '1') {

            $reminderDays = $BookingFeeReminder->day_from;
            $allStudents = StudentsInfo::where('year', $this_year)->where('rob_10_fee', '0')->get();

            foreach ($allStudents as $student) {

                if ($student->fee_reminder_sent == null) {
                    $file = UserFiles::where('fileId', 'proof_of_payment')->where('userUid', $student->userUid)->where('year', $this_year)->first();

                    if (!empty($file)) {
                        echo $file->status;
                        if ($file->status == "Rejected") {
                            $updatedDte = $student->updated_at;
                            $projectedDte = date('Y-m-d', strtotime($updatedDte . ' + ' . $reminderDays . ' days'));
                            $nowDte = date('Y-m-d');

                            //check if doc rejected more than required amount of days for reminder
                            if ($projectedDte <= $nowDte) {
                                //send reminder mail

                                if ($mailerForFeeMessage->status == '1') {
                                    $booking = Booking::where('userid', $student->userUid)->where('year', $this_year)->first();
                                    $user = User::where('id', $student->userUid)->first();

                                    $message_replace['bookingRef'] = $booking->booking_code;
                                    $message_replace['name'] = $student->userFirstName;
                                    $message_replace['application_link'] = "<a href='https://$_SERVER[HTTP_HOST]/login' target='_blank'>";
                                    $message_replace['close_application_link'] = "</a>";

                                    $mailerMessage = $mailerForFeeMessage->message;

                                    $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                                    $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                                    $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                                    $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
                                    $newSubject = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForFeeMessage->subject);

                                    $data['name'] = $student->userFirstName;
                                    $data['email'] = $user->email;
                                    $data['messageForMail'] = $newMessage;
                                    $data['subject'] = $newSubject;
                                    $data['cc'] = explode(";", $allowedMailsCC);

                                    if (!empty($allowedMails)) {

                                        $mailArr = explode(";", $allowedMails);

                                        if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                                            $data['email'] = $user->email;
                                        } else {
                                            $data['email'] = "studentsca00@gmail.com";
                                        }
                                    } else {
                                        $data['email'] = $user->email;
                                    }

                                    Mail::send('email.yellow_back', $data, function ($message) use ($data) {
                                        $message->to($data['email'])->subject($data['subject']);
                                        $message->bcc($data['cc']);
                                        $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                                    });

                                    StudentsInfo::where('id', $student->id)->update(["fee_reminder_sent" => date('Y-m-d')]);

                                    ActionList::create([
                                        "userUid" => $student->userUid,
                                        "action" => "Reminder Email sent for Booking Fee Proof of Payment",
                                        "actionUserUid" => '0',
                                        "actionUserDisplayName" => "System",
                                        "actionUserRole" => 1,
                                        "year" => $this_year,
                                    ]);
                                }
                            }
                        }
                    } else {
                        //check if account has a verified email
                        if ($student->email_verified == 1) {

                            $createdDte = $student->created_at;
                            $projectedDte = date('Y-m-d', strtotime($createdDte . ' + ' . $reminderDays . ' days'));
                            $nowDte = date('Y-m-d');

                            //check if account was created more than required amount of days for reminder
                            if ($projectedDte <= $nowDte) {
                                //send reminder mail

                                if ($mailerForFeeMessage->status == '1') {
                                    $booking = Booking::where('userid', $student->userUid)->where('year', $this_year)->first();
                                    $user = User::where('id', $student->userUid)->first();

                                    $message_replace['bookingRef'] = $booking->booking_code;
                                    $message_replace['name'] = $student->userFirstName;
                                    $message_replace['application_link'] = "<a href='https://$_SERVER[HTTP_HOST]/login' target='_blank'>";
                                    $message_replace['close_application_link'] = "</a>";

                                    $mailerMessage = $mailerForFeeMessage->message;

                                    $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                                    $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                                    $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                                    $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
                                    $newSubject = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForFeeMessage->subject);

                                    $data['name'] = $student->userFirstName;
                                    $data['email'] = $user->email;
                                    $data['messageForMail'] = $newMessage;
                                    $data['subject'] = $newSubject;
                                    $data['cc'] = explode(";", $allowedMailsCC);

                                    if (!empty($allowedMails)) {

                                        $mailArr = explode(";", $allowedMails);

                                        if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                                            $data['email'] = $user->email;
                                        } else {
                                            $data['email'] = "studentsca00@gmail.com";
                                        }
                                    } else {
                                        $data['email'] = $user->email;
                                    }

                                    Mail::send('email.yellow_back', $data, function ($message) use ($data) {
                                        $message->to($data['email'])->subject($data['subject']);
                                        $message->bcc($data['cc']);
                                        $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                                    });

                                    StudentsInfo::where('id', $student->id)->update(["fee_reminder_sent" => date('Y-m-d')]);

                                    ActionList::create([
                                        "userUid" => $student->userUid,
                                        "action" => "Reminder Email sent for Booking Fee Proof of Payment",
                                        "actionUserUid" => '0',
                                        "actionUserDisplayName" => "System",
                                        "actionUserRole" => 1,
                                        "year" => $this_year,
                                    ]);
                                }
                            }
                        }
                    }
                } else {
                    $lastDte = $student->fee_reminder_sent;
                    $projectedDte = date('Y-m-d', strtotime($lastDte . ' + ' . $reminderDays . ' days'));
                    $nowDte = date('Y-m-d');

                    //check if last mail was sent more than required amount of days for reminder
                    if ($projectedDte <= $nowDte) {
                        //send reminder mail

                        if ($mailerForFeeMessage->status == '1') {
                            $booking = Booking::where('userid', $student->userUid)->where('year', $this_year)->first();
                            $user = User::where('id', $student->userUid)->first();

                            $message_replace['bookingRef'] = $booking->booking_code;
                            $message_replace['name'] = $student->userFirstName;
                            $message_replace['application_link'] = "<a href='https://$_SERVER[HTTP_HOST]/login' target='_blank'>";
                            $message_replace['close_application_link'] = "</a>";

                            $mailerMessage = $mailerForFeeMessage->message;

                            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
                            $newSubject = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForFeeMessage->subject);

                            $data['name'] = $student->userFirstName;
                            $data['email'] = $user->email;
                            $data['messageForMail'] = $newMessage;
                            $data['subject'] = $newSubject;
                            $data['cc'] = explode(";", $allowedMailsCC);

                            if (!empty($allowedMails)) {

                                $mailArr = explode(";", $allowedMails);

                                if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                                    $data['email'] = $user->email;
                                } else {
                                    $data['email'] = "studentsca00@gmail.com";
                                }
                            } else {
                                $data['email'] = $user->email;
                            }

                            Mail::send('email.yellow_back', $data, function ($message) use ($data) {
                                $message->to($data['email'])->subject($data['subject']);
                                $message->bcc($data['cc']);
                                $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                            });

                            StudentsInfo::where('id', $student->id)->update(["fee_reminder_sent" => date('Y-m-d')]);

                            ActionList::create([
                                "userUid" => $student->userUid,
                                "action" => "Reminder Email sent for Booking Fee Proof of Payment",
                                "actionUserUid" => '0',
                                "actionUserDisplayName" => "System",
                                "actionUserRole" => 1,
                                "year" => $this_year,
                            ]);
                        }
                    }
                }
            }
        }











        //check if reminder is online
        if ($oustandingReminder->lookup_name == '1') {
            //
            $reminderDays = $oustandingReminder->day_from;
            $allStudents = StudentsInfo::where('year', $this_year)->where('rob_50_approve', '0')->get();

            foreach ($allStudents as $student) {

                if ($student->general_reminder_sent == null) {

                    $all = ['studentCard', 'proofRegistration', 'copyID'];
                    $outstanding = [];

                    if ($student->studentType == "Cash / Direct") {
                        array_push($all, "bankStatement");
                    }
                    if ($student->studentType !== "Cash / Direct") {
                        array_push($all, "bursaryApproval");
                    }

                    $allSearchable = UserFiles::where("userUid", $student->userUid)->where('year', $this_year)->get();

                    foreach ($allSearchable as $search) {
                        if (explode("_", $search->fileId)[0] == "other") {
                            array_push($all, $search->fileId);
                        }
                    }

                    foreach ($all as $file) {
                        $file_search = UserFiles::where("userUid", $student->userUid)->where('fileId', $file)->where('year', $this_year)->first();

                        if (($student->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $student->studentType != "Cash / Direct" && $file !== 'bankStatement') {

                            if ($file == "studentCard") {
                                $fileName = "Student Card";
                            } elseif ($file == "copyID") {
                                $fileName = "ID Document";
                            } elseif ($file == "proofRegistration") {
                                $fileName = "Proof of Registration";
                            } elseif ($file == "bankStatement") {
                                $fileName = "Bank Statements";
                            } elseif ($file == "bursaryApproval") {
                                if ($student->studentType != "NSFAS") {
                                    $fileName = "Bursary Approval";
                                } else {
                                    $fileName = "NSFAS Approval";
                                }
                            } elseif (explode("_", $file)[0] == "other") {
                                $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                            }

                            if ($file_search && !empty($file_search)) {
                                if ($file_search->status !== "Approved" && $file_search->status !== "Uploaded") {
                                    array_push($outstanding, $fileName);
                                }
                            } else {
                                array_push($outstanding, $fileName);
                            }
                        }
                    }

                    if (count($outstanding) > 0) {

                        $outstandingDocsText = "<ol>";
                        $outDocs = "";

                        foreach ($outstanding as $out) {
                            $outstandingDocsText .= "<li style='color: #000; font-size: 21px; font-weight: 400; line-height: 30px;'>" . $out . "</li>";
                            $outDocs .= " " . $out . ",";
                        }

                        $outstandingDocsText .= "</ol>";


                        $booking = Booking::where('userid', $student->userUid)->where('year', $this_year)->first();
                        $message_replace['bookingRef'] = $booking->booking_code;
                        $message_replace['name'] = $student->userFirstName;
                        $message_replace['application_link'] = "<a href='https://$_SERVER[HTTP_HOST]/login' target='_blank'>";
                        $message_replace['close_application_link'] = "</a>";
                        $message_replace['missing'] = $outstandingDocsText;

                        $mailerForMessage = MailerList::where("mailer_name", "Reminder Mail All Outstanding")->first();

                        if ($mailerForMessage->status == '1') {

                            $mailerMessage = $mailerForMessage->message;

                            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                            $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
                            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

                            $mailData['name'] = $student->userFirstName;
                            $mailData['email'] = $student->userEmail;
                            $mailData['messageForMail'] = $newMessage;
                            $mailData['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

                            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                            $mailData['cc'] = explode(";", $allowedMailsCC);

                            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                            if (!empty($allowedMails)) {

                                $mailArr = explode(";", $allowedMails);

                                if (in_array(explode("@", $mailData['email'])[1], $mailArr)) {
                                    $mailData['email'] = $student->userEmail;
                                } else {
                                    $mailData['email'] = "studentsca00@gmail.com";
                                }
                            } else {
                                $mailData['email'] = $student->userEmail;
                            }

                            Mail::send('email.yellow_back', $mailData, function ($message) use ($mailData) {
                                $message->to($mailData['email'])->subject($mailData['subject']);
                                $message->bcc($mailData['cc']);
                                $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                            });

                            StudentsInfo::where('id', $student->id)->update(["general_reminder_sent" => date('Y-m-d')]);

                            ActionList::create([
                                "userUid" => $student->userUid,
                                "action" => "Reminder Email sent for Outstanding Documents",
                                "actionUserUid" => '0',
                                "actionUserDisplayName" => "System",
                                "actionUserRole" => 1,
                                "year" => $this_year,
                            ]);
                        }
                    }
                } else {

                    $lastDte = $student->general_reminder_sent;
                    $projectedDte = date('Y-m-d', strtotime($lastDte . ' + ' . $reminderDays . ' days'));
                    $nowDte = date('Y-m-d');

                    if ($projectedDte <= $nowDte) {
                        $all = ['studentCard', 'proofRegistration', 'copyID'];
                        $outstanding = [];

                        if ($student->studentType == "Cash / Direct") {
                            array_push($all, "bankStatement");
                        }
                        if ($student->studentType !== "Cash / Direct") {
                            array_push($all, "bursaryApproval");
                        }

                        $allSearchable = UserFiles::where("userUid", $student->userUid)->where('year', $this_year)->get();

                        foreach ($allSearchable as $search) {
                            if (explode("_", $search->fileId)[0] == "other") {
                                array_push($all, $search->fileId);
                            }
                        }

                        foreach ($all as $file) {
                            $file_search = UserFiles::where("userUid", $student->userUid)->where('fileId', $file)->where('year', $this_year)->first();

                            if (($student->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $student->studentType != "Cash / Direct" && $file !== 'bankStatement') {

                                if ($file == "studentCard") {
                                    $fileName = "Student Card";
                                } elseif ($file == "copyID") {
                                    $fileName = "ID Document";
                                } elseif ($file == "proofRegistration") {
                                    $fileName = "Proof of Registration";
                                } elseif ($file == "bankStatement") {
                                    $fileName = "Bank Statements";
                                } elseif ($file == "bursaryApproval") {
                                    if ($student->studentType != "NSFAS") {
                                        $fileName = "Bursary Approval";
                                    } else {
                                        $fileName = "NSFAS Approval";
                                    }
                                } elseif (explode("_", $file)[0] == "other") {
                                    $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                                }

                                if ($file_search && !empty($file_search)) {
                                    if ($file_search->status !== "Approved" && $file_search->status !== "Uploaded") {
                                        array_push($outstanding, $fileName);
                                    }
                                } else {
                                    array_push($outstanding, $fileName);
                                }
                            }
                        }

                        if (count($outstanding) > 0) {

                            $outstandingDocsText = "<ol>";
                            $outDocs = "";

                            foreach ($outstanding as $out) {
                                $outstandingDocsText .= "<li style='color: #000; font-size: 21px; font-weight: 400; line-height: 30px;'>" . $out . "</li>";
                                $outDocs .= " " . $out . ",";
                            }

                            $outstandingDocsText .= "</ol>";


                            $booking = Booking::where('userid', $student->userUid)->where('year', $this_year)->first();
                            $message_replace['bookingRef'] = $booking->booking_code;
                            $message_replace['name'] = $student->userFirstName;
                            $message_replace['application_link'] = "<a href='https://$_SERVER[HTTP_HOST]/login' target='_blank'>";
                            $message_replace['close_application_link'] = "</a>";
                            $message_replace['missing'] = $outstandingDocsText;

                            $mailerForMessage = MailerList::where("mailer_name", "Reminder Mail All Outstanding")->first();

                            if ($mailerForMessage->status == '1') {

                                $mailerMessage = $mailerForMessage->message;

                                $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                                $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
                                $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                                $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

                                $mailData['name'] = $student->userFirstName;
                                $mailData['email'] = $student->userEmail;
                                $mailData['messageForMail'] = $newMessage;
                                $mailData['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

                                $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                                $mailData['cc'] = explode(";", $allowedMailsCC);

                                $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                                if (!empty($allowedMails)) {

                                    $mailArr = explode(";", $allowedMails);

                                    if (in_array(explode("@", $mailData['email'])[1], $mailArr)) {
                                        $mailData['email'] = $student->userEmail;
                                    } else {
                                        $mailData['email'] = "studentsca00@gmail.com";
                                    }
                                } else {
                                    $mailData['email'] = $student->userEmail;
                                }

                                Mail::send('email.yellow_back', $mailData, function ($message) use ($mailData) {
                                    $message->to($mailData['email'])->subject($mailData['subject']);
                                    $message->bcc($mailData['cc']);
                                    $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                                });

                                StudentsInfo::where('id', $student->id)->update(["general_reminder_sent" => date('Y-m-d')]);

                                ActionList::create([
                                    "userUid" => $student->userUid,
                                    "action" => "Reminder Email sent for Outstanding Documents",
                                    "actionUserUid" => '0',
                                    "actionUserDisplayName" => "System",
                                    "actionUserRole" => 1,
                                    "year" => $this_year,
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
