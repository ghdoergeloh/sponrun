<?php

use Illuminate\Database\Seeder;

class DatabaseNeededSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SponsoredRunsTableSeeder::class);
        $this->call(ProjectsTableSeeder::class);
    }
}
