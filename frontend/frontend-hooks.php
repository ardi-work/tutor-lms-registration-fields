<?php
/**
 * Frontend Hooks for Tutor LMS Integration
 *
 * @package TutorCustomFields
 */

// Hook into Tutor LMS student registration form (Tutor LMS 3.x)
// add_action("tutor_student_reg_form_start", "tcf_render_registration_fields");
add_action("tutor_student_reg_form_end", "tcf_render_registration_fields");

/**
 * Render custom fields in registration form
 */
function tcf_render_registration_fields()
{
    $fields = tcf_get_fields();

    if (empty($fields)) {
        return;
    }
    echo '<div class="tutor-form-row" style="margin-bottom: 32px;">';
    wp_nonce_field('tcf_registration', 'tcf_reg_nonce');
    foreach ($fields as $key => $field_data) {
        $field = new TCFField($field_data);

        $value = isset($_POST["tcf_fields"][$key])
            ? sanitize_text_field(wp_unslash($_POST["tcf_fields"][$key]))
            : "";

        $field->get_input_html($value);
    }
    echo "</div>";
}

// Hook into saving extra profile fields
add_action(
    "tutor_save_extra_profile_fields",
    "tcf_save_registration_fields",
    10,
    2,
);

/**
 * Save custom field data during registration
 */
function tcf_save_registration_fields($user_id, $args)
{
    if (!isset($_POST["tcf_fields"]) || !is_array($_POST["tcf_fields"])) {
        return;
    }

    if (
        !isset($_POST["tcf_reg_nonce"]) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["tcf_reg_nonce"])), "tcf_registration")
    ) {
        return;
    }

    $fields = tcf_get_fields();

    foreach ($_POST["tcf_fields"] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }

        $field = new TCFField($fields[$field_key]);

        $sanitized_value = tcf_sanitize_field_value($value, $field->field_type);

        // Save to user meta
        update_user_meta($user_id, $field->meta_key, $sanitized_value);
    }
}

// Hook into tutor validation
add_filter("tutor_form_validation", "tcf_validate_registration");

/**
 * Validate custom fields on registration
 */
function tcf_validate_registration($is_valid)
{
    if (!isset($_POST["tcf_fields"]) || !is_array($_POST["tcf_fields"])) {
        return $is_valid;
    }

    if (
        !isset($_POST["tcf_reg_nonce"]) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["tcf_reg_nonce"])), "tcf_registration")
    ) {
        return false;
    }

    $fields = tcf_get_fields();

    foreach ($_POST["tcf_fields"] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }

        $field = new TCFField($fields[$field_key]);
        $validation = $field->validate($value);

        if (is_wp_error($validation)) {
            if (function_exists("tutor") && tutor() && isset(tutor()->utils)) {
                tutor()->utils->add_flash(
                    $validation->get_error_message(),
                    "tcf_error_" . $field_key,
                );
            }
            $is_valid = false;
        }
    }

    return $is_valid;
}

// Hook into profile display
add_action("tutor_show_extra_profile_fields", "tcf_render_profile_fields");

/**
 * Render custom fields in student profile
 */
function tcf_render_profile_fields()
{
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
add_action("tutor_edit_extra_profile_fields", "tcf_render_profile_edit_fields");

/**
 * Render custom fields in profile edit form
 */
function tcf_render_profile_edit_fields()
{
    $fields = tcf_get_fields();

    if (empty($fields)) {
        return;
    }

    $user_id = get_current_user_id();

    wp_nonce_field('tcf_profile_edit', 'tcf_edit_nonce');

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
add_action(
    "tutor_update_extra_profile_fields",
    "tcf_save_profile_edit_fields",
    10,
    1,
);

/**
 * Save custom fields when profile is updated
 */
function tcf_save_profile_edit_fields($user_id)
{
    if (!isset($_POST["tcf_fields"]) || !is_array($_POST["tcf_fields"])) {
        return;
    }

    if (
        !isset($_POST["tcf_edit_nonce"]) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["tcf_edit_nonce"])), "tcf_profile_edit")
    ) {
        return;
    }

    // Ensure user can only update their own profile
    if (get_current_user_id() !== (int) $user_id) {
        return;
    }

    $fields = tcf_get_fields();

    foreach ($_POST["tcf_fields"] as $field_key => $value) {
        if (!isset($fields[$field_key])) {
            continue;
        }

        // Check if field is allowed for edit
        if (!$fields[$field_key]["display_in_edit"]) {
            continue;
        }

        $field = new TCFField($fields[$field_key]);

        $sanitized_value = tcf_sanitize_field_value($value, $field->field_type);

        // Validate required
        $validation = $field->validate($value);
        if (is_wp_error($validation)) {
            do_action('tcf_profile_edit_validation_error', $validation, $field, $user_id);
            continue;
        }

        // Save to user meta
        update_user_meta($user_id, $field->meta_key, $sanitized_value);
    }
}

