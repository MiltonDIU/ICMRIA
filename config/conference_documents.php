<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Conference Official Documents & Templates
    |--------------------------------------------------------------------------
    |
    | Central configuration for downloadable conference documents and templates.
    | Set 'path' to the relative path inside public/ (e.g. 'documents/filename.docx').
    | If a file is not yet finalized (draft/demo), set 'path' => null. The application
    | will safely render '#' and display a 'Coming Soon' state automatically.
    |
    */

    // Official final template (ACTIVE)
    'template_word' => [
        'title'    => 'Conference Manuscript Template (A4 .DOCX)',
        'path'     => 'documents/conference-template-a4.docx',
        'filename' => 'conference-template-a4.docx',
        'badge'    => 'IEEE A4 Format',
        'format'   => 'DOCX',
    ],

    // Pending official files (set to null so demo/draft files are not served)
    'cfp_pdf' => [
        'title'    => 'Call for Papers (Full PDF)',
        'path'     => null,
        'filename' => null,
        'badge'    => 'PDF Brochure',
        'format'   => 'PDF',
    ],

    'cfp_bangla' => [
        'title'    => 'Call for Papers (Bangla PDF)',
        'path'     => null,
        'filename' => null,
        'badge'    => 'PDF Brochure',
        'format'   => 'PDF',
    ],

    'cfp_long' => [
        'title'    => 'Call for Papers with References (Long Form)',
        'path'     => null,
        'filename' => null,
        'badge'    => 'PDF',
        'format'   => 'PDF',
    ],

    'copyright_form' => [
        'title'    => 'Copyright Transfer Form',
        'path'     => null,
        'filename' => null,
        'badge'    => 'PDF',
        'format'   => 'PDF',
    ],
];