<?php

namespace Dy05\LaravelTricks\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class ConsoleCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'dy05:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Dy05 controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($name = 'controller.stub')
    {
        return file_exists($customPath = $this->laravel->basePath('stubs/laravel-tricks').'/'.$name)
            ? $customPath
            : __DIR__ . '/../../stubs/'.$name;
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     */
    public function handle()
    {
        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->error('The name "'.$this->getNameInput().'" is reserved by PHP.');

            return false;
        }

        $name = $this->qualifyClass($this->getNameInput().'Controller');

        $path = $this->getPath($name);

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info('All files related to the controller have been created successfully.');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace = $this->buildModelReplacements($replace);
        $this->buildRequestReplacements();

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }


        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    protected function buildParentReplacements()
    {
        $parentModelClass = $this->parseModel($this->option('parent'));

        if (! class_exists($parentModelClass)) {
            $this->call('make:model', ['name' => $parentModelClass]);
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->getNameInput());

        if (! class_exists($modelClass)) {
            $this->call('make:model', ['name' => $modelClass]);
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Build request class.
     */
    protected function buildRequestReplacements()
    {
        $name = $this->getNameInput().'Request';
        $requestClass = $this->parseRequest($name);

        if (! is_dir($requestsPath = app_path('Http/Requests'))) {
            (new Filesystem)->makeDirectory($requestsPath);
        }

        if (! file_exists(app_path('Requests').'/BaseRequest.php')) {
            $baseRequestFile = file_exists(base_path('stubs/laravel-tricks').'/BaseRequest.stub')
                ? file_get_contents(base_path('stubs/laravel-tricks'). '/BaseRequest.stub')
                : file_get_contents(__DIR__ . '/../../stubs/BaseRequest.stub');
            $baseRequestFile = str_replace('{{ namespace }}', $this->getNamespace($requestClass), $baseRequestFile);

            $this->files->put(app_path('Http/Requests').'/BaseRequest.php',
                $baseRequestFile);
        }

        $stub = $this->files->get($this->getStub('request.stub'));
        $stub = str_replace('{{ namespace }}', $this->getNamespace($requestClass), $stub);
        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $name, $stub);
        $this->files->put(app_path('Http/Requests')."/{$name}.php", $stub);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Get the fully-qualified request class name.
     *
     * @param  string  $request
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseRequest($request)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $request)) {
            throw new InvalidArgumentException('Request name contains invalid characters.');
        }

        $request = ltrim($request, '\\/');

        $request = str_replace('/', '\\', $request);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($request, $rootNamespace)) {
            return $request;
        }

        return $rootNamespace.'Http\Requests\\'.$request;
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested parent controller class.'],
        ];
    }
}
