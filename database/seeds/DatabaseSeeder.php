<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserTableSeeder::class);
        /*$this->call(TagTableSeeder::class);
        $this->call(DiscussionTableSeeder::class);
        $this->call(RatingAspectTableSeeder::class);
        $this->call(AmendmentTableSeeder::class);
        $this->call(SubAmendmentTableSeeder::class);
        $this->call(CommentTableSeeder::class);*/
    }
}
