<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SavedProgrammeNoteRequest;
use App\Http\Requests\Api\V1\SavedProgrammeStoreRequest;
use App\Models\Programme;
use Illuminate\Http\Request;

class SavedProgrammeController extends Controller
{
    public function index(Request $req)
    {
        $programmes = $req->user()->savedProgrammes()
            ->with([
                'faculty:id,name,institution_id',
                'faculty.institution:id,name',
                'interestArea:id,name',
            ])
            ->orderBy('programmes.name')
            ->get();

        // Wrap each as { programme_id, note, programme: {...} }
        $items = $programmes->map(function ($p) {
            return [
                'programme_id' => $p->id,
                'note'         => $p->pivot->note ?? null,
                'programme'    => $p,
            ];
        });

        return response()->json(['data' => $items]);
    }

    public function store(SavedProgrammeStoreRequest $req)
    {
        $user = $req->user();
        $pid  = (int) $req->programme_id;

        // ensure programme exists
        $programme = Programme::findOrFail($pid);

        // attach (idempotent)
        $user->savedProgrammes()->syncWithoutDetaching([$pid => ['note' => $req->note]]);

        return response()->json(['message' => 'Saved', 'programme_id' => $pid], 201);
    }

    public function destroy(Request $req, Programme $programme)
    {
        $req->user()->savedProgrammes()->detach($programme->id);
        return response()->json(['message' => 'Removed']);
    }

    public function updateNote(SavedProgrammeNoteRequest $req, Programme $programme)
    {
        $req->user()->savedProgrammes()->updateExistingPivot($programme->id, ['note' => $req->note]);
        return response()->json(['message' => 'Note updated']);
    }
}
