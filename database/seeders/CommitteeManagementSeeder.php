<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeType;
use App\Models\ConferenceMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ICMRIA 2027 Official Approved Committee Structure.
 * Source: Official Approved Memorandum (Ref: DIU/Reg./Memo/03/2026/172)
 * "Formation of the committee for organizing DIU Silver Jubilee International Multidisciplinary Conference 2027 (DSJiMC-2027)"
 */
class CommitteeManagementSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('committee_conference_member')->truncate();
        DB::table('committees')->truncate();
        DB::table('conference_members')->truncate();
        DB::table('committee_types')->truncate();
        Schema::enableForeignKeyConstraints();

        $type = CommitteeType::create(['name' => 'Conference Committee']);

        $committees = [
            [
                'name'        => 'Advisory Council',
                'order'       => 1,
                'description' => 'Guiding strategic direction, academic standards, and multidisciplinary excellence for ICMRIA 2027.',
                'members'     => [
                    ['Professor Dr. M. R. Kabir', 'Vice Chancellor', 'Daffodil International University (DIU)', 'Chief Advisor'],
                    ['Professor Dr. Mostafa Kamal', 'Dean, Academic Affairs (AA)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. S.M. Mahbub Ul Haque Majumder', 'Dean, Faculty of Health & Life Sciences (FHLS)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. M. Shamsul Alam', 'Dean, Faculty of Engineering (FE)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. M. A. Rahim', 'Dean, Faculty of Agricultural Sciences (FAS)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. Md. Fokhray Hossain', 'Dean, Faculty of Science & Information Technology (FSIT)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. Liza Sharmin', 'Dean, Faculty of Humanities & Social Sciences (FHSS)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Professor Dr. Mohammad Rokibul Kabir', 'Dean, Faculty of Business & Entrepreneurship (FBE)', 'Daffodil International University (DIU)', 'Advisor'],
                    ['Dr. Mohammed Nadir Bin Ali', 'Registrar', 'Daffodil International University (DIU)', 'Advisor'],
                ],
            ],
            [
                'name'        => 'Organizing Committee',
                'order'       => 2,
                'description' => 'Overall conference execution, administration, and inter-departmental coordination.',
                'members'     => [
                    ['Professor Dr. Mohammed Masum Iqbal', 'Pro-Vice Chancellor', 'Daffodil International University (DIU)', 'Convener'],
                    ['Professor Dr. Imran Mahmud', 'Head, Department of Software Engineering (SWE)', 'Daffodil International University (DIU)', 'Co-Convener'],
                    ['Dr. Md. Sarowar Hossain', 'Director, Division of Research (DoR)', 'Daffodil International University (DIU)', 'Co-Convener'],
                    ['Dr. Md. Mamun Mia', 'Department of Business Administration (DBA)', 'Daffodil International University (DIU)', 'Co-Convener'],
                ],
            ],
            [
                'name'        => 'Steering Committee',
                'order'       => 3,
                'description' => 'Faculty leadership steering operational milestones and academic policies.',
                'members'     => [
                    ['Professor Dr. Syed Mizanur Rahman', 'Associate Dean, Faculty of Business & Entrepreneurship (FBE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Professor Dr. Kudrat-E-Khuda Babu', 'Associate Dean, Faculty of Humanities & Social Sciences (FHSS)', 'Daffodil International University (DIU)', 'Member'],
                    ['Professor Dr. Bimal Chandra Das', 'Associate Dean, Faculty of Science & Information Technology (FSIT)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Miah M. Hussainuzzaman', 'Associate Dean, Faculty of Engineering (FE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Mohammed Shafikur Rahman', 'Associate Dean, Faculty of Health & Life Sciences (FHLS)', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Budget and Sponsorship Committee',
                'order'       => 4,
                'description' => 'Financial planning, sponsorship acquisitions, and fiscal governance.',
                'members'     => [
                    ['Professor Dr. Syed Mizanur Rahman', 'Associate Dean, Faculty of Business & Entrepreneurship (FBE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Professor Dr. Kudrat-E-Khuda Babu', 'Associate Dean, Faculty of Humanities & Social Sciences (FHSS)', 'Daffodil International University (DIU)', 'Member'],
                    ['Professor Dr. Mohammad Rokibul Kabir', 'Dean, Faculty of Business & Entrepreneurship (FBE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Kamruzzaman', 'Head, Department of Innovation & Entrepreneurship (DIE), FBE', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Hospitality Service, Audience and Program Management Committee',
                'order'       => 5,
                'description' => 'Guest hospitality, session management, protocol, and audience engagement.',
                'members'     => [
                    ['Dr. Amir Ahmed', 'Associate Professor and Head, Department of Real Estate (DRE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Md. Zahid Hasan', 'Associate Professor, Department of Computer Science & Engineering (CSE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Tama Fouzder', 'Associate Professor, Department of Electrical & Electronic Engineering (EEE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Md. Masud Alom', 'Associate Professor, Department of Civil Engineering (DCE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Indrajeet Mallick', 'Assistant Professor, Department of Tourism & Hospitality Management (DTHM)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Nazmul Islam', 'Senior Administrative Officer, International Affairs', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Media, Promotion & Souvenir Committee',
                'order'       => 6,
                'description' => 'Branding, public relations, press releases, digital campaigns, and souvenir publication.',
                'members'     => [
                    ['Dr. Md. Abdul Kabil Khan', 'Associate Professor and Director, MSS Program', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Kamruzzaman', 'Assistant Professor, Head, Department of Innovation & Entrepreneurship (DIE), FBE', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. SK Rahat Auyon', 'Head of Branding & Communications', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Khaled Hasan Munna', 'Assistant Director, Media Lab', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Mehedi Hasan Rinku', 'Senior Officer, Branding & Communication', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Shouvik Roy Snigdha', 'Assistant Administrative Officer, International Affairs', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Saidul Islam Sajib', 'Assistant Coordination Officer, Department of Innovation & Entrepreneurship (DIE)', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Website Development & External Affairs Committee',
                'order'       => 7,
                'description' => 'Online submission portal, IT infrastructure, international outreach, and external relations.',
                'members'     => [
                    ['Mr. Md. Shohel Arman', 'Assistant Professor, Department of Software Engineering (SWE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Md. Mamun Mia', 'Assistant Professor, Department of Marketing', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Syed Raihan-Ul-Islam', 'Deputy Director, International Affairs', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Muntachir Razzaque', 'Senior Assistant Director (IT)', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Venue Confirmation, Decoration and Management Committee',
                'order'       => 8,
                'description' => 'Auditorium allocation, stage branding, decor, and on-site logistics management.',
                'members'     => [
                    ['Dr. Indrajeet Mallick', 'Assistant Professor, Department of Tourism & Hospitality Management (DTHM)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Mobasher Kalam', 'Lecturer, Department of Innovation & Entrepreneurship (DIE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Mohammad Monir Hossan', 'Deputy Director, University Ranking Cell', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Toki Yeasir', 'Senior Administrative Officer, Division of Research (DoR)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Masud Rana', 'Administrative Officer, Department of Business Administration (DBA)', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Safety, Security, Transport and Logistics Support Committee',
                'order'       => 9,
                'description' => 'Campus security, transport coordination, shuttle services, and emergency protocol.',
                'members'     => [
                    ['Professor Dr. Shaikh Muhammad Allayear', 'Proctor', 'Daffodil International University (DIU)', 'Member'],
                    ['Major (Retd.) Md Shah Alam', 'Director of Safety & Security Management', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Ansur Rahman', 'Administrative Officer, Transport', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Kazi Md. Diljeb Kabir', 'Deputy Director and Assistant Proctor', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. SM Tariqul Islam', 'Security In-Charge Officer', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Shouvik Roy Snigdha', 'Administrative Officer, International Affairs', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
            [
                'name'        => 'Paper Selection, Processing and Proceedings Committee',
                'order'       => 10,
                'description' => 'Call for papers management, author communications, peer-review oversight, and journal proceedings.',
                'members'     => [
                    ['Professor Dr. Imran Mahmud', 'Head, Department of Software Engineering (SWE)', 'Daffodil International University (DIU)', 'Convener'],
                    ['Dr. Mahfuza Parveen', 'Associate Professor, Department of Environmental Science and Disaster Management (ESDM)', 'Daffodil International University (DIU)', 'Co-Convener'],
                    ['Professor Dr. Binoy Barman', 'Executive Editor, Journal of Society and Progress (JSP)', 'Daffodil International University (DIU)', 'Member'],
                    ['Professor Dr. Bimal Chandra Das', 'Executive Editor, Journal of Advanced Information and Intelligent Technology (JAIIT)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Arif Mahmud', 'Associate Professor and Associate Head, Department of Computer Science & Engineering (CSE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Md. Kamrul Hossain', 'Associate Professor, Department of Computer Science & Engineering (CSE)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Mohammad Azam Khan', 'Associate Professor, Department of Computing & Information System (CIS)', 'Daffodil International University (DIU)', 'Member'],
                    ['Dr. Md. Mamun Mia', 'Executive Editor, Journal of Business Insights and Perspectives (JBIP)', 'Daffodil International University (DIU)', 'Member'],
                    ['Mr. Md. Salman Sohel', 'Executive Editor, Journal of Emerging Global Health (JEGH)', 'Daffodil International University (DIU)', 'Member'],
                ],
            ],
        ];

        foreach ($committees as $cData) {
            $committee = Committee::create([
                'committee_type_id' => $type->id,
                'parent_id'         => 0,
                'name'              => $cData['name'],
                'description'       => $cData['description'] ?? null,
                'sort_order'        => $cData['order'],
            ]);

            $memberOrder = 1;
            foreach ($cData['members'] as $m) {
                $name        = trim($m[0]);
                $designation = trim($m[1]);
                $institution = trim($m[2]);
                $role        = $m[3] ?? 'Member';

                $member = ConferenceMember::firstOrCreate(
                    ['name' => $name, 'institution' => $institution],
                    ['designation' => $designation, 'is_active' => true]
                );

                $committee->members()->syncWithoutDetaching([
                    $member->id => [
                        'role'       => $role,
                        'sort_order' => $memberOrder++,
                    ],
                ]);
            }
        }
    }
}
