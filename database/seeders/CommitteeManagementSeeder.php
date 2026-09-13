<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeType;
use App\Models\ConferenceMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ICMRIA 2027 committee & organisation structure.
 *
 * Source:
 *   Requirement document, section 1 ("Committee" menu) & section 6:
 *   1. Chief Patron & Patron
 *   2. International Advisory Committee
 *   3. National Advisory Committee
 *   4. Conference Chairs & Co-Chairs
 *   5. Organizing Committee
 *   6. Technical Program Committee (TPC)
 *   7. Track Chairs & Track Co-Chairs
 *   8. Others Committee
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

        $order = 1;
        $chiefPatron      = $this->root($type, 'Chief Patron & Patron', $order++);
        $international    = $this->root($type, 'International Advisory Committee', $order++);
        $national         = $this->root($type, 'National Advisory Committee', $order++);
        $conferenceChairs = $this->root($type, 'Conference Chairs & Co-Chairs', $order++);
        $organizing       = $this->root($type, 'Organizing Committee', $order++);
        $tpc              = $this->root($type, 'Technical Program Committee (TPC)', $order++);
        $trackChairs      = $this->root($type, 'Track Chairs & Track Co-Chairs', $order++);
        $others           = $this->root($type, 'Others Committee', $order++);

        // ---------------------------------------------------------------------
        // 1. Chief Patron & Patron
        // ---------------------------------------------------------------------
        $this->attach($chiefPatron, [
            ['Dr. Md. Sabur Khan',                   'Chairman, Board of Trustees', 'Daffodil International University (DIU)', 'Chief Patron'],
            ['Prof. Dr. M. Lutfar Rahman',           'Vice-Chancellor',             'Daffodil International University (DIU)', 'Patron'],
            ['Professor Mohammad Masum Iqbal, PhD.', 'Pro-Vice-Chancellor',         'Daffodil International University (DIU)', 'Patron'],
        ]);

        // ---------------------------------------------------------------------
        // 2. International Advisory Committee (doc section 6.6)
        // ---------------------------------------------------------------------
        $this->attach($international, [
            ['Prof. Dr. Hrishikesh Chakraborty', 'International Advisor, Division of Research, DIU', 'Duke University, Durham, North Carolina, United States'],
            ['Prof. Dr. Hironori Washizaki',     'Professor',                                        'Waseda University, Japan'],
            ['Prof. Dr. Vincenzo Piuri',         'Professor',                                        'University of Milan, Italy'],
            ['Prof. T. Ramayah',                 'Visiting Professor',                               'Universiti Sains Malaysia (USM), Malaysia'],
            ['Dr. Neil Perez Balba',             'Visiting Professor & Scholar',                     'Philippines'],
            ['Dr. Bibhuti Roy',                  'Visiting Professor',                               'University of Bremen, Germany'],
            ['Dr. Ismail Rakip Karas',           'Associate Professor / Researcher',                 'Karabuk University, Turkey'],
            ['Dr. Tan Cheng Ling',               'Visiting Professor',                               'Universiti Sains Malaysia (USM) / DIU'],
            ['Prof. Dr. Mohammad Ali Moni',      'Associate Professor / AI & Health Lead',           'University of Queensland, Australia'],
        ]);

        // ---------------------------------------------------------------------
        // 3. National Advisory Committee (doc section 6.5)
        // ---------------------------------------------------------------------
        $this->attach($national, [
            ['Prof. Dr. Mostafa Kamal',     'Dean (Academic Affairs)',  'Daffodil International University (DIU)'],
            ['Prof. Dr. Mohammad Kaykobad', 'Distinguished Professor',   'BRAC University (Ex-BUET)'],
            ['Prof. Dr. M. Sohel Rahman',   'Professor, Dept. of CSE',   'Bangladesh University of Engineering and Technology (BUET)'],
            ['Prof. Dr. Md. Saidur Rahman', 'Professor, Dept. of CSE',   'Bangladesh University of Engineering and Technology (BUET)'],
        ]);

        // ---------------------------------------------------------------------
        // 4. Conference Chairs & Co-Chairs
        // ---------------------------------------------------------------------
        $this->attach($conferenceChairs, [
            ['Professor Mohammad Masum Iqbal, PhD.', 'Pro-Vice-Chancellor',                                'Daffodil International University (DIU)', 'General Chair'],
            ['Prof. Dr. Md. Fokhray Hossain',        'Dean, Faculty of Science & Information Technology (FSIT)', 'Daffodil International University (DIU)', 'Conference Co-Chair'],
            ['Prof. Dr. M. Shamsul Alam',           'Dean, Faculty of Engineering (FE)',                  'Daffodil International University (DIU)', 'Conference Co-Chair'],
            ['Prof. Dr. Mohammad Rokibul Kabir',     'Dean, Faculty of Business & Entrepreneurship (FBE)',  'Daffodil International University (DIU)', 'Conference Co-Chair'],
            ['Prof. Dr. Liza Sharmin',               'Dean, Faculty of Humanities & Social Sciences (FHSS)', 'Daffodil International University (DIU)', 'Conference Co-Chair'],
            ['Prof. Dr. Bellal Hossain',             'Dean, Faculty of Health & Life Sciences (FHLS)',       'Daffodil International University (DIU)', 'Conference Co-Chair'],
        ]);

        // ---------------------------------------------------------------------
        // 5. Organizing Committee (doc section 6.2)
        // ---------------------------------------------------------------------
        $this->attach($organizing, [
            ['Professor Mohammad Masum Iqbal, PhD.', 'Pro-Vice-Chancellor',                                         'Daffodil International University (DIU)', 'Convener'],
            ['Professor Dr. Imran Mahmud',           'Professor & Head, Department of Software Engineering (SWE)',  'Daffodil International University (DIU)', 'Co-convener'],
            ['Dr. Md. Sarowar Hossain',              'Director, Division of Research & Associate Professor, Dept. of Pharmacy', 'Daffodil International University (DIU)', 'Co-convener'],
            ['Dr. Md Mamun Mia',                     'Assistant Professor, Department of Marketing',                 'Daffodil International University (DIU)', 'Co-convener'],
        ]);

        // ---------------------------------------------------------------------
        // 6. Technical Program Committee (TPC)
        // ---------------------------------------------------------------------
        $this->attach($tpc, [
            ['Prof. Dr. Sheak Rashed Haider Noori', 'Head, Department of Computer Science and Engineering (CSE)',            'Daffodil International University (DIU)', 'TPC Chair'],
            ['Dr. S. M. Aminul Haque',             'Professor & Associate Head, Dept. of CSE',                              'Daffodil International University (DIU)', 'TPC Co-Chair'],
            ['Dr. A. B. M. Kamal Pasha',           'Associate Professor & Head, Dept. of Environmental Science & Disaster Management (ESDM)', 'Daffodil International University (DIU)', 'TPC Co-Chair'],
            ['Dr. Dara Abdus Satter',              'Associate Professor & Head, Department of Electrical & Electronic Engineering (EEE)',   'Daffodil International University (DIU)', 'TPC Co-Chair'],
            ['Dr. Kazi A. S. M. Nurul Huda',       'Associate Professor & Head, Department of Civil Engineering (CE)',       'Daffodil International University (DIU)', 'TPC Member'],
            ['Dr. Nusrat Jahan',                   'Associate Professor & Head, Dept. of Information Technology & Management (ITM)', 'Daffodil International University (DIU)', 'TPC Member'],
            ['Prof. Dr. Kudrat-E-Khuda Babu',       'Professor & Head, Department of Law',                                    'Daffodil International University (DIU)', 'TPC Member'],
        ]);

        // ---------------------------------------------------------------------
        // 7. Track Chairs & Track Co-Chairs (doc section 6.4)
        // ---------------------------------------------------------------------
        $this->seedTrackChairs($type, $trackChairs);

        // ---------------------------------------------------------------------
        // 8. Others Committee (Publication, Media & PR, Logistics)
        // ---------------------------------------------------------------------
        $this->attach($others, [
            ['Dr. Md. Sarowar Hossain',                        'Director, Division of Research', 'Daffodil International University (DIU)', 'Publication Committee Chair'],
            ['Mr. Aftab Hossain',                              'Assistant Professor & Head, Dept. of Journalism, Media & Communication (JMC)', 'Daffodil International University (DIU)', 'Media & Public Relations Chair'],
            ['Mr. Sheikh Muhammad Rezwan',                     'Assistant Professor & Head, Department of Architecture', 'Daffodil International University (DIU)', 'Exhibition & Protocol Chair'],
            ['Office of the Director of Students Affairs (DSA)', 'Logistics & Volunteer Management', 'Daffodil International University (DIU)', 'Secretariat & Logistics'],
        ]);
    }

    private function seedTrackChairs(CommitteeType $type, Committee $parent): void
    {
        $tracks = [
            'Track 1: Artificial Intelligence, Data Science & Emerging Technologies' => [
                'overall' => ['Prof. Dr. Md. Fokhray Hossain', 'Dean, Faculty of Science & Information Technology (FSIT), DIU'],
                'subs' => [
                    ['Machine Learning, Deep Learning & Generative AI',                    'Prof. Dr. Sheak Rashed Haider Noori', 'Head, Department of Computer Science and Engineering (CSE), DIU'],
                    ['Big Data Analytics, Data Mining & Information Retrieval',             'Dr. S. M. Aminul Haque',              'Professor & Associate Head, Dept. of CSE, DIU'],
                    ['Computer Vision, Natural Language Processing & Pattern Recognition',  'Dr. Imran Mahmud',                    'Professor & Head, Department of Software Engineering (SWE), DIU'],
                    ['Cloud, Edge Computing, Internet of Things (IoT) & Cybersecurity',     'Mr. Md. Sarwar Hossain Mollah',       'Associate Professor & Head, Department of Computing & Information System (CIS), DIU'],
                ],
            ],
            'Track 2: Sustainable Development, Environment & Climate Action' => [
                'overall' => ['Prof. Dr. Bimal Chandra Das', 'Associate Dean, Faculty of Science & Information Technology (FSIT), DIU'],
                'subs' => [
                    ['Climate Change Mitigation, Adaptation & Environmental Dynamics',     'Dr. A. B. M. Kamal Pasha',     'Associate Professor & Head, Dept. of Environmental Science & Disaster Management (ESDM), DIU'],
                    ['Renewable Energy Systems, Green Tech & Energy Policy',                'Prof. Dr. Md. Saidur Rahman',  'Professor, Department of ESDM, DIU'],
                    ['Environmental Risk Assessment, Water Resources & Waste Management',   'Dr. Kazi A. S. M. Nurul Huda', 'Associate Professor & Head, Department of Civil Engineering (CE), DIU'],
                    ['Sustainable Urbanization, Smart Cities & Biodiversity Conservation',  'Mr. Sheikh Muhammad Rezwan',   'Assistant Professor & Head, Department of Architecture, DIU'],
                ],
            ],
            'Track 3: Business, Economics, Management & Innovation' => [
                'overall' => ['Prof. Dr. Mohammad Rokibul Kabir', 'Dean, Faculty of Business & Entrepreneurship (FBE), DIU'],
                'subs' => [
                    ['Digital Transformation, E-Commerce & Entrepreneurship',            'Dr. Md. Azizur Rahman',           'Associate Professor & Head, Department of Business Administration, DIU'],
                    ['Sustainable Finance, Banking, Accounting & Fintech Innovations',   'Professor Dr. Md. Abdur Rouf',     'Professor, Department of Accounting, DIU'],
                    ['Supply Chain, Logistics, Operations & Strategic Management',       'Mr. Siddiqur Rahman',             'Assistant Professor, Department of Business Administration, DIU'],
                    ['Marketing Analytics, Consumer Behavior & Real Estate Management',  'Dr. Dewan Golam Yazdani Showrav', 'Associate Professor & Head, Department of Marketing, DIU'],
                ],
            ],
            'Track 4: Core Engineering, Infrastructure & Smart Systems' => [
                'overall' => ['Prof. Dr. M. Shamsul Alam', 'Dean, Faculty of Engineering (FE), DIU'],
                'subs' => [
                    ['Electrical & Electronic Engineering (EEE): Smart Grids, Power Systems, Semiconductor Devices, VLSI, Robotics & Automation', 'Dr. Dara Abdus Satter',        'Associate Professor & Head, Department of Electrical & Electronic Engineering (EEE), DIU'],
                    ['Textile Engineering: Advanced Textile Materials, Smart Fabrics, Sustainable Apparel & Textile Chemical Processing',          'Prof. Dr. Md. Mahbubul Haque', 'Head, Department of Textile Engineering, DIU'],
                    ['Civil Engineering: Structural, Geotechnical, Transportation, Construction Management & Infrastructure Resilience',           'Dr. Kazi A. S. M. Nurul Huda', 'Associate Professor & Head, Department of Civil Engineering (CE), DIU'],
                    ['Architecture & Urban Planning: Sustainable Building Design, Architectural Heritage, Urban Dynamics & Spatial Planning',      'Mr. Sheikh Muhammad Rezwan',   'Assistant Professor & Head, Department of Architecture, DIU'],
                    ['Information & Communication Engineering (ICE): Wireless Tech, Telecommunications, Optical Communications & Signal Processing', 'Dr. Nusrat Jahan',            'Associate Professor & Head, Dept. of Information Technology & Management (ITM), DIU'],
                ],
            ],
            'Track 5: Social Sciences, Humanities, Law & Public Policy' => [
                'overall' => ['Prof. Dr. Liza Sharmin', 'Dean, Faculty of Humanities & Social Sciences (FHSS), DIU'],
                'subs' => [
                    ['Legal Frameworks, Human Rights, Ethics & Governance',                'Prof. Dr. Kudrat-E-Khuda Babu', 'Professor & Head, Department of Law, DIU'],
                    ['Digital Humanities, Culture, Identity & Societal Transformation',    'Dr. Ehatasham Ul Hoque Eiten',  'Assistant Professor & Head, Department of English, DIU'],
                    ['Public Policy, International Relations, Peace & Conflict Resolution', 'Dr. Md. Fouad Hossain Sarker',  'Associate Professor & Head, Department of Development Studies, DIU'],
                    ['Social Welfare, Community Development & Public Administration',       'Ms. Bilkis Khanam',             'Assistant Professor & Head, Department of General Educational Development (GED), DIU'],
                ],
            ],
            'Track 6: Health Sciences, Biotechnology & Public Health' => [
                'overall' => ['Prof. Dr. Bellal Hossain', 'Dean, Faculty of Health & Life Sciences (FHLS), DIU'],
                'subs' => [
                    ['Pharmaceutical Sciences, Drug Discovery & Advanced Therapeutics',        'Prof. Dr. Muniruddin Ahmed',      'Head, Department of Pharmacy, DIU'],
                    ['Biotechnology, Genomics, Bioinformatics & Molecular Biology',            'Prof. Dr. Md. Bellal Hossain',    'Professor & Head, Dept. of Nutrition & Food Engineering (NFE), DIU'],
                    ['Public Health Interventions, Epidemiology & Healthcare Systems',         'Dr. A. B. M. Alauddin Chowdhury', 'Associate Professor & Head, Department of Public Health, DIU'],
                    ['Health Technology, Telemedicine, Medical Devices & Clinical Innovations', 'Dr. Md. Shahjahan',              'Professor, Faculty of Health & Life Sciences, DIU'],
                ],
            ],
            'Track 7: Education, Language, Literature & Communication Studies' => [
                'overall' => ['Prof. Dr. Liza Sharmin', 'Dean, Faculty of Humanities & Social Sciences (FHSS), DIU'],
                'subs' => [
                    ['Smart Pedagogy, EdTech, E-Learning & Outcome-Based Education (OBE)', 'Prof. Dr. Md. Mostafa Kamal',   'Dean (Academic Affairs) & Professor, DIU'],
                    ['Applied Linguistics, Language Teaching (ELT) & Translation Studies', 'Dr. Ehatasham Ul Hoque Eiten',  'Assistant Professor & Head, Department of English, DIU'],
                    ['Modern & Comparative Literature, Cultural Studies',                  'Prof. A. M. M. Hamidur Rahman', 'Professor, Department of English, DIU'],
                    ['Mass Media, Digital Journalism, PR & Strategic Communication',       'Mr. Aftab Hossain',             'Assistant Professor & Head, Dept. of Journalism, Media & Communication (JMC), DIU'],
                ],
            ],
            'Track 8: Agriculture, Food Security & Rural Development' => [
                'overall' => ['Prof. Dr. Bellal Hossain', 'Dean, Faculty of Health & Life Sciences (FHLS), DIU'],
                'subs' => [
                    ['Smart Agriculture, Precision Farming, Agribusiness & IoT in Farming',    'Prof. Dr. M. A. Rahim',        'Head, Department of Agricultural Science, DIU'],
                    ['Food Safety, Processing, Quality Assurance & Food Security',             'Prof. Dr. Md. Bellal Hossain', 'Head, Department of Nutrition & Food Engineering (NFE), DIU'],
                    ['Agro-Ecology, Crop Protection, Soil Health & Plant Genetics',            'Dr. Md. Ahad Ali',             'Associate Professor, Department of Agricultural Science, DIU'],
                    ['Rural Economics, Sustainable Food Supply Chains & Community Upliftment',  'Dr. Md. Rashedul Islam',       'Associate Professor, Dept. of Agricultural Science, DIU'],
                ],
            ],
        ];

        $order = 1;
        foreach ($tracks as $trackName => $info) {
            $sub = Committee::create([
                'committee_type_id' => $type->id,
                'parent_id'         => $parent->id,
                'name'              => $trackName,
                'sort_order'        => $order++,
            ]);

            $members = [[$info['overall'][0], $info['overall'][1], null, 'Overall Track Chair']];
            foreach ($info['subs'] as [$subTitle, $chairName, $chairAff]) {
                $members[] = [$chairName, $chairAff, null, $subTitle];
            }
            $this->attach($sub, $members);
        }
    }

    /* ------------------------------------------------------------------- */

    private function root(CommitteeType $type, string $name, int $order): Committee
    {
        return Committee::create([
            'committee_type_id' => $type->id,
            'parent_id'         => 0,
            'name'              => $name,
            'sort_order'        => $order,
        ]);
    }

    /**
     * @param  array<int, array{0:string,1:?string,2:?string,3:?string}>  $rows
     *         [name, designation, institution|country, role?]
     */
    private function attach(Committee $committee, array $rows): void
    {
        $order = 1;
        foreach ($rows as $r) {
            $name        = trim($r[0]);
            $designation = $r[1] ?? null;
            $institution = $r[2] ?? null;
            $role        = $r[3] ?? null;

            if ($institution === null && $designation !== null && str_contains($designation, ',')) {
                [$designation, $institution] = array_map('trim', explode(',', $designation, 2));
            }

            $member = ConferenceMember::firstOrCreate(
                ['name' => $name, 'institution' => $institution],
                ['designation' => $designation, 'is_active' => true]
            );

            $committee->members()->syncWithoutDetaching([
                $member->id => ['role' => $role, 'sort_order' => $order++],
            ]);
        }
    }
}