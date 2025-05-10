$(document).ready(function() {
    // Load initial data
    loadStudents();
    loadBooks();
    loadStudentOptions();

    // Student Form Submit Handler
    $('#studentForm').submit(function(e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                add_student: 1,
                name: $form.find('input[name="name"]').val(),
                age: $form.find('input[name="age"]').val()
            },
            success: function(response) {
                $form.trigger('reset');
                $('#studentModal').modal('hide');
                loadStudents();
                loadStudentOptions();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    // Book Form Submit Handler
    $('#bookForm').submit(function(e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                add_book: 1,
                title: $form.find('input[name="title"]').val(),
                student_id: $form.find('select[name="student_id"]').val()
            },
            success: function(response) {
                $form.trigger('reset');
                $('#bookModal').modal('hide');
                loadBooks();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    // Delete Student Handler
    $(document).on('click', '.delete-student', function() {
        if(confirm('Are you sure you want to delete this student and all their books?')) {
            const studentId = $(this).data('id');
            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    delete_student: 1,
                    id: studentId
                },
                success: function() {
                    loadStudents();
                    loadBooks();
                    loadStudentOptions();
                }
            });
        }
    });

    // Delete Book Handler
    $(document).on('click', '.delete-book', function() {
        if(confirm('Are you sure you want to delete this book?')) {
            const bookId = $(this).data('id');
            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    delete_book: 1,
                    id: bookId
                },
                success: function() {
                    loadBooks();
                }
            });
        }
    });

    // Refresh student list when book modal is shown
    $('#bookModal').on('show.bs.modal', function() {
        loadStudentOptions();
    });
});

// Load Students into Table
function loadStudents() {
    $.ajax({
        url: 'index.php?action=get_students',
        method: 'GET',
        success: function(data) {
            $('#studentsTable tbody').html(
                data.map(student => `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td>${student.age}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-student"
                                    data-id="${student.id}">Delete</button>
                        </td>
                    </tr>
                `).join('')
            );
        }
    });
}

// Load Books into Table
function loadBooks() {
    $.ajax({
        url: 'index.php?action=get_books',
        method: 'GET',
        success: function(data) {
            $('#booksTable tbody').html(
                data.map(book => `
                    <tr>
                        <td>${book.id}</td>
                        <td>${book.title}</td>
                        <td>${book.student_name}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-book"
                                    data-id="${book.id}">Delete</button>
                        </td>
                    </tr>
                `).join('')
            );
        }
    });
}

// Load Student Options for Dropdown
function loadStudentOptions() {
    $.ajax({
        url: 'index.php?action=get_student_list',
        method: 'GET',
        success: function(data) {
            const $select = $('select[name="student_id"]');
            $select.html(
                data.map(student =>
                    `<option value="${student.id}">${student.name}</option>`
                ).join('')
            );
        }
    });
}
