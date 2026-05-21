<?php

namespace App\Http\Controllers;

use App\Services\IdentitasPemotongJsonStore;
use Illuminate\Http\Request;

class IdentitasPemotongController extends Controller
{
    public function store(Request $request, IdentitasPemotongJsonStore $store)
    {
        $validated = $request->validate([
            'npwp' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'tanda_tangan' => ['nullable', 'file', 'mimes:png', 'max:2048'],
            'cap' => ['nullable', 'file', 'mimes:png', 'max:2048'],
        ]);

        $signaturePath = null;
        if ($request->hasFile('tanda_tangan')) {
            $signaturePath = $store->storeSignaturePng($request->file('tanda_tangan'));
        }

        $capPath = null;
        if ($request->hasFile('cap')) {
            $capPath = $store->storeCapPng($request->file('cap'));
        }

        $tanggal = $validated['tanggal'] ?? null;
        $tanggalNormalized = $tanggal ? date('Y-m-d', strtotime($tanggal)) : null;

        $store->create([
            'npwp' => $validated['npwp'],
            'nama' => $validated['nama'],
            'tanggal' => $tanggalNormalized,
            'tanda_tangan_path' => $signaturePath,
            'cap_path' => $capPath,
        ]);

        return redirect()->back()->with('success', 'Identitas pemotong berhasil ditambahkan!');
    }

    public function update(Request $request, string $id, IdentitasPemotongJsonStore $store)
    {
        $validated = $request->validate([
            'npwp' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'tanda_tangan' => ['nullable', 'file', 'mimes:png', 'max:2048'],
            'cap' => ['nullable', 'file', 'mimes:png', 'max:2048'],
        ]);

        $existing = $store->find($id);
        if (!$existing) {
            return redirect()->back()->with('error', 'Identitas pemotong tidak ditemukan.');
        }

        $payload = [
            'npwp' => $validated['npwp'],
            'nama' => $validated['nama'],
        ];

        if (array_key_exists('tanggal', $validated) && $validated['tanggal'] !== null) {
            $payload['tanggal'] = date('Y-m-d', strtotime($validated['tanggal']));
        }

        if ($request->hasFile('tanda_tangan')) {
            $newPath = $store->storeSignaturePng($request->file('tanda_tangan'));
            $payload['tanda_tangan_path'] = $newPath;
            $store->deleteSignatureIfExists($existing['tanda_tangan_path'] ?? null);
        }

        if ($request->hasFile('cap')) {
            $newPath = $store->storeCapPng($request->file('cap'));
            $payload['cap_path'] = $newPath;
            $store->deleteCapIfExists($existing['cap_path'] ?? null);
        }

        $store->update($id, $payload);

        return redirect()->back()->with('success', 'Identitas pemotong berhasil diperbarui!');
    }

    public function destroy(string $id, IdentitasPemotongJsonStore $store)
    {
        $existing = $store->find($id);
        if ($existing) {
            $store->deleteSignatureIfExists($existing['tanda_tangan_path'] ?? null);
            $store->deleteCapIfExists($existing['cap_path'] ?? null);
            $store->delete($id);
        }

        return redirect()->back()->with('success', 'Identitas pemotong berhasil dihapus.');
    }
}
