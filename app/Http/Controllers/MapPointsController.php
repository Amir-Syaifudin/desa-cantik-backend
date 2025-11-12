<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThematicMap; // Pastikan model ThematicMap digunakan
use App\Models\MapPoint;    // Pastikan model MapPoint ada

class MapPointsController extends Controller
{
    // POST /thematic-maps/{id}/points (Create Titik Peta)
    public function createMapPoint(Request $request, $id)
    {
        $thematicMap = ThematicMap::find($id);
        if ($thematicMap) {
            $validated = $request->validate([
                'name' => 'required|string',
                'coordinates' => 'required|array', // Misalnya koordinat titik
                // Validasi lainnya jika diperlukan
            ]);

            $mapPoint = $thematicMap->mapPoints()->create($validated);
            return response()->json($mapPoint, 201); // Kembalikan data titik peta yang baru
        } else {
            return response()->json(['message' => 'Thematic map not found'], 404);
        }
    }

    // PUT /thematic-maps/{id}/points/{pointId} (Update Titik)
    public function updateMapPoint(Request $request, $id, $pointId)
    {
        $thematicMap = ThematicMap::find($id);
        if ($thematicMap) {
            $mapPoint = $thematicMap->mapPoints()->find($pointId);
            if ($mapPoint) {
                $mapPoint->update($request->only(['name', 'coordinates'])); // Update titik peta
                return response()->json($mapPoint);
            } else {
                return response()->json(['message' => 'Map point not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Thematic map not found'], 404);
        }
    }

    // DELETE /thematic-maps/{id}/points/{pointId} (Delete Titik)
    public function deleteMapPoint($id, $pointId)
    {
        $thematicMap = ThematicMap::find($id);
        if ($thematicMap) {
            $mapPoint = $thematicMap->mapPoints()->find($pointId);
            if ($mapPoint) {
                $mapPoint->delete(); // Menghapus titik peta
                return response()->json(['message' => 'Map point deleted']);
            } else {
                return response()->json(['message' => 'Map point not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Thematic map not found'], 404);
        }
    }

    // POST /thematic-maps/{id}/points/{pointId}/image (Upload Gambar Titik)
    public function uploadMapPointImage(Request $request, $id, $pointId)
    {
        $thematicMap = ThematicMap::find($id);
        if ($thematicMap) {
            $mapPoint = $thematicMap->mapPoints()->find($pointId);
            if ($mapPoint) {
                // Validasi file gambar
                $request->validate([
                    'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                ]);

                // Simpan gambar
                $imagePath = $request->file('image')->store('public/map_points_images');
                $mapPoint->image = $imagePath; // Simpan path gambar ke titik peta
                $mapPoint->save();

                return response()->json(['message' => 'Image uploaded successfully', 'image' => $imagePath]);
            } else {
                return response()->json(['message' => 'Map point not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Thematic map not found'], 404);
        }
    }
}
