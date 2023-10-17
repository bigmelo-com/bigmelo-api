<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class OrganizationControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/OrganizationControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class OrganizationControllerTest extends TestApi
{
    /**
     * Organization api endpoint
     */
    const ENDPOINT_ORGANIZATION = '/v1/organization';

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_store_a_new_organization(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $organization_data = [
            'name'        => $this->faker->name,
            'description' => $this->faker->text(200)
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('POST', self::ENDPOINT_ORGANIZATION, $organization_data);

        $response_content = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Organization has been stored successfully.');

        $new_organization = Organization::find($response_content->organization_id);
        $this->assertEquals($user->id, $new_organization->owner_id);
        $this->assertEquals($organization_data['name'], $new_organization->name);
        $this->assertEquals($organization_data['description'], $new_organization->description);
        $this->assertTrue((boolean)$new_organization->active);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_organization(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $organization_data = [
            'name'        => $this->faker->name,
            'description' => $this->faker->text(200),
            'user_id'     => $user->id
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_ORGANIZATION, $organization_data);

        $response_content = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Organization has been stored successfully.');

        $new_organization = Organization::find($response_content->organization_id);
        $this->assertEquals($user->id, $new_organization->owner_id);
        $this->assertEquals($organization_data['name'], $new_organization->name);
        $this->assertEquals($organization_data['description'], $new_organization->description);
        $this->assertTrue((boolean)$new_organization->active);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_organization_if_user_does_not_exist(): void
    {
        $organization_data = [
            'name'        => $this->faker->name,
            'description' => $this->faker->text(200),
            'user_id'     => 100
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_ORGANIZATION, $organization_data);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'User not found.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_organization_if_empty_name(): void
    {
        $organization_data = [
            'name'        => '',
            'description' => $this->faker->text(200),
            'user_id'     => 100
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_ORGANIZATION, $organization_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_not_store_a_new_organization_if_he_is_owner_already(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $organization = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user->own_organizations()->save($organization);

        $organization_data = [
            'name'        => $this->faker->name,
            'description' => $this->faker->text(200)
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('POST', self::ENDPOINT_ORGANIZATION, $organization_data);

        $response->assertStatus(409);
        $response->assertJsonPath('message', 'User already is an organization owner.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_create_organizations()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('POST', self::ENDPOINT_ORGANIZATION, []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
