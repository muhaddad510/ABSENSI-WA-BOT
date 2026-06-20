<?php

namespace App\Http\Controllers;

use App\Models\TeachingLocation;
use Illuminate\Http\Request;

class TeachingLocationController extends Controller
{
    public function index()
    {
        $loc = TeachingLocation::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_m' => 200,
            ]
        );

        return view('teaching_location.index', compact('loc'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_m' => 'required|integer|min:1|max:5000',
        ]);

        TeachingLocation::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_m' => $request->radius_m,
            ]
        );

        return redirect()
            ->route('teaching-location.index')
            ->with('success', 'Lokasi mengajar berhasil disimpan.');
    }
}
