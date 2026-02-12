<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MobileSuit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class MobileSuitController extends Controller
{
    /**
     * Check if the creator name and password match.
     */
    private function checkCreator(Request $request, MobileSuit $mobileSuit)
    {
        $creator = $mobileSuit->creator;
        if (!$creator || $creator->creator_name !== $request->creator_name || !Hash::check($request->edit_password, $creator->edit_password)) {
            return response()->json(['message' => '作成者名またはパスワードが正しくありません'], 403);
        }
        return null;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mobileSuits = MobileSuit::with('creator')->get();
        return response()->json($mobileSuits);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_id' => 'required|string',
            'ms_number' => 'nullable|string',
            'ms_name' => 'required|string',
            'ms_name_optional' => 'nullable|string',
            'ms_icon' => 'nullable|string',
            'ms_data' => 'required|array',
            'creator_name' => 'required|string',
            'edit_password' => 'required|string',
        ], [
            'data_id.required' => 'データIDは必須です',
            'ms_name.required' => 'MS名称は必須です',
            'ms_data.required' => 'MSデータは必須です',
            'creator_name.required' => '作成者名は必須です',
            'edit_password.required' => '編集パスワードは必須です',
        ]);

        $mobileSuitData = Arr::except($validated, ['creator_name', 'edit_password']);
        $mobileSuit = MobileSuit::create($mobileSuitData);

        $mobileSuit->creator()->create([
            'creator_name' => $validated['creator_name'],
            'edit_password' => Hash::make($validated['edit_password']),
        ]);

        return response()->json($mobileSuit->load('creator'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mobileSuit = MobileSuit::with('creator')->findOrFail($id);
        return response()->json($mobileSuit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'data_id' => 'required|string',
            'ms_number' => 'nullable|string',
            'ms_name' => 'required|string',
            'ms_name_optional' => 'nullable|string',
            'ms_icon' => 'nullable|string',
            'ms_data' => 'required|array',
            'creator_name' => 'required|string',
            'edit_password' => 'required|string',
        ], [
            'data_id.required' => 'データIDは必須です',
            'ms_name.required' => 'MS名称は必須です',
            'ms_data.required' => 'MSデータは必須です',
            'creator_name.required' => '作成者名は必須です',
            'edit_password.required' => '編集パスワードは必須です',
        ]);

        $mobileSuit = MobileSuit::findOrFail($id);
        if ($mobileSuit->creator && ($errorResponse = $this->checkCreator($request, $mobileSuit))) {
            return $errorResponse;
        }
        $mobileSuitData = Arr::except($validated, ['creator_name', 'edit_password']);
        $mobileSuit->update($mobileSuitData);

        $mobileSuit->creator()->updateOrCreate([], [
            'creator_name' => $validated['creator_name'],
            'edit_password' => Hash::make($validated['edit_password']),
        ]);

        return response()->json($mobileSuit->load('creator'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $validated = $request->validate([
            'creator_name' => 'required|string',
            'edit_password' => 'required|string',
        ], [
            'creator_name.required' => '作成者名は必須です',
            'edit_password.required' => '編集パスワードは必須です',
        ]);

        $mobileSuit = MobileSuit::findOrFail($id);
        if ($errorResponse = $this->checkCreator($request, $mobileSuit)) {
            return $errorResponse;
        }
        $mobileSuit->delete();

        return response()->noContent();
    }
}
