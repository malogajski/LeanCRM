<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Activity Management
 */
class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all activities
     *
     * Retrieve a paginated list of activities for the authenticated user's team.
     * Activities can be linked to any resource (deals, companies, contacts).
     *
     * @group Activity Management
     * @authenticated
     * @queryParam filter[type] Filter activities by type. Example: call
     * @queryParam filter[completed] Filter by completion status. Example: false
     * @queryParam filter[user_id] Filter activities by assigned user. Example: 1
     * @queryParam filter[subject_type] Filter by subject type. Example: App\Models\Deal
     * @queryParam filter[subject_id] Filter by subject ID. Example: 1
     * @queryParam sort Sort activities by field. Example: -due_date
     * @queryParam include Include related models. Example: user,subject
     * @queryParam page[size] Number of items per page. Example: 15
     */
    public function index(): JsonResponse
    {
        $activities = QueryBuilder::for(Activity::class)
            ->where('team_id', auth()->user()->team_id)
            ->allowedFilters(['type', 'completed', 'user_id', 'subject_type', 'subject_id'])
            ->allowedSorts(['title', 'type', 'due_date', 'completed', 'created_at', 'updated_at'])
            ->allowedIncludes(['user', 'subject'])
            ->defaultSort('-created_at')
            ->paginate();

        return response()->json($activities);
    }

    /**
     * Create a new activity
     *
     * Store a new activity for the authenticated user's team.
     * Activities can be linked to deals, companies, or contacts.
     *
     * @group Activity Management
     * @authenticated
     * @bodyParam subject_type string required The type of resource this activity is linked to. Example: App\Models\Deal
     * @bodyParam subject_id integer required The ID of the resource this activity is linked to. Example: 1
     * @bodyParam type string required The activity type (call, email, meeting, task). Example: call
     * @bodyParam title string required The activity title. Example: Follow-up call with client
     * @bodyParam description string optional Activity description. Example: Discuss project timeline
     * @bodyParam due_date string optional Due date and time (Y-m-d H:i:s format). Example: 2024-12-31 14:30:00
     * @bodyParam completed boolean optional Completion status. Example: false
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => 'required|string|in:App\Models\Deal,App\Models\Company,App\Models\Contact',
            'subject_id'   => 'required|integer',
            'type'         => 'required|string|in:call,email,meeting,task',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'due_date'     => 'nullable|date',
            'completed'    => 'boolean',
        ]);

        $activity = Activity::create(array_merge($validated, [
            'team_id'   => auth()->user()->team_id,
            'user_id'   => auth()->id(),
            'completed' => $validated['completed'] ?? false,
        ]));

        return response()->json($activity, 201);
    }

    /**
     * Get a specific activity
     *
     * Retrieve a specific activity by ID with optional related models.
     *
     * @group Activity Management
     * @authenticated
     * @urlParam activity integer required The activity ID. Example: 1
     * @queryParam include Include related models. Example: user,subject
     */
    public function show(Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);

        $activity = QueryBuilder::for(Activity::class)
            ->where('id', $activity->id)
            ->allowedIncludes(['user', 'subject'])
            ->firstOrFail();

        return response()->json($activity);
    }

    /**
     * Update an activity
     *
     * Update an existing activity's information.
     *
     * @group Activity Management
     * @authenticated
     * @urlParam activity integer required The activity ID. Example: 1
     * @bodyParam type string optional The activity type. Example: meeting
     * @bodyParam title string optional The activity title. Example: Updated meeting title
     * @bodyParam description string optional Activity description. Example: Updated description
     * @bodyParam due_date string optional Due date and time. Example: 2024-12-31 16:00:00
     * @bodyParam completed boolean optional Completion status. Example: true
     */
    public function update(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'type'        => 'sometimes|required|string|in:call,email,meeting,task',
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'completed'   => 'boolean',
        ]);

        $activity->update($validated);

        return response()->json($activity);
    }

    /**
     * Delete an activity
     *
     * Delete a specific activity.
     *
     * @group Activity Management
     * @authenticated
     * @urlParam activity integer required The activity ID. Example: 1
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully']);
    }
}
