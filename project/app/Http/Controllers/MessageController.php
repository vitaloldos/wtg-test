<?php

namespace App\Http\Controllers;

use App\Events\MessageSaved;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Message;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $messages = Message::with('user')->where('read_status', false)->where('receiver_id', $user->id)->get();
        $users = User::all();

        return Inertia::render('Dashboard', [
            'messages' => isset($messages) ? MessageResource::collection($messages) : [],
            'users' => UserResource::collection($users)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'user' =>  'required|integer',
            ]);

        $receiverUser = User::where('id', $request->user)->first();
;
        $message = $request->user()->messages()->create([
            'content' => $request->content,
            'receiver_id' => $request->user,
            'read_status' => $receiverUser->chat_status ? true : false,
        ]);

        broadcast(new MessageSaved($message))->toOthers();

        return redirect()->back();
    }

    public function connectChat(Request $request)
    {
        $request->validate(['user' => 'required|integer']);
        $chatUser = User::where('id', $request->user)->first();
        if($chatUser){
            $chatUser->chat_status = true;
            $chatUser->save();
        }
        return redirect()->back();
    }

    public function disconnectChat(Request $request)
    {
        $request->validate(['user' => 'required|integer']);
        $chatUser = User::where('id', $request->user)->first();
        if($chatUser){
            $chatUser->chat_status = false;
            $chatUser->save();
        }
        return redirect()->back();
    }

    public function messageStatusUpdate(Request $request)
    {
        $messages = Message::whereIn('id', $request->messages)->get();
        $messages->each->update(['read_status' => true]);
        return redirect()->back();
    }
}
