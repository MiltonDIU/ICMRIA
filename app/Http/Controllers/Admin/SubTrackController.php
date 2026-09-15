<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubTrack;
use App\Models\Track;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubTrackController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('sub_track_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subTracks = SubTrack::with(['track', 'chairs.user'])
            ->withCount('papers')
            ->join('tracks', 'tracks.id', '=', 'sub_tracks.track_id')
            ->orderBy('tracks.name')
            ->orderBy('sub_tracks.name')
            ->select('sub_tracks.*')
            ->get();

        return view('admin.sub_tracks.index', compact('subTracks'));
    }

    public function create()
    {
        abort_if(Gate::denies('sub_track_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sub_tracks.create', ['tracks' => Track::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('sub_track_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate($this->rules());

        SubTrack::create($request->only('track_id', 'name'));

        return redirect()->route('admin.sub-tracks.index')->with('success', 'Sub-track created successfully.');
    }

    public function edit(SubTrack $subTrack)
    {
        abort_if(Gate::denies('sub_track_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sub_tracks.edit', [
            'subTrack' => $subTrack,
            'tracks' => Track::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SubTrack $subTrack)
    {
        abort_if(Gate::denies('sub_track_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate($this->rules($subTrack->id));

        $subTrack->update($request->only('track_id', 'name'));

        return redirect()->route('admin.sub-tracks.index')->with('success', 'Sub-track updated successfully.');
    }

    public function destroy(SubTrack $subTrack)
    {
        abort_if(Gate::denies('sub_track_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($subTrack->papers()->exists()) {
            return back()->with('error', 'This sub-track cannot be deleted: papers have already been submitted to it.');
        }

        $subTrack->delete();

        return redirect()->route('admin.sub-tracks.index')->with('success', 'Sub-track deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('sub_track_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        SubTrack::whereIn('id', request('ids'))->whereDoesntHave('papers')->delete();

        return response(null, 204);
    }

    /** Names only have to be unique inside their own track. */
    private function rules(?int $ignoreId = null): array
    {
        $unique = 'unique:sub_tracks,name,' . ($ignoreId ?? 'NULL') . ',id,track_id,' . request('track_id');

        return [
            'track_id' => 'required|exists:tracks,id',
            'name' => ['required', 'string', 'max:255', $unique],
        ];
    }
}
