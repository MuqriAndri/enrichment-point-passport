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
            
            $stmt->bindParam(':event_name', $eventData['event_name']);
            $stmt->bindParam(':event_description', $eventData['event_description']);
            $stmt->bindParam(':event_location', $eventData['event_location']);
            $stmt->bindParam(':event_participants', $eventData['event_participants']);
            $stmt->bindParam(':event_date', $eventData['event_date']);
            $stmt->bindParam(':start_time', $eventData['start_time']);
            $stmt->bindParam(':end_time', $eventData['end_time']);
            $stmt->bindParam(':status', $eventData['status']);
            $stmt->bindParam(':events_images', $eventData['events_images']);
            $stmt->bindParam(':enrichment_points_awarded', $eventData['enrichment_points_awarded']);
            $stmt->bindParam(':organizer', $eventData['organizer']);
            
            $stmt->execute();
            $eventId = $this->eventsDB->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Event added successfully',
                'event_id' => $eventId
            ];
        } catch (PDOException $e) {
            error_log("Error adding event: " . $e->getMessage());
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
            $sql = "UPDATE events SET 
                event_name = :event_name, 
                event_description = :event_description, 
                event_location = :event_location, 
                event_participants = :event_participants, 
                event_date = :event_date, 
                start_time = :start_time, 
                end_time = :end_time, 
                status = :status,
                enrichment_points_awarded = :enrichment_points_awarded,
                organizer = :organizer";
            
            $params = [
                ':event_id' => $eventId,
                ':event_name' => $eventData['event_name'],
                ':event_description' => $eventData['event_description'],
                ':event_location' => $eventData['event_location'],
                ':event_participants' => $eventData['event_participants'],
                ':event_date' => $eventData['event_date'],
                ':start_time' => $eventData['start_time'],
                ':end_time' => $eventData['end_time'],
                ':status' => $eventData['status'],
                ':enrichment_points_awarded' => $eventData['enrichment_points_awarded'],
                ':organizer' => $eventData['organizer']
            ];
            
            // Only update image if a new one is provided
            if (!empty($eventData['events_images'])) {
                $sql .= ", events_images = :events_images";
                $params[':events_images'] = $eventData['events_images'];
            }
            
            $sql .= " WHERE event_id = :event_id";
            
            $stmt = $this->eventsDB->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Event updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No changes were made or event does not exist'
                ];
            }
        } catch (PDOException $e) {
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