<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        return view('companies.index', [
            'companies' => Company::withCount('products')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('companies.create', [
            'company' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Company::create($this->validatedCompany($request));

        return redirect()
            ->route('companies.index')
            ->with('status', 'Company created successfully.');
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->validatedCompany($request, $company));

        return redirect()
            ->route('companies.index')
            ->with('status', 'Company updated successfully.');
    }

    private function validatedCompany(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('companies', 'name')->ignore($company)],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
