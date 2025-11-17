<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\Http\Request;

class VillageProfileController extends Controller
{
    public function getProfile($id)
    {
        $village = Village::find($id); // Mencari desa berdasarkan ID
        if ($village) {
            return response()->json($village->profile); // Kembalikan profil desa
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $village = Village::find($id);
        if ($village) {
            // Validasi dan update data profil desa
            $validated = $request->validate([
                'name' => 'required|string',
                'location' => 'required|string',
                // Tambahkan validasi lainnya sesuai kebutuhan
            ]);

            $village->profile()->update($validated); // Update profil desa

            return response()->json($village->profile); // Kembalikan profil yang sudah diupdate
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }

    public function uploadLogo(Request $request, $id)
    {
        $village = Village::find($id);
        if ($village) {
            // Validasi file logo
            $request->validate([
                'logo' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Upload logo dan simpan
            $path = $request->file('logo')->store('public/village_logos');
            $village->profile->logo = $path; // Simpan path logo di profil desa
            $village->profile->save();

            return response()->json(['message' => 'Logo uploaded successfully', 'logo' => $path]);
        } else {
            return response()->json(['message' => 'Village not found'], 404);
        }
    }
}
