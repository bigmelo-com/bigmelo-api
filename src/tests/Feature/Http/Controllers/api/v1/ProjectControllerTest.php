<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Project;

/**
 * Class ProjectControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/ProjectControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class ProjectControllerTest extends TestApi
{
    /**
     * Project api endpoint
     */
    const ENDPOINT_PROJECT = '/v1/project';

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_project(): void
    {
        $project_data = [
            'organization_id'           => 1,
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'phone_number'              => $this->faker->numerify('+############'),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
            'message_limit'             => -1,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_PROJECT, $project_data);

        $response_content = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Project has been stored successfully.');

        $new_project = Project::find($response_content->project_id);
        $this->assertEquals($project_data['organization_id'], $new_project->organization_id);
        $this->assertEquals($project_data['name'], $new_project->name);
        $this->assertEquals($project_data['description'], $new_project->description);
        $this->assertEquals($project_data['phone_number'], $new_project->phone_number);
        $this->assertEquals($project_data['language'], $new_project->language);
        $this->assertTrue((boolean)$new_project->has_system_prompt);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_project_if_organization_does_not_exist(): void
    {
        $project_data = [
            'organization_id'   => 100,
            'name'              => $this->faker->name,
            'description'       => $this->faker->text(200),
            'system_prompt'     => $this->faker->text(100),
            'phone_number'      => $this->faker->numerify('+############')
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_PROJECT, $project_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['organization_id']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_project_if_wrong_phone_number_format(): void
    {
        $project_data = [
            'organization_id'   => 100,
            'name'              => $this->faker->name,
            'description'       => $this->faker->text(200),
            'system_prompt'     => $this->faker->text(100),
            'phone_number'      => $this->faker->numerify('##########')
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_PROJECT, $project_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['phone_number']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_create_projects()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('POST', self::ENDPOINT_PROJECT, []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_edit_a_specific_project(): void
    {
        $project = Project::create([
            'organization_id'           => 1,
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

        $project_data = [
            'name'                      => $this->faker->name,
            'description'               => $this->faker->text(200),
            'assistant_description'     => $this->faker->text(200),
            'assistant_goal'            => $this->faker->text(200),
            'assistant_knowledge_about' => $this->faker->text(200),
            'target_public'             => $this->faker->text(200),
            'language'                  => $this->faker->word(),
            'default_answer'            => $this->faker->text(200),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('PATCH', self::ENDPOINT_PROJECT . '/' . $project->id, $project_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Project has been updated successfully.');

        $project->refresh();

        $this->assertEquals($project_data['name'], $project->name);
        $this->assertEquals($project_data['description'], $project->description);
        $this->assertEquals($project_data['assistant_description'], $project->assistant_description);
        $this->assertEquals($project_data['assistant_goal'], $project->assistant_goal);
        $this->assertEquals($project_data['assistant_knowledge_about'], $project->assistant_knowledge_about);
        $this->assertEquals($project_data['target_public'], $project->target_public);
        $this->assertEquals($project_data['language'], $project->language);
        $this->assertEquals($project_data['default_answer'], $project->default_answer);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_update_a_project_if_default_answer_very_short(): void
    {
        $project = Project::create([
            'organization_id'           => 1,
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

        $project_data = [
            'default_answer'            => $this->faker->text(10),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('PATCH', self::ENDPOINT_PROJECT . '/' . $project->id, $project_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['default_answer']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_update_a_project_if_it_does_not_exist(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('PATCH', self::ENDPOINT_PROJECT . '/1000', []);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Project not found.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_update_projects()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('PATCH', self::ENDPOINT_PROJECT . '/1000', []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
