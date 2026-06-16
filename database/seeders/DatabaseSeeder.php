<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Get or create test user
        $user = User::firstOrCreate(
            ['email' => 'daffabdullah111@gmail.com'],
            [
                'name' => 'Daffa Abdullah',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Predefined realistic code snippets formatted in WYSIWYG HTML style
        $snippets = [
            [
                'title' => 'Laravel Route Group',
                'content' => '<div>Grup rute Laravel dengan proteksi middleware <strong>auth</strong> dan <strong>verified</strong>:</div><pre><code>Route::middleware([\'auth\', \'verified\'])->group(function () {
    Route::get(\'/dashboard\', [DashboardController::class, \'index\'])->name(\'dashboard\');
});</code></pre>',
            ],
            [
                'title' => 'Alpine.js Click Handler',
                'content' => '<div>Contoh sederhana <em>event handling</em> menggunakan Alpine.js untuk menampilkan atau menyembunyikan komponen secara dinamis:</div><pre><code>&lt;button x-data="{ open: false }" @click="open = !open"&gt;
    Toggle
&lt;/button&gt;</code></pre><div>Sangat mudah digunakan langsung di file blade HTML Anda!</div>',
            ],
            [
                'title' => 'Tailwind Flex Center',
                'content' => '<div>Gunakan utilitas Flexbox dari Tailwind CSS untuk memposisikan konten tepat di tengah layar secara vertikal dan horisontal:</div><pre><code>&lt;div class="flex items-center justify-center min-h-screen bg-slate-100"&gt;
    &lt;p&gt;Centered Content&lt;/p&gt;
&lt;/div&gt;</code></pre><blockquote>Catatan: Pastikan container luar memiliki tinggi minimum 100vh (min-h-screen).</blockquote>',
            ],
            [
                'title' => 'PHP Array Map Example',
                'content' => '<div>Mengkuadratkan elemen array di PHP menggunakan fungsi <em>array_map</em> dan <em>arrow function</em>:</div><pre><code>$numbers = [1, 2, 3, 4, 5];
$squares = array_map(fn($n) => $n * $n, $numbers);</code></pre>',
            ],
            [
                'title' => 'JavaScript Fetch API',
                'content' => '<div>Meminta data JSON secara asynchronous dari backend menggunakan Fetch API bawaan browser modern:</div><pre><code>fetch(\'/api/data\')
  .then(res =&gt; res.json())
  .then(data =&gt; console.log(data));</code></pre>',
            ],
            [
                'title' => 'Python List Comprehension',
                'content' => '<div>Cara singkat dan elegan di Python untuk membuat list baru dari list yang sudah ada:</div><pre><code>numbers = [1, 2, 3, 4, 5]
squares = [x**2 for x in numbers]
print(squares)</code></pre>',
            ],
            [
                'title' => 'C++ Hello World',
                'content' => '<div>Program dasar bahasa C++ untuk mencetak string ke konsol/terminal standar:</div><pre><code>#include &lt;iostream&gt;

int main() {
    std::cout &lt;&lt; "Hello, World!" &lt;&lt; std::endl;
    return 0;
}</code></pre>',
            ],
            [
                'title' => 'SQL Select Join',
                'content' => '<div>Query SQL untuk mengambil relasi data catatan milik masing-masing user:</div><pre><code>SELECT users.name, notes.title 
FROM users 
INNER JOIN notes ON users.id = notes.user_id 
ORDER BY notes.created_at DESC;</code></pre>',
            ]
        ];

        // Seed snippets and random text notes
        $faker = \Faker\Factory::create();
        $totalNotes = 30;

        for ($i = 0; $i < $totalNotes; $i++) {
            // Generate unique slug
            do {
                $slug = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
            } while (\App\Models\Note::where('slug', $slug)->exists());

            // Predefined code snippet or random text note
            if ($i < count($snippets)) {
                $title = $snippets[$i]['title'];
                $content = $snippets[$i]['content'];
            } else {
                $title = $faker->words(rand(2, 5), true);
                
                $p1 = $faker->paragraph(3);
                $p2 = $faker->paragraph(4);
                $quote = $faker->sentence(10);
                $listItem1 = $faker->sentence(6);
                $listItem2 = $faker->sentence(6);
                $listItem3 = $faker->sentence(6);

                // Add rich text formatting elements into raw random text
                $content = "<div>{$p1}</div>" .
                           "<blockquote>{$quote}</blockquote>" .
                           "<div>Berikut adalah beberapa poin penting untuk dicatat:</div>" .
                           "<ul>" .
                           "<li>Poin utama: <strong>{$listItem1}</strong></li>" .
                           "<li>Poin pendukung: {$listItem2}</li>" .
                           "<li>Kesimpulan akhir: <em>{$listItem3}</em></li>" .
                           "</ul>" .
                           "<div>Untuk informasi selengkapnya, silakan kunjungi <a href=\"https://laravel.com\">dokumentasi resmi Laravel</a>. {$p2}</div>";
            }

            // Distribute notes over the last 15 days
            $createdAt = now()->subMinutes(rand(1, 21600)); // within 15 days

            \App\Models\Note::create([
                'user_id' => $user->id,
                'title' => ucwords($title),
                'content' => $content,
                'slug' => $slug,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
