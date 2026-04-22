<?php
/**
 * Frontend Hooks for Tutor LMS Integration
 * 
 * @package TutorCustomFields
 */

// Hook into Tutor LMS student registration form (Tutor LMS 3.x)
// Use 'tutor_student_register_form_fields' hook for fields BEFORE submit button
// Fallback: 'tutor_student_reg_form_start' for fields at top of form
add_action('tutor_student_register_form_fields', 'tcf_render_registration_fields');

// Add enctype for file uploads
add_action('tutor_after_reg_form_bottom', 'tcf_add_form_enctype');

/**
 * Add enctype to registration form for file uploads
 */
function tcf_add_form_enctype() {
    $fields = tcf_get_fields();
    $has_image = false;

    foreach ($fields as $field_data) {
        if (isset($field_data['field_type']) && $field_data['field_type'] === 'image') {
            $has_image = true;
            break;
        }
    }

    if ($has_image) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var form = document.querySelector(".tutor-registration-form");
            if (form) {
                form.enctype = "multipart/form-data";
            }
        });
        </script>';
    }
}

/**
 * Render custom fields in registration form
 */
function tcf_render_registration_fields() {
    $fields = tcf_get_fields();
    
    if (empty($fields)) {
        return;
    }
    
    foreach ($fields as $key => $field_data) {
        $field = new TCFField($field_data);
        
        // Check if field should display in registration (all fields show in registration)
        $value = isset($_POST['tcf_fields'][$key]) ? $_POST['tcf_fields'][$key] : '';
        
        $field->get_input_html($value);
    }
}

// Hook into saving extra profile fields
add_action('tutor_save_extra_profile_fields', 'tcf_save_registration_fields', 10, 2);

/**
 * Save custom field data during registration
 */
function tcf_save_registration_fields($user_id, $args) {
    if (!isset($_POST['tcf_fields']) || !is_array($_POST['tcf_fields'])) {
        return;
    }

    $fields = tcf_get_fields();

    foreach ($_POST['tcf_fields'] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }

        $field = new TCFField($fields[$field_key]);

        // Handle image upload
        if ($field->field_type === 'image' && !empty($_FILES['tcf_fields']['name'][$field_key])) {
            $result = tcf_handle_image_upload($field_key, $user_id);
            if (!is_wp_error($result)) {
                update_user_meta($user_id, $field->meta_key, $result);
            }
            continue;
        }

        $sanitized_value = tcf_sanitize_field_value($value, $field->field_type);

        // Save to user meta
        update_user_meta($user_id, $field->meta_key, $sanitized_value);
    }
}

/**
 * Handle image upload for custom fields
 */
function tcf_handle_image_upload($field_key, $user_id) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    if (!function_exists('wp_generate_attachment_metadata')) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    }

    // Check if file was uploaded
    if (!isset($_FILES['tcf_fields']) || !isset($_FILES['tcf_fields']['name'][$field_key])) {
        return new WP_Error('no_file', __('No file uploaded'));
    }

    $file = array(
        'name' => $_FILES['tcf_fields']['name'][$field_key],
        'type' => $_FILES['tcf_fields']['type'][$field_key],
        'tmp_name' => $_FILES['tcf_fields']['tmp_name'][$field_key],
        'error' => $_FILES['tcf_fields']['error'][$field_key],
        'size' => $_FILES['tcf_fields']['size'][$field_key],
    );

    // Validate file
    if (empty($file['name']) || $file['error'] !== 0) {
        return new WP_Error('upload_error', __('Error uploading file'));
    }

    // Validate MIME type
    $allowed_mimes = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp');
    $file_type = wp_check_filetype($file['name'], $allowed_mimes);
    if (!$file_type['ext']) {
        return new WP_Error('invalid_type', __('Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'));
    }

    $upload = wp_handle_upload($file, array('test_form' => false));

    if (isset($upload['error'])) {
        return new WP_Error('upload_error', $upload['error']);
    }

    // Create attachment
    $attachment = array(
        'post_title' => basename($upload['file']),
        'post_content' => '',
        'post_type' => 'attachment',
        'post_parent' => 0,
        'post_mime_type' => $upload['type'],
    );

    $attachment_id = wp_insert_attachment($attachment, $upload['file']);

    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }

    // Generate attachment metadata
    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $metadata);

    return $attachment_id;
}

