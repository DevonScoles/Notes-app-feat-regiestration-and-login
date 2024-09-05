<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Note;
use App\Models\User;
use Egulias\EmailValidator\Parser\Comment;
use Egulias\EmailValidator\Parser\CommentStrategy\LocalComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\SkippedTest;
use Tests\TestCase;

/*
    Tests will follow the AAA (Arange Act Assert) format
*/

class NoteTest extends TestCase
{
    use RefreshDatabase; //clears the database for each test run
    protected $userOne, $userTwo;

    protected function setUp(): void
    {
        parent::setUp();
        // Create 2 test users, need 2 so we can test note viewing authentication
        $userData = [];

        for ($i = 1; $i <= 2; $i++) {
            $userData[] = [
                'name' => 'Test User #' . $i,
                'email' => 'testemail' . $i . '@test.com',
                'password' => bcrypt('pass123.'),
            ];
        }

        User::factory()->createMany($userData);
        $this->userOne = User::find(1); //assign userOne
        $this->userTwo = User::find(2);//assign userTwo

        //Prefab'd notes for user using different looping method than user prefab loop
        Note::factory(10)->create()->each(function ($note) {
            $note->update([
                'note' => 'this is note #' . $note->id . ' for user #' . $note->user_id,
            ]);
        });

    }

    public function test_application_response_if_user_isnt_logged_in()
    {

        $response = $this->get('/');
        $response->assertStatus(302);

        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_application_response_if_user_is_logged_in()
    {
        $this->actingAs($this->userOne);
        $response = $this->get('/note');
        $response->assertStatus(200);
        $response->assertSee('Test User #1');
    }

    /*
    Tests TODOS:
        ✔ note view when logged in
        ✔ note view when logged out/different user
        ✔ note creation
        ✔ note update
        ☐ note delete
    */

    public function test_note_viewability_and_validity()
    {
        $this->actingAs($this->userOne);
        $response = $this->get('/note/10');
        $response->assertStatus(200);
        $response->assertSee('this is note #10 for user #1'); //Correct note test
        $response->assertSee('Edit'); //edit button present test
        $response->assertSee('Delete'); //delete button present test
    }

    public function test_note_viewability_from_unathorized_user()
    {
        $this->actingAs($this->userTwo);
        $response = $this->get('/note/10');
        $response->assertStatus(403); //verifies that the migration redirects the user if it's not the correct user attempting to access the note
        $response->assertSee('Forbidden');
    }

    public function test_new_note_button()
    {
        $this->actingAs($this->userOne);

        $response = $this->get('/note/create'); //simulates clicking the new note button utilizes note.create route and create method from the controller

        $response->assertStatus(200);
        $response->assertSee('Enter your note here');
    }

    public function test_note_creation_and_redirect_after_creation()
    {
        $this->actingAs($this->userOne);
        $noteData = ['note' => 'this is a newly created note'];

        $response = $this->post('/note', $noteData);

        //create note variable then use the where() to locate the latest note created by the user
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first();

        $response->assertLocation('/note/' . $note->id); //verify we are being redirected
        $response = $this->get('/note/' . $note->id); //follow the redirect
        $response->assertViewIs('note.show');
        $response->assertViewHas('note', $note); // this is better than assertSee because it verifies the data has been created rather than if the text is on the screen
    }

    public function test_note_edit_button()
    {
        $this->actingAs($this->userOne);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first(); //grab the latest note since that will be at the top of the app

        $response = $this->get('/note/' . $note->id . '/edit'); //simulate user clicking the edit button

        $response->assertStatus(200);
        $response->assertSee('this is note #10 for user #1');
    }

    public function test_note_update_method()
    {
        $noteData = ['note' => 'this is an updated note'];
        $this->actingAs($this->userOne);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first(); //grab the latest note since that will be at the top of the app

        $response = $this->put('/note/' . $note->id, $noteData); //simulate the user typing the update note contents and hitting submit button

        $response->assertLocation('/note/' . $note->id); //verify we are being redirected after updating the note
        $response = $this->get('/note/' . $note->id); //follow the redirect
        $response->assertViewIs('note.show');
        $response->assertViewHas('note', $note);
    }

    public function test_note_delete_method()
    {
        $this->actingAs($this->userOne);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first(); //grab the latest note since that will be at the top of the app

        $response = $this->delete('/note/' . $note->id);

        $response->assertLocation('/note');

        $response = $this->get('/note');
        $response->assertViewMissing('note'); //confirm that the note has been deleted from the database
        $response->assertDontSee($note->note); //extra confirmation
    }
}
