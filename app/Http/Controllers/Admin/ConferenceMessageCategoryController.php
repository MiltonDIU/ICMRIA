<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConferenceMessageCategory;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ConferenceMessageCategoryController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('conference_message_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ConferenceMessageCategory::withCount('messages')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.conference_message_categories.index', compact('categories'));
    }

    public function create()
    {
        abort_if(Gate::denies('conference_message_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.conference_message_categories.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('conference_message_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:conference_message_categories,name',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) $request->input('is_active', 1);

        ConferenceMessageCategory::create($validated);

        return redirect()->route('admin.conference-message-categories.index')->with('success', 'Message Category created successfully.');
    }

    public function edit(ConferenceMessageCategory $conferenceMessageCategory)
    {
        abort_if(Gate::denies('conference_message_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.conference_message_categories.edit', compact('conferenceMessageCategory'));
    }

    public function update(Request $request, ConferenceMessageCategory $conferenceMessageCategory)
    {
        abort_if(Gate::denies('conference_message_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:conference_message_categories,name,' . $conferenceMessageCategory->id,
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) $request->input('is_active', 0);

        $conferenceMessageCategory->update($validated);

        return redirect()->route('admin.conference-message-categories.index')->with('success', 'Message Category updated successfully.');
    }

    public function show(ConferenceMessageCategory $conferenceMessageCategory)
    {
        abort_if(Gate::denies('conference_message_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return redirect()->route('admin.conference-message-categories.edit', $conferenceMessageCategory->id);
    }

    public function destroy(ConferenceMessageCategory $conferenceMessageCategory)
    {
        abort_if(Gate::denies('conference_message_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $conferenceMessageCategory->delete();

        return redirect()->route('admin.conference-message-categories.index')->with('success', 'Message Category deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('conference_message_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ConferenceMessageCategory::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}