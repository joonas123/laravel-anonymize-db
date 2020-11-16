# Anonymize DB with power of Faker

### Installation
`composer require --dev joonas1234/laravel-anonymize-db`

### Publish the config:
`php artisan vendor:publish --tag=config --provider=Joonas1234\\LaravelAnonymizeDB\\AnonymizeDBServiceProvider`

This will create `config\anonymize-db.php` file for you.
You can use this file to configure which columns should be anonymized

Config consists of `tables` and `fakerProviders` arrays. `tables` is used to define which tables and columns should be anonymized. 
For example if you want to anonymize register numbers and owner names in `cars` -table, you can do it like this:
```
<?php

return [
    'tables' => [
        'cars' => [
            'register_number' => 'vehicleRegistration',
            'owner_name' => 'name',
        ],
    ],
    'fakerProviders' => [
        'Fakecar'
    ]
]
```
It is required to add `FakeCar` to `fakerProviders` so faker can utilize `vehicleRegistration` from `Fakecar`.

### Modifiers
You can also use Faker's special providers: `unique()`, `optional()` and `valid()`:
```
<?php

return [
    'tables' => [
        'users' => [
            'email' => 'email|unique',
        ],
    ],
]
```

### Command to anonymize
`php artisan db:anonymize`
