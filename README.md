<h1 align="center">Laravel Tricks</h1>
<p align="center">
<a href="https://packagist.org/packages/dy05/laravel-tricks"><img src="https://poser.pugx.org/dy05/laravel-tricks/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/dy05/laravel-tricks"><img src="https://poser.pugx.org/dy05/laravel-tricks/license.svg" alt="License"></a>
</p>

## Introduction

This package help you to create custom controller with corresponding request and model.

## Installation

Use the following command to add package to your Laravel project

`composer require dy05/laravel-tricks`

You have one command to publish stub file into stubs\laravel-tricks directory in you base app directory, feel free to update file at your convenience

`php artisan dy05:publish`

## Usage

You can start by creating new Laravel Controller, Model and Request for Book entity using the following command

`php artisan dy05:create Book`

It will create BookController in Controllers directory, BookRequest in Requests directory and Book model in  Model directory

For the first, it creates BaseRequest if file doesn't exist

You can update the stub file in stubs\laravel-tricks to customize as you want

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Obby Sidane YOUTA][link-author]

## License

license. Please see the [license file](license.md) for more information.

[link-packagist]: https://packagist.org/packages/dy05/laravel-tricks
[link-downloads]: https://packagist.org/packages/dy05/laravel-tricks
[link-author]: https://twitter.com/dos_plague
