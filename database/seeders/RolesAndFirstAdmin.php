<?php

use App\Domain\Model\Auth\Role;
use App\Domain\Model\Auth\User;
use Illuminate\Database\Seeder;

class RolesAndFirstAdmin extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$admin = Role::create(['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Superuser mit allen rechten']);

		$user = User::find(1);
		$user->attachRole($admin);
	}

}
