<?php

namespace Database\Seeders;

use App\Services\SaveAvatarsFromGithub as SaveAvatarsFromGithubService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaveAvatarsFromGithub extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new SaveAvatarsFromGithubService();
        $service->handle();
    }
}
