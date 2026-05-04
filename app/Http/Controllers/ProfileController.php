<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'institution' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'bio' => 'sometimes|nullable|string',
            'profile_photo' => 'sometimes|nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $file->store('secretary_photos', 'public');
            // gerar URL pública (requer php artisan storage:link)
            $publicUrl = Storage::url($path);
            $data['profile_photo'] = $publicUrl;
        }

        $user->fill($data);
        $user->save();

        return response()->json(['message' => 'Perfil atualizado', 'data' => $user]);
    }
}
