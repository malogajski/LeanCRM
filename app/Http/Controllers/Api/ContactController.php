<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Contact Management
 */
class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all contacts
     *
     * Retrieve a paginated list of contacts for the authenticated user's team.
     * Supports filtering, sorting, and including related models.
     *
     * @group Contact Management
     * @authenticated
     * @queryParam filter[first_name] Filter contacts by first name. Example: John
     * @queryParam filter[last_name] Filter contacts by last name. Example: Doe
     * @queryParam filter[email] Filter contacts by email. Example: john@example.com
     * @queryParam filter[company_id] Filter contacts by company. Example: 1
     * @queryParam sort Sort contacts by field. Example: -created_at
     * @queryParam include Include related models. Example: company,deals,activities
     * @queryParam page[size] Number of items per page. Example: 15
     */
    public function index(): JsonResponse
    {
        $contacts = QueryBuilder::for(Contact::class)
            ->where('team_id', auth()->user()->team_id)
            ->allowedFilters(['first_name', 'last_name', 'email', 'company_id'])
            ->allowedSorts(['first_name', 'last_name', 'email', 'created_at', 'updated_at'])
            ->allowedIncludes(['company', 'deals', 'activities', 'notes'])
            ->defaultSort('-created_at')
            ->paginate();

        return response()->json($contacts);
    }

    /**
     * Create a new contact
     *
     * Store a new contact for the authenticated user's team.
     *
     * @group Contact Management
     * @authenticated
     * @bodyParam company_id integer optional The company ID this contact belongs to. Example: 1
     * @bodyParam first_name string required The contact's first name. Example: John
     * @bodyParam last_name string required The contact's last name. Example: Doe
     * @bodyParam email string optional The contact's email. Example: john.doe@company.com
     * @bodyParam phone string optional The contact's phone. Example: +1-555-0123
     * @bodyParam position string optional The contact's position/title. Example: CEO
     * @bodyParam notes string optional Additional notes about the contact. Example: Key decision maker
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
        ]);

        $contact = Contact::create(array_merge($validated, [
            'team_id' => auth()->user()->team_id,
        ]));

        return response()->json($contact, 201);
    }

    /**
     * Get a specific contact
     *
     * Retrieve a specific contact by ID with optional related models.
     *
     * @group Contact Management
     * @authenticated
     * @urlParam contact integer required The contact ID. Example: 1
     * @queryParam include Include related models. Example: company,deals,activities,notes
     */
    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('view', $contact);

        $contact = QueryBuilder::for(Contact::class)
            ->where('id', $contact->id)
            ->allowedIncludes(['company', 'deals', 'activities', 'notes'])
            ->firstOrFail();

        return response()->json($contact);
    }

    /**
     * Update a contact
     *
     * Update an existing contact's information.
     *
     * @group Contact Management
     * @authenticated
     * @urlParam contact integer required The contact ID. Example: 1
     * @bodyParam company_id integer optional The company ID. Example: 2
     * @bodyParam first_name string optional The contact's first name. Example: Jane
     * @bodyParam last_name string optional The contact's last name. Example: Smith
     * @bodyParam email string optional The contact's email. Example: jane.smith@company.com
     * @bodyParam phone string optional The contact's phone. Example: +1-555-9999
     * @bodyParam position string optional The contact's position. Example: CTO
     * @bodyParam notes string optional Additional notes. Example: Updated notes
     */
    public function update(Request $request, Contact $contact): JsonResponse
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'sometimes|required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
        ]);

        $contact->update($validated);

        return response()->json($contact);
    }

    /**
     * Delete a contact
     *
     * Delete a specific contact. This will also update any related deals to remove the contact reference.
     *
     * @group Contact Management
     * @authenticated
     * @urlParam contact integer required The contact ID. Example: 1
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}
