<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = StampCorrectionRequest::with('user')
            ->where('user_id', auth()->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->get();

        return view('stamp_correction_requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with('user', 'attendance')
            ->findOrFail($id);

        return view('stamp_correction_requests.show', compact('request'));
    }

}