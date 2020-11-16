<?php

namespace Joonas1234\LaravelAnonymizeDB;

use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AnonymizeDB
{
    public $tables = [];
    public $faker;
    protected $uniqueValues = [];

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

        $this->buildUniqueLists($columns, count($rows));

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

            $query[$columnName] = $this->anonymizedValue($columnName, $fakerProperty);

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
    protected function anonymizedValue(string $columnName, string $fakerProperty)
    {
        $valueConfig = array_reverse(explode('|', $fakerProperty));

        $property = array_pop($valueConfig);

        foreach($valueConfig as $specialProperty) {

            if($specialProperty == 'unique') {
                return $this->uniqueAnonymizedValue($columnName);
            }

            $this->faker->{$specialProperty}();
        }
        
        return $this->faker->{$property};
    }

    /**
     * Return unique value from uniqueValues list
     * 
     * @param string $columnName
     * 
     * @return mixed
     */
    protected function uniqueAnonymizedValue($columnName)
    {
        return array_pop($this->uniqueValues[$columnName]);
    }

    /**
     * Build lists for unique values
     * 
     * @param array $columns
     * @param int $count
     * 
     * @return void
     */
    protected function buildUniqueLists(array $columns, $count)
    {
        // We want to do this only for one table at a time to avoid using too much memory
        $this->uniqueValues = [];
        $values = [];
        
        foreach($columns as $columnName => $valueConfig) {

            $valueConfig = explode('|', $valueConfig);   

            if(!in_array('unique', $valueConfig)) {
                continue;
            }

            $values[$columnName] = [];

            $fakerProperty = reset($valueConfig);

            for ($i = 0; $i < $count; $i++) {

                $values[$columnName][] = $this->faker->unique()->{$fakerProperty};

            }
        }

        $this->uniqueValues = $values;
    }

}
