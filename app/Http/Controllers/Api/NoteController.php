<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Note Management
 */
class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all notes
     *
     * Retrieve a paginated list of notes for the authenticated user's team.
     * Notes can be attached to any resource (deals, companies, contacts).
     *
     * @group Note Management
     * @authenticated
     * @queryParam filter[user_id] Filter notes by author. Example: 1
     * @queryParam filter[notable_type] Filter by resource type. Example: App\Models\Deal
     * @queryParam filter[notable_id] Filter by resource ID. Example: 1
     * @queryParam sort Sort notes by field. Example: -created_at
     * @queryParam include Include related models. Example: user,notable
     * @queryParam page[size] Number of items per page. Example: 15
     */
    public function index(): JsonResponse
    {
        $notes = QueryBuilder::for(Note::class)
            ->where('team_id', auth()->user()->team_id)
            ->allowedFilters(['user_id', 'notable_type', 'notable_id'])
            ->allowedSorts(['created_at', 'updated_at'])
            ->allowedIncludes(['user', 'notable'])
            ->defaultSort('-created_at')
            ->paginate();

        return response()->json($notes);
    }

    /**
     * Create a new note
     *
     * Store a new note for the authenticated user's team.
     * Notes can be attached to deals, companies, or contacts.
     *
     * @group Note Management
     * @authenticated
     * @bodyParam notable_type string required The type of resource this note is attached to. Example: App\Models\Deal
     * @bodyParam notable_id integer required The ID of the resource this note is attached to. Example: 1
     * @bodyParam content string required The note content. Example: Client expressed interest in premium package
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notable_type' => 'required|string|in:App\Models\Deal,App\Models\Company,App\Models\Contact',
            'notable_id'   => 'required|integer',
            'content'      => 'required|string',
        ]);

        $note = Note::create(array_merge($validated, [
            'team_id' => auth()->user()->team_id,
            'user_id' => auth()->id(),
        ]));

        return response()->json($note, 201);
    }

    /**
     * Get a specific note
     *
     * Retrieve a specific note by ID with optional related models.
     *
     * @group Note Management
     * @authenticated
     * @urlParam note integer required The note ID. Example: 1
     * @queryParam include Include related models. Example: user,notable
     */
    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        $note = QueryBuilder::for(Note::class)
            ->where('id', $note->id)
            ->allowedIncludes(['user', 'notable'])
            ->firstOrFail();

        return response()->json($note);
    }

    /**
     * Update a note
     *
     * Update an existing note's content.
     *
     * @group Note Management
     * @authenticated
     * @urlParam note integer required The note ID. Example: 1
     * @bodyParam content string required The updated note content. Example: Updated note content with more details
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $note->update($validated);

        return response()->json($note);
    }

    /**
     * Delete a note
     *
     * Delete a specific note.
     *
     * @group Note Management
     * @authenticated
     * @urlParam note integer required The note ID. Example: 1
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
