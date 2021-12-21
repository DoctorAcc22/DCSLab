<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Services\RoleService;
use App\Services\UserService;

use Database\Seeders\UnitTableSeeder;
use Database\Seeders\UserTableSeeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductGroupTableSeeder;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper for this applications';

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
     * @return int
     */
    public function handle()
    {
        if (!File::exists('.env')) {
            $this->error('File Not Found: .env');
            $this->error('Aborted');
            return false;
        }

        $loop = true;

        while($loop)
        {
            $this->info('Available Helper:');
            $this->info('[1] Update Composer And NPM');
            $this->info('[2] Clear All Cache');
            $this->info('[3] Change User Roles');
            $this->info('[4] Data Seeding');
            $this->info('[5] Refresh Database');
            $this->info('[X] Exit');

            $choose = $this->ask('Choose Helper','X');

            switch (strtoupper($choose)) {
                case 1:
                    $this->updateComposerAndNPM();
                    $this->info('Done!');
                    break;
                case 2:
                    $this->clearCache();
                    $this->info('Done!');
                    break;
                case 3:
                    $this->changeUserRoles();
                    $this->info('Done!');
                    break;
                case 4:
                    $this->dataSeeding();
                    $this->info('Done.');
                    break;
                case 5:
                    $this->refreshDatabase();
                    break;
                case 'X':
                default:
                    $loop = false;
                    break;
            }
            sleep(3);
        }
        $this->info('Bye!');
    }

    private function dataSeeding()
    {
        $unattended_mode = $this->confirm('Unattended Mode?', true);

        $user = $unattended_mode ? true : $this->confirm('Do you want to seed users?', true);
        $roles = $unattended_mode ? true : $this->confirm('Do you want to seed roles?', true);
        $companies = $unattended_mode ? true : $this->confirm('Do you want to seed companies for each users?', true);
        $supplier = $unattended_mode ? true : $this->confirm('Do you want to seed dummy suppliers for each companies?', true);
        $product = $unattended_mode ? true : $this->confirm('Do you want to seed dummy products for each companies?', true);

        if (Permission::count() == 0) {
            $this->info('Roles and Permissions table is empty. seeding...');
            Artisan::call('db:seed');
        }

        if ($user)
        {
            $this->info('Starting UserSeeder');
            $truncate = $unattended_mode ? false : $this->confirm('Do you want to truncate the users table first?', false);
            $count = $unattended_mode ? 5 : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new UserTableSeeder();
            $seeder->callWith(UserTableSeeder::class, [$truncate, $count]);

            $this->info('UserSeeder Finish.');
        }

        sleep(3);

        if ($roles)
        {
            $this->info('Starting RoleSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many data:', 5);

            $this->info('Seeding...');

            $seeder = new RoleTableSeeder();
            $seeder->callWith(RoleTableSeeder::class, [true, $count]);

            $this->info('RoleSeeder Finish.');
        }

        sleep(3);

        if ($companies)
        {
            $this->info('Starting CompanyTableSeeder');
            $count = $unattended_mode ? 3 : $this->ask('How many companies for each users:', 3);

            $seeder = new CompanyTableSeeder();
            $seeder->callWith(CompanyTableSeeder::class, [$count]);

            $this->info('CompanyTableSeeder Finish.');
        }

        sleep(3);

        if ($product)
        {
            $this->info('Starting UnitTableSeeder');
            $seeder_unit = new UnitTableSeeder();
            $seeder_unit->callWith(UnitTableSeeder::class);
            $this->info('UnitTableSeeder Finish.');

            $this->info('Starting ProductGroupTableSeeder');
            $count_pg = $unattended_mode ? 3 : $this->ask('How many product groups (0 to skip):', 3);

            if ($count_pg != 0) {
                $seeder_pg = new ProductGroupTableSeeder();
                $seeder_pg->callWith(ProductGroupTableSeeder::class, [$count_pg]);

                $this->info('ProductGroupTableSeeder Finish.');
            }

            $this->info('Starting BrandTableSeeder');
            $count_pb = $unattended_mode ? 5 : $this->ask('How many brands (0 to skip):', 5);

            if ($count_pb != 0) {

                $seeder_pb = new BrandTableSeeder();
                $seeder_pb->callWith(BrandTableSeeder::class, [$count_pb]);

                $this->info('BrandTableSeeder Finish.');
            }

            $this->info('Starting ProductTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many products for each companies:', 5);

            $seeder = new ProductTableSeeder();
            $seeder->callWith(ProductTableSeeder::class, [$count]);

            $this->info('ProductTableSeeder Finish.');
        }

        sleep(3);

        if ($supplier)
        {
            $this->info('Starting SupplierTableSeeder');
            $count = $unattended_mode ? 5 : $this->ask('How many supplier for each companies:', 5);

            $seeder = new SupplierTableSeeder();
            $seeder->callWith(SupplierTableSeeder::class, [$count]);

            $this->info('SupplierTableSeeder Finish.');
        }
    }

    private function refreshDatabase()
    {
        $this->info('THIS ACTIONS WILL REMOVE ALL DATA AND LEAVING ONLY DEFAULT DATA');
        $run = $this->confirm('CONFIRM TO REFRESH DATABASE?', false);

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->info('Done.');
        sleep(3);
    }

    private function changeUserRoles()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $valid = false;

        $email = '';

        while (!$valid) {
            $email = $this->ask('Email:', $email);

            $usr = $userService->readby('EMAIL', $email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleService->read()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleService->readBy('DISPLAY_NAME', $roleDisplayName);

                if (!$role) {
                    $this->error('Invalid Role');
                    return false;
                }

                $confirmed = $this->confirm("Proceed to $mode Role $role->display_name to $usr->name?", true);

                if (!$confirmed) {
                    $this->error('Aborted');
                    return false;
                }

                if ($mode == 'Attach') {
                    $usr->attachRole($role);
                } else if ($mode == 'Remove') {
                    $usr->detachRole($role);
                } else {

                }

                $this->info('Done');
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                sleep(3);

                $confirmedExit = $this->confirm("Do you want to attach/remove another role?", false);

                if (!$confirmedExit) {
                    $this->error('Exiting');
                    return false;
                }
            }
        }
    }

    private function clearCache()
    {
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('clear-compiled');
    }

    private function updateComposerAndNPM()
    {
        $this->info('Starting Composer Update');
        exec('composer update');

        $this->info('Starting NPM Update');
        exec('npm update');

        $this->info('Starting Mix');
        if (App::environment('prod', 'production')) {
            $this->info('Executing for production enviroment');
            exec('npm run prod');
        } else {
            exec('npm run dev');
        }
    }
}
