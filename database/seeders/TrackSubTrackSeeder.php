<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\SubTrack;
use Illuminate\Database\Seeder;

/**
 * ICMRIA 2027 conference tracks & sub-tracks.
 * Source: requirement document, section 4 (Conference Tracks & Call for Papers).
 * Track / sub-track chairs are seeded via CommitteeManagementSeeder.
 *
 * @return void
 */
class TrackSubTrackSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'Track 1: Artificial Intelligence, Data Science & Emerging Technologies' => [
                'Machine Learning, Deep Learning & Generative AI',
                'Big Data Analytics, Data Mining & Information Retrieval',
                'Computer Vision, Natural Language Processing & Pattern Recognition',
                'Cloud, Edge Computing, Internet of Things (IoT) & Cybersecurity',
            ],
            'Track 2: Sustainable Development, Environment & Climate Action' => [
                'Climate Change Mitigation, Adaptation & Environmental Dynamics',
                'Renewable Energy Systems, Green Tech & Energy Policy',
                'Environmental Risk Assessment, Water Resources & Waste Management',
                'Sustainable Urbanization, Smart Cities & Biodiversity Conservation',
            ],
            'Track 3: Business, Economics, Management & Innovation' => [
                'Digital Transformation, E-Commerce & Entrepreneurship',
                'Sustainable Finance, Banking, Accounting & Fintech Innovations',
                'Supply Chain, Logistics, Operations & Strategic Management',
                'Marketing Analytics, Consumer Behavior & Real Estate Management',
            ],
            'Track 4: Core Engineering, Infrastructure & Smart Systems' => [
                'Electrical & Electronic Engineering (EEE): Smart Grids, Power Systems, Semiconductor Devices, VLSI, Robotics & Automation',
                'Textile Engineering: Advanced Textile Materials, Smart Fabrics, Sustainable Apparel & Textile Chemical Processing',
                'Civil Engineering: Structural, Geotechnical, Transportation, Construction Management & Infrastructure Resilience',
                'Architecture & Urban Planning: Sustainable Building Design, Architectural Heritage, Urban Dynamics & Spatial Planning',
                'Information & Communication Engineering (ICE): Wireless Tech, Telecommunications, Optical Communications & Signal Processing',
            ],
            'Track 5: Social Sciences, Humanities, Law & Public Policy' => [
                'Legal Frameworks, Human Rights, Ethics & Governance',
                'Digital Humanities, Culture, Identity & Societal Transformation',
                'Public Policy, International Relations, Peace & Conflict Resolution',
                'Social Welfare, Community Development & Public Administration',
            ],
            'Track 6: Health Sciences, Biotechnology & Public Health' => [
                'Pharmaceutical Sciences, Drug Discovery & Advanced Therapeutics',
                'Biotechnology, Genomics, Bioinformatics & Molecular Biology',
                'Public Health Interventions, Epidemiology & Healthcare Systems',
                'Health Technology, Telemedicine, Medical Devices & Clinical Innovations',
            ],
            'Track 7: Education, Language, Literature & Communication Studies' => [
                'Smart Pedagogy, EdTech, E-Learning & Outcome-Based Education (OBE)',
                'Applied Linguistics, Language Teaching (ELT) & Translation Studies',
                'Modern & Comparative Literature, Cultural Studies',
                'Mass Media, Digital Journalism, PR & Strategic Communication',
            ],
            'Track 8: Agriculture, Food Security & Rural Development' => [
                'Smart Agriculture, Precision Farming, Agribusiness & IoT in Farming',
                'Food Safety, Processing, Quality Assurance & Food Security',
                'Agro-Ecology, Crop Protection, Soil Health & Plant Genetics',
                'Rural Economics, Sustainable Food Supply Chains & Community Upliftment',
            ],
        ];

        foreach ($data as $trackName => $subTracks) {
            $track = Track::updateOrCreate(['name' => $trackName]);
            foreach ($subTracks as $subTrackName) {
                SubTrack::firstOrCreate([
                    'track_id' => $track->id,
                    'name'     => $subTrackName,
                ]);
            }
        }
    }
}
