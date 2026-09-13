<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\ConferenceMessage;
use App\Models\ConferenceMessageCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConferenceMessageController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('conference_message_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $messages = ConferenceMessage::with('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.conference_messages.index', compact('messages'));
    }

    public function create()
    {
        abort_if(Gate::denies('conference_message_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ConferenceMessageCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.conference_messages.create', compact('categories'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('conference_message_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validateData($request);

        $message = ConferenceMessage::create($data);

        if ($request->input('photo', false)) {
            $message->addMedia(storage_path('tmp/uploads/' . $request->input('photo')))->toMediaCollection('photo');
        }

        return redirect()->route('admin.conference-messages.index')->with('success', 'Message created successfully.');
    }

    public function edit(ConferenceMessage $conferenceMessage)
    {
        abort_if(Gate::denies('conference_message_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ConferenceMessageCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.conference_messages.edit', compact('conferenceMessage', 'categories'));
    }

    public function update(Request $request, ConferenceMessage $conferenceMessage)
    {
        abort_if(Gate::denies('conference_message_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validateData($request);

        $conferenceMessage->update($data);

        if ($request->input('photo', false)) {
            if (!$conferenceMessage->photo || $request->input('photo') !== $conferenceMessage->photo->file_name) {
                $conferenceMessage->addMedia(storage_path('tmp/uploads/' . $request->input('photo')))->toMediaCollection('photo');
            }
        } elseif ($conferenceMessage->photo) {
            $conferenceMessage->photo->delete();
        }

        return redirect()->route('admin.conference-messages.index')->with('success', 'Message updated successfully.');
    }

    public function destroy(ConferenceMessage $conferenceMessage)
    {
        abort_if(Gate::denies('conference_message_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $conferenceMessage->delete();

        return redirect()->route('admin.conference-messages.index')->with('success', 'Message deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('conference_message_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ConferenceMessage::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'conference_message_category_id' => 'required|exists:conference_message_categories,id',
            'variant'                         => 'nullable|string|max:255',
            'person_name'                     => 'required|string|max:255',
            'designation'                     => 'nullable|string|max:255',
            'affiliation'                     => 'nullable|string|max:255',
            'message'                         => 'required|string',
            'profile_url'                     => 'nullable|url|max:255',
            'sort_order'                      => 'nullable|integer',
            'is_published'                    => 'nullable|boolean',
        ]);

        $validated['is_published'] = (bool) $request->input('is_published');
        $validated['sort_order'] = (int) $request->input('sort_order', 0);

        return $validated;
    }
}