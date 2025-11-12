<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Village; 

class VillageModuleController extends Controller
{
    // Menampilkan modul desa berdasarkan ID desa
    public function getModules($id)
    {
        $village = Village::find($id);

        if (!$village) {
            return response()->json(['message' => 'Village not found'], 404);
        }

        // Ambil dan kembalikan data modul desa
        return response()->json($village->modules); // Sesuaikan dengan relasi yang ada di model
    }

    // Menyalakan atau mematikan modul berdasarkan nama
    public function toggleModule($id, $name)
    {
        $village = Village::find($id);

        if (!$village) {
            return response()->json(['message' => 'Village not found'], 404);
        }

        // Cek jika modul dengan nama tertentu ada di desa
        $module = $village->modules()->where('name', $name)->first();

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        // Toggle status modul
        $module->status = !$module->status;
        $module->save();

        return response()->json(['message' => 'Module status toggled successfully', 'module' => $module]);
    }
}
