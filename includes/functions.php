<?php
/**
 * Custom Fields Functions
 *
 * @package TutorCustomFields
 */

// Get all custom fields
function tcf_get_fields()
{
    $option = get_option(TCF_OPTION_KEY, ["fields" => []]);
    return isset($option["fields"]) ? $option["fields"] : [];
}

// Get single field by key
function tcf_get_field($field_key)
{
    $fields = tcf_get_fields();
    return isset($fields[$field_key]) ? $fields[$field_key] : null;
}

// Create or update field
function tcf_create_field($field_data)
{
    $fields = tcf_get_fields();

    $field_key = sanitize_key($field_data["field_key"]);

    // Validate field key format (only lowercase, numbers, underscore)
    if (!preg_match('/^[a-z0-9_]+$/', $field_key)) {
        return new WP_Error(
            "invalid_key",
            __(
                "Field key must contain only lowercase letters, numbers, and underscores.",
            ),
        );
    }

    // Check for duplicate
    if (isset($fields[$field_key])) {
        return new WP_Error(
            "duplicate_key",
            __("Field key already exists. Please use a different key."),
        );
    }

    $fields[$field_key] = [
        "field_key" => $field_key,
        "label" => sanitize_text_field($field_data["label"]),
        "meta_key" => !empty($field_data["meta_key"])
            ? sanitize_key($field_data["meta_key"])
            : $field_key,
        "field_type" => sanitize_text_field($field_data["field_type"]),
        "placeholder" => sanitize_text_field($field_data["placeholder"]),
        "column_width" => sanitize_text_field($field_data["column_width"]),
        "required" => !empty($field_data["required"]) ? true : false,
        "error_message" => sanitize_text_field($field_data["error_message"]),
        "display_in_profile" => !empty($field_data["display_in_profile"])
            ? true
            : false,
        "display_in_edit" => !empty($field_data["display_in_edit"])
            ? true
            : false,
        "select_options" => isset($field_data["select_options"])
            ? $field_data["select_options"]
            : "",
    ];

    $option = get_option(TCF_OPTION_KEY, ["fields" => []]);
    $option["fields"] = $fields;
    $option["version"] = TCF_VERSION;

    update_option(TCF_OPTION_KEY, $option);

    return $field_key;
}

// Delete field
function tcf_delete_field($field_key)
{
    $fields = tcf_get_fields();

    if (!isset($fields[$field_key])) {
        return new WP_Error("not_found", __("Field not found."));
    }

    unset($fields[$field_key]);

    $option = get_option(TCF_OPTION_KEY, ["fields" => []]);
    $option["fields"] = $fields;
    $option["version"] = TCF_VERSION;

    update_option(TCF_OPTION_KEY, $option);

    return true;
}

// Get available field types
function tcf_get_field_types()
{
    return [
        "text" => __("Text"),
        "email" => __("Email"),
        "number" => __("Number"),
        "date" => __("Date"),
        "textarea" => __("Textarea"),
        "select" => __("Select"),
    ];
}

// Get column width options
function tcf_get_column_widths()
{
    return [
        "full" => __("Full Width"),
        "half" => __("Half Width"),
        "third" => __("One Third"),
    ];
}

// Sanitize field input
function tcf_sanitize_field_value($value, $field_type)
{
    switch ($field_type) {
        case "email":
            return sanitize_email($value);
        case "number":
            return absint($value);
        case "textarea":
            return sanitize_textarea_field($value);
        // case 'image':
        //     // For image fields, the value should be the attachment ID or URL after processing
        //     return esc_url_raw($value);
        default:
            return sanitize_text_field($value);
    }
}

// Validate required field
function tcf_validate_required($value, $error_message)
{
    if (empty(trim($value))) {
        return new WP_Error(
            "required",
            !empty($error_message)
                ? $error_message
                : __("This field is required."),
        );
    }
    return true;
}
