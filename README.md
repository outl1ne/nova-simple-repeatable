# Nova Simple Repeatable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outl1ne/nova-simple-repeatable.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-simple-repeatable)
[![Total Downloads](https://img.shields.io/packagist/dt/outl1ne/nova-simple-repeatable.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-simple-repeatable)

This [Laravel Nova](https://nova.laravel.com/) package allows you to create simple horizontal rows of fields that the user can add/remove.

## Requirements

- `php: >=8.0`
- `laravel/nova: ^4.0`

## Features

A Laravel Nova simple repeatable rows field.

## Screenshots

![Form page](./docs/form.png)

## Installation

Install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require outl1ne/nova-simple-repeatable
```

## Usage

```php
use Outl1ne\NovaSimpleRepeatable\SimpleRepeatable;

public function fields(Request $request) {
    SimpleRepeatable::make('Users', 'users', [
        Text::make('First name'),
        Text::make('Last name'),
        Email::make('Email'),
      ])
      ->canAddRows(true) // Optional, true by default
      ->canDeleteRows(true), // Optional, true by default
}
```

## Localization

The translations file can be published by using the following publish command:

```bash
php artisan vendor:publish --provider="Outl1ne\NovaSimpleRepeatable\SimpleRepeatableServiceProvider" --tag="translations"
```

You can then edit the strings to your liking.

## DependsOn for siblings

``dependsOn()`` function is made available for fields within the `SimpleRepeatable`. To make the feature available, be sure
to use ``DependsOnSiblings`` trait on the resource you want to have `dependsOn()` available for sibling fields.

Key on which the field depends on is constructed out of two parts: ``{parent attribute}.{child attribute}``. This key
will be made available to fetch within the function via ``$request->get('parent.child')``.

Example:

```php
SimpleRepeatable::make('Adding', 'parent', [
    // Depending on 1 field
    Text::make('Child'),
    Text::make('Dependent Child')
      ->dependsOn('parent.child', function ($field, NovaRequest $request, FormData $formData) {
          $attribute = $request->get('parent.child');
      }),

    // Depending on multiple fields
    Text::make('Second Child'),
    Text::make('Third Child'),
    Text::make('Really Dependent Child')
      ->dependsOn(['parent.second_child', 'parent.third_child'], function ($field, NovaRequest $request, FormData $formData) {
          $attribute1 = $request->get('parent.second_child');
          $attribute2 = $request->get('parent.third_child');
      }),

])
```

## Credits

- [Tarvo Reinpalu](https://github.com/tarpsvo)

## License

Nova Simple Repeatable is open-sourced software licensed under the [MIT license](LICENSE.md).
