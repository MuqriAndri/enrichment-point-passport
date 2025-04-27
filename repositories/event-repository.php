<?php

class EventRepository {
    private $eventsDB;
    private $profilesDB;

    public function __construct($eventsDB, $profilesDB) {
        $this->eventsDB = $eventsDB;
        $this->profilesDB = $profilesDB;
    }

    /**
     * Get all events
     *
     * @return array List of all events
     */
    public function getAllEvents() {
        try {
            $stmt = $this->eventsDB->prepare("SELECT * FROM events ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all events: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get scheduled events
     *
     * @return array List of scheduled events
     */
    public function getScheduledEvents() {
        try {
            $stmt = $this->eventsDB->prepare("SELECT * FROM events WHERE status = 'Scheduled' ORDER BY event_date DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting scheduled events: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get event by ID
     *
     * @param int $eventId The event ID to fetch
     * @return array|false Event details or false on error
     */
    public function getEventById($eventId) {
        try {
            $stmt = $this->eventsDB->prepare("SELECT * FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting event by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new event
     *
     * @param array $eventData Event data
     * @return array Result with success status and message
     */
    public function addEvent($eventData) {
        try {
            // Start a transaction to ensure data consistency
            $this->eventsDB->beginTransaction();
            
            error_log("Adding event: " . print_r($eventData, true));
            
            $sql = "INSERT INTO events (
                event_name, 
                event_description, 
                event_location, 
                event_participants, 
                event_date, 
                start_time, 
                end_time, 
                status, 
                events_images, 
                enrichment_points_awarded,
                organizer
            ) VALUES (
                :event_name, 
                :event_description, 
                :event_location, 
                :event_participants, 
                :event_date, 
                :start_time, 
                :end_time, 
                :status, 
                :events_images, 
                :enrichment_points_awarded,
                :organizer
            )";

            $stmt = $this->eventsDB->prepare($sql);
            
            // Check if statement preparation failed
            if (!$stmt) {
                $errorInfo = $this->eventsDB->errorInfo();
                error_log("Statement preparation failed: " . ($errorInfo[2] ?? 'Unknown error'));
                throw new PDOException("Failed to prepare SQL statement");
            }
            
            // Bind and validate parameters
            foreach ([
                'event_name', 'event_description', 'event_location', 
                'event_participants', 'event_date', 'start_time', 
                'end_time', 'status', 'events_images', 
                'enrichment_points_awarded', 'organizer'
            ] as $param) {
                if (!array_key_exists($param, $eventData)) {
                    error_log("Missing parameter in event data: $param");
                    $eventData[$param] = ($param === 'events_images' || $param === 'event_description') ? '' : 'Default';
                }
                $stmt->bindParam(":$param", $eventData[$param]);
            }
            
            // Execute with error checking
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . ($errorInfo[2] ?? 'Unknown error'));
                throw new PDOException("Failed to execute SQL: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            $eventId = $this->eventsDB->lastInsertId();
            
            // Verify the inserted record with a select query
            if ($eventId) {
                $verifyStmt = $this->eventsDB->prepare("SELECT event_id FROM events WHERE event_id = ?");
                $verifyStmt->execute([$eventId]);
                if (!$verifyStmt->fetch()) {
                    error_log("Event ID $eventId not found after insert - possible insert failure");
                    throw new PDOException("Insert verification failed");
                }
                error_log("Event successfully inserted and verified with ID: $eventId");
            } else {
                error_log("No event ID returned from lastInsertId()");
                throw new PDOException("No event ID returned from insert operation");
            }
            
            // Commit the transaction
            $this->eventsDB->commit();
            
            return [
                'success' => true,
                'message' => 'Event added successfully',
                'event_id' => $eventId
            ];
        } catch (PDOException $e) {
            // Rollback on error
            if ($this->eventsDB->inTransaction()) {
                $this->eventsDB->rollBack();
                error_log("Transaction rolled back due to error: " . $e->getMessage());
            }
            
            error_log("Error adding event: " . $e->getMessage());
            
            // Database reconnection logic if connection was lost
            if (strpos($e->getMessage(), 'server has gone away') !== false || 
                strpos($e->getMessage(), 'Connection refused') !== false) {
                error_log("Database connection problem detected - attempting to reconnect");
                
                // Recreate connection - would need to be implemented in a production system
                // Here we're just logging the error
            }
            
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing event
     *
     * @param int $eventId The event ID to update
     * @param array $eventData Updated event data
     * @return array Result with success status and message
     */
    public function updateEvent($eventId, $eventData) {
        try {
            // Start a transaction
            $this->eventsDB->beginTransaction();
            
            // Build the update SQL statement dynamically
            $sql = "UPDATE events SET ";
            $updateFields = [];
            $params = [];
            
            // Create update fields for all event data except event_id
            foreach ($eventData as $field => $value) {
                if ($field !== 'event_id' && $field !== 'created_at') {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $value;
                }
            }
            
            // Combine update fields and add WHERE clause
            $sql .= implode(', ', $updateFields);
            $sql .= " WHERE event_id = :event_id";
            $params[':event_id'] = $eventId;
            
            error_log("Updating event ID $eventId with SQL: $sql");
            
            $stmt = $this->eventsDB->prepare($sql);
            
            // Execute the statement
            $stmt->execute($params);
            
            // Verify update success
            $rowCount = $stmt->rowCount();
            error_log("Update affected $rowCount rows");
            
            // Retrieve updated event to confirm changes
            $verifyStmt = $this->eventsDB->prepare("SELECT * FROM events WHERE event_id = ?");
            $verifyStmt->execute([$eventId]);
            $updatedEvent = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$updatedEvent) {
                error_log("Failed to retrieve updated event with ID $eventId");
                throw new PDOException("Failed to verify update");
            }
            
            // Log successful update
            error_log("Event $eventId successfully updated");
            
            // Commit transaction
            $this->eventsDB->commit();
            
            return [
                'success' => true,
                'message' => $rowCount > 0 ? 'Event updated successfully' : 'No changes were made to the event',
                'event_id' => $eventId
            ];
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->eventsDB->inTransaction()) {
                $this->eventsDB->rollBack();
            }
            
            error_log("Error updating event: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete an event
     *
     * @param int $eventId The event ID to delete
     * @return array Result with success status and message
     */
    public function deleteEvent($eventId) {
        try {
            // Check if there are participants first
            $stmt = $this->eventsDB->prepare("SELECT COUNT(*) FROM events_participants WHERE event_id = ?");
            $stmt->execute([$eventId]);
            $participantCount = $stmt->fetchColumn();
            
            if ($participantCount > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete event with registered participants'
                ];
            }
            
            // Delete the event
            $stmt = $this->eventsDB->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Event not found or already deleted'
                ];
            }
        } catch (PDOException $e) {
            error_log("Error deleting event: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get registered participants for an event
     *
     * @param int $eventId The event ID
     * @return array List of participants
     */
    public function getEventParticipants($eventId) {
        try {
            $sql = "SELECT ep.participant_id, ep.event_id, ep.user_id, 
                           ep.participant_name, ep.participant_email,
                           ep.participant_phone, ep.registration_date, 
                           ep.attendance_status, ep.status,
                           u.full_name, u.user_email, u.student_id
                    FROM events_participants ep
                    JOIN profiles.users u ON ep.user_id = u.user_id
                    WHERE ep.event_id = ?
                    ORDER BY ep.registration_date DESC";
            
            $stmt = $this->eventsDB->prepare($sql);
            $stmt->execute([$eventId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting event participants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update participant status
     *
     * @param int $participantId The participant ID
     * @param string $status New status
     * @return array Result with success status and message
     */
    public function updateParticipantStatus($participantId, $status) {
        try {
            $validStatuses = ['Registered', 'Confirmed', 'Attended', 'Cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status provided'
                ];
            }
            
            // Log the update attempt
            error_log("Updating participant ID $participantId to status $status");
            
            $stmt = $this->eventsDB->prepare("UPDATE events_participants SET attendance_status = ? WHERE participant_id = ?");
            $stmt->execute([$status, $participantId]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Participant status updated successfully'
                ];
            } else {
                // Check if the participant exists
                $stmt = $this->eventsDB->prepare("SELECT COUNT(*) FROM events_participants WHERE participant_id = ?");
                $stmt->execute([$participantId]);
                $exists = (int)$stmt->fetchColumn() > 0;
                
                if (!$exists) {
                    return [
                        'success' => false,
                        'message' => 'Participant not found'
                    ];
                } else {
                    // Participant exists but status was already set to the requested value
                    return [
                        'success' => true,
                        'message' => 'Status already set to ' . $status
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Error updating participant status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
} 