<?php


namespace Tests\Feature\Http\Controllers\api\v1;

use Faker\Factory;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TestApi extends TestCase
{
    /**
     * Faker object
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Get api token
     *
     * @return string
     */
    protected function getToken(): string
    {
        $response = $this->json('post', '/v1/auth/get-token', [
            'email' => 'admin@mydomain.com',
            'password' => 'qwerty123',
        ]);

        $response_content = json_decode($response->getContent());

        return $response_content->access_token;
    }

    /**
     * Initialize migration
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $migrations = glob('/app/database/migrations/*.php');
        unset($migrations[18]);
        unset($migrations[17]);
        unset($migrations[4]);

        foreach ($migrations as $migration) {
            Artisan::call('migrate', ['--path' => str_replace('app/', '', $migration)]);
        }

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->faker = Factory::create();
    }

}
