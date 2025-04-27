<div id="clubApplicationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Club Application Form</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p class="modal-description">Please provide your information to join this club:</p>
            <form id="clubApplicationForm" method="POST" action="<?php echo BASE_URL; ?>/cca">
                <input type="hidden" name="action" value="cca">
                <input type="hidden" name="operation" value="join">
                <input type="hidden" name="club_id" id="applicationClubId" value="<?php echo htmlspecialchars($clubId); ?>">

                <div class="form-actions form-top-actions">
                    <button type="button" class="autofill-btn" id="autofillButton">Autofill My Information</button>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required>
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" name="student_id" id="student_id" required>
                </div>

                <div class="form-group">
                    <label for="student_email">Student Email</label>
                    <input type="email" name="student_email" id="student_email" required>
                </div>

                <div class="form-group">
                    <label for="school">School</label>
                    <select name="school" id="school" required>
                        <option value="">Please select</option>
                        <option value="School of Business">School of Business</option>
                        <option value="School of Health Sciences">School of Health Sciences</option>
                        <option value="School of Information and Communication Technology">School of Information and Communication Technology</option>
                        <option value="School of Science and Engineering">School of Science and Engineering</option>
                        <option value="School of Petrochemical">School of Petrochemical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" name="course" id="course" required>
                </div>

                <div class="form-group">
                    <label for="group_code">Group Code</label>
                    <input type="text" name="group_code" id="group_code" required>
                </div>

                <div class="form-group">
                    <label for="intake">Intake</label>
                    <input type="text" name="intake" id="intake" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" name="phone_number" id="phone_number" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-application-btn">Cancel</button>
                    <button type="submit" class="submit-application-btn">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pass session data to JavaScript -->
<script>
    window.sessionUserData = <?php 
        echo json_encode([
            'full_name' => isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '',
            'student_id' => isset($_SESSION['student_id']) ? $_SESSION['student_id'] : '',
            'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '',
            'school' => isset($_SESSION['school']) ? $_SESSION['school'] : '',
            'programme' => isset($_SESSION['programme']) ? $_SESSION['programme'] : '',
            'group_code' => isset($_SESSION['group_code']) ? $_SESSION['group_code'] : '',
            'intake' => isset($_SESSION['intake']) ? $_SESSION['intake'] : ''
        ]);
    ?>;
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/join-club.js"></script>