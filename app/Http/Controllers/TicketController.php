<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Ticket;

class TicketController extends Controller
{
    public function index() {
        if (
            !empty(Auth::user()->with('role')->where('id', '=', Auth::user()->id)->first()->role->role_id)
            && Auth::user()->with('role')->where('id', '=', Auth::user()->id)->first()->role->role_id === 1
        ) {
            return response(Ticket::with('user')->get(), 201);
        }
        return response(Ticket::with('user')->where('user_id', '=', Auth::user()->id)->get(), 201);
    }

    public function solicit() {
        $ticket = Ticket::create([
            'code' => '',
            'user_id' => Auth::user()->id,
        ]);
        return response()->json(['success' => true, 'message' => 'Ticket solicitado!', 'data' => $ticket], 201);
    }

    public function approve(Request $request) {
        $data = $request->validate([
            'id' => 'required',
            'code' => 'required|min:6|max:6'
        ]);

        $ticket = Ticket::where('code', $request->code)->first();
    
        if ($ticket) {
            return response([
                'message' => 'El ticket ya fue asignado.'
            ], 404);
        }

        $ticket = Ticket::where('id', $request->id)
            ->update([
                'code' => $request->code,
            ]);
        return response()->json(['success' => true, 'message' => 'Ticket aprobado!', 'data' => $ticket], 200);
    }
}
