<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Note;
use App\Models\User;
use Egulias\EmailValidator\Parser\Comment;
use Egulias\EmailValidator\Parser\CommentStrategy\LocalComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase; //clears the database for each test run

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'id' => 1, // Optional, as this is usually auto-incremented by default
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('pass123.') // Hash the password
        ]);
        //Create notes for user
        Note::factory(10)->create();
    }

    public function test_if_user_isnt_logged_in()
    {

        $response = $this->get('/');
        $response->assertStatus(302);

        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_notes_appear_if_the_user_is_indeed_logged_in_()
    {
        $this->actingAs($this->user);
        $response = $this->get('/note');
        $response->assertStatus(200);
        $response->assertSee('Test User');
    }
}
