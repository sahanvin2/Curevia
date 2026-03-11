<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin & Contributors ──
        $admin = DB::table('users')->insertGetId([
            'name' => 'Curevia Admin',
            'email' => 'admin@curevia.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $contributors = [];
        $contribData = [
            ['name' => 'Dr. Elena Vasquez', 'email' => 'elena@curevia.com', 'expertise' => 'Astrophysics & Cosmology', 'bio' => 'PhD in Astrophysics from MIT. 15+ years studying black holes and dark matter.', 'avatar' => 'https://i.pravatar.cc/150?img=32', 'reputation' => 9850],
            ['name' => 'Prof. James Okoro', 'email' => 'james@curevia.com', 'expertise' => 'Ancient History & Archaeology', 'bio' => 'Professor of Ancient History at Oxford. Led excavations across Egypt and Rome.', 'avatar' => 'https://i.pravatar.cc/150?img=11', 'reputation' => 9120],
            ['name' => 'Dr. Maya Chen', 'email' => 'maya@curevia.com', 'expertise' => 'Marine Biology & Ecology', 'bio' => 'Marine biologist with National Geographic. Specialist in coral reef ecosystems.', 'avatar' => 'https://i.pravatar.cc/150?img=26', 'reputation' => 8740],
            ['name' => 'Dr. Amir Petrov', 'email' => 'amir@curevia.com', 'expertise' => 'Neuroscience & Human Anatomy', 'bio' => 'Neuroscientist at Johns Hopkins. Researching neural plasticity and consciousness.', 'avatar' => 'https://i.pravatar.cc/150?img=53', 'reputation' => 8200],
            ['name' => 'Dr. Sarah Kimura', 'email' => 'sarah@curevia.com', 'expertise' => 'Zoology & Wildlife Conservation', 'bio' => 'Wildlife conservationist with WWF. Expert in endangered species research.', 'avatar' => 'https://i.pravatar.cc/150?img=47', 'reputation' => 7650],
            ['name' => 'Prof. David Morales', 'email' => 'david@curevia.com', 'expertise' => 'Geology & Earth Sciences', 'bio' => 'Geologist specializing in plate tectonics and volcanic systems.', 'avatar' => 'https://i.pravatar.cc/150?img=60', 'reputation' => 7300],
        ];

        foreach ($contribData as $c) {
            $uid = DB::table('users')->insertGetId([
                'name' => $c['name'],
                'email' => $c['email'],
                'password' => Hash::make('password'),
                'role' => 'contributor',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $contributors[] = $uid;

            DB::table('contributor_profiles')->insert([
                'user_id' => $uid,
                'expertise' => $c['expertise'],
                'bio' => $c['bio'],
                'avatar' => $c['avatar'],
                'reputation' => $c['reputation'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Categories ──
        $categories = [
            ['name' => 'Space', 'slug' => 'space', 'description' => 'Explore the cosmos — galaxies, stars, planets, and the mysteries of the universe.', 'icon' => 'globe', 'color' => '#22F2E2', 'sort_order' => 1],
            ['name' => 'Earth', 'slug' => 'earth', 'description' => 'Discover the geological wonders, mountains, oceans, and landscapes of our planet.', 'icon' => 'earth', 'color' => '#2DD4BF', 'sort_order' => 2],
            ['name' => 'Science', 'slug' => 'science', 'description' => 'Physics, chemistry, biology, and the fundamental laws governing our reality.', 'icon' => 'flask', 'color' => '#7C6CFF', 'sort_order' => 3],
            ['name' => 'History', 'slug' => 'history', 'description' => 'Journey through time — wars, empires, revolutions, and pivotal moments.', 'icon' => 'clock', 'color' => '#F59E0B', 'sort_order' => 4],
            ['name' => 'Animals', 'slug' => 'animals', 'description' => 'The incredible diversity of animal life from the deep sea to mountain peaks.', 'icon' => 'paw', 'color' => '#22F2E2', 'sort_order' => 5],
            ['name' => 'Human Body', 'slug' => 'human-body', 'description' => 'Anatomy, physiology, the brain, and the extraordinary human machine.', 'icon' => 'heart', 'color' => '#EC4899', 'sort_order' => 6],
            ['name' => 'Countries', 'slug' => 'countries', 'description' => 'Nations, cultures, geography, and peoples around the world.', 'icon' => 'flag', 'color' => '#2DD4BF', 'sort_order' => 7],
            ['name' => 'Nature', 'slug' => 'nature', 'description' => 'Ecosystems, forests, weather, climate, and the natural world.', 'icon' => 'leaf', 'color' => '#34D399', 'sort_order' => 8],
            ['name' => 'Mythology', 'slug' => 'mythology', 'description' => 'Gods, legends, and the mythological traditions of civilizations.', 'icon' => 'star', 'color' => '#7C6CFF', 'sort_order' => 9],
            ['name' => 'Zodiac', 'slug' => 'zodiac', 'description' => 'Astrology, zodiac signs, constellations, and celestial symbolism.', 'icon' => 'sun', 'color' => '#F59E0B', 'sort_order' => 10],
            ['name' => 'Civilizations', 'slug' => 'civilizations', 'description' => 'The rise and fall of great empires and ancient societies.', 'icon' => 'building', 'color' => '#EC4899', 'sort_order' => 11],
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Computing, AI, engineering, and the innovations shaping the future.', 'icon' => 'cpu', 'color' => '#22F2E2', 'sort_order' => 12],
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[$cat['slug']] = DB::table('categories')->insertGetId(array_merge($cat, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ── Articles ──
        $articles = $this->getArticles();
        foreach ($articles as $a) {
            DB::table('articles')->insert([
                'title' => $a['title'],
                'slug' => $a['slug'],
                'summary' => $a['summary'],
                'content' => collect($a['content_sections'])->map(fn($s) => "## {$s['title']}\n\n{$s['body']}")->implode("\n\n"),
                'featured_image' => $a['featured_image'],
                'category_id' => $catIds[$a['category']],
                'author_id' => $contributors[$a['author_idx']],
                'status' => 'published',
                'read_time' => $a['read_time'],
                'views' => $a['views'],
                'quick_facts' => json_encode($a['quick_facts']),
                'images' => json_encode($a['images']),
                'content_sections' => json_encode($a['content_sections']),
                'video_url' => $a['video_url'] ?? null,
                'meta' => null,
                'meta_title' => $a['title'] . ' | Curevia Encyclopedia',
                'meta_description' => $a['summary'],
                'published_at' => Carbon::now()->subDays(rand(1, 90)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Stories ──
        $stories = [
            ['title' => 'Could Humans Live on Mars?', 'slug' => 'could-humans-live-on-mars', 'excerpt' => 'Examining the challenges of Martian colonization — from radiation to resource scarcity — and the bold plans.', 'category' => 'space', 'author_idx' => 0, 'read_time' => 12, 'featured' => true, 'img' => 'https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?w=1200&q=80', 'content' => "Mars has captivated human imagination for centuries. Today, with SpaceX's Starship program and NASA's Artemis-to-Mars roadmap, the dream of human settlement on the Red Planet is closer than ever.\n\nBut the challenges are enormous. Mars's thin atmosphere provides almost no protection from cosmic radiation. Surface temperatures average -60°C. There's no breathable air, and water exists primarily as ice.\n\nDespite these challenges, innovative solutions are emerging. Underground habitats could shield colonists from radiation. MOXIE technology has already demonstrated oxygen production from Martian CO2. Hydroponic farming systems could provide food.\n\nThe question isn't just whether we can live on Mars — it's whether we should. As we stand at the threshold of becoming a multi-planetary species, the ethical, scientific, and philosophical implications are profound."],
            ['title' => 'The Mystery of the Bermuda Triangle', 'slug' => 'mystery-of-bermuda-triangle', 'excerpt' => 'Separating fact from fiction as we examine the science behind one of the most notorious regions on the planet.', 'category' => 'earth', 'author_idx' => 5, 'read_time' => 9, 'featured' => true, 'img' => 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=1200&q=80', 'content' => "The Bermuda Triangle, roughly bounded by Miami, Bermuda, and Puerto Rico, has been blamed for mysterious disappearances of dozens of ships and aircraft.\n\nScientific investigation reveals that the number of incidents is not significantly greater than in other comparable regions. Many attributed disappearances have mundane explanations.\n\nHowever, some real phenomena do make this area challenging. Unusual magnetic variations can cause compass malfunctions. Sudden weather patterns develop with little warning. The Gulf Stream can rapidly disperse debris.\n\nThe Bermuda Triangle's reputation owes more to sensationalism than genuine supernatural forces. But its story reveals something important about human psychology."],
            ['title' => 'The Largest Animals on Earth', 'slug' => 'largest-animals-on-earth', 'excerpt' => 'From the blue whale to the African elephant, discover the giants that share our planet.', 'category' => 'animals', 'author_idx' => 4, 'read_time' => 8, 'featured' => true, 'img' => 'https://images.unsplash.com/photo-1568430462989-44163eb1752f?w=1200&q=80', 'content' => "Earth is home to truly enormous creatures. The blue whale, the largest animal ever to have existed, can reach 30 meters in length and weigh up to 200 tonnes.\n\nOn land, the African elephant reigns supreme at up to 6 tonnes. The tallest animal, the giraffe, can reach 5.5 meters.\n\nThese giants play crucial roles in their ecosystems. Elephants are 'ecosystem engineers,' creating water holes and clearing paths. Whales contribute to ocean nutrient cycling.\n\nTragically, many of Earth's largest animals face extinction. Conservation efforts are critical to ensuring these magnificent creatures continue to thrive."],
            ['title' => 'The Rise and Fall of Ancient Civilizations', 'slug' => 'rise-fall-ancient-civilizations', 'excerpt' => 'How did mighty empires like Rome, Egypt, and the Aztecs emerge, flourish, and ultimately collapse?', 'category' => 'civilizations', 'author_idx' => 1, 'read_time' => 15, 'featured' => true, 'img' => 'https://images.unsplash.com/photo-1564399579883-451a5d44ec08?w=1200&q=80', 'content' => "Throughout history, great civilizations have risen to prominence, achieved extraordinary feats, and eventually declined. This cycle offers profound lessons.\n\nCivilizations typically emerge from favorable geographic conditions. They flourish through innovation, strong governance, and military power.\n\nBut decline often follows success. Overextension, economic inequality, corruption, and environmental degradation combine to erode even the mightiest empires.\n\nFrom the fall of Rome in 476 AD to the collapse of the Maya civilization, these patterns remain relevant to modern societies."],
            ['title' => "The Deep Ocean: Earth's Last Frontier", 'slug' => 'deep-ocean-last-frontier', 'excerpt' => 'More than 80% of the ocean remains unmapped, harboring creatures and ecosystems we are only beginning to understand.', 'category' => 'nature', 'author_idx' => 2, 'read_time' => 11, 'featured' => false, 'img' => 'https://images.unsplash.com/photo-1551244072-5d12893278ab?w=1200&q=80', 'content' => "The deep ocean represents the least explored habitat on Earth. More than 80% of the ocean floor remains unmapped.\n\nIn the hadal zone, pressures exceed 600 atmospheres. Yet life thrives here in complete darkness. Hydrothermal vents support ecosystems based on chemosynthesis.\n\nRecent discoveries include bioluminescent creatures, fish that survive extreme pressures, and microbial communities in ocean crust.\n\nThe deep ocean also plays a critical role in climate regulation, absorbing vast amounts of carbon dioxide and heat."],
            ['title' => 'How the Brain Creates Consciousness', 'slug' => 'brain-creates-consciousness', 'excerpt' => "The hard problem of consciousness remains one of science's greatest mysteries.", 'category' => 'science', 'author_idx' => 3, 'read_time' => 13, 'featured' => false, 'img' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=1200&q=80', 'content' => "Every morning when you wake up, something remarkable happens: you become aware. This phenomenon — consciousness — is arguably the greatest unsolved mystery in science.\n\nPhilosopher David Chalmers called this the 'hard problem' of consciousness. We understand how the brain processes information, but explaining subjective experience remains elusive.\n\nSeveral theories compete. Integrated Information Theory suggests consciousness is fundamental to systems that integrate information. Global Workspace Theory proposes consciousness arises from broadcast information.\n\nWhat makes this question compelling is that it sits at the intersection of neuroscience, philosophy, physics, and artificial intelligence."],
            ['title' => 'The Mythology Behind the Zodiac', 'slug' => 'mythology-behind-zodiac', 'excerpt' => 'Each zodiac sign carries centuries of mythology, astronomical observation, and cultural significance.', 'category' => 'mythology', 'author_idx' => 0, 'read_time' => 10, 'featured' => false, 'img' => 'https://images.unsplash.com/photo-1608581905906-0d36d2857cf0?w=1200&q=80', 'content' => "The twelve signs of the zodiac are far more than sun sign horoscopes. Each carries millennia of mythological significance from Babylonian, Greek, Roman, and Egyptian traditions.\n\nThe zodiac system originated in ancient Babylon around 500 BC. The Greeks adopted and expanded the system, associating each constellation with their own myths.\n\nAries remembers the golden-fleeced ram. Leo commemorates the Nemean Lion. Sagittarius represents the wise centaur Chiron.\n\nWhile modern astronomy has moved beyond astrological associations, the zodiac remains one of humanity's oldest frameworks for understanding the cosmos."],
        ];

        foreach ($stories as $s) {
            DB::table('stories')->insert([
                'title' => $s['title'],
                'slug' => $s['slug'],
                'excerpt' => $s['excerpt'],
                'content' => $s['content'],
                'featured_image' => $s['img'],
                'category_id' => $catIds[$s['category']],
                'author_id' => $contributors[$s['author_idx']],
                'status' => 'published',
                'read_time' => $s['read_time'],
                'views' => rand(50000, 500000),
                'is_featured' => $s['featured'],
                'meta_title' => $s['title'] . ' | Curevia Stories',
                'meta_description' => $s['excerpt'],
                'published_at' => Carbon::now()->subDays(rand(1, 60)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Products ──
        $products = [
            ['name' => 'Celestron NexStar 8SE Telescope', 'slug' => 'celestron-nexstar-telescope', 'desc' => 'Computerized telescope with GoTo mount, tracking over 40,000 celestial objects.', 'price' => 1299.00, 'original' => 1499.00, 'rating' => 4.80, 'reviews' => 342, 'badge' => 'Best Seller', 'category' => 'Astronomy', 'img' => 'https://images.unsplash.com/photo-1532968961962-8a0cb3a2d4f0?w=500&q=80', 'long_desc' => "The Celestron NexStar 8SE features an 8-inch Schmidt-Cassegrain optical design with a fully automated GoTo mount locating 40,000+ objects.\n\nStarBright XLT coatings maximize light transmission. SkyAlign makes setup simple — point at three bright objects and it aligns itself.", 'features' => ['8" Schmidt-Cassegrain design', 'GoTo mount with 40,000+ objects', 'StarBright XLT coatings', 'SkyAlign technology', 'WiFi compatible'], 'specs' => ['Aperture' => '203.2mm', 'Focal Length' => '2032mm', 'Weight' => '13.6 kg', 'Mount' => 'Altazimuth']],
            ['name' => 'National Geographic Star Map', 'slug' => 'national-geographic-star-map', 'desc' => 'Large-format chart featuring all 88 constellations with star positions and mythology notes.', 'price' => 24.99, 'original' => null, 'rating' => 4.60, 'reviews' => 891, 'badge' => 'Popular', 'category' => 'Space', 'img' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=500&q=80', 'long_desc' => "Stunning star map featuring all 88 officially recognized constellations with detailed star positions, magnitudes, and mythological origins. Premium archival-quality paper.", 'features' => ['All 88 constellations', 'Premium archival paper', 'Mythological annotations', '91cm x 61cm'], 'specs' => ['Size' => '91x61cm', 'Paper' => '200gsm archival']],
            ['name' => 'Sapiens: A Brief History of Humankind', 'slug' => 'sapiens-book', 'desc' => 'Yuval Noah Harari\'s journey through human history from the Stone Age to Silicon.', 'price' => 18.99, 'original' => 24.99, 'rating' => 4.90, 'reviews' => 2140, 'badge' => 'Editor Pick', 'category' => 'Books', 'img' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=500&q=80', 'long_desc' => "In Sapiens, Dr. Yuval Noah Harari spans human history from the first humans to the radical breakthroughs of the Cognitive, Agricultural, and Scientific Revolutions.\n\nBlending science, history, and philosophy, Sapiens challenges everything about being human.", 'features' => ['512 pages', 'NY Times Bestseller', '65 languages', 'Updated 2024 edition'], 'specs' => ['Pages' => '512', 'Format' => 'Paperback', 'Publisher' => 'Harper Perennial']],
            ['name' => 'Human Anatomy 3D Model Kit', 'slug' => 'anatomy-3d-model-kit', 'desc' => 'Detailed 3D torso model with 15 removable organs for hands-on anatomy education.', 'price' => 49.99, 'original' => null, 'rating' => 4.50, 'reviews' => 456, 'badge' => 'New', 'category' => 'Science', 'img' => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=500&q=80', 'long_desc' => "Detailed human anatomy model with 15 removable organs. Each organ is accurately sculpted with educational labels. Includes comprehensive guidebook.", 'features' => ['15 removable parts', 'Life-size proportions', 'Guidebook included', 'Display stand'], 'specs' => ['Height' => '45cm', 'Material' => 'PVC medical-grade']],
            ['name' => 'Cosmos by Carl Sagan', 'slug' => 'cosmos-carl-sagan', 'desc' => 'The classic that inspired millions. One of the most influential science books ever written.', 'price' => 14.99, 'original' => null, 'rating' => 4.90, 'reviews' => 3200, 'badge' => 'Classic', 'category' => 'Books', 'img' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500&q=80', 'long_desc' => "Carl Sagan's legendary Cosmos explores the universe from atoms to galaxies, from the origins of life to human civilization. Updated edition with new introduction.", 'features' => ['365 pages', 'Updated edition', 'Full color illustrations', 'Companion to PBS series'], 'specs' => ['Pages' => '365', 'Format' => 'Paperback']],
            ['name' => 'Professional Microscope Kit', 'slug' => 'professional-microscope-kit', 'desc' => 'Lab-quality compound microscope with LED illumination and 40x-2000x magnification.', 'price' => 189.99, 'original' => 249.99, 'rating' => 4.70, 'reviews' => 178, 'badge' => 'Sale', 'category' => 'Science', 'img' => 'https://images.unsplash.com/photo-1576086213369-97a306d36557?w=500&q=80', 'long_desc' => "Professional-grade compound microscope with 40x-2000x magnification and dual LED illumination. Comes with 25 prepared slides and carrying case.", 'features' => ['40x-2000x magnification', 'Dual LED illumination', '25 prepared slides', 'Carrying case'], 'specs' => ['Magnification' => '40x-2000x', 'Weight' => '3.2 kg']],
            ['name' => 'World History Timeline Chart', 'slug' => 'world-history-timeline', 'desc' => 'Comprehensive illustrated timeline spanning 5,000 years of human civilization.', 'price' => 34.99, 'original' => null, 'rating' => 4.40, 'reviews' => 267, 'badge' => null, 'category' => 'History', 'img' => 'https://images.unsplash.com/photo-1461360228754-6e81c478b882?w=500&q=80', 'long_desc' => "Beautifully illustrated wall chart covering 5,000 years of world history. Color-coded civilizations with major events highlighted. Laminated finish.", 'features' => ['5,000 years covered', 'Color-coded', 'Laminated finish', 'Premium print'], 'specs' => ['Size' => '152x61cm', 'Material' => 'Laminated poster']],
            ['name' => 'Solar System Model Kit', 'slug' => 'solar-system-model-kit', 'desc' => 'Motorized model with accurate planet sizes, orbital paths, and LED Sun.', 'price' => 39.99, 'original' => null, 'rating' => 4.60, 'reviews' => 534, 'badge' => 'Popular', 'category' => 'Astronomy', 'img' => 'https://images.unsplash.com/photo-1545156521-77bd85671d30?w=500&q=80', 'long_desc' => "Motorized solar system model with all 8 planets in accurate relative sizes. Integrated motor for correct orbital speeds. 32-page educational guidebook.", 'features' => ['8 planets', 'Motorized orbits', '32-page guidebook', 'LED Sun'], 'specs' => ['Diameter' => '45cm', 'Power' => '4x AA batteries']],
            ['name' => 'National Parks Field Guide', 'slug' => 'national-parks-field-guide', 'desc' => 'Guide covering all 63 US national parks with wildlife identification and trail maps.', 'price' => 22.99, 'original' => null, 'rating' => 4.50, 'reviews' => 189, 'badge' => null, 'category' => 'Nature', 'img' => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=500&q=80', 'long_desc' => "Definitive guide to all 63 US national parks with wildlife identification charts, trail maps, best viewpoints, and photography tips. Waterproof pages.", 'features' => ['All 63 parks', 'Wildlife identification', 'Trail maps', 'Waterproof pages'], 'specs' => ['Pages' => '448', 'Format' => 'Spiral-bound']],
            ['name' => 'Arduino Electronics Starter Kit', 'slug' => 'arduino-starter-kit', 'desc' => 'Complete kit with Arduino UNO, 200+ components, sensors, and 15 guided projects.', 'price' => 59.99, 'original' => 79.99, 'rating' => 4.70, 'reviews' => 920, 'badge' => 'Sale', 'category' => 'Technology', 'img' => 'https://images.unsplash.com/photo-1553406830-ef2513450d76?w=500&q=80', 'long_desc' => "Ultimate starter kit for electronics. Genuine Arduino UNO board, 200+ components, multiple sensors, and 15 guided projects. Perfect for beginners and makers.", 'features' => ['Arduino UNO R3', '200+ components', '15 projects', 'Multiple sensors'], 'specs' => ['Board' => 'Arduino UNO R3', 'Components' => '200+']],
            ['name' => 'Ancient Civilizations Encyclopedia', 'slug' => 'ancient-civilizations-encyclopedia', 'desc' => 'Illustrated reference covering 50+ ancient civilizations from every continent.', 'price' => 42.99, 'original' => null, 'rating' => 4.80, 'reviews' => 312, 'badge' => 'Editor Pick', 'category' => 'History', 'img' => 'https://images.unsplash.com/photo-1564399579883-451a5d44ec08?w=500&q=80', 'long_desc' => "Lavishly illustrated encyclopedia covering more than 50 ancient civilizations. Over 500 photographs, maps, timelines. Written by leading historians.", 'features' => ['50+ civilizations', '500+ photographs', 'Maps & timelines', 'Hardcover'], 'specs' => ['Pages' => '640', 'Format' => 'Hardcover']],
            ['name' => 'Wildlife Photography Camera Trap', 'slug' => 'wildlife-camera-trap', 'desc' => 'Motion-activated trail camera with night vision, 4K video, and WiFi.', 'price' => 129.99, 'original' => 159.99, 'rating' => 4.30, 'reviews' => 87, 'badge' => 'New', 'category' => 'Nature', 'img' => 'https://images.unsplash.com/photo-1474511320723-9a56873571b7?w=500&q=80', 'long_desc' => "Professional trail camera with 4K Ultra HD, 48MP photos, invisible infrared night vision. WiFi for remote viewing. IP66 waterproof.", 'features' => ['4K Ultra HD', '48MP photos', 'IR night vision', 'WiFi', 'IP66 waterproof'], 'specs' => ['Resolution' => '48MP/4K', 'Battery' => '8x AA (6 months)']],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert([
                'name' => $p['name'],
                'slug' => $p['slug'],
                'description' => $p['desc'],
                'image' => $p['img'],
                'price' => $p['price'],
                'original_price' => $p['original'],
                'rating' => $p['rating'],
                'reviews_count' => $p['reviews'],
                'badge' => $p['badge'],
                'category' => $p['category'],
                'long_description' => $p['long_desc'],
                'features' => json_encode($p['features']),
                'specifications' => json_encode($p['specs']),
                'gallery' => null,
                'affiliate_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getArticles(): array
    {
        return [
            [
                'title' => 'Black Holes', 'slug' => 'black-holes', 'category' => 'space', 'author_idx' => 0,
                'summary' => 'Regions of spacetime where gravity is so strong that nothing, not even light, has enough energy to escape the event horizon.',
                'featured_image' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=1200&q=80',
                'read_time' => 12, 'views' => 1247000,
                'images' => ['https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=800&q=80','https://images.unsplash.com/photo-1506318137071-a8e063b4bec0?w=800&q=80','https://images.unsplash.com/photo-1454789548928-9efd52dc4031?w=800&q=80','https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=800&q=80'],
                'quick_facts' => ['First Predicted' => '1916 by Karl Schwarzschild', 'First Image' => 'April 10, 2019 (M87*)', 'Nearest Known' => 'Gaia BH1 (~1,560 ly)', 'Largest Known' => 'TON 618 — 66B solar masses', 'Types' => 'Stellar, Intermediate, Supermassive, Primordial', 'Temperature' => 'Near absolute zero at horizon', 'Speed of Growth' => 'Can consume a star in hours'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "A black hole is a region of spacetime where gravity is so strong that nothing — no particles or electromagnetic radiation — can escape once past the event horizon. The theory of general relativity predicts that a sufficiently compact mass can deform spacetime to form a black hole.\n\nThe boundary of no escape is called the event horizon. Although it has a great effect on the fate of an object crossing it, it has no locally detectable features according to general relativity."],
                    ['title' => 'History & Discovery', 'body' => "The idea of a body so massive that even light could not escape was first proposed by John Michell in 1784. In 1915, Albert Einstein's general relativity predicted black holes. The term 'black hole' was coined by John Wheeler in 1967.\n\nThe first direct image of a black hole was captured by the Event Horizon Telescope in 2019 — the supermassive black hole at the center of galaxy M87."],
                    ['title' => 'How Black Holes Form', 'body' => "Stellar black holes form when massive stars (20-25x the Sun's mass) exhaust their nuclear fuel and collapse. This gravitational collapse occurs during a supernova explosion.\n\nSupermassive black holes, containing millions to billions of solar masses, are found at the centers of most galaxies. The Milky Way's central black hole, Sagittarius A*, has about 4 million solar masses."],
                    ['title' => 'The Event Horizon', 'body' => "The event horizon is the boundary from which nothing can escape. For a non-rotating black hole, the Schwarzschild radius defines this boundary. For Earth, this radius would be just 8.87 mm.\n\nTime dilation near black holes means that to a distant observer, an object would appear to slow down and freeze at the event horizon."],
                    ['title' => 'Hawking Radiation', 'body' => "In 1974, Stephen Hawking predicted that black holes emit radiation due to quantum effects near the event horizon. This means black holes slowly lose mass and could eventually evaporate — though this would take far longer than the age of the universe.\n\nThis prediction connected quantum mechanics, thermodynamics, and general relativity."],
                ],
                'video_url' => 'https://www.youtube-nocookie.com/embed/e-P5IFTqB98',
            ],
            [
                'title' => 'Mount Everest', 'slug' => 'mount-everest', 'category' => 'earth', 'author_idx' => 5,
                'summary' => "Earth's highest mountain above sea level at 8,849 meters, located in the Himalayas on the Nepal-Tibet border.",
                'featured_image' => 'https://images.unsplash.com/photo-1516302752625-fcc3c50ae61f?w=1200&q=80',
                'read_time' => 10, 'views' => 892000,
                'images' => ['https://images.unsplash.com/photo-1516302752625-fcc3c50ae61f?w=800&q=80','https://images.unsplash.com/photo-1486911278844-a81c5267e227?w=800&q=80','https://images.unsplash.com/photo-1544735716-ea9ef790fcec?w=800&q=80','https://images.unsplash.com/photo-1580309237429-661ea0007a61?w=800&q=80'],
                'quick_facts' => ['Height' => '8,849 m (29,032 ft)', 'First Summit' => 'May 29, 1953', 'First Climbers' => 'Hillary & Tenzing Norgay', 'Location' => 'Nepal / Tibet border', 'Mountain Range' => 'Himalayas', 'Annual Summits' => '~800 climbers/year', 'Death Rate' => '~1.2% fatality rate'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Mount Everest is Earth's highest mountain above sea level, located in the Mahalangur Himal sub-range of the Himalayas. Its peak reaches 8,849 meters as determined by a 2020 survey.\n\nIn Nepali it is called Sagarmatha ('Goddess of the Sky') and in Tibetan, Chomolungma ('Goddess Mother of the World')."],
                    ['title' => 'Geological Formation', 'body' => "Mount Everest formed 50-60 million years ago when the Indian tectonic plate collided with the Eurasian plate. The summit is made of marine limestone — rocks once at the bottom of an ancient ocean.\n\nThe mountain continues to grow by about 4mm per year due to ongoing tectonic activity."],
                    ['title' => 'History of Climbing', 'body' => "The first confirmed summit was achieved on May 29, 1953, by Edmund Hillary and Tenzing Norgay. Over 6,000 individuals have since reached the summit.\n\nNotable milestones include Junko Tabei (first woman, 1975), Reinhold Messner (solo without oxygen, 1980), and Jordan Romero (youngest at 13, 2010)."],
                    ['title' => 'The Death Zone', 'body' => "Above 8,000 meters, oxygen is roughly one-third sea level. The body cannot acclimatize — it is slowly dying. Over 300 people have died attempting the climb.\n\nDangerous areas include the Khumbu Icefall, the Hillary Step, and the Balcony."],
                ],
            ],
            [
                'title' => 'Ancient Egypt', 'slug' => 'ancient-egypt', 'category' => 'history', 'author_idx' => 1,
                'summary' => 'One of the oldest and most influential civilizations, flourishing along the Nile for over 3,000 years with remarkable advances in architecture, writing, and medicine.',
                'featured_image' => 'https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=1200&q=80',
                'read_time' => 15, 'views' => 1560000,
                'images' => ['https://images.unsplash.com/photo-1539650116574-8efeb43e2750?w=800&q=80','https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?w=800&q=80','https://images.unsplash.com/photo-1568322445389-f64e1bbea832?w=800&q=80','https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=800&q=80'],
                'quick_facts' => ['Period' => '3100 BC – 30 BC', 'Capital Cities' => 'Memphis, Thebes, Alexandria', 'Writing System' => 'Hieroglyphics (700+ symbols)', 'Great Pyramid' => 'Built ~2560 BC, 146.6m', 'Pharaohs' => '170+ known rulers', 'Key River' => 'Nile (6,650 km)', 'Famous Pharaoh' => 'Tutankhamun (1332-1323 BC)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Ancient Egypt was a civilization of ancient North Africa concentrated along the Nile River. For nearly 30 centuries it was the preeminent civilization in the Mediterranean world.\n\nFrom the Great Pyramids of Giza to hieroglyphics to mummification, Egyptian civilization produced some of the most iconic monuments and cultural achievements in human history."],
                    ['title' => 'Pyramids & Architecture', 'body' => "The Great Pyramid of Giza, built around 2560 BC for Pharaoh Khufu, stood as the tallest man-made structure for over 3,800 years with an estimated 2.3 million stone blocks.\n\nEgyptian architects also created the Sphinx, the temples of Karnak and Luxor, and Abu Simbel."],
                    ['title' => 'Hieroglyphics & Writing', 'body' => "The Egyptian writing system consisted of over 700 hieroglyphic symbols. The Rosetta Stone discovery in 1799 allowed modern scholars to decode them.\n\nEgyptians wrote on papyrus scrolls and produced extensive records of governance, religion, medicine, and daily life."],
                    ['title' => 'Mummification & Afterlife', 'body' => "Egyptians developed elaborate burial practices for the afterlife. Mummification involved removing organs, drying with natron salt, and linen wrapping — taking up to 70 days.\n\nThe Book of the Dead contained spells and instructions to guide the deceased through the underworld."],
                ],
            ],
            [
                'title' => 'Amazon Rainforest', 'slug' => 'amazon-rainforest', 'category' => 'nature', 'author_idx' => 2,
                'summary' => 'The world\'s largest tropical rainforest covering over 5.5 million km² across South America, home to 10% of all species on Earth.',
                'featured_image' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=1200&q=80',
                'read_time' => 9, 'views' => 730000,
                'images' => ['https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=800&q=80','https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80','https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800&q=80','https://images.unsplash.com/photo-1470058869958-2a77e919e0d2?w=800&q=80'],
                'quick_facts' => ['Area' => '5.5M km²', 'Countries' => '9 (Brazil has 60%)', 'Tree Species' => '~16,000', 'Animal Species' => '~2.5M insect species', 'Oxygen' => '~6% of world\'s oxygen', 'River Length' => '6,400 km', 'Indigenous Peoples' => '~400 tribes'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Amazon rainforest is a moist broadleaf tropical rainforest covering most of the Amazon basin. This encompassing 7,000,000 km², of which 5,500,000 km² are forest.\n\nThe majority is within Brazil (60%), followed by Peru (13%) and Colombia (10%)."],
                    ['title' => 'Biodiversity', 'body' => "The Amazon is the most biodiverse region on Earth with approximately 390 billion trees among 16,000 species. One in ten known species lives here.\n\nNew species are discovered at an average of one every two days."],
                    ['title' => 'Threats & Conservation', 'body' => "Since 1970, over 17% of the forest has been destroyed for cattle ranching, agriculture, and logging.\n\nScientists warn continued deforestation could push the Amazon past a tipping point, transforming it from a carbon sink into a carbon source."],
                ],
            ],
            [
                'title' => 'Human Brain', 'slug' => 'human-brain', 'category' => 'human-body', 'author_idx' => 3,
                'summary' => 'The most complex organ with approximately 86 billion neurons, controlling thought, memory, emotion, and every process regulating our body.',
                'featured_image' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=1200&q=80',
                'read_time' => 11, 'views' => 985000,
                'images' => ['https://images.unsplash.com/photo-1559757175-5700dde675bc?w=800&q=80','https://images.unsplash.com/photo-1617791160505-6f00504e3519?w=800&q=80','https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=800&q=80','https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=800&q=80'],
                'quick_facts' => ['Weight' => '~1.4 kg (3 lbs)', 'Neurons' => '~86 billion', 'Synapses' => '~100 trillion', 'Energy Use' => '20% of body total', 'Water Content' => '~75%', 'Processing Speed' => '~120 m/s', 'Memory Capacity' => '~2.5 petabytes'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The human brain is the central organ of the nervous system. Weighing about 1.4 kg, it contains roughly 86 billion neurons connected by trillions of synapses.\n\nDespite being only 2% of body weight, the brain consumes approximately 20% of total energy."],
                    ['title' => 'Brain Structure', 'body' => "The brain consists of the cerebrum (thinking, voluntary actions), cerebellum (balance, coordination), and brainstem (automatic functions).\n\nThe cerebral cortex is folded into gyri and sulci, vastly increasing surface area for neural processing."],
                    ['title' => 'How Neurons Work', 'body' => "Neurons communicate through electrical impulses and chemical signals across synapses. Each neuron can form thousands of connections.\n\nNeuroplasticity, the brain's ability to reorganize, underlies learning, memory, and recovery from injury."],
                    ['title' => 'Consciousness & Memory', 'body' => "How the brain creates consciousness remains one of science's greatest unsolved mysteries — the 'hard problem of consciousness.'\n\nThe brain's estimated storage capacity is approximately 2.5 petabytes — enough to store 3 million hours of TV."],
                ],
            ],
            [
                'title' => 'Roman Empire', 'slug' => 'roman-empire', 'category' => 'civilizations', 'author_idx' => 1,
                'summary' => 'One of the largest empires in ancient history, at its peak controlling around 5 million km² and 70 million people across Europe, North Africa, and the Middle East.',
                'featured_image' => 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=1200&q=80',
                'read_time' => 14, 'views' => 1120000,
                'images' => ['https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800&q=80','https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&q=80','https://images.unsplash.com/photo-1564399579883-451a5d44ec08?w=800&q=80','https://images.unsplash.com/photo-1523978591478-c753949ff840?w=800&q=80'],
                'quick_facts' => ['Duration' => '27 BC – 476 AD', 'Peak Area' => '5M km² (117 AD)', 'Population' => '~70M (25% of world)', 'Capital' => 'Rome / Constantinople', 'Language' => 'Latin (Greek in east)', 'Famous Emperor' => 'Augustus (first)', 'Road Network' => '400,000 km'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Starting with Augustus in 27 BC, the Roman Empire grew to become one of the largest empires in the ancient world. At its peak under Trajan in 117 AD, it covered 5 million km².\n\nRoman civilization profoundly shaped Western culture in language, law, architecture, engineering, and religion."],
                    ['title' => 'Rise of the Empire', 'body' => "The transformation from Republic to Empire began with Caesar's crossing of the Rubicon in 49 BC. Octavian (Augustus) became the first Emperor, inaugurating the Pax Romana — about 200 years of relative peace."],
                    ['title' => 'Engineering & Architecture', 'body' => "Romans built 400,000 km of roads. The Colosseum could seat 50,000-80,000 spectators. Roman aqueducts, concrete, arches, and domes influenced architecture for millennia."],
                    ['title' => 'Fall of Rome', 'body' => "The Western Roman Empire fell in 476 AD through gradual decline — military overextension, economic troubles, political instability, and barbarian invasions.\n\nThe Eastern Byzantine Empire continued for another thousand years until 1453."],
                ],
            ],
            [
                'title' => 'Milky Way Galaxy', 'slug' => 'milky-way-galaxy', 'category' => 'space', 'author_idx' => 0,
                'summary' => 'Our home galaxy, a barred spiral galaxy containing 100-400 billion stars spanning approximately 100,000 light-years.',
                'featured_image' => 'https://images.unsplash.com/photo-1506318137071-a8e063b4bec0?w=1200&q=80',
                'read_time' => 10, 'views' => 670000,
                'images' => ['https://images.unsplash.com/photo-1506318137071-a8e063b4bec0?w=800&q=80','https://images.unsplash.com/photo-1419242902214-272b3f66ee7a?w=800&q=80','https://images.unsplash.com/photo-1444703686981-a3abbc4d4fe3?w=800&q=80','https://images.unsplash.com/photo-1465101162946-4377e57745c3?w=800&q=80'],
                'quick_facts' => ['Type' => 'Barred spiral galaxy', 'Diameter' => '~100,000 light-years', 'Stars' => '100-400 billion', 'Age' => '~13.6 billion years', 'Central Black Hole' => 'Sagittarius A*', 'Sun Position' => '26,000 ly from center', 'Orbital Period' => '225-250 million years'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Milky Way is the galaxy that includes our Solar System. It is a barred spiral galaxy with a diameter of 100,000 to 180,000 light-years containing 100-400 billion stars.\n\nOur Solar System is located about 26,000 light-years from the galactic center."],
                    ['title' => 'Structure', 'body' => "The galaxy has a central bulge, a disk with spiral arms, and a surrounding halo. The disk is about 2,000 light-years thick.\n\nOur Solar System lies within the Orion Arm, between the Perseus and Sagittarius arms."],
                    ['title' => 'Sagittarius A*', 'body' => "The supermassive black hole Sagittarius A* at the center has 4 million solar masses. The Event Horizon Telescope released its first image in 2022.\n\nThe star S2 orbits it at speeds exceeding 7,600 km/s — 2.5% the speed of light."],
                ],
            ],
            [
                'title' => 'Great Barrier Reef', 'slug' => 'great-barrier-reef', 'category' => 'nature', 'author_idx' => 2,
                'summary' => 'The world\'s largest coral reef system with over 2,900 individual reefs stretching 2,300 km along Australia\'s northeast coast.',
                'featured_image' => 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=1200&q=80',
                'read_time' => 8, 'views' => 520000,
                'images' => ['https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800&q=80','https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80','https://images.unsplash.com/photo-1583212292454-1fe6229603b7?w=800&q=80','https://images.unsplash.com/photo-1546026423-cc4642628d2b?w=800&q=80'],
                'quick_facts' => ['Length' => '2,300 km', 'Reefs' => '2,900+', 'Area' => '344,400 km²', 'Coral Species' => '400+', 'Fish Species' => '1,500+', 'UNESCO Status' => 'World Heritage (1981)', 'Visible From' => 'Space'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Great Barrier Reef is the world's largest coral reef system, composed of over 2,900 individual reefs stretching 2,300 km. It is the largest structure made by living organisms.\n\nDesignated a UNESCO World Heritage Site in 1981 and considered one of the seven natural wonders."],
                    ['title' => 'Marine Life', 'body' => "Home to over 1,500 species of fish, 4,000 types of mollusk, 240 species of birds, and 400 types of coral. Six species of sea turtles breed here.\n\nThis incredible biodiversity makes it one of the most important natural ecosystems on Earth."],
                    ['title' => 'Threats', 'body' => "Rising temperatures cause coral bleaching. Mass events occurred in 2016, 2017, 2020, and 2022.\n\nThe reef has lost half its coral cover since 1995 due to climate change, acidification, cyclones, and pollution."],
                ],
            ],
            [
                'title' => 'DNA & Genetics', 'slug' => 'dna-genetics', 'category' => 'science', 'author_idx' => 3,
                'summary' => 'DNA is the molecule carrying genetic instructions for growth, development, functioning, and reproduction of all known living organisms.',
                'featured_image' => 'https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=1200&q=80',
                'read_time' => 13, 'views' => 610000,
                'images' => ['https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=800&q=80','https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=800&q=80','https://images.unsplash.com/photo-1576086213369-97a306d36557?w=800&q=80','https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=800&q=80'],
                'quick_facts' => ['Full Name' => 'Deoxyribonucleic Acid', 'Structure' => 'Double helix', 'Discovered' => 'Watson & Crick (1953)', 'Base Pairs' => '~3.2 billion (human)', 'Genes' => '20,000-25,000 in humans', 'Similarity' => '99.9% identical between humans', 'Sequenced' => '2003 (Human Genome Project)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "DNA is the hereditary material in virtually all living organisms. Its double-helix structure was described by Watson and Crick in 1953.\n\nHuman DNA has 3.2 billion base pairs in 23 chromosome pairs. Only about 1.5% codes for proteins."],
                    ['title' => 'The Double Helix', 'body' => "Two strands wound together like a twisted ladder. The rungs are base pairs: A-T and C-G. The base sequence constitutes the genetic code.\n\nIf uncoiled, DNA in a single cell would stretch approximately 2 meters."],
                    ['title' => 'CRISPR & Gene Editing', 'body' => "CRISPR-Cas9 allows scientists to precisely cut and modify DNA. It has been used in trials for sickle cell disease, cancers, and hereditary blindness.\n\nEthical debates continue, especially regarding germline editing that could be passed to future generations."],
                ],
                'video_url' => 'https://www.youtube-nocookie.com/embed/zwibgNGe4aY',
            ],
            [
                'title' => 'Greek Mythology', 'slug' => 'greek-mythology', 'category' => 'mythology', 'author_idx' => 1,
                'summary' => 'The body of myths of the ancient Greeks concerning their gods, heroes, the nature of the world, and the significance of their ritual practices.',
                'featured_image' => 'https://images.unsplash.com/photo-1608581905906-0d36d2857cf0?w=1200&q=80',
                'read_time' => 12, 'views' => 840000,
                'images' => ['https://images.unsplash.com/photo-1608581905906-0d36d2857cf0?w=800&q=80','https://images.unsplash.com/photo-1564399579883-451a5d44ec08?w=800&q=80','https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&q=80','https://images.unsplash.com/photo-1504730030853-eff311f57d3c?w=800&q=80'],
                'quick_facts' => ['Period' => '~900 BC – 146 BC', 'Major Gods' => '12 Olympians', 'King of Gods' => 'Zeus', 'Primary Sources' => 'Homer\'s Iliad & Odyssey', 'Mount Olympus' => '2,917 m', 'Heroes' => 'Heracles, Achilles, Odysseus', 'Legacy' => 'Basis for Roman mythology'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Greek mythology encompasses myths and legends about gods, heroes, and the nature of the world. These stories formed the bedrock of ancient Greek religion and culture.\n\nGreek myths continue to inspire art, literature, philosophy, and popular culture worldwide."],
                    ['title' => 'The Olympian Gods', 'body' => "The twelve Olympians resided atop Mount Olympus. Zeus controlled sky and thunder. Poseidon ruled the sea, Hades the underworld.\n\nOther Olympians included Athena (wisdom), Apollo (sun), Artemis (hunt), Aphrodite (love), and Hermes (messengers)."],
                    ['title' => 'Heroes & Legends', 'body' => "Heracles performed the Twelve Labors. Odysseus journeyed home for ten years after Troy. Achilles was invulnerable except for his heel.\n\nThese stories of heroism, hubris, and fate remain compelling narratives across millennia."],
                    ['title' => 'Creation Myths', 'body' => "From Chaos arose Gaia, Tartarus, and Eros. Gaia bore Uranus, producing the Titans. Cronus overthrew Uranus, then was overthrown by Zeus.\n\nZeus's victory in the Titanomachy established the reign of the Olympians."],
                ],
            ],
            [
                'title' => 'Blue Whale', 'slug' => 'blue-whale', 'category' => 'animals', 'author_idx' => 4,
                'summary' => 'The largest animal known to have ever existed, reaching 30 meters and 200 tonnes — heavier than any dinosaur.',
                'featured_image' => 'https://images.unsplash.com/photo-1568430462989-44163eb1752f?w=1200&q=80',
                'read_time' => 7, 'views' => 480000,
                'images' => ['https://images.unsplash.com/photo-1568430462989-44163eb1752f?w=800&q=80','https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80','https://images.unsplash.com/photo-1559827291-baf5c1dc6ef4?w=800&q=80','https://images.unsplash.com/photo-1551244072-5d12893278ab?w=800&q=80'],
                'quick_facts' => ['Length' => 'Up to 30m (98 ft)', 'Weight' => 'Up to 200 tonnes', 'Heart Size' => 'Size of a small car', 'Diet' => 'Krill (3,600 kg/day)', 'Lifespan' => '80-90 years', 'Population' => '10,000-25,000', 'Loudest Animal' => '188 dB (heard 800 km away)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The blue whale is the largest animal known to have ever existed. Despite their size, blue whales feed almost exclusively on tiny krill — up to 3,600 kg in a single day."],
                    ['title' => 'Anatomy & Size', 'body' => "A blue whale's heart weighs ~180 kg. Its heartbeat can be detected from 3 km away. Calves are 7m at birth and gain 90 kg per day in their first year."],
                    ['title' => 'Communication', 'body' => "Blue whales are the loudest animals on Earth at 188 dB — heard 800 km away. Their call pitch has been decreasing for decades — a scientific mystery."],
                    ['title' => 'Conservation', 'body' => "Hunted nearly to extinction in the 20th century. By 1966, only 5,000-10,000 remained. Today 10,000-25,000 exist worldwide, classified as Endangered."],
                ],
            ],
            [
                'title' => 'Artificial Intelligence', 'slug' => 'artificial-intelligence', 'category' => 'technology', 'author_idx' => 3,
                'summary' => 'Intelligence demonstrated by machines, capable of learning, reasoning, problem-solving, and language understanding — transforming every industry.',
                'featured_image' => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&q=80',
                'read_time' => 10, 'views' => 1890000,
                'images' => ['https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=800&q=80','https://images.unsplash.com/photo-1555255707-c07966088b7b?w=800&q=80','https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&q=80','https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=800&q=80'],
                'quick_facts' => ['Term Coined' => '1956 by John McCarthy', 'Key Milestone' => 'Deep Blue beats Kasparov (1997)', 'Market Size' => '$200+ billion (2025)', 'ChatGPT' => '100M users in 2 months', 'Types' => 'Narrow, General, Super AI', 'Techniques' => 'ML, deep learning, NLP', 'Impact' => '$15.7T GDP by 2030'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "AI is the simulation of human intelligence by computer systems — learning, reasoning, and self-correction.\n\nFrom voice assistants to autonomous vehicles to medical diagnosis, AI is transforming virtually every industry."],
                    ['title' => 'History', 'body' => "Founded at the 1956 Dartmouth Conference. Key milestones: Deep Blue (1997), AlphaGo (2016), ChatGPT (2022) reaching 100 million users in two months."],
                    ['title' => 'Machine Learning', 'body' => "ML systems learn from data without explicit programming. Deep learning uses neural networks for complex patterns. Transformer architecture (2017) led to GPT-4, Claude, and Gemini."],
                    ['title' => 'Ethics & Future', 'body' => "Concerns include algorithmic bias, job displacement, privacy, and autonomous weapons. PwC estimates AI will contribute $15.7 trillion to global GDP by 2030."],
                ],
                'video_url' => 'https://www.youtube-nocookie.com/embed/5dZ_lvDgevk',
            ],
            [
                'title' => 'Mars', 'slug' => 'mars', 'category' => 'space', 'author_idx' => 0,
                'summary' => 'The fourth planet from the Sun and the second-smallest planet in the Solar System, known as the Red Planet due to iron oxide on its surface.',
                'featured_image' => 'https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?w=1200&q=80',
                'read_time' => 9, 'views' => 740000,
                'images' => ['https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?w=800&q=80','https://images.unsplash.com/photo-1545243424-0ce743321e11?w=800&q=80','https://images.unsplash.com/photo-1630694093867-4b947d812bf0?w=800&q=80','https://images.unsplash.com/photo-1446776709462-d6b525b55056?w=800&q=80'],
                'quick_facts' => ['Distance from Sun' => '227.9 million km avg', 'Day Length' => '24h 37min (sol)', 'Year Length' => '687 Earth days', 'Moons' => 'Phobos & Deimos', 'Largest Volcano' => 'Olympus Mons (21.9 km tall)', 'Atmosphere' => '95% CO₂, very thin', 'First Landing' => 'Viking 1 — July 20, 1976'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Mars is the fourth planet from the Sun and the second-smallest in the Solar System. Its reddish appearance, caused by iron oxide (rust) on its surface, earned it the name the Red Planet.\n\nMars has been a target of exploration for decades and remains humanity's most likely destination for future crewed missions."],
                    ['title' => 'Surface & Geology', 'body' => "Mars has the tallest volcano in the Solar System — Olympus Mons at 21.9 km — and the longest canyon, Valles Marineris, stretching 4,000 km.\n\nDespite its thin atmosphere, evidence of ancient river valleys and lake beds suggests Mars once had liquid water on its surface."],
                    ['title' => 'Atmosphere & Climate', 'body' => "The Martian atmosphere is 95% CO₂ but incredibly thin — surface pressure is less than 1% of Earth's. Temperatures range from -125°C at the poles to 20°C near the equator.\n\nDust storms can engulf the entire planet for months, dramatically reducing solar power for rovers."],
                    ['title' => 'Mars Missions', 'body' => "NASA's Perseverance rover (2021) is collecting rock samples for return to Earth. The Ingenuity helicopter became the first powered aircraft to fly on another planet.\n\nArtemis and SpaceX's Starship plan for a crewed Mars landing in the 2030s."],
                ],
            ],
            [
                'title' => 'Sahara Desert', 'slug' => 'sahara-desert', 'category' => 'earth', 'author_idx' => 5,
                'summary' => 'The world\'s largest hot desert covering 9.2 million km² across North Africa, a landscape of vast sand dunes, rocky plateaus, and ancient trade routes.',
                'featured_image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=1200&q=80',
                'read_time' => 8, 'views' => 580000,
                'images' => ['https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80','https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80','https://images.unsplash.com/photo-1548777123-e216912df7d8?w=800&q=80','https://images.unsplash.com/photo-1542401886-65d6c61db217?w=800&q=80'],
                'quick_facts' => ['Area' => '9.2 million km²', 'Countries' => '11 African nations', 'Highest Temp' => '58°C (136°F)', 'Annual Rainfall' => 'Less than 25 mm', 'Largest Sand Sea' => 'Issaouane Erg (38,800 km²)', 'Nile River' => 'Flows through eastern edge', 'Age' => '~5-7 million years old'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Sahara is the world's largest hot desert and third largest desert overall (after Antarctica and the Arctic). It spans 11 countries across North Africa from the Atlantic coast to the Red Sea.\n\nDespite its harsh conditions, the Sahara supports nomadic peoples, ancient trade routes, and remarkable wildlife."],
                    ['title' => 'Landscape & Geography', 'body' => "Only about 25% of the Sahara is sandy erg (sand sea). The rest is rocky hamada (stone plateaus), reg (gravel plains), mountains, and salt flats.\n\nThe Ahaggar Mountains reach 2,918 meters; the Tibesti Massif includes volcanic peaks up to 3,415m."],
                    ['title' => 'Life in the Desert', 'body' => "Despite extremes, the Sahara hosts 70 species of mammals, 90 species of birds, and 100 species of reptiles. The fennec fox, addax antelope, and deathstalker scorpion are iconic residents.\n\nThe Tuareg people have navigated these lands for thousands of years, using camel caravans across historic trade routes."],
                    ['title' => 'Climate Change & Green Sahara', 'body' => "Scientists have documented the 'Green Sahara' — periods when rainfall transformed the desert into a lush savanna with rivers and lakes, most recently 5,000-11,000 years ago.\n\nModeling suggests climate change could trigger another greening cycle by the end of this century."],
                ],
            ],
            [
                'title' => 'World War II', 'slug' => 'world-war-ii', 'category' => 'history', 'author_idx' => 1,
                'summary' => 'The deadliest conflict in human history (1939–1945) involving over 30 countries, resulting in 70–85 million deaths and reshaping the world\'s geopolitical order.',
                'featured_image' => 'https://images.unsplash.com/photo-1580130379624-3a069adbffc5?w=1200&q=80',
                'read_time' => 16, 'views' => 2100000,
                'images' => ['https://images.unsplash.com/photo-1580130379624-3a069adbffc5?w=800&q=80','https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=800&q=80','https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&q=80','https://images.unsplash.com/photo-1495121605193-b116b5b9c9e6?w=800&q=80'],
                'quick_facts' => ['Duration' => 'September 1939 – September 1945', 'Total Deaths' => '70–85 million', 'Countries Involved' => '30+', 'Holocaust Victims' => '~6 million Jews', 'D-Day' => 'June 6, 1944', 'Atomic Bombs' => 'Hiroshima & Nagasaki (1945)', 'Cost' => '$4.1 trillion (2023 USD)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "World War II was a global conflict lasting from 1939 to 1945 involving most of the world's nations. The Allied Powers (UK, US, USSR, France, China) fought the Axis Powers (Germany, Italy, Japan).\n\nWith 70–85 million fatalities, it was the deadliest conflict in history, affecting every inhabited continent."],
                    ['title' => 'Causes & Rise of Fascism', 'body' => "The Treaty of Versailles' harsh conditions on Germany after WWI fueled resentment. Adolf Hitler's Nazi party rose to power in 1933, promoting extreme nationalism and expansionism.\n\nGermany's invasion of Poland on September 1, 1939, triggered declarations of war from Britain and France."],
                    ['title' => 'Major Turning Points', 'body' => "The Battle of Stalingrad (1942–43) turned the tide against Germany on the Eastern Front — the costliest battle in history with nearly 2 million casualties.\n\nD-Day (June 6, 1944) saw 156,000 Allied troops storm Normandy beaches, beginning the liberation of Western Europe."],
                    ['title' => 'The Holocaust', 'body' => "Nazi Germany systematically murdered approximately 6 million Jews alongside millions of Roma, disabled people, political prisoners, and others in death camps across Europe.\n\nThe Nuremberg trials established international law for war crimes and crimes against humanity."],
                    ['title' => 'End & Legacy', 'body' => "Germany surrendered May 8, 1945 (V-E Day). The US dropped atomic bombs on Hiroshima and Nagasaki in August 1945, leading to Japan's surrender on September 2.\n\nThe post-war order created the United Nations, NATO, the Marshall Plan, and set the stage for the Cold War."],
                ],
                'video_url' => 'https://www.youtube-nocookie.com/embed/DwKPFT-RioU',
            ],
            [
                'title' => 'Human Heart', 'slug' => 'human-heart', 'category' => 'human-body', 'author_idx' => 3,
                'summary' => 'A fist-sized muscle that beats around 100,000 times per day, pumping approximately 7,600 liters of blood through 96,000 km of blood vessels.',
                'featured_image' => 'https://images.unsplash.com/photo-1616279969856-759f316a5ac1?w=1200&q=80',
                'read_time' => 10, 'views' => 670000,
                'images' => ['https://images.unsplash.com/photo-1616279969856-759f316a5ac1?w=800&q=80','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&q=80','https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=800&q=80','https://images.unsplash.com/photo-1576086213369-97a306d36557?w=800&q=80'],
                'quick_facts' => ['Size' => 'Fist-sized (~300g)', 'Beats per Day' => '~100,000', 'Blood Pumped Daily' => '~7,600 liters', 'Chambers' => '4 (2 atria, 2 ventricles)', 'Blood Vessels' => '~96,000 km total length', 'Heartbeat Speed' => 'Nerve signal at 0.1 seconds', 'First Transplant' => 'Dr. Christiaan Barnard, 1967'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The heart is a muscular organ about the size of a fist, located slightly left of center in the chest. It beats approximately 100,000 times per day, pumping blood through roughly 96,000 km of blood vessels.\n\nIf all your blood vessels were laid end to end, they would circle the Earth twice."],
                    ['title' => 'Structure', 'body' => "The heart has four chambers: the right atrium and ventricle handle deoxygenated blood to the lungs; the left atrium and ventricle pump oxygenated blood to the body.\n\nFour valves — mitral, tricuspid, aortic, and pulmonary — ensure blood flows in one direction only."],
                    ['title' => 'Electrical System', 'body' => "The sinoatrial (SA) node acts as a natural pacemaker, firing electrical impulses that spread through the heart muscle. This signal triggers the coordinated contraction we feel as a heartbeat.\n\nAn ECG (electrocardiogram) records these electrical patterns, revealing heart rhythm and health."],
                    ['title' => 'Heart Disease & Prevention', 'body' => "Cardiovascular disease is the leading cause of death globally, responsible for 17.9 million deaths annually. Risk factors include high blood pressure, high cholesterol, smoking, and diabetes.\n\nRegular exercise, Mediterranean diet, and not smoking can reduce heart disease risk by over 80%."],
                ],
            ],
            [
                'title' => 'Quantum Physics', 'slug' => 'quantum-physics', 'category' => 'science', 'author_idx' => 3,
                'summary' => 'The branch of physics describing the behavior of matter and energy at the smallest scales — atoms and subatomic particles — where the rules of classical physics break down.',
                'featured_image' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=1200&q=80',
                'read_time' => 14, 'views' => 820000,
                'images' => ['https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&q=80','https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=800&q=80','https://images.unsplash.com/photo-1581090464777-f3220bbe1b8b?w=800&q=80','https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=800&q=80'],
                'quick_facts' => ['Born' => '~1900 with Planck\'s quantum theory', 'Key Principle' => 'Wave-particle duality', 'Heisenberg\'s Principle' => 'Cannot know position + momentum simultaneously', 'Schrödinger\'s Cat' => 'Famous thought experiment (1935)', 'Nobel Prizes' => '30+ for quantum discoveries', 'Applications' => 'Lasers, MRI, transistors, solar cells', 'Quantum Computing' => 'Uses qubits instead of bits'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "Quantum physics describes nature at the smallest scales of energy levels of atoms and subatomic particles. It emerged in the early 20th century when classical physics failed to explain phenomena like blackbody radiation.\n\nQuantum mechanics has led to technologies including lasers, MRI machines, transistors, and semiconductors that underpin the modern world."],
                    ['title' => 'Wave-Particle Duality', 'body' => "Particles like electrons behave as both waves and particles depending on how they are observed — the double-slit experiment demonstrates this strikingly.\n\nThe act of measurement itself affects what is measured, a concept deeply alien to classical physics."],
                    ['title' => 'Superposition & Entanglement', 'body' => "Superposition allows particles to exist in multiple states simultaneously until measured. Schrödinger's famous cat thought experiment illustrated this paradox.\n\nQuantum entanglement links two particles so that measuring one instantly affects the other, regardless of distance — what Einstein called 'spooky action at a distance.'"],
                    ['title' => 'Quantum Computing', 'body' => "Unlike classical bits (0 or 1), quantum bits (qubits) exploit superposition to process multiple states simultaneously. Google's Sycamore processor performed a calculation in 200 seconds that would take a classical computer 10,000 years.\n\nQuantum computers could revolutionize drug discovery, cryptography, and optimization problems."],
                ],
                'video_url' => 'https://www.youtube-nocookie.com/embed/Da-2h2B4faU',
            ],
            [
                'title' => 'The Internet', 'slug' => 'the-internet', 'category' => 'technology', 'author_idx' => 3,
                'summary' => 'The global system of interconnected networks enabling billions of people to communicate, share knowledge, and access information — the most transformative technology of the modern era.',
                'featured_image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&q=80',
                'read_time' => 11, 'views' => 1120000,
                'images' => ['https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&q=80','https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&q=80','https://images.unsplash.com/photo-1516110833967-0b5716ca1387?w=800&q=80','https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=800&q=80'],
                'quick_facts' => ['Origins' => 'ARPANET — 1969', 'WWW Invented' => 'Tim Berners-Lee, 1989', 'Users' => '5.4 billion (67% of world)', 'Daily Email' => '~333 billion sent per day', 'Data Created Daily' => '2.5 quintillion bytes', 'Submarine Cables' => '400+ cables, 1.3M km undersea', 'Largest Site' => 'Google (8.5B+ searches/day)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Internet is a vast network of networks that links billions of devices worldwide using standardized protocols. It began as ARPANET in 1969, a US Defense Department project.\n\nToday 5.4 billion people use the internet — two-thirds of humanity — making it the most transformative communication technology ever created."],
                    ['title' => 'How It Works', 'body' => "Data travels in packets through routers across physical infrastructure — fiber optic cables, satellites, and wireless networks. The TCP/IP protocol suite governs how data is addressed and transmitted.\n\nOver 400 submarine cables spanning 1.3 million km carry approximately 95% of all international internet traffic."],
                    ['title' => 'The World Wide Web', 'body' => "Tim Berners-Lee invented the World Wide Web at CERN in 1989 — a system of hyperlinked documents accessible via the Internet using HTTP and HTML.\n\nThe first website went live in 1991. Today over 1.9 billion websites exist, though only about 200 million are actively maintained."],
                    ['title' => 'Impact on Society', 'body' => "The internet has transformed commerce (e-commerce was $5.8T in 2023), education, healthcare, news, and social connection. It has democratized access to information.\n\nChallenges include misinformation, privacy erosion, cybercrime ($8 trillion in damages in 2023), and the digital divide affecting billions without access."],
                ],
            ],
            [
                'title' => 'Snow Leopard', 'slug' => 'snow-leopard', 'category' => 'animals', 'author_idx' => 4,
                'summary' => 'The elusive "ghost of the mountains" — a large cat native to Central Asia\'s mountain ranges, adapted to cold, rugged terrain with stunning spotted fur.',
                'featured_image' => 'https://images.unsplash.com/photo-1456926631375-92c8ce872def?w=1200&q=80',
                'read_time' => 7, 'views' => 410000,
                'images' => ['https://images.unsplash.com/photo-1456926631375-92c8ce872def?w=800&q=80','https://images.unsplash.com/photo-1557800636-894a64c1696f?w=800&q=80','https://images.unsplash.com/photo-1517479149777-5f3b1511d5ad?w=800&q=80','https://images.unsplash.com/photo-1534567110353-a6e35697746e?w=800&q=80'],
                'quick_facts' => ['Range' => '12 countries in Central Asia', 'Altitude' => '3,000–5,500 m', 'Weight' => '22–55 kg', 'Tail Length' => 'Nearly as long as body', 'Diet' => 'Bharal, ibex, marmots', 'Population' => '2,000–6,000 wild', 'Status' => 'Vulnerable (IUCN)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The snow leopard is a large cat native to the mountain ranges of Central and South Asia, from the Himalayas to the Altai. Known as the 'ghost of the mountains,' it is rarely seen in the wild.\n\nIts thick, pale-gray to creamy coat patterned with dark rosettes provides perfect camouflage against rocky terrain."],
                    ['title' => 'Adaptations', 'body' => "Snow leopards have wide, fur-covered paws that act as snowshoes, a long tail used for balance and warmth, and enlarged nasal cavities to warm cold air.\n\nTheir powerful legs can leap as far as 15 meters, and they are the only big cat that cannot roar — instead they make a chuffing sound called a 'prusten.'"],
                    ['title' => 'Habitat & Range', 'body' => "Snow leopards live across 12 countries including China, Mongolia, Russia, Afghanistan, Pakistan, India, and Nepal. Their total range spans about 1.8 million km².\n\nThey prefer steep, rugged terrain with rocky outcrops and cliffs, patrolling vast territories up to 1,000 km²."],
                    ['title' => 'Conservation', 'body' => "Classified as Vulnerable with an estimated 2,000–6,000 individuals remaining in the wild. Threats include poaching for fur and bones, retaliatory killing by herders, and habitat loss.\n\nThe Snow Leopard Trust works with local communities to protect both livestock and leopards through innovative coexistence programs."],
                ],
            ],
            [
                'title' => 'Viking Age', 'slug' => 'viking-age', 'category' => 'history', 'author_idx' => 1,
                'summary' => 'The era (793–1066 AD) when Norse seafarers from Scandinavia explored, raided, and settled across Europe, North Atlantic, and even reached North America.',
                'featured_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80',
                'read_time' => 11, 'views' => 760000,
                'images' => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80','https://images.unsplash.com/photo-1466442929976-97f336a657be?w=800&q=80','https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?w=800&q=80','https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=80'],
                'quick_facts' => ['Period' => '793 AD – 1066 AD', 'Origin' => 'Scandinavia (Denmark, Norway, Sweden)', 'First Raid' => 'Lindisfarne, England (793 AD)', 'North America' => 'Leif Erikson reached ~1000 AD', 'Ships' => 'Longships (30m, shallow draft)', 'Writing' => 'Runic alphabet', 'Religion' => 'Norse paganism (Odin, Thor, Freya)'],
                'content_sections' => [
                    ['title' => 'Overview', 'body' => "The Viking Age began with the raid on Lindisfarne monastery in 793 AD and lasted until the Norman Conquest of England in 1066. Norse explorers, traders, and warriors transformed European history.\n\nThe word 'Viking' likely derives from the Old Norse 'víkingr,' meaning a pirate or sea raider."],
                    ['title' => 'Longships & Navigation', 'body' => "Viking longships were engineering marvels — shallow-drafted enough to navigate rivers, yet seaworthy enough for ocean crossings. A typical longship was 23–24 meters long carrying 25–30 warriors.\n\nVikings navigated using the sun, stars, ocean currents, and possibly magnetite (lodestone) crystals."],
                    ['title' => 'Exploration & Settlements', 'body' => "Vikings settled Iceland (~874 AD), Greenland (~985 AD), and reached North America at L'Anse aux Meadows in Newfoundland around 1000 AD — 500 years before Columbus.\n\nThey established the Duchy of Normandy in France and founded the Kievan Rus state in Eastern Europe."],
                    ['title' => 'Norse Mythology & Culture', 'body' => "Vikings worshipped a pantheon of gods including Odin (wisdom), Thor (thunder), and Freya (love). Valhalla was the great hall where warriors slain in battle feasted eternally.\n\nThe rich sagas literature — stories of gods, heroes, and history — preserve enormous cultural knowledge from this era."],
                ],
            ],
        ];
    }
}
