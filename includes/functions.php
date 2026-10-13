<?php

const DATA_FILE = __DIR__ . '/../data/tickets.json';
const UPLOAD_DIR = __DIR__ . '/../uploads/';

function allowedCategories(): array
{
    return ['technical', 'billing', 'account', 'other'];
}

function allowedStatuses(): array
{
    return ['Open', 'In Progress', 'Resolved'];
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadTickets(): array
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }

    $json = file_get_contents(DATA_FILE);
    $tickets = json_decode($json, true);

    return is_array($tickets) ? $tickets : [];
}

function saveTickets(array $tickets): bool
{
    /*
     * TODO 1:
     * Convert $tickets to formatted JSON and write it to DATA_FILE.
     *
     * Think about:
     * - json_encode(...)
     * - file_put_contents(...)
     * - what should this function return if saving fails?
     */
    $jsonData = json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    // Check if JSON encoding failed
    if ($jsonData === false) {
        return false;
    }

    // Write the JSON string to the file
    $result = file_put_contents(DATA_FILE, $jsonData);

    // Return true if bytes were written, false otherwise
    return $result !== false;
}

function findTicketById(array $tickets, int $id): ?array
{
    foreach ($tickets as $ticket) {
        if ((int)$ticket['id'] === $id) {
            return $ticket;
        }
    }

    return null;
}

function nextTicketId(array $tickets): int
{
    /*
     * TODO 2:
     * Return a unique integer ID for a new ticket.
     *
     * Hint:
     * The next ID can be one larger than the current maximum.
     */
    if (empty($tickets)) {
        return 1;
    }

    // Extracts all 'id' values from the multi-dimensional array
    $ids = array_column($tickets, 'id');

    // Returns the highest ID + 1
    return max($ids) + 1;

    return 0;
}

function filterTickets(
    array $tickets,
    string $query = '',
    string $status = '',
    string $category = ''
): array {
    /*
     * TODO 3:
     * Filter tickets using the supplied GET parameters.
     *
     * Requirements:
     * - blank filters should not remove records
     * - status should match exactly
     * - category should match exactly
     * - keyword search should be case-insensitive
     * - search at least name, email, and description
     */
    return array_filter($tickets, function ($ticket) use ($query, $status, $category) {
        // Check if status filter was selected. If ticket doesn't match, return false
        if ($status !== '' && ($ticket['status'] ?? '') !== $status) {
            return false;
        }

        // Same logic as status
        if ($category !== '' && ($ticket['category'] ?? '') !== $category) {
            return false;
        }

        // First check to make sure a query was entered in the search box
        if ($query !== '') {
            // Keyword Search using stripos to be case insensitive
            // Each line has a boolean checking if the query matches name/email/description respectively
            $nameMatch        = stripos($ticket['name'] ?? '', $query) !== false;
            $emailMatch       = stripos($ticket['email'] ?? '', $query) !== false;
            $descriptionMatch = stripos($ticket['description'] ?? '', $query) !== false;

            // If none of the above booleans are true, return false. Meaning ticket doesn't match
            if (!$nameMatch && !$emailMatch && !$descriptionMatch) {
                return false;
            }
        }

        // Ticket passed all active filters, and will be returned
        return true;
    });
    // return $tickets; originally return from before I implemented the logic
}

function updateTicketStatus(array &$tickets, int $id, string $newStatus): bool
{
    /*
     * TODO 4:
     * Locate the ticket with the matching ID and update its status.
     * Return true if a ticket was updated, otherwise false.
     */
    foreach ($tickets as &$ticket) {
        // Check if the id matches with the id in server storage. If not, we use ?? null to prevent any errors
        if (($ticket['id'] ?? null) === $id) {
            $ticket['status'] = $newStatus;
            return true; // Stop execution
        }
    }

    return false; // No matching ID was found
}
