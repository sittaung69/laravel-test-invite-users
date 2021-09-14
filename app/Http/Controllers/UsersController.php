<?php

namespace App\Http\Controllers;

use Str;
use URL;
use App\Invite;
use App\Notifications\InviteNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('users.index', compact('users'));
    }

    public function invite()
    {
        return view('users.invite');
    }

    public function processInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email'
        ]);

        $validator->after(function ($validator) use ($request) {
            if (Invite::where('email', $request->input('email'))->exists()) {
                $validator->errors()->add('email', 'There exists an invite with this email!');
            }
        });

        if ($validator->fails()) {
            return redirect(route('invite'))
                ->withErrors($validator)
                ->withInput();
        }

        do {
            $token = Str::random(20);
        } while (Invite::where('token', $token)->first());

        Invite::create([
            'token' => $token,
            'email' => $request->input('email')
        ]);

        $url = URL::temporarySignedRoute('registration', now()->addMinutes(300), ['token' => $token]);

        Notification::route('mail', $request->input('email'))->notify(new InviteNotification($url));

        return redirect('/users')->with('success', 'The Invite has been sent successfully');
    }

    public function registration($token)
    {
        $invite = Invite::where('token', $token)->first();

        if (!$invite) {
            abort(404);
        }

        return view('auth.register', ['invite' => $invite]);
    }
}