// Hook into tutor validation
add_filter('tutor_form_validation', 'tcf_validate_registration');

/**
 * Validate custom fields on registration
 */
function tcf_validate_registration($is_valid) {
    if (!isset($_POST['tcf_fields']) || !is_array($_POST['tcf_fields'])) {
        return $is_valid;
    }
    
    $fields = tcf_get_fields();
    
    foreach ($_POST['tcf_fields'] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }
        
        $field = new TCFField($fields[$field_key]);
        $validation = $field->validate($value);
        
        if (is_wp_error($validation)) {
            // Add error message
            tutor()->utils->add_flash($validation->get_error_message(), 'tcf_error_' . $field_key);
            $is_valid = false;
        }
    }
    
    return $is_valid;
}

// Hook into profile display
add_action('tutor_show_extra_profile_fields', 'tcf_render_profile_fields');

/**
 * Render custom fields in student profile
 */
function tcf_render_profile_fields() {
    $fields = tcf_get_fields();
    
    if (empty($fields)) {
        return;
    }
    
    $user_id = get_current_user_id();
    
    foreach ($fields as $key => $field_data) {
        $field = new TCFField($field_data);
        
        // Only show fields marked for profile display
        if (!$field->display_in_profile) {
            continue;
        }
        
        $value = get_user_meta($user_id, $field->meta_key, true);
        
        if (!empty($value)) {
            $field->get_display_html($value);
        }
    }
}

// Hook into profile edit
add_action('tutor_edit_extra_profile_fields', 'tcf_render_profile_edit_fields');

/**
 * Render custom fields in profile edit form
 */
function tcf_render_profile_edit_fields() {
    $fields = tcf_get_fields();
    
    if (empty($fields)) {
        return;
    }
    
    $user_id = get_current_user_id();
    
    foreach ($fields as $key => $field_data) {
        $field = new TCFField($field_data);
        
        // Only show fields marked for edit display
        if (!$field->display_in_edit) {
            continue;
        }
        
        $value = get_user_meta($user_id, $field->meta_key, true);
        
        $field->get_input_html($value);
    }
}

// Hook into saving profile edits
add_action('tutor_update_extra_profile_fields', 'tcf_save_profile_edit_fields', 10, 1);

/**
 * Save custom fields when profile is updated
 */
function tcf_save_profile_edit_fields($user_id) {
    if (!isset($_POST['tcf_fields']) || !is_array($_POST['tcf_fields'])) {
        return;
    }

    $fields = tcf_get_fields();

    foreach ($_POST['tcf_fields'] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }

        // Check if field is allowed for edit
        if (!$fields[$field_key]['display_in_edit']) {
            continue;
        }

        $field = new TCFField($fields[$field_key]);

        // Handle image upload for profile edit
        if ($field->field_type === 'image' && !empty($_FILES['tcf_fields']['name'][$field_key])) {
            $result = tcf_handle_image_upload($field_key, $user_id);
            if (!is_wp_error($result)) {
                update_user_meta($user_id, $field->meta_key, $result);
            }
            continue;
        }

        $sanitized_value = tcf_sanitize_field_value($value, $field->field_type);

        // Validate required
        $validation = $field->validate($value);
        if (is_wp_error($validation)) {
            continue; // Skip validation on edit, or add error
        }

        // Save to user meta
        update_user_meta($user_id, $field->meta_key, $sanitized_value);
    }
}

/**
 * Get tutor instance helper
 */
if (!function_exists('tutor')) {
    function tutor() {
        if (class_exists('Tutor\Utils')) {
            return new \Tutor\Utils();
        }
        return false;
    }
}