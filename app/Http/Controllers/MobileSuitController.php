<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MobileSuit;
use Illuminate\Http\Request;

class MobileSuitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mobileSuits = MobileSuit::all();
        return response()->json($mobileSuits);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_id' => 'required|string',
            'ms_number' => 'required|string',
            'ms_name' => 'required|string',
            'ms_name_optional' => 'nullable|string',
            'ms_icon' => 'nullable|string',
            'ms_data' => 'required|array',
        ]);

        $mobileSuit = MobileSuit::create($validated);

        return response()->json($mobileSuit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mobileSuit = MobileSuit::findOrFail($id);
        return response()->json($mobileSuit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'data_id' => 'required|string',
            'ms_number' => 'required|string',
            'ms_name' => 'required|string',
            'ms_name_optional' => 'nullable|string',
            'ms_icon' => 'nullable|string',
            'ms_data' => 'required|array',
        ]);

        $mobileSuit = MobileSuit::findOrFail($id);
        $mobileSuit->update($validated);

        return response()->json($mobileSuit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mobileSuit = MobileSuit::findOrFail($id);
        $mobileSuit->delete();

        return response()->noContent();
    }
}
