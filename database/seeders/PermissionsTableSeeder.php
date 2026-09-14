<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'title' => 'user_management_access',
            ],
            [
                'title' => 'permission_create',
            ],
            [
                'title' => 'permission_edit',
            ],
            [
                'title' => 'permission_show',
            ],
            [
                'title' => 'permission_delete',
            ],
            [
                'title' => 'permission_access',
            ],
            [
                'title' => 'role_create',
            ],
            [
                'title' => 'role_edit',
            ],
            [
                'title' => 'role_show',
            ],
            [
                'title' => 'role_delete',
            ],
            [
                'title' => 'role_access',
            ],
            [
                'title' => 'user_create',
            ],
            [
                'title' => 'user_edit',
            ],
            [
                'title' => 'user_show',
            ],
            [
                'title' => 'user_delete',
            ],
            [
                'title' => 'user_access',
            ],
            [
                'title' => 'setting_create',
            ],
            [
                'title' => 'setting_edit',
            ],
            [
                'title' => 'setting_show',
            ],
            [
                'title' => 'setting_delete',
            ],
            [
                'title' => 'setting_access',
            ],
            [
                'title' => 'speaker_create',
            ],
            [
                'title' => 'speaker_edit',
            ],
            [
                'title' => 'speaker_show',
            ],
            [
                'title' => 'speaker_delete',
            ],
            [
                'title' => 'speaker_access',
            ],
            [
                'title' => 'schedule_create',
            ],
            [
                'title' => 'schedule_edit',
            ],
            [
                'title' => 'schedule_show',
            ],
            [
                'title' => 'schedule_delete',
            ],
            [
                'title' => 'schedule_access',
            ],
            [
                'title' => 'venue_create',
            ],
            [
                'title' => 'venue_edit',
            ],
            [
                'title' => 'venue_show',
            ],
            [
                'title' => 'venue_delete',
            ],
            [
                'title' => 'venue_access',
            ],
            [
                'title' => 'hotel_create',
            ],
            [
                'title' => 'hotel_edit',
            ],
            [
                'title' => 'hotel_show',
            ],
            [
                'title' => 'hotel_delete',
            ],
            [
                'title' => 'hotel_access',
            ],
            [
                'title' => 'gallery_create',
            ],
            [
                'title' => 'gallery_edit',
            ],
            [
                'title' => 'gallery_show',
            ],
            [
                'title' => 'gallery_delete',
            ],
            [
                'title' => 'gallery_access',
            ],
            [
                'title' => 'sponsor_create',
            ],
            [
                'title' => 'sponsor_edit',
            ],
            [
                'title' => 'sponsor_show',
            ],
            [
                'title' => 'sponsor_delete',
            ],
            [
                'title' => 'sponsor_access',
            ],
            [
                'title' => 'faq_create',
            ],
            [
                'title' => 'faq_edit',
            ],
            [
                'title' => 'faq_show',
            ],
            [
                'title' => 'faq_delete',
            ],
            [
                'title' => 'faq_access',
            ],
            [
                'title' => 'amenity_create',
            ],
            [
                'title' => 'amenity_edit',
            ],
            [
                'title' => 'amenity_show',
            ],
            [
                'title' => 'amenity_delete',
            ],
            [
                'title' => 'amenity_access',
            ],
            [
                'title' => 'price_create',
            ],
            [
                'title' => 'price_edit',
            ],
            [
                'title' => 'price_show',
            ],
            [
                'title' => 'price_delete',
            ],
            [
                'title' => 'price_access',
            ],
            [
                'title' => 'events_create',
            ],
            [
                'title' => 'events_edit',
            ],
            [
                'title' => 'events_show',
            ],
            [
                'title' => 'events_delete',
            ],
            [
                'title' => 'events_access',
            ],
            [
                'title' => 'admin_dashboard',
            ],
            [
                'title' => 'blog_category_create',
            ],
            [
                'title' => 'blog_category_edit',
            ],
            [
                'title' => 'blog_category_show',
            ],
            [
                'title' => 'blog_category_delete',
            ],
            [
                'title' => 'blog_category_access',
            ],
            [
                'title' => 'tag_create',
            ],
            [
                'title' => 'tag_edit',
            ],
            [
                'title' => 'tag_show',
            ],
            [
                'title' => 'tag_delete',
            ],
            [
                'title' => 'tag_access',
            ],
            [
                'title' => 'blogs_post_access',
            ],
            [
                'title' => 'post_create',
            ],
            [
                'title' => 'post_edit',
            ],
            [
                'title' => 'post_show',
            ],
            [
                'title' => 'post_delete',
            ],
            [
                'title' => 'post_access',
            ],
            [
                'title' => 'comment_create',
            ],
            [
                'title' => 'comment_edit',
            ],
            [
                'title' => 'comment_show',
            ],
            [
                'title' => 'comment_delete',
            ],
            [
                'title' => 'comment_access',
            ],
            [
                'title' => 'upload_medium_create',
            ],
            [
                'title' => 'upload_medium_edit',
            ],
            [
                'title' => 'upload_medium_show',
            ],
            [
                'title' => 'upload_medium_delete',
            ],
            [
                'title' => 'upload_medium_access',
            ],
            [
                'title' => 'event_activity_create',
            ],
            [
                'title' => 'event_activity_edit',
            ],
            [
                'title' => 'event_activity_show',
            ],
            [
                'title' => 'event_activity_delete',
            ],
            [
                'title' => 'event_activity_access',
            ],
            [
                'title' => 'referral_create',
            ],
            [
                'title' => 'referral_edit',
            ],
            [
                'title' => 'referral_show',
            ],
            [
                'title' => 'referral_delete',
            ],
            [
                'title' => 'referral_access',
            ],
            [
                'title' => 'attendance_create',
            ],
            [
                'title' => 'attendance_show',
            ],
            [
                'title' => 'attendance_taken',
            ],
            [
                'title' => 'attendance_certificate',
            ],
            [
                'title' => 'attendance_access',
            ],
            [
                'title' => 'email_data_bank_access',
            ],
            [
                'title' => 'data_bank_category_create',
            ],
            [
                'title' => 'data_bank_category_edit',
            ],
            [
                'title' => 'data_bank_category_show',
            ],
            [
                'title' => 'data_bank_category_delete',
            ],
            [
                'title' => 'data_bank_category_access',
            ],
            [
                'title' => 'data_bank_create',
            ],
            [
                'title' => 'data_bank_edit',
            ],
            [
                'title' => 'data_bank_show',
            ],
            [
                'title' => 'data_bank_delete',
            ],
            [
                'title' => 'data_bank_access',
            ],
            [
                'title' => 'custom_email_access',
            ],
            [
                'title' => 'admin_report',
            ],
            [
                'title' => 'committee_type_create',
            ],
            [
                'title' => 'committee_type_edit',
            ],
            [
                'title' => 'committee_type_show',
            ],
            [
                'title' => 'committee_type_delete',
            ],
            [
                'title' => 'committee_type_access',
            ],
            [
                'title' => 'conference_member_create',
            ],
            [
                'title' => 'conference_member_edit',
            ],
            [
                'title' => 'conference_member_show',
            ],
            [
                'title' => 'conference_member_delete',
            ],
            [
                'title' => 'conference_member_access',
            ],
            [
                'title' => 'committee_create',
            ],
            [
                'title' => 'committee_edit',
            ],
            [
                'title' => 'committee_show',
            ],
            [
                'title' => 'committee_delete',
            ],
            [
                'title' => 'committee_access',
            ],
            [
                'title' => 'conference_message_access',
            ],
            [
                'title' => 'conference_message_create',
            ],
            [
                'title' => 'conference_message_edit',
            ],
            [
                'title' => 'conference_message_show',
            ],
            [
                'title' => 'conference_message_delete',
            ],
            [
                'title' => 'conference_message_category_access',
            ],
            [
                'title' => 'conference_message_category_create',
            ],
            [
                'title' => 'conference_message_category_edit',
            ],
            [
                'title' => 'conference_message_category_show',
            ],
            [
                'title' => 'conference_message_category_delete',
            ],
            [
                'title' => 'paper_create',
            ],
            [
                'title' => 'paper_edit',
            ],
            [
                'title' => 'paper_show',
            ],
            [
                'title' => 'paper_delete',
            ],
            [
                'title' => 'paper_access',
            ],
            [
                'title' => 'profile',
            ],
            [
                'title' => 'profile_edit',
            ],
            [
                'title' => 'strategic_create',
            ],
            [
                'title' => 'sponsors_show',
            ],
            [
                'title' => 'strategic_delete',
            ],
            [
                'title' => 'track_report',
            ],
            [
                'title' => 'coupon_delete',
            ],
            [
                'title' => 'coupon',
            ],
            [
                'title' => 'strategic_access',
            ],
            [
                'title' => 'student_delete',
            ],
            [
                'title' => 'abstract_review',
            ],
            [
                'title' => 'domain_edit',
            ],
            [
                'title' => 'domain_delete',
            ],
            [
                'title' => 'track_access',
            ],
            [
                'title' => 'track_create',
            ],
            [
                'title' => 'track_show',
            ],
            [
                'title' => 'track_edit',
            ],
            [
                'title' => 'track_delete',
            ],
            [
                'title' => 'sub_track_access',
            ],
            [
                'title' => 'sub_track_create',
            ],
            [
                'title' => 'sub_track_show',
            ],
            [
                'title' => 'sub_track_edit',
            ],
            [
                'title' => 'sub_track_delete',
            ],
            [
                'title' => 'review_access',
            ],
            [
                'title' => 'review_submit',
            ],
            [
                'title' => 'review_assign',
            ],
            [
                'title' => 'decision_access',
            ],
            [
                'title' => 'decision_make',
            ],
            [
                'title' => 'final_approval',
            ],
            [
                'title' => 'camera_ready_access',
            ],
            [
                'title' => 'camera_ready_review',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['title' => $permission['title']], $permission);
        }
    }
}
