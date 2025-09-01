<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Company Management
 */
class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all companies
     *
     * Retrieve a paginated list of companies for the authenticated user's team.
     * Supports filtering, sorting, and including related models.
     *
     * @group Company Management
     * @authenticated
     * @queryParam filter[name] Filter companies by name. Example: Acme
     * @queryParam sort Sort companies by field. Example: -created_at
     * @queryParam include Include related models. Example: contacts,deals
     * @queryParam page[size] Number of items per page. Example: 15
     * @queryParam page[number] Page number. Example: 1
     */
    public function index(): JsonResponse
    {
        $companies = QueryBuilder::for(Company::class)
            ->where('team_id', auth()->user()->team_id)
            ->allowedFilters(['name', 'email'])
            ->allowedSorts(['name', 'created_at', 'updated_at'])
            ->allowedIncludes(['contacts', 'deals'])
            ->defaultSort('-created_at')
            ->paginate();

        return response()->json($companies);
    }

    /**
     * Create a new company
     *
     * Store a new company for the authenticated user's team.
     *
     * @group Company Management
     * @authenticated
     * @bodyParam name string required The company name. Example: Acme Corporation
     * @bodyParam email string optional The company email. Example: contact@acme.com
     * @bodyParam phone string optional The company phone. Example: +1-555-0123
     * @bodyParam address string optional The company address. Example: 123 Business St, City, State 12345
     * @bodyParam website string optional The company website. Example: https://acme.com
     * @bodyParam notes string optional Additional notes about the company. Example: Important client since 2020
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'notes'   => 'nullable|string',
        ]);

        $company = Company::create(array_merge($validated, [
            'team_id' => auth()->user()->team_id,
        ]));

        return response()->json($company, 201);
    }

    /**
     * Get a specific company
     *
     * Retrieve a specific company by ID with optional related models.
     *
     * @group Company Management
     * @authenticated
     * @urlParam company integer required The company ID. Example: 1
     * @queryParam include Include related models. Example: contacts,deals,activities,notes
     */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $company = QueryBuilder::for(Company::class)
            ->where('id', $company->id)
            ->allowedIncludes(['contacts', 'deals', 'activities', 'notes'])
            ->firstOrFail();

        return response()->json($company);
    }

    /**
     * Update a company
     *
     * Update an existing company's information.
     *
     * @group Company Management
     * @authenticated
     * @urlParam company integer required The company ID. Example: 1
     * @bodyParam name string optional The company name. Example: Updated Corp Name
     * @bodyParam email string optional The company email. Example: newemail@company.com
     * @bodyParam phone string optional The company phone. Example: +1-555-9999
     * @bodyParam address string optional The company address. Example: New address
     * @bodyParam website string optional The company website. Example: https://newwebsite.com
     * @bodyParam notes string optional Additional notes. Example: Updated notes
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'notes'   => 'nullable|string',
        ]);

        $company->update($validated);

        return response()->json($company);
    }

    /**
     * Delete a company
     *
     * Delete a specific company. This will also delete all related contacts and deals.
     *
     * @group Company Management
     * @authenticated
     * @urlParam company integer required The company ID. Example: 1
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }
}
