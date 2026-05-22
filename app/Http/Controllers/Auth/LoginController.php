<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MailerList;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;
use App\Models\Lookups;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function passwordlessLogin(Request $request)
    {

        $mailerForMessage = MailerList::where("mailer_name", "Passwordless Login")->first();

        if ($mailerForMessage->status == '1') {

            $user = User::where('userEmail', $request->email)->first();

            if ($user && !empty($user)) {

                if ($user->userRole == '0') {

                    $booking = Booking::where("userId", $user->id)->where('year', date('Y'))->first();

                    $generator = new LoginUrl($user);
                    $generator->setRedirectUrl('/dashboard');
                    $url = $generator->generate();

                    $message_replace['bookingRef'] = $booking->booking_code;
                    $message_replace['name'] = $user->userFirstName;
                    $message_replace['application_link'] = "<a href='" . $url . "' target='_blank'>";
                    $message_replace['close_application_link'] = "</a>";

                    $mailerForMessage = MailerList::where("mailer_name", "Passwordless Login")->first();

                    if ($mailerForMessage->status == '1') {

                        $mailerMessage = $mailerForMessage->message;

                        $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                        $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                        $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                        $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

                        $data['name'] = $user->userFirstName;
                        $data['email'] = $user->email;
                        $data['messageForMail'] = $newMessage;
                        $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);



                        $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                        $data['cc'] = array_filter(
                            array_map('trim', explode(";", $allowedMailsCC ?? "")),
                            fn($v) => !empty($v)
                        );

                        $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

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

                        // if(explode("@", $data['email'])[1] == "conurban.co.za"){
                        //     $data['email'] = $user->email;
                        // }else{
                        //     $data['email'] = "studentsca00@gmail.com";
                        // }

                        Mail::send('email.yellow_back', $data, function ($message) use ($data) {
                            // $message->to($data['email'], $data['name'])->subject
                            //     ('Campus Africa User Application');
                            $message->to($data['email'])->subject($data['subject']);
                            if (!empty($data['cc'])) {
                                $message->bcc($data['cc']);
                            }
                            // $message->cc(['studentsca00@gmail.com', 'roger@whitehart.co.za']);
                            $message->from('noreply@campusafrica.co.za', 'Campus Africa');
                        });
                    }

                    return "true";
                } else {
                    return "admin";
                }
            } else {
                return "false";
            }
        } else {
            return "offline";
        }
    }

    public function terms()
    {
        return view('terms', ["headerTitle" => "Terms And Conditions"]);
    }
}
