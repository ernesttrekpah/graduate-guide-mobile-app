<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterestQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterestQuestionAdminController extends Controller
{
    public function index(Request $req)
    {
        $q      = trim((string) $req->query('q', ''));
        $areaId = $req->query('interest_area_id', null);

        $rows = InterestQuestion::query()
            ->when($q !== '', fn($qr) => $qr->where('text', 'like', "%{$q}%"))
            ->when($areaId !== null, function ($qr) use ($areaId) {
                if ($areaId === 'null' || $areaId === '') {
                    $qr->whereNull('interest_area_id');
                } else {
                    $qr->where('interest_area_id', (int) $areaId);
                }
            })
            ->orderBy('id', 'desc')
            ->get(['id', 'text', 'interest_area_id', 'is_active']);

        return response()->json(['data' => $rows]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'text'             => ['required', 'string', 'max:1000'],
            'interest_area_id' => ['nullable', 'integer', Rule::exists('interest_areas', 'id')],
            'is_active'        => ['nullable', 'boolean'],
        ]);
        $row = InterestQuestion::create([
            'text'             => $data['text'],
            'interest_area_id' => $data['interest_area_id'] ?? null,
            'is_active'        => $data['is_active'] ?? true,
        ]);
        return response()->json(['message' => 'Created', 'data' => $row], 201);
    }

    public function update(Request $req, InterestQuestion $question)
    {
        $data = $req->validate([
            'text'             => ['sometimes', 'string', 'max:1000'],
            'interest_area_id' => ['nullable', 'integer', Rule::exists('interest_areas', 'id')],
            'is_active'        => ['nullable', 'boolean'],
        ]);
        $question->update($data);
        return response()->json(['message' => 'Updated', 'data' => $question]);
    }

    public function destroy(InterestQuestion $question)
    {
        $question->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
