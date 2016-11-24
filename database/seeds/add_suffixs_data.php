<?php

use Illuminate\Database\Seeder;

class add_suffixs_data extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::insert("insert into suffixs (`name`) values ('com'),('net'),('me'),('xyz'),('cc'),('im'),('org'),('io'),('info'),('name'),('co'),('tw'),('cn'),('com.cn'),('mobi'),('asia'),('hk'),('aero'),('ca'),('us'),('fr'),('se'),('ie'),('tv'),('biz'),('pro'),('in'),('nu'),('ch'),('ws'),('be'),('la'),('wang')");
    }
}
