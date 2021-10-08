<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFiles;
use Auth;
use Mail;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        if(Auth::user()->userRole == "0"){

            return view('user.dashboard', ["headerTitle" => "Your Dashboard"]);
        }else{

            return view('admin.dashboard', ["headerTitle" => "Admin Dashboard"]);
        }

    }

    public function room_booking(){

        return view('user.room_booking', ["headerTitle" => "Room Booking"]);

    }

    public function lease(){

        return view('user.lease', ["headerTitle" => "Sign Lease"]);

    }

    public function move_in(){

        return view('user.move_in', ["headerTitle" => "Move in Resources"]);

    }

    protected function fileUpload(Request $request){
        $userUid = $request->userUid;
        $fileType = $request->fileType;

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = '/files/'.$userUid.'/'. $fileNameToStore;

                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $userUid,
                    'fileName' => $fileNameToStore,
                    'fileUrl' => $fileUrl,
                    'status' => 'Uploaded',
                ]);

            }
        }


        return "done";
    }

    public function complete_reservation(Request $request){
        
        return view('user.complete_res', ["headerTitle" => "Congratulations!"]);

    }

    public function applications(Request $request){
        
        if(Auth::user()->userRole == "1"){

            return view('admin.applications', ["headerTitle" => "Applications"]);
        }
    }

    public function application_profile(Request $request, $userId){

        if(Auth::user()->userRole == "1"){

            $user = User::where('id', $userId)->first();

            $name = $user['userFirstName']." ".$user['userLastname'];

            return view('admin.app_profile', ["headerTitle" => $name, "appProfile" => "true", "user" => $user]);
        }
    }

    public function thanks(Request $request){

        $data['test'] = "";

        // Mail::send('email.application_thank_you', $data, function($message) use ($data) {
        //     $message->to("mirichea@whitehart.co.za", "Mirichea")->subject
        //         ('Thank you Application');
        //     $message->cc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        return view('email.application_rejected');
    }
}
