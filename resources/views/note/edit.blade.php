<x-layout>
    <div class="note-container single-note">
        <h1>Edit your note</h1>        
        <form action="" method="POST" class="note">
            <textarea name="note" rows="10" class="note-body" placeholder="Enter your note here">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Odit, accusantium amet vitae, quis voluptatum corporis qui earum officia magnam veniam nostrum, quae animi. Possimus explicabo optio, tenetur esse labore quibusdam!
            </textarea>
            <div class="note-buttons">
                <a href="{{ route('note.index') }}" class="note-cancel-button">Cancel</a>
                <button class="note-submit-button">Submit</button>
            </div>
        </form>
    </div>
</x-layout>
