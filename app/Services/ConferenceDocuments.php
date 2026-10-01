<?php

namespace App\Services;

class ConferenceDocuments
{
    /**
     * Get the public asset URL for a document.
     */
    public static function url(string $key = 'template_word'): string
    {
        $path = config("conference_documents.{$key}.path");
        if (!$path) {
            return '#';
        }

        return asset($path);
    }

    /**
     * Get the suggested download file name.
     */
    public static function filename(string $key = 'template_word'): string
    {
        return (string) (config("conference_documents.{$key}.filename")
            ?: basename((string) config("conference_documents.{$key}.path", 'download.docx')));
    }

    /**
     * Check if the document physically exists in the public directory.
     */
    public static function exists(string $key = 'template_word'): bool
    {
        $path = config("conference_documents.{$key}.path");

        return !empty($path) && file_exists(public_path($path));
    }

    /**
     * Get the document title / label.
     */
    public static function title(string $key = 'template_word'): string
    {
        return (string) config("conference_documents.{$key}.title", 'Document');
    }

    /**
     * Get the badge text (e.g. 'IEEE A4 Format').
     */
    public static function badge(string $key = 'template_word'): string
    {
        return (string) config("conference_documents.{$key}.badge", '');
    }

    /**
     * Direct shortcut for the conference manuscript template URL.
     */
    public static function templateUrl(): string
    {
        return self::url('template_word');
    }

    /**
     * Direct shortcut for the conference manuscript template download filename.
     */
    public static function templateFilename(): string
    {
        return self::filename('template_word');
    }

    /**
     * Direct shortcut to check if conference manuscript template exists.
     */
    public static function templateExists(): bool
    {
        return self::exists('template_word');
    }
}