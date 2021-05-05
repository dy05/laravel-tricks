<?php

namespace Dy05\LaravelTricks\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dy05:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Dy05 Laravel tricks files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        if (! is_dir($stubsPath = $this->laravel->basePath('stubs/laravel-tricks'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        $files = [
            realpath(__DIR__.'/../../stubs/BaseRequest.stub') => $stubsPath.'/BaseRequest.stub',
            realpath(__DIR__.'/../../stubs/request.stub') => $stubsPath.'/request.stub',
            realpath(__DIR__.'/../../stubs/controller.stub') => $stubsPath.'/controller.stub'
        ];

        foreach ($files as $from => $to) {
            if (! file_exists($to) || $this->option('force')) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->info('Dy05 Laravel tricks stubs published successfully.');
    }
}
