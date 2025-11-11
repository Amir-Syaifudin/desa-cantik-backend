<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Village; 

class VillageController extends Controller
{
    // GET /villages (Get All)
    public function getAll()
    {
        $villages = Village::all(); // Mengambil semua data desa
        return response()->json($villages);
    }

    // GET /villages/{id} (Get Detail)
    public function getDetail($id)
    {
        $village = Village::find($id); // Mencari desa berdasarkan ID
        if ($village) {
            return response()->json($village);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // POST /villages (Create)
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            // Validasi data lainnya
        ]);

        $village = Village::create($validated); // Menyimpan desa baru ke database
        return response()->json($village, 201);
    }

    // PUT /villages/{id} (Update)
    public function update(Request $request, $id)
    {
        $village = Village::find($id);
        if ($village) {
            $village->update($request->all()); // Update data desa
            return response()->json($village);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // DELETE /villages/{id} (Delete)
    public function delete($id)
    {
        $village = Village::find($id);
        if ($village) {
            $village->delete(); // Menghapus desa
            return response()->json(['message' => 'Village deleted']);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // PUT /villages/{id}/toggle-status (Toggle Status Aktif)
    public function toggleStatus($id)
    {
        $village = Village::find($id);
        if ($village) {
            $village->is_active = !$village->is_active; // Toggle status aktif
            $village->save();
            return response()->json($village);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }
}


