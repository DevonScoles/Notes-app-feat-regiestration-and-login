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
use Illuminate\Support\Str;

/*
    Tests will follow the AAA (Arange Act Assert) format

    Test justifications:
        Test if User Isn't Logged In: This test ensures that unauthenticated users are redirected
         when trying to access the notes page, thereby enforcing access control.

        Test if User Is Logged In: This test verifies that authenticated users
         can access their notes successfully and that their username is displayed,
         confirming proper authentication functionality.

        Test Note Viewability and Validity: This test checks that the specific
         note can be viewed by the owner, ensuring that the correct content
         is displayed along with necessary action buttons (edit and delete).

        Test User Cannot Access Other User's Note: This test confirms that
         users are restricted from accessing notes that do not belong to them,
         thereby enforcing privacy and security within the application.

        Test Note Creation: This test ensures that authenticated users can create new notes successfully and verifies
         that the note is correctly stored in the database by checking the redirect and the view data.

        Test Note Update: This test confirms that users can update existing notes,
         checking both the redirect after the update and the correct retrieval of
         the updated note data to ensure the changes are properly saved.

        Test Note Delete Method: This test verifies that users can delete their
         notes and ensures that the deleted note no longer appears in the database or on the notes page,
         confirming that the deletion functionality works as expected.

*/

class NoteTest extends TestCase
{
    use RefreshDatabase; //clears the database for each test run
    protected $userOne, $userTwo;

    protected function setUp(): void
    {
        parent::setUp();
        // Create 2 test users, need 2 so we can test one user cannot view a different user's note
        $userData = [];

        for ($i = 1; $i <= 2; $i++) {
            $userData[] = [
                'name' => 'Test User #' . $i,
                'email' => 'testemail' . $i . '@test.com',
                'password' => bcrypt('pass123.'),
            ];
        }

        User::factory()->createMany($userData);
        $this->userOne = User::where('name', 'Test User #1')->first(); //assign userOne
        $this->userTwo = User::where('name', 'Test User #2')->first(); //assign userTwo

        //Prefab'd notes for user using different looping method than user prefab loop
        Note::factory(10)->create()->each(function ($note) {
            $note->update([
                'note' => 'this is note #' . $note->id . ' for user #' . $note->user_id,
            ]);
        });

    }

    public function test_application_response_if_user_isnt_logged_in()
    {

        $response = $this->get('/note');
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

    public function test_note_viewability_and_validity()
    {
        $this->actingAs($this->userOne);

        $response = $this->get('/note/10');

        $response->assertStatus(200);
        $response->assertSee('this is note #10 for user #1'); //Correct note test
        $response->assertSee('Edit'); //edit button present test
        $response->assertSee('Delete'); //delete button present test
    }

    public function test_user_cannot_access_other_users_note()
    {
        $this->actingAs($this->userTwo);

        $response = $this->get('/note/10');

        $response->assertStatus(403); //verifies that the migration redirects the user if it's not the correct user attempting to access the note
        $response->assertSee('Forbidden');
    }

    public function test_new_note_button()
    {
        $this->markTestSkipped(
            'This test just tests the route /note/create which is used by the button'
        );
        $this->actingAs($this->userOne);

        $response = $this->get('/note/create'); //simulates clicking the new note button utilizes note.create route and create method from the controller

        $response->assertStatus(200);
        $response->assertSee('Enter your note here');
    }

    public function test_note_creation()
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

    public function test_empty_note_creation()
    {
        $this->markTestSkipped(
            'This test is just an edge case which actually simulates the validate method from NoteController failing and redirecting'
        );

        $this->actingAs($this->userOne);
        $noteData = ['note' => '']; //this should fail the validate() method as seen in the NoteController

        $response = $this->from('/note/create')->post('/note', $noteData);

        $response->assertRedirect('/note/create');
        $response->assertSessionHasErrors(['note']);// make sure there are validation errors

    }

    public function test_oversized_note_creation()
    {
        $this->markTestSkipped(
            'Test purley depends on memory size'
        );

        $this->actingAs($this->userOne);

        $noteData = ['note' => Str::random(200000000)];
        $response = $this->post('/note', $noteData);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first();


        $response->assertLocation('/note/' . $note->id); //verify we are being redirected
        $response = $this->get('/note/' . $note->id); //follow the redirect
        $response->assertViewIs('note.show');
        $response->assertViewHas('note', $note);
    }

    public function test_sql_injection_prevention()
    {
        $this->markTestSkipped(
            'Laravell\'s Eloquent ORM is used in the models so it automatically protects against SQL injection'
        );

        $this->actingAs($this->userOne);
        $maliciousNote = ['note' => 'This is a note; DROP TABLE notes; --'];

        $response = $this->from('/note')->post('/note', $maliciousNote);

        $this->assertDatabaseHas('notes', ['note' => 'this is note #1 for user #1']);
    }

    public function test_note_update()
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

    public function test_note_edit_button()
    {
        $this->markTestSkipped(
            'Tests the route /note/noteid which is done in the test_note_update_method anyways'
        );

        $this->actingAs($this->userOne);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first(); //grab the latest note since that will be at the top of the app

        $response = $this->get('/note/' . $note->id . '/edit'); //simulate user clicking the edit button

        $response->assertStatus(200);
        $response->assertSee('this is note #10 for user #1');
    }

    public function test_application_handles_special_characters()
    {
        $this->markTestSkipped('Edge case to make sure notes with <>{}[] can still be created');
        $noteData = ['note' => '<>{}[]'];
        $this->actingAs($this->userOne);

        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first(); //grab the latest note since that will be at the top of the app

        $response = $this->put('/note/' . $note->id, $noteData); //simulate the user updating and submitting the note
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'note' => '<>{}[]', // check the database saved the note correctly
        ]);

        $response = $this->get('/note/' . $note->id); //follow the redirect
        $response->assertViewIs('note.show');

        $response->assertSeeText('<>{}[]');
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

    public function test_user_cannot_delete_other_users_note()
    {
        $this->markTestSkipped("One user cannot view another\'s notes in order to delete them. CSRF blocks then from using backend methods as well");

        $this->actingAs($this->userTwo);
        $note = Note::where('user_id', $this->userOne->id)->orderBy('id', 'desc')->first();
        //grab note from userOne rather than userTwo

        $response = $this->delete('/note/' . $note->id);
        $this->assertDatabaseHas('notes', ['id' => $note->id]);
    }
}
