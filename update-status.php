<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

/*
 * TODO 10:
 * Read and validate the submitted ticket ID and status.
 *
 * Requirements:
 * - ID must be a valid integer
 * - status must be in allowedStatuses()
 * - ticket must exist
 * - update must be saved to the JSON file
 * - redirect back to detail.php?id=...
 *
 * Think about what status code/message should be returned
 * when the request is invalid.
 */

//http_response_code(501);
// echo 'Status update not implemented yet.';

// Validate the ticket ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    http_response_code(400); // Bad request for structural parameter failues
    exit('Invalid or missing ticket identifier');
}

// Make sure status is not empty and allowed from the list.
$newStatus = trim($_POST['status'] ?? '');
if ($newStatus === '' || !in_array($newStatus, allowedStatuses(), true)) {
    http_response_code(400);
    exit('Invalid status type selected');
}

$tickets = loadTickets();

$isUpdated = updateTicketStatus($tickets, $id, $newStatus);
if (!$isUpdated) {
    http_response_code(404);
    exit('The requested ticket could not be found');
}

$isSaved = saveTickets($tickets);
if (!$isSaved) {
    http_response_code(500);
    exit('Failed to write changes to storage');
}

header("Location: detail.php?id=" . $id);
exit;


