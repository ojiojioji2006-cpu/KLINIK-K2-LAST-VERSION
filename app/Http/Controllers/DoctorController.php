<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $doctors = Doctor::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('specialty', 'like', "%{$q}%")
                        ->orWhere('sip', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('doctors.index', compact('doctors', 'q'));
    }

    public function create(): View
    {
        return view('doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sip' => ['nullable', 'string', 'max:50'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Doctor::create($data);

        return redirect()
            ->route('doctors.index')
            ->with('success', "Dokter {$data['name']} berhasil ditambahkan.");
    }
}