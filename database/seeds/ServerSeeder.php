<?php

use App\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Server::create([
            'name' => 'FTP Test Server',
            'url' => 'dlptest.com',
            'protocol' => 'ftp',
            'ftp_username' => 'dlpuser',
            'ftp_password' => 'rNrKYTX9g7z3RgJRmxWuGHbeu',
        ]);
    }
}
