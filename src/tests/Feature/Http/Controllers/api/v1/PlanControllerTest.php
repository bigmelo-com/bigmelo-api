<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\User\UserValidated;
use App\Models\Plan;
use App\Models\Transaction;
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
    const ENDPOINT_PLAN = '/v1/plan';

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_plan(): void
    {
        $plan_data = [
            'project_id'    => 1,
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('POST', self::ENDPOINT_PLAN, $plan_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Plan has been stored successfully.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_new_plan_if_has_equal_name_than_other_plan_of_the_same_project(): void
    {
        $plan_data = [
            'project_id'    => 1,
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        Plan::create(['project_id' => 1, ...$plan_data]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('POST', self::ENDPOINT_PLAN, $plan_data);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'A plan with the name test already exists.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function not_admin_user_can_not_store_a_new_plan(): void
    {
        User::create([
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
            'project_id'    => 1,
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('notadmin@test.com', 'test')
        )->json('POST', self::ENDPOINT_PLAN, $plan_data);

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
            'project_id'    => 1,
            'name'          => 'test',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->faker->word()
        )->json('POST', self::ENDPOINT_PLAN, $plan_data);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_list_all_plans_of_a_project(): void
    {   
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test1',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test2',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test3',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test4',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('GET', '/v1/project/1/plan');

        $response_data = json_decode($response->getContent())->data;
        
        $response->assertStatus(200);
        $this->assertCount(5, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_list_all_plans_of_a_project(): void
    {
        User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        Plan::create([
            'project_id'    => 1,
            'name'          => 'test1',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test2',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test3',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);
        Plan::create([
            'project_id'    => 1,
            'name'          => 'test4',
            'description'   => 'Plan test',
            'price'         => 3,
            'message_limit' => 20,
            'period'        => "1d, 1w, 1m, 1y",
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com', 'test')
        )->json('GET', '/v1/project/1/plan');

        $response_data = json_decode($response->getContent())->data;
        
        $response->assertStatus(200);
        $this->assertCount(5, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_update_a_plan(): void
    {
        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('PATCH', self::ENDPOINT_PLAN . '/1');
        
        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Plan has been updated successfully.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function not_admin_user_can_not_update_a_plan(): void
    {
        User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com','test')
        )->json('PATCH', self::ENDPOINT_PLAN . '/1');
        
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Not Authorized');
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_get_lead_available_plans(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'active'            => true,
            'validation_code'   => null
        ]);

        event(new UserValidated($user));

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com','test')
        )->json('GET', self::ENDPOINT_PLAN . '/purchase');
        
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_get_preference_id(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'active'            => true,
            'validation_code'   => null
        ]);

        event(new UserValidated($user));

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com','test')
        )->json('GET', self::ENDPOINT_PLAN . '/purchase/1');
        
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_register_a_transaction_successfully(): void
    {
        User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'active'            => true,
            'validation_code'   => null
        ]);
        
        Transaction::create([
            'preference_id' => 'test',
            'lead_id'       => 1,
            'plan_id'       => 1,
            'amount'        => '5.70'
        ]);

        $transaction_data = [
            'preference_id' => 'test',
            'payment_id'    => 12345,
            'status'        => 'approved'
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com','test')
        )->json('POST', self::ENDPOINT_PLAN . '/payment', $transaction_data);
        
        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Transacition registered successfully');
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_not_register_a_transaction(): void
    {
        User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'active'            => true,
            'validation_code'   => null
        ]);

        $transaction_data = [
            'preference_id' => 'test',
            'payment_id'    => 12345,
            'status'        => 'approved'
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('user@test.com','test')
        )->json('POST', self::ENDPOINT_PLAN . '/payment', $transaction_data);
        
        $response->assertStatus(404);
        $response->assertJsonPath('message', 'This transaction does not exist');
    }

}
