<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marker;

class MarkerController extends Controller
{
    public function index() {
        $markers = Marker::all();
        return response()->json($markers);
    }

    public function store(Request $request) {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        // Simpan marker ke database
        $marker = Marker::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return response()->json([
            'success' => true,
            'marker' => $marker
        ], 201);
    }

    public function destroy($id) {
        $marker = Marker::find($id);
        if ($marker) {
            $marker->delete();
            return response()->json(['success' => true, 'message' => 'Marker deleted successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Marker not found'], 404);
    }
}
