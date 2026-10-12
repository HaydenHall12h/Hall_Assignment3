<?php
require_once __DIR__ . '/includes/functions.php';

$values = [
    'name' => '',
    'email' => '',
    'category' => '',
    'description' => ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim($_POST['name'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['category'] = trim($_POST['category'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');

    /*
     * TODO 6: Server-side validation
     *
     * Validate:
     * - name is not empty
     * - email is valid
     * - category is in allowedCategories()
     * - description meets your chosen minimum length
     *
     * Store useful messages in $errors.
     */
    
    // Make sure name field isn't empty
    if ($values['name'] === '') {
        $errors['name'] = 'Name is required.';
    }

    // Make sure email field isn't empty and email is valid.
    if ($values['email'] === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Validate Category
    if ($values['category'] === '') {
        $errors['category'] = 'Please select a category.';
    } elseif (!in_array($values['category'], allowedCategories(), true)) {
        $errors['category'] = 'Invalid category selected.';
    }

    // Validate Description 
    $minLength = 10;
    // Make sure it isn't empty
    if ($values['description'] === '') {
        $errors['description'] = 'Description is required.'; 
    } elseif (mb_strlen($values['description']) < $minLength) { // Make sure it's longer than 10 characters
        $errors['description'] = "Description must be at least {$minLength} characters long.";
    }

    $attachment = null;

    /*
     * TODO 7: Optional file upload
     *
     * If a file was submitted:
     * - check upload error
     * - enforce a maximum size
     * - validate the real file type
     * - generate a safe filename
     * - move it to UPLOAD_DIR
     *
     * Add an error to $errors if the upload is invalid.
     */
    if (empty($errors)) {
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            
            // Define directory
            $target_dir = "uploads/"; 
            $target_file = $target_dir . basename($_FILES["attachment"]["name"]);
            $uploadErrorTriggered = false;

            // Check for upload errors
            if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
                $errors['attachment'] = 'File upload failed with error code: ' . $_FILES['attachment']['error'];
                $uploadErrorTriggered = true;
            }

            // Limit File Size (I'm just using the slide's example of 500,000 bytes (~500KB))
            if (!$uploadErrorTriggered && $_FILES["attachment"]["size"] > 500000) {
                $errors['attachment'] = "Sorry, your file is too large.";
                $uploadErrorTriggered = true;
            }

            // Limit File Type
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (!$uploadErrorTriggered && $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "webp") {
                $errors['attachment'] = "Sorry, only JPG, JPEG, PNG & WEBP files are allowed.";
                $uploadErrorTriggered = true;
            }

            // Check if file already exists in directory
            if (!$uploadErrorTriggered && file_exists($target_file)) {
                $errors['attachment'] = "Sorry, file already exists.";
                $uploadErrorTriggered = true;
            }

            // Store the file
            if (!$uploadErrorTriggered) {
                if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                    $attachment = basename($_FILES["attachment"]["name"]);
                } else {
                    $errors['attachment'] = "Sorry, there was an error uploading your file.";
                }
            }
        }
    }

    if (empty($errors)) {
        $tickets = loadTickets();

        /*
         * TODO 8: Build the new ticket.
         *
         * Use nextTicketId($tickets) for the ID.
         * Include name, email, category, description,
         * status, and attachment.
         */

        $newTicket = [
            'id' => nextTicketId($tickets),
            'name' => $values['name'],
            'email' => $values['email'],
            'category' => $values['category'],
            'description' => $values['description'],
            'status' => 'Open', // Set default status to open
            'attachment' => $attachment
        ];

        $tickets[] = $newTicket;

        /*
         * TODO 9:
         * Save the updated ticket collection.
         *
         * If saving succeeds, redirect to index.php.
         * Do not continue rendering this form after the redirect.
         */

        if (saveTickets($tickets)) {
            header('Location: index.php');
            exit;
        } else {
            $errors['global'] = 'Failed to write the ticket to storage';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<main class="container">
    <p><a href="index.php">&larr; Back to tickets</a></p>

    <section class="panel">
        <h1>Create Support Ticket</h1>
        <p>Complete the form below. Important validation must occur on the server.</p>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Please correct the following:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php" enctype="multipart/form-data">
            <label for="name">Customer Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= e($values['name']) ?>"
            >

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($values['email']) ?>"
            >

            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="">Choose a category</option>
                <?php foreach (allowedCategories() as $category): ?>
                    <option
                        value="<?= e($category) ?>"
                        <?= $values['category'] === $category ? 'selected' : '' ?>
                    >
                        <?= e(ucfirst($category)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="7"><?= e($values['description']) ?></textarea>

            <label for="attachment">Optional Image</label>
            <input
                type="file"
                id="attachment"
                name="attachment"
                accept="image/jpeg,image/png,image/webp"
            >

            <button type="submit">Submit Ticket</button>
        </form>
    </section>
</main>
</body>
</html>
