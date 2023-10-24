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

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_update_an_organizations()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('PATCH', self::ENDPOINT_ORGANIZATION . '/100', []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_not_update_an_organization_if_he_is_not_its_owner(): void
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

        $organization = Organization::create([
            'owner_id'      => 1,
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('PATCH', self::ENDPOINT_ORGANIZATION . '/' . $organization->id, []);

        $response->assertStatus(409);
        $response->assertJsonPath('message', 'User is not the organization owner.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_not_update_an_organization_if_it_does_not_exist(): void
    {
        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('PATCH', self::ENDPOINT_ORGANIZATION . '/100', []);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Organization not found.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_not_update_an_organization_if_name_very_short(): void
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
            'name'        => 'ab'
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('PATCH', self::ENDPOINT_ORGANIZATION . '/' . $organization->id, $organization_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function a_user_can_update_his_organization(): void
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
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('PATCH', self::ENDPOINT_ORGANIZATION . '/' . $organization->id, $organization_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Organization has been updated successfully.');

        $organization->refresh();

        $this->assertEquals($organization_data['name'], $organization->name);
        $this->assertEquals($organization_data['description'], $organization->description);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_update_an_organization_if_he_is_not_its_owner(): void
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
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ];

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('PATCH', self::ENDPOINT_ORGANIZATION . '/' . $organization->id, $organization_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Organization has been updated successfully.');

        $organization->refresh();

        $this->assertEquals($organization_data['name'], $organization->name);
        $this->assertEquals($organization_data['description'], $organization->description);
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_list_organizations_related_to_him(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization1 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization3 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization4 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user1->own_organizations()->save($organization1);
        $user1->own_organizations()->save($organization3);
        $user1->own_organizations()->save($organization4);
        $user1->organizations()->attach($organization1);
        $user1->organizations()->attach($organization3);
        $user1->organizations()->attach($organization4);

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $user2->organizations()->attach($organization4);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('GET', self::ENDPOINT_ORGANIZATION);

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(3, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_list_all_organizations(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization1 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization3 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization4 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user1->own_organizations()->save($organization1);
        $user1->own_organizations()->save($organization3);
        $user1->own_organizations()->save($organization4);
        $user1->organizations()->attach($organization1);
        $user1->organizations()->attach($organization3);
        $user1->organizations()->attach($organization4);

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $user2->organizations()->attach($organization4);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('GET', self::ENDPOINT_ORGANIZATION);

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(5, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_list_a_specific_user_organizations(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization1 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization3 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);
        $organization4 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user1->own_organizations()->save($organization1);
        $user1->own_organizations()->save($organization3);
        $user1->own_organizations()->save($organization4);
        $user1->organizations()->attach($organization1);
        $user1->organizations()->attach($organization3);
        $user1->organizations()->attach($organization4);

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $user2->organizations()->attach($organization4);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('GET', self::ENDPOINT_ORGANIZATION . '?user_id=' . $user2->id);

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(2, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_list_organizations()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('GET', self::ENDPOINT_ORGANIZATION);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_list_projects_from_organizations_related_to_him(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization1 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user1->own_organizations()->save($organization1);
        $user1->organizations()->attach($organization1);
        $organization1->refresh();

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $organization2->refresh();

        $project11 = Project::create([
            'organization_id'           => $organization1->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);
        $project12 = Project::create([
            'organization_id'           => $organization1->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);
        $project21 = Project::create([
            'organization_id'           => $organization2->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('GET', self::ENDPOINT_ORGANIZATION . '/' . $organization1->id . '/projects');

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(2, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_list_projects_from_any_organization(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization1 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user1->own_organizations()->save($organization1);
        $user1->organizations()->attach($organization1);
        $organization1->refresh();

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $organization2->refresh();

        $project11 = Project::create([
            'organization_id'           => $organization1->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);
        $project12 = Project::create([
            'organization_id'           => $organization1->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);
        $project21 = Project::create([
            'organization_id'           => $organization2->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken()
        )->json('GET', self::ENDPOINT_ORGANIZATION . '/' . $organization1->id . '/projects');

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(2, $response_data);
    }/**
 * @test
 *
 * @return void
 */
    public function user_can_not_list_projects_from_organizations_do_not_related_to_him(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'Peter Parker',
            'email'             => 'peter.parker@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'Tony Stark',
            'email'             => 'tony.stark@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133888888',
            'full_phone_number' => '+573133888888',
            'password'          => Hash::make('test123')
        ]);

        $organization2 = new Organization([
            'name'          => $this->faker->name,
            'description'   => $this->faker->text(200)
        ]);

        $user2->own_organizations()->save($organization2);
        $user2->organizations()->attach($organization2);
        $organization2->refresh();

        $project21 = Project::create([
            'organization_id'           => $organization2->id,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ]);

        $response = $this->withHeader(
            'Authorization', 'Bearer ' . $this->getToken('peter.parker@gmail.com', 'test123')
        )->json('GET', self::ENDPOINT_ORGANIZATION . '/' . $organization2->id . '/projects');

        $response->assertStatus(409);
        $response->assertJsonPath('message', 'User is not related to the organization.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_list_projects()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('GET', self::ENDPOINT_ORGANIZATION . '/100/projects');

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
