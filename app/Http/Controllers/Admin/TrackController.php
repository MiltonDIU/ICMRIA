<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackController extends Controller
{
    /** Roles whose holders may be put in charge of a track or sub-track. */
    private const CHAIR_ROLE_IDS = [5, 6];

    public function index()
    {
        abort_if(Gate::denies('track_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tracks = Track::withCount(['subTracks', 'papers'])
            ->with(['chairs.user', 'subTracks.chairs.user'])
            ->orderBy('name')
            ->get();

        return view('admin.tracks.index', compact('tracks'));
    }

    public function create()
    {
        abort_if(Gate::denies('track_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.tracks.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('track_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate(['name' => 'required|string|max:255|unique:tracks,name']);

        Track::create($request->only('name'));

        return redirect()->route('admin.tracks.index')->with('success', 'Track created successfully.');
    }

    public function edit(Track $track)
    {
        abort_if(Gate::denies('track_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $track->load(['subTracks.chairs', 'chairs']);

        return view('admin.tracks.edit', [
            'track' => $track,
            'chairCandidates' => $this->chairCandidates(),
            'trackChairIds' => $track->chairs->pluck('user_id')->all(),
        ]);
    }

    public function update(Request $request, Track $track)
    {
        abort_if(Gate::denies('track_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'required|string|max:255|unique:tracks,name,' . $track->id,
            'reviewers_per_paper' => 'nullable|integer|min:1|max:10',
            'max_papers_per_reviewer' => 'nullable|integer|min:1|max:100',
            'chairs' => 'nullable|array',
            'chairs.*' => 'integer|exists:users,id',
            'sub_track_chairs' => 'nullable|array',
            'sub_track_chairs.*' => 'nullable|array',
            'sub_track_chairs.*.*' => 'integer|exists:users,id',
        ]);

        DB::transaction(function () use ($request, $track) {
            $track->update($request->only('name', 'reviewers_per_paper', 'max_papers_per_reviewer'));

            $this->syncChairs($track->id, null, $request->input('chairs', []));

            $subTrackIds = $track->subTracks->pluck('id')->all();
            foreach ($subTrackIds as $subTrackId) {
                $this->syncChairs($track->id, $subTrackId, $request->input("sub_track_chairs.$subTrackId", []));
            }
        });

        return redirect()->route('admin.tracks.index')->with('success', 'Track updated successfully.');
    }

    public function destroy(Track $track)
    {
        abort_if(Gate::denies('track_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($track->papers()->exists()) {
            return back()->with('error', 'This track cannot be deleted: papers have already been submitted to it.');
        }

        $track->delete();

        return redirect()->route('admin.tracks.index')->with('success', 'Track deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('track_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Track::whereIn('id', request('ids'))->whereDoesntHave('papers')->delete();

        return response(null, 204);
    }

    /**
     * Replace the chairs of one scope. Passing a null sub-track id addresses the
     * track as a whole, which is what an overall Track Chair holds.
     *
     * @param array<int, int|string> $userIds
     */
    private function syncChairs(int $trackId, ?int $subTrackId, array $userIds): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        // An overall chair holds the Track Chair role (5), a sub-track chair the Sub-Track Chair role (6).
        $roleId = $subTrackId === null ? 5 : 6;

        $leaving = TrackAssignment::where('track_id', $trackId)
            ->where('sub_track_id', $subTrackId)
            ->where('role', 'chair')
            ->whereNotIn('user_id', $userIds ?: [0]);

        $removedUserIds = (clone $leaving)->pluck('user_id')->all();
        $leaving->delete();

        foreach ($userIds as $userId) {
            TrackAssignment::firstOrCreate([
                'user_id' => $userId,
                'track_id' => $trackId,
                'sub_track_id' => $subTrackId,
                'role' => 'chair',
            ]);

            // Placing someone is enough; the role that lets them act comes with it.
            User::find($userId)?->roles()->syncWithoutDetaching([$roleId]);
        }

        // Someone who no longer chairs anything of this kind loses the role that came with it.
        foreach ($removedUserIds as $userId) {
            $stillChairs = TrackAssignment::where('user_id', $userId)
                ->where('role', 'chair')
                ->when($subTrackId === null,
                    fn ($q) => $q->whereNull('sub_track_id'),
                    fn ($q) => $q->whereNotNull('sub_track_id'))
                ->exists();

            if (!$stillChairs) {
                User::find($userId)?->roles()->detach($roleId);
            }
        }
    }

    /**
     * Staff who could chair: anyone holding a role other than Author, so a teacher already
     * in the system, as a reviewer for instance, can be placed directly without first
     * being given a chair role under Users. Shown with their address, since a chair has
     * no profile.
     */
    private function chairCandidates()
    {
        return User::whereHas('roles', fn ($query) => $query->where('roles.id', '!=', 3))
            ->orderBy('name')
            ->get();
    }
}
