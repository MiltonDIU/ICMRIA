<?php

/*
 * What each row of the Settings table does, shown on the Settings list and edit pages so
 * an administrator knows what a value changes before changing it.
 *
 *   group        heading the setting is listed under
 *   description  what it controls, in one or two sentences
 *   values       what may be entered
 *
 * Keep this in step when a setting is added: the code that reads each key is named in
 * the description where it is not obvious.
 */

return [

    // ---------------------------------------------------------------- Dates
    'registration_start_date' => ['group' => 'Dates', 'values' => 'Date and time, e.g. 2026-09-01 00:00:00',
        'description' => 'When registration and abstract submission open. Before this the forms are closed.'],
    'abstract_submission_deadline' => ['group' => 'Dates', 'values' => 'Date and time',
        'description' => 'Last moment to submit an abstract. Authors can edit a pending abstract until then (if no reviewer has it yet).'],
    'manuscript_submission_start' => ['group' => 'Dates', 'values' => 'Date and time',
        'description' => 'When authors can start uploading the full manuscript. Both start and end must be set or uploads stay closed.'],
    'manuscript_submission_end' => ['group' => 'Dates', 'values' => 'Date and time',
        'description' => 'Last moment to upload or replace the full manuscript. With review_waits_for_manuscript_deadline on, reviewers are assigned only after this.'],
    'early_registration_start_date' => ['group' => 'Dates', 'values' => 'Date and time (blank = from the beginning)',
        'description' => 'Early-bird fees apply from this date …'],
    'early_registration_last_date' => ['group' => 'Dates', 'values' => 'Date and time (blank = no early-bird)',
        'description' => '… until this date. Outside the window the regular fee applies. Unpaid amounts are recalculated whenever an author opens their profile or pays; paid amounts never change.'],
    'revision_deadline' => ['group' => 'Dates', 'values' => 'Date and time (blank = camera-ready deadline)',
        'description' => 'Last moment for the revised manuscript of a paper accepted with minor revisions.'],
    'camera_ready_deadline' => ['group' => 'Dates', 'values' => 'Date and time (blank = registration close date)',
        'description' => 'Last moment to upload the camera-ready manuscript and the signed copyright form.'],
    'registration_close_date' => ['group' => 'Dates', 'values' => 'Date and time',
        'description' => 'Registration deadline shown on the site; also the fallback camera-ready deadline.'],
    'payment_last_date' => ['group' => 'Dates', 'values' => 'Date and time',
        'description' => 'Last moment to pay the registration fee (authors and participants).'],
    'event_date' => ['group' => 'Dates', 'values' => 'Date, e.g. 2027-01-09',
        'description' => 'First day of the conference (timeline and site).'],
    'event_end_date' => ['group' => 'Dates', 'values' => 'Date',
        'description' => 'Last day of the conference (timeline and site).'],

    // ---------------------------------------------------------------- Submission
    'is_abstract_submission_open' => ['group' => 'Submission', 'values' => 'true / false',
        'description' => 'Master switch for abstract submission from the author portal (Papers > Submit). Even when true, the dates above still apply.'],
    'is_registration_abstract_submission_open' => ['group' => 'Submission', 'values' => 'true / false',
        'description' => 'Whether the public registration page also takes an abstract. false = people register first and submit from the portal.'],
    'maximum_abstract_submission' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'How many abstracts one account may submit.'],
    'abstract_min_words' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Fewest words an abstract may have.'],
    'abstract_max_words' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Most words an abstract may have.'],
    'keywords_min' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Fewest keywords on a paper.'],
    'keywords_max' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Most keywords on a paper.'],
    'manuscript_min_pages' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Shortest manuscript, in pages (stated to authors; they confirm it on upload).'],
    'manuscript_max_pages' => ['group' => 'Submission', 'values' => 'Number',
        'description' => 'Longest manuscript, in pages.'],
    'author_conflict_declaration_enabled' => ['group' => 'Submission', 'values' => 'true / false',
        'description' => 'Whether authors may name chairs or reviewers they have a conflict of interest with. false hides it from authors; declarations already made still apply.'],
    'manuscript_locks_on_review' => ['group' => 'Submission', 'values' => 'true / false',
        'description' => 'true = once a reviewer holds a paper, the author can no longer replace its manuscript, so reviewers all read the same file.'],

    // ---------------------------------------------------------------- Review
    'blind_review_mode' => ['group' => 'Review', 'values' => 'double / single',
        'description' => 'double = reviewers do not see author names (authors confirm their file is anonymised); single = they do.'],
    'bidding_enabled' => ['group' => 'Review', 'values' => 'true / false',
        'description' => 'Whether reviewers may bid (Want / Can / Neutral / Conflict) on papers before assignment.'],
    'reviewers_per_paper' => ['group' => 'Review', 'values' => 'Number',
        'description' => 'How many reviewers automatic assignment aims for per paper. A track may override it.'],
    'min_reviewers_per_paper' => ['group' => 'Review', 'values' => 'Number',
        'description' => 'How many submitted evaluations a paper needs before a chair can enter a decision.'],
    'max_papers_per_reviewer' => ['group' => 'Review', 'values' => 'Number',
        'description' => 'Most open papers one reviewer may hold. A track may override it.'],
    'reviewer_keywords_min' => ['group' => 'Review', 'values' => 'Number',
        'description' => 'Fewest research keywords a reviewer gives about themselves.'],
    'reviewer_keywords_max' => ['group' => 'Review', 'values' => 'Number',
        'description' => 'Most research keywords a reviewer (or an admin for them) can record.'],
    'review_requires_manuscript' => ['group' => 'Review', 'values' => 'true / false',
        'description' => 'true = a paper gets reviewers only after its full manuscript is uploaded.'],
    'review_waits_for_manuscript_deadline' => ['group' => 'Review', 'values' => 'true / false',
        'description' => 'true = no reviewer is assigned (by hand or automatically) until manuscript_submission_end has passed.'],
    'review_feedback_min_chars' => ['group' => 'Review', 'values' => 'Number (0 = any length)',
        'description' => 'Shortest "feedback for authors" a reviewer may submit. 0 lets a one-line comment through.'],
    'chairs_can_review_own_track' => ['group' => 'Review', 'values' => 'true / false',
        'description' => 'true = a Track or Sub-Track Chair may be assigned as a reviewer in a track they chair (useful when reviewers are short). TPC Chair and admins never review.'],

    // ---------------------------------------------------------------- Decisions
    'reviewer_chair_can_decide' => ['group' => 'Decisions', 'values' => 'true / false',
        'description' => 'true = a chair who reviewed a paper may still enter its decision. false = another chair decides.'],
    'tpc_can_override_decision' => ['group' => 'Decisions', 'values' => 'true / false',
        'description' => 'true = on Final Approval the TPC Chair can set Accept / Minor Revisions / Reject themselves (with a reason) and approve it. false = the TPC Chair only approves or returns the chair\'s decision.'],
    'email_all_authors' => ['group' => 'Decisions', 'values' => 'true / false',
        'description' => 'Who receives paper emails (submission, decision, camera-ready). true = every author on the paper; false = the corresponding author only.'],

    // ---------------------------------------------------------------- Registration & payment
    'is_payment_enabled' => ['group' => 'Payment', 'values' => 'true / false',
        'description' => 'Master switch for online payment. false closes payment for everyone.'],
    'max_paid_papers_limit' => ['group' => 'Payment', 'values' => 'Number (blank or 0 = no limit)',
        'description' => 'Payment closes once this many papers have been paid (conference capacity).'],
    'manual_payment_enabled' => ['group' => 'Payment', 'values' => 'true / false',
        'description' => 'true = authors may also report a bank or mobile transfer with a receipt for an admin to verify. false = OneCard only.'],
    'attendance_change_enabled' => ['group' => 'Payment', 'values' => 'true / false',
        'description' => 'true = authors can change who attends (and so the fee) right up to paying. false = the choice made at submission stands.'],
    'special_discount_is_true' => ['group' => 'Payment', 'values' => 'true / false',
        'description' => 'Turns on the flat fee for approved email domains (Domains list).'],
    'selected_domain_discount' => ['group' => 'Payment', 'values' => 'Amount',
        'description' => 'The flat fee for someone on an approved email domain, used only where it is lower than their normal fee.'],
    'seat_is_full' => ['group' => 'Payment', 'values' => 'true / false',
        'description' => 'true shows the registration page as full.'],

    // ---------------------------------------------------------------- Site content
    'title' => ['group' => 'Site content', 'values' => 'Text (HTML allowed)', 'description' => 'Conference title on the home page.'],
    'subtitle' => ['group' => 'Site content', 'values' => 'Text (HTML allowed)', 'description' => 'Line under the title (dates and place).'],
    'theme' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Conference theme.'],
    'theme_description' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Paragraph explaining the theme.'],
    'about_description' => ['group' => 'Site content', 'values' => 'Text', 'description' => '"About" paragraph on the home page.'],
    'about_where' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Venue shown in the About section.'],
    'about_when' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Dates shown in the About section.'],
    'youtube_link' => ['group' => 'Site content', 'values' => 'URL', 'description' => 'Video shown on the home page (blank = none).'],
    'contact_address' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Contact address on the site.'],
    'contact_phone' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Contact phone numbers.'],
    'contact_email' => ['group' => 'Site content', 'values' => 'Email', 'description' => 'Contact email shown on the site.'],
    'contact_whatsapp' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'WhatsApp numbers on the contact section.'],
    'footer_description' => ['group' => 'Site content', 'values' => 'Text', 'description' => 'Footer text.'],
    'footer_address' => ['group' => 'Site content', 'values' => 'Text (HTML allowed)', 'description' => 'Footer address.'],
    'footer_twitter' => ['group' => 'Site content', 'values' => 'URL or #', 'description' => 'Footer social link.'],
    'footer_facebook' => ['group' => 'Site content', 'values' => 'URL or #', 'description' => 'Footer social link.'],
    'footer_instagram' => ['group' => 'Site content', 'values' => 'URL or #', 'description' => 'Footer social link.'],
    'footer_googleplus' => ['group' => 'Site content', 'values' => 'URL or #', 'description' => 'Footer social link.'],
    'footer_linkedin' => ['group' => 'Site content', 'values' => 'URL or #', 'description' => 'Footer social link.'],
];
