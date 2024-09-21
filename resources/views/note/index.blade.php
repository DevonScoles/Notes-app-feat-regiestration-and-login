<x-app-layout>

    {{-- x-app-layout referrs to the app.blade layout under veiws where
    everything between <x-app-layout> </x-app-layout> gets pasted into $slot --}}

    <div class="note-container py-12">
        <a href="{{ route('note.create') }}" class="new-note-btn">
            New Note
        </a>
        <div class="notes">
            @foreach ($notes as $note)
                <div class="note">
                    <div class="note-body">
                        {{ Str::words($note->note, 30) }}
                    </div>
                    <div class="note-buttons">
                        <a href="{{ route('note.show', $note) }}" class="note-edit-button">View</a>
                        <a href="{{ route('note.edit', $note) }}" class="note-edit-button">Edit</a>
                        <form action="{{ route('note.destroy', $note) }}" method="POST">
                            @csrf {{--  Laravel requires this token for POST, PUT, PATCH, and DELETE requests to protect against CSRF attacks --}}
                            @method('DELETE')
                            {{-- @method('DELETE') laravel specific to spoof the method..... generates a hidden input field named _method with a value of DELETE. This allows the form to be submitted as a DELETE request to the server --}}
                            <button class="note-delete-button">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-6">
            {{ $notes->links() }}
            {{-- Links() generates all the pagniation links and info when using pagination such as $notes->...
                essentially creates a 1234 list at the bottom that's associated with links to all the pages of notes  --}}
        </div>
    </div>
</x-app-layout>
