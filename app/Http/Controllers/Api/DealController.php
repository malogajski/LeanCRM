<?php

namespace App\Http\Controllers\Api;

use App\Events\DealStageChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\DealStoreRequest;
use App\Http\Requests\DealUpdateRequest;
use App\Http\Resources\DealResource;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Deal Management
 */
class DealController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Deal::class, 'deal');
    }

    /**
     * Get all deals
     *
     * Retrieve a paginated list of deals for the authenticated user's team.
     * Supports filtering, sorting, and including related models.
     *
     * @group Deal Management
     * @authenticated
     * @security sanctum
     * @queryParam filter[stage] Filter deals by stage. Example: prospect
     * @queryParam filter[user_id] Filter deals by user. Example: 1
     * @queryParam sort Sort deals by field. Example: -created_at
     * @queryParam include Include related models. Example: company,contact,user
     * @queryParam page[size] Number of items per page. Example: 15
     * @queryParam page[number] Page number. Example: 1
     */
    public function index(): AnonymousResourceCollection
    {
        $deals = QueryBuilder::for(Deal::class)
            ->forTeam(auth()->user()->team_id)
            ->allowedFilters(['stage', 'user_id', 'company_id', 'contact_id'])
            ->allowedSorts(['title', 'amount', 'stage', 'expected_close_date', 'created_at', 'updated_at'])
            ->allowedIncludes(['company', 'contact', 'user'])
            ->defaultSort('-created_at')
            ->paginate();

        return DealResource::collection($deals);
    }

    /**
     * Create a new deal
     *
     * Store a new deal for the authenticated user's team.
     *
     * @group Deal Management
     * @authenticated
     * @bodyParam company_id integer optional The company ID. Example: 1
     * @bodyParam contact_id integer optional The contact ID. Example: 1
     * @bodyParam title string required The deal title. Example: New Website Project
     * @bodyParam description string optional The deal description. Example: Complete website redesign
     * @bodyParam amount number required The deal amount. Example: 5000.00
     * @bodyParam stage string required The deal stage. Example: prospect
     * @bodyParam expected_close_date string optional Expected close date (Y-m-d format). Example: 2024-12-31
     */
    public function store(DealStoreRequest $request): DealResource
    {
        $validated = $request->validated();
        $validated['team_id'] = auth()->user()->team_id;
        $validated['user_id'] = auth()->id();

        $deal = Deal::create($validated);

        return new DealResource($deal->load(['company', 'contact', 'user']));
    }

    /**
     * Get a specific deal
     *
     * Retrieve a specific deal by ID with optional related models.
     *
     * @group Deal Management
     * @authenticated
     * @urlParam deal integer required The deal ID. Example: 1
     * @queryParam include Include related models. Example: company,contact,user,activities,notes
     */
    public function show(Deal $deal): DealResource
    {
        $deal = QueryBuilder::for(Deal::class)
            ->where('id', $deal->id)
            ->allowedIncludes(['company', 'contact', 'user', 'activities', 'notes'])
            ->firstOrFail();

        return new DealResource($deal);
    }

    /**
     * Update a deal
     *
     * Update an existing deal. When the stage changes, a DealStageChanged event is fired.
     *
     * @group Deal Management
     * @authenticated
     * @urlParam deal integer required The deal ID. Example: 1
     * @bodyParam company_id integer optional The company ID. Example: 1
     * @bodyParam contact_id integer optional The contact ID. Example: 1
     * @bodyParam title string optional The deal title. Example: Updated Website Project
     * @bodyParam description string optional The deal description. Example: Updated description
     * @bodyParam amount number optional The deal amount. Example: 7500.00
     * @bodyParam stage string optional The deal stage. Example: qualified
     * @bodyParam expected_close_date string optional Expected close date (Y-m-d format). Example: 2024-12-31
     */
    public function update(DealUpdateRequest $request, Deal $deal): DealResource
    {
        $oldStage = $deal->stage;
        $deal->update($request->validated());

        if ($deal->wasChanged('stage')) {
            event(new DealStageChanged($deal, $oldStage, $deal->stage));
        }

        return new DealResource($deal->load(['company', 'contact', 'user']));
    }

    /**
     * Delete a deal
     *
     * Delete a specific deal. Only the deal owner or admin can delete deals.
     *
     * @group Deal Management
     * @authenticated
     * @urlParam deal integer required The deal ID. Example: 1
     */
    public function destroy(Deal $deal): JsonResponse
    {
        $deal->delete();

        return response()->json(['message' => 'Deal deleted successfully']);
    }
}
