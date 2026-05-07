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
            // FIX: "name" adicionado na validação para ser salvo no banco
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|string|email|max:255',
            'institution'   => 'sometimes|string|max:255',
            'phone'         => 'sometimes|nullable|string|max:50',
            'bio'           => 'sometimes|nullable|string',
            'profile_photo' => 'sometimes|nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('profile_photo')) {
            // Remove foto antiga se existir e for um caminho local (não Cloudinary)
            if ($user->profile_photo && !str_starts_with($user->profile_photo, 'http')) {
                $oldPath = ltrim(str_replace('/storage', '', $user->profile_photo), '/');
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('profile_photo');
            $path = $file->store('secretary_photos', 'public');

            // FIX: Salva a URL pública completa no banco
            // Storage::url() retorna '/storage/secretary_photos/arquivo.jpg'
            // No Railway, certifique-se de rodar: php artisan storage:link
            // Ou migre para Cloudinary para persistência entre deploys
            $data['profile_photo'] = Storage::url($path);
        }

        $user->fill($data);
        $user->save();

        // FIX: Retorna o user atualizado dentro de "data"
        // O front espera resp.data para normalizar o usuário (normalizeUser)
        return response()->json([
            'message' => 'Perfil atualizado com sucesso',
            'data'    => $user,
        ]);
    }
}
