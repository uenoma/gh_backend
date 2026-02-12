<?php

namespace Database\Seeders;

use App\Models\MobileSuit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class MobileSuitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleFiles = [
            'MS-06.json',
            'MSZ-006 MS-HML.json',
            'MSZ-010.json',
        ];

        foreach ($sampleFiles as $file) {
            $path = base_path('samples/' . $file);
            if (File::exists($path)) {
                $data = json_decode(File::get($path), true);
                $mobileSuit = MobileSuit::create($data);
                // Add default creator
                $mobileSuit->creator()->create([
                    'creator_name' => 'admin',
                    'edit_password' => Hash::make('password'),
                ]);
            }
        }
    }
}
