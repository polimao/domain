<?php

namespace Domain\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yep:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '这是一个测试命令';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	   $this->info('This is a command test!');
    }
}
