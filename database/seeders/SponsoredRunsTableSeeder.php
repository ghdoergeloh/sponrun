<?php

use App\Domain\Model\Sponsor\SponsoredRun;
use Illuminate\Database\Seeder;

class SponsoredRunsTableSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		factory(SponsoredRun::class, 5)->create();
	}

}
