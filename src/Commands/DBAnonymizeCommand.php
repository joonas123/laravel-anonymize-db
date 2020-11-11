<?php

namespace Joonas1234\LaravelAnonymizeDB\Commands;

use Exception;
use Illuminate\Console\Command;
use Joonas1234\LaravelAnonymizeDB\AnonymizeDB;

class DBAnonymizeCommand extends Command
{
    /** @var string */
    protected $signature = 'db:anonymize';

    /** @var string */
    protected $description = 'Anonymize database';

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->info('Starting DB anonymization...');

        try {
            (new AnonymizeDB)->anonymizeAll();

            $this->info('Anonymization completed!');

        } catch (Exception $exception) {
            $this->error("Anonymization failed because: {$exception->getMessage()}.");
            
            return 1;
        }
    }

   /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param  string  $warning
     * @param  \Closure|bool|null  $callback
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production!', $callback = null)
    {
        $callback = is_null($callback) 
            ? $this->getLaravel()->environment() === 'production'
            : $callback;

        $shouldConfirm = value($callback);

        if ($shouldConfirm) {
            if ($this->hasOption('force') && $this->option('force')) {
                return true;
            }

            $this->alert($warning);

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Canceled!');

                return false;
            }
        }

        return true;
    }

}
