<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $patients = Patient::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('mrn', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'q'));
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string', 'max:100'],
            'insurance_number' => ['nullable', 'string', 'max:100'],
        ]);

        $data['mrn'] = $this->generateMrn();
        $data['created_by'] = Auth::id();

        Patient::create($data);

        return redirect()
            ->route('patients.index')
            ->with('success', "Pasien {$data['name']} terdaftar dengan MRN {$data['mrn']}.");
    }

    protected function generateMrn(): string
    {
        $year = now()->year;
        $prefix = "MRN-{$year}-";

        $last = Patient::where('mrn', 'like', $prefix . '%')
            ->orderByDesc('mrn')
            ->value('mrn');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}