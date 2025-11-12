<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Village; 

class GeospatialDataController extends Controller
{
    // GET /villages/{id}/geospatial (Get Data GeoJSON)
    public function getGeoSpatialData($id)
    {
        $village = Village::find($id);
        if ($village) {
            return response()->json($village->geospatial_data); // Mengembalikan data geospasial dalam format GeoJSON
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // POST /villages/{id}/geospatial (Create Geospatial Data)
    public function createGeoSpatialData(Request $request, $id)
    {
        $village = Village::find($id);
        if ($village) {
            $validated = $request->validate([
                'data' => 'required|json', // Validasi format data GeoJSON
            ]);
            
            // Menambahkan data geospasial ke desa
            $village->geospatial_data()->create([
                'data' => $validated['data']
            ]);

            return response()->json(['message' => 'Geospatial data created successfully']);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // PUT /villages/{id}/geospatial/{geoId} (Update Geospatial Data)
    public function updateGeoSpatialData(Request $request, $id, $geoId)
    {
        $village = Village::find($id);
        if ($village) {
            $geospatialData = $village->geospatial_data()->find($geoId);
            if ($geospatialData) {
                $geospatialData->update($request->only('data')); // Update data geospasial
                return response()->json($geospatialData);
            } else {
                return response()->json(['message' => 'Geospatial data not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    // DELETE /villages/{id}/geospatial/{geoId} (Delete Geospatial Data)
    public function deleteGeoSpatialData($id, $geoId)
    {
        $village = Village::find($id);
        if ($village) {
            $geospatialData = $village->geospatial_data()->find($geoId);
            if ($geospatialData) {
                $geospatialData->delete(); // Menghapus data geospasial
                return response()->json(['message' => 'Geospatial data deleted']);
            } else {
                return response()->json(['message' => 'Geospatial data not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }
}
