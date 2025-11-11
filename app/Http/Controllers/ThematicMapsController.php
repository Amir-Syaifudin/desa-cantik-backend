<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Village; // Pastikan untuk menggunakan model Village jika diperlukan
use App\Models\ThematicMap; // Pastikan model thematic map ada

class ThematicMapsController extends Controller
{
    // GET /villages/{id}/thematic-maps (Get Tema Peta)
    public function getThematicMaps($id)
    {
        $village = Village::find($id);
        if ($village) {
            return response()->json($village->thematic_maps); // Mengembalikan data peta tematik
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // GET /thematic-maps/{id} (Detail Tema & Points)
    public function getThematicMapDetail($id)
    {
        $thematicMap = ThematicMap::find($id);
        if ($thematicMap) {
            return response()->json($thematicMap); // Mengembalikan detail peta tematik
        } else {
            return response()->json(['message' => 'Thematic map not found'], 404);
        }
    }

    // POST /villages/{id}/thematic-maps (Create Tema)
    public function createThematicMap(Request $request, $id)
    {
        $village = Village::find($id);
        if ($village) {
            $validated = $request->validate([
                'name' => 'required|string',
                'points' => 'required|array', // Pastikan data points terstruktur dengan benar
                'geojson_data' => 'required|json', // Misalnya data GeoJSON
            ]);

            $thematicMap = $village->thematic_maps()->create($validated); // Menyimpan peta tematik untuk desa
            return response()->json($thematicMap, 201);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // PUT /villages/{id}/thematic-maps/{id} (Update Tema)
    public function updateThematicMap(Request $request, $id, $mapId)
    {
        $village = Village::find($id);
        if ($village) {
            $thematicMap = $village->thematic_maps()->find($mapId);
            if ($thematicMap) {
                $thematicMap->update($request->only(['name', 'points', 'geojson_data'])); // Update data peta tematik
                return response()->json($thematicMap);
            } else {
                return response()->json(['message' => 'Thematic map not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // DELETE /villages/{id}/thematic-maps/{id} (Delete Tema)
    public function deleteThematicMap($id, $mapId)
    {
        $village = Village::find($id);
        if ($village) {
            $thematicMap = $village->thematic_maps()->find($mapId);
            if ($thematicMap) {
                $thematicMap->delete(); // Menghapus peta tematik
                return response()->json(['message' => 'Thematic map deleted']);
            } else {
                return response()->json(['message' => 'Thematic map not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }
}
