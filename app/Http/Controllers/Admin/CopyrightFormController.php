<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * The official blank Copyright Transfer Form (requirement document, Phase 6). An
 * administrator uploads it once; accepted authors download it from their paper page,
 * sign it and upload it back with the camera-ready manuscript.
 */
class CopyrightFormController extends Controller
{
    private const PATH_KEY = 'copyright_form_template';
    private const NAME_KEY = 'copyright_form_template_name';

    /** Whether a blank form has been uploaded and is on disk. */
    public static function available(): bool
    {
        $path = Setting::where('key', self::PATH_KEY)->value('value');

        return $path && Storage::exists($path);
    }

    public function upload(Request $request)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'copyright_form' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ], [
            'copyright_form.required' => 'Choose the blank Copyright Transfer Form to upload.',
            'copyright_form.mimes' => 'The form must be a PDF or Word document.',
        ]);

        $this->deleteCurrent();

        $file = $request->file('copyright_form');
        Setting::updateOrCreate(['key' => self::PATH_KEY], ['value' => $file->store('templates')]);
        Setting::updateOrCreate(['key' => self::NAME_KEY], ['value' => $file->getClientOriginalName()]);

        return back()->with('success', 'Blank Copyright Transfer Form uploaded. Accepted authors can now download it.');
    }

    public function remove()
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->deleteCurrent();
        Setting::whereIn('key', [self::PATH_KEY, self::NAME_KEY])->delete();

        return back()->with('success', 'Blank Copyright Transfer Form removed.');
    }

    /** Any signed-in user may download the blank form; it holds nothing private. */
    public function download()
    {
        abort_unless(self::available(), Response::HTTP_NOT_FOUND, 'The Copyright Transfer Form has not been published yet.');

        $path = Setting::where('key', self::PATH_KEY)->value('value');
        $name = Setting::where('key', self::NAME_KEY)->value('value');
        $extension = pathinfo((string) ($name ?: $path), PATHINFO_EXTENSION);

        return Storage::download($path, 'ICMRIA-2027-Copyright-Transfer-Form' . ($extension ? '.' . $extension : ''));
    }

    private function deleteCurrent(): void
    {
        $path = Setting::where('key', self::PATH_KEY)->value('value');

        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
