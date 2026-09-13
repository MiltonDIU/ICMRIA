<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyScheduleRequest;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\ScheduleCategory;
use App\Models\Speaker;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('schedule_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedules = Schedule::with(['speakers', 'speaker', 'scheduleCategory'])->orderBy('day_number')->orderBy('start_time')->get();

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        abort_if(Gate::denies('schedule_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speakers = Speaker::with('speakerType')
            ->orderBy('serial', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->mapWithKeys(function ($s) {
                $type = $s->speakerType ? ' (' . $s->speakerType->title . ')' : '';
                $affil = $s->affiliation ? ' — ' . $s->affiliation : '';
                return [$s->id => $s->name . $type . $affil];
            });

        $scheduleCategories = ScheduleCategory::where('is_active', 1)->orderBy('sort_order')->pluck('name', 'id');

        return view('admin.schedules.create', compact('speakers', 'scheduleCategories'));
    }

    public function store(StoreScheduleRequest $request)
    {
        $data = $request->only('day_number', 'start_time', 'title', 'subtitle', 'schedule_category_id', 'is_workshop', 'total_seat', 'event_session', 'is_active');
        if (!isset($data['is_active'])) {
            $data['is_active'] = '1';
        }

        $speakers = $request->input('speaker_id');
        $data['speaker_id'] = !empty($speakers) && is_array($speakers) ? $speakers[0] : (is_numeric($speakers) ? $speakers : null);

        if ($request->input('is_workshop') == '1') {
            $benefits = [];
            if ($request->has('benefit_title')) {
                foreach ($request->input('benefit_title') as $key => $title) {
                    $benefits[] = [
                        'title' => $title,
                        'link'  => $request->input('benefit_link')[$key] ?? '',
                    ];
                }
            }
            $data['benefits'] = json_encode($benefits);
            $data['tools']    = json_encode($request->input('tools'));
            $data['about']    = $request->input('about');
        }

        $schedule = Schedule::create($data);

        if (!empty($speakers)) {
            $schedule->speakers()->sync((array)$speakers);
        }

        return redirect()->route('admin.schedules.index');
    }

    public function edit(Schedule $schedule)
    {
        abort_if(Gate::denies('schedule_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speakers = Speaker::with('speakerType')
            ->orderBy('serial', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->mapWithKeys(function ($s) {
                $type = $s->speakerType ? ' (' . $s->speakerType->title . ')' : '';
                $affil = $s->affiliation ? ' — ' . $s->affiliation : '';
                return [$s->id => $s->name . $type . $affil];
            });

        $scheduleCategories = ScheduleCategory::where('is_active', 1)
            ->orWhere('id', $schedule->schedule_category_id)
            ->orderBy('sort_order')
            ->pluck('name', 'id');
        $schedule->load('speakers', 'speaker', 'scheduleCategory');

        return view('admin.schedules.edit', compact('speakers', 'scheduleCategories', 'schedule'));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $data = $request->only('day_number', 'start_time', 'title', 'subtitle', 'schedule_category_id', 'event_session', 'is_workshop', 'total_seat', 'is_active');

        $speakers = $request->input('speaker_id');
        $data['speaker_id'] = !empty($speakers) && is_array($speakers) ? $speakers[0] : (is_numeric($speakers) ? $speakers : null);

        if ($request->input('is_workshop') == '1') {
            $benefits = [];
            if ($request->has('benefit_title')) {
                foreach ($request->input('benefit_title') as $key => $title) {
                    $benefits[] = [
                        'title' => $title,
                        'link'  => $request->input('benefit_link')[$key] ?? '',
                    ];
                }
            }
            $data['benefits'] = json_encode($benefits);
            $data['tools']    = json_encode($request->input('tools'));
            $data['about']    = $request->input('about');
        }

        $schedule->update($data);

        if (!empty($speakers)) {
            $schedule->speakers()->sync((array)$speakers);
        } else {
            $schedule->speakers()->detach();
        }

        return redirect()->route('admin.schedules.index');
    }

    public function show(Schedule $schedule)
    {
        abort_if(Gate::denies('schedule_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedule->load('speakers', 'speaker', 'scheduleCategory');

        return view('admin.schedules.show', compact('schedule'));
    }

    public function destroy(Schedule $schedule)
    {
        abort_if(Gate::denies('schedule_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $schedule->delete();

        return back();
    }

    public function massDestroy(MassDestroyScheduleRequest $request)
    {
        Schedule::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
