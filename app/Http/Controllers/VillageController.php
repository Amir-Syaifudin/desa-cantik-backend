<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function getAll()
    {
        $villages = Village::with('profile')
            ->orderBy('name')
            ->get()
            ->map(fn (Village $village) => $this->formatVillageResponse($village));

        return response()->json($villages);
    }

    public function getDetail($id)
    {
        $village = Village::with('profile')->find($id); // Mencari desa berdasarkan ID
        if ($village) {
            return response()->json($this->formatVillageResponse($village));
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

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

    public function toggleStatus($id)
    {
        $village = Village::find($id);
        if ($village) {
            $village->is_visible = ! $village->is_visible; // Toggle status aktif
            $village->save();

            return response()->json($village);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    private function formatVillageResponse(Village $village): array
    {
        $profile = $village->profile;
        $image = $profile?->thumbnail_url
            ?? $profile?->foto_url
            ?? $village->logo_url
            ?? 'https://placehold.co/800x600/1C6EA4/FFFFFF?text=Desa+Cantik';

        $area = $profile?->area;

        return [
            'id' => (string) $village->id,
            'name' => $village->name,
            'district' => $village->kecamatan,
            'regency' => $village->kabupaten,
            'province' => $village->provinsi,
            'population' => (int) ($profile?->population ?? 0),
            'status' => $village->is_visible ? 'Aktif' : 'Tidak Aktif',
            'image' => $image,
            'area' => $area !== null ? (float) $area : 1.0,
            'households' => (int) ($profile?->households ?? 0),
            'malePopulation' => (int) ($profile?->male_population ?? 0),
            'femalePopulation' => (int) ($profile?->female_population ?? 0),
        ];
    }
}
