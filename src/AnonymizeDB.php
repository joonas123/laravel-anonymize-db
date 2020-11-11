<?php

namespace Joonas1234\LaravelAnonymizeDB;

use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AnonymizeDB
{
    public $tables = [];
    public $faker;

    public function __construct()
    {
        $this->tables = config('anonymize-db')['tables'];

        $this->faker = Faker::create();

        $this->applyFakerProviders(config('anonymize-db')['fakerProviders']);
    }

    /**
     * Apply faker providers to Faker Generator if we want to use other than default properties
     * 
     * @param array $providers
     * 
     * @return void
     */
    public function applyFakerProviders(array $providers): void
    {   
        if(empty($providers)) {
            return;
        }

        foreach($providers as $provider) {

            $className = "\Faker\Provider\\$provider";
            
            $this->faker->addProvider(new $className($this->faker));
            
        }
    }

    /**
     * Anonymize all selected table columns
     * 
     * @return void
     */
    public function anonymizeAll(): void
    {
        foreach($this->tables as $table => $columns) {

            $this->anonymizeTable($table, $columns);

        }
    }

    /**
     * Anonymize selected columns from a table
     * 
     * @return void
     */
    public function anonymizeTable(string $table, array $columns): void
    {
        $rows = DB::table($table)->pluck('id');

        DB::beginTransaction();

            foreach($rows as $id) {

                DB::table($table)->where('id', $id)->update(
                    $this->buildUpdateQuery($columns)
                );

            }

        DB::commit();
    }

    /**
     * Build the update query for DB transaction
     * 
     * @param array $columns
     * 
     * @return array
     */
    protected function buildUpdateQuery(array $columns): array
    {
        $query = [];

        foreach($columns as $columnName => $fakerProperty) {

            $query[$columnName] = $this->anonymizedValue($fakerProperty);

        }

        return $query;
    }

    /**
     * Generate anonymized value for the column
     * 
     * @param string $fakerProperty
     * 
     * @return mixed
     */
    protected function anonymizedValue(string $fakerProperty)
    {
        return $this->faker->{$fakerProperty};
    }
}
