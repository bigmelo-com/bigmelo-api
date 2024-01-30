<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\User\UserStored;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class PlanControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/PlanControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class PlanControllerTest extends TestApi
{
    /**
     * Plan api endpoint
     */
    const ENDPOINT_PLAN = '/v1/project';

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_plan(): void
    {
        $plan_data = [
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('POST', self::ENDPOINT_PLAN . '/1/plan', $plan_data);

        $response_content = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Plan has been stored successfully.');

        $new_plan = Plan::find($response_content->plan_id);
        $this->assertEquals($new_plan->project_id, 1);
        $this->assertEquals($new_plan->name, $plan_data['name']);
        $this->assertEquals($new_plan->description, $plan_data['description']);
        $this->assertEquals($new_plan->price, $plan_data['price']);
        $this->assertEquals($new_plan->message_limit, $plan_data['message_limit']);
        $this->assertEquals($new_plan->period, $plan_data['period']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_new_plan_if_has_equal_name_than_other_plan_of_the_same_project(): void
    {
        $plan_data = [
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        Plan::create(['project_id' => 1, ...$plan_data]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('POST', self::ENDPOINT_PLAN . '/1/plan', $plan_data);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Plan already exists.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function not_admin_user_can_not_store_new_plan(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'notadmin@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $plan_data = [
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        event(new UserStored($user));

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('notadmin@test.com', 'test')
        )->json('POST', self::ENDPOINT_PLAN . '/1/plan', $plan_data);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Not Authorized');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthenticated_user_can_not_store_new_plan(): void
    {

        $plan_data = [
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->faker->word()
        )->json('POST', self::ENDPOINT_PLAN . '/1/plan', $plan_data);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }
}
