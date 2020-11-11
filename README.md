# Anonymize DB with power of Faker

### Publish the config:
`php artisan vendor:publish --tag=config --provider=Joonas1234\\LaravelAnonymizeDB\\AnonymizeDBServiceProvider`

This will create `config\anonymize-db.php` file for you.
You can use this file to configure which columns should be anonymized

### Command to anonymize
`php artisan db:anonymize`
