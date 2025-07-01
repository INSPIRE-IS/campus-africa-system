<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function showStructure()
    {
        $structure = $this->firebase->getFirestoreStructure();
        return view('firebase.structure', compact('structure'));
    }


    public function showRegister()
    {
        return view('firebase.register');
    }

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            // 'first_name' => 'required',
            // 'last_name' => 'required',
            // 'display_name' => 'nullable',
            // 'phone_number' => 'nullable',
            // 'bed_code' => 'nullable',
            // 'residence' => 'nullable',
            // 'role' => 'nullable',
            // Add more as needed
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $existing = $this->getUserByEmail($request->email);
            return back()->with('error', 'User already exists in Firebase.');
        } catch (\Throwable $e) {
            // OK to proceed
        }

        try {
            $user = $this->firebase->registerUser($request->email, $request->password);

            // Store additional fields into Firestore or your DB
            // Example: save to 'students' table in Laravel
            // \DB::table('students')->insert([
            //     'firebase_uid' => $user->uid,
            //     'email' => $request->email,
            //     'first_name' => $request->first_name,
            //     'last_name' => $request->last_name,
            //     'bed_code' => $request->bed_code,
            //     'residence' => $request->residence,
            //     'role' => $request->role,
            //     'created_at' => now(),
            // ]);

            // return back()->with('success', 'Student registered in Firebase.');


            return back()->with('success', 'User registered: ' . $user->uid);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function showPasswordForm()
    {
        return view('firebase.password');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'email' => 'required|email',
            'new_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $this->firebase->updatePassword($request->uid, $request->new_password);
            return back()->with('success', 'Password updated.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function viewMaintenanceItems(Request $request)
    {
        $items = $this->firebase->getMaintenanceItems(20);
        return view('firebase.maintenance', compact('items'));
    }
}
