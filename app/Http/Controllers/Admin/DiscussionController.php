<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperDiscussionMessage;
use App\Services\Discussion;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Posting to a paper's internal discussion. Chairs post from the decision page and
 * reviewers from their review page; Discussion decides who may do either.
 */
class DiscussionController extends Controller
{
    public function store(Request $request, Paper $paper)
    {
        $paper->load(['decision', 'authors', 'conflicts', 'reviewerAssignments.evaluation']);

        abort_unless(Discussion::canTakePart($paper, auth()->user()), Response::HTTP_FORBIDDEN,
            '403 Forbidden - you are not part of the discussion of this paper.');

        if (!$paper->discussion_opened_at) {
            return back()->with('error', 'No discussion has been opened for this paper.');
        }

        if ($paper->decision?->isApproved()) {
            return back()->with('error', 'The decision on this paper has been approved, so the discussion is closed.');
        }

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ], [
            'body.required' => 'Write a message before posting.',
        ]);

        PaperDiscussionMessage::create([
            'paper_id' => $paper->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Message posted.');
    }
}
