<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('notification::index', [
            'notifications' => $request->user()->notifications()->latest()->limit(50)->get(),
        ]);
    }
}
