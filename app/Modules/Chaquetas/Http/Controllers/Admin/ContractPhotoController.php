<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\ContractPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractPhotoController extends Controller
{
    public function store(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'photos'   => 'required|array|max:20',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:8192',
            'caption'  => 'nullable|string|max:160',
            'phase'    => 'required|in:proceso,final,diseno',
        ]);

        foreach ($request->file('photos') as $file) {
            $path = $file->store('contratos-fotos', 'public');
            $contract->photos()->create([
                'path'    => $path,
                'caption' => $data['caption'] ?? null,
                'phase'   => $data['phase'],
            ]);
        }

        return back()->with('ok', 'Fotos subidas.');
    }

    public function destroy(Contract $contract, ContractPhoto $photo)
    {
        abort_unless($photo->contract_id === $contract->id, 404);
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('ok', 'Foto eliminada.');
    }
}
