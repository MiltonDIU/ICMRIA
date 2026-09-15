<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleCategory;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ScheduleCategoryController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('schedule_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ScheduleCategory::withCount('schedules')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.schedule_categories.index', compact('categories'));
    }

    public function create()
    {
        abort_if(Gate::denies('schedule_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.schedule_categories.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('schedule_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:schedule_categories,name',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['color'] = $validated['color'] ?: '#00396B';
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) $request->input('is_active', 1);

        ScheduleCategory::create($validated);

        return redirect()->route('admin.schedule-categories.index')
            ->with('success', 'Schedule Session Category created successfully.');
    }

    public function edit(ScheduleCategory $scheduleCategory)
    {
        abort_if(Gate::denies('schedule_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.schedule_categories.edit', compact('scheduleCategory'));
    }

    public function update(Request $request, ScheduleCategory $scheduleCategory)
    {
        abort_if(Gate::denies('schedule_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:schedule_categories,name,' . $scheduleCategory->id,
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['color'] = $validated['color'] ?: '#00396B';
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) $request->input('is_active', 0);

        $scheduleCategory->update($validated);

        return redirect()->route('admin.schedule-categories.index')
            ->with('success', 'Schedule Session Category updated successfully.');
    }

    public function show(ScheduleCategory $scheduleCategory)
    {
        abort_if(Gate::denies('schedule_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return redirect()->route('admin.schedule-categories.edit', $scheduleCategory->id);
    }

    public function destroy(ScheduleCategory $scheduleCategory)
    {
        abort_if(Gate::denies('schedule_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scheduleCategory->delete();

        return redirect()->route('admin.schedule-categories.index')
            ->with('success', 'Schedule Session Category deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('schedule_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ScheduleCategory::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
