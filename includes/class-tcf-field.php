<?php
/**
 * TCFField Class
 *
 * @package TutorCustomFields
 */

class TCFField
{
    public $field_key;
    public $label;
    public $meta_key;
    public $field_type;
    public $placeholder;
    public $column_width;
    public $required;
    public $error_message;
    public $display_in_profile;
    public $display_in_edit;
    public $select_options;
    public $value;

    /**
     * Constructor
     */
    public function __construct($data = [])
    {
        $this->field_key = isset($data["field_key"]) ? $data["field_key"] : "";
        $this->label = isset($data["label"]) ? $data["label"] : "";
        $this->meta_key = isset($data["meta_key"])
            ? $data["meta_key"]
            : $data["field_key"];
        $this->field_type = isset($data["field_type"])
            ? $data["field_type"]
            : "text";
        $this->placeholder = isset($data["placeholder"])
            ? $data["placeholder"]
            : "";
        $this->column_width = isset($data["column_width"])
            ? $data["column_width"]
            : "full";
        $this->required = isset($data["required"])
            ? (bool) $data["required"]
            : false;
        $this->error_message = isset($data["error_message"])
            ? $data["error_message"]
            : "";
        $this->display_in_profile = isset($data["display_in_profile"])
            ? (bool) $data["display_in_profile"]
            : false;
        $this->display_in_edit = isset($data["display_in_edit"])
            ? (bool) $data["display_in_edit"]
            : false;
        $this->select_options = isset($data["select_options"])
            ? $data["select_options"]
            : "";
        $this->value = "";
    }

    /**
     * Get field types
     */
    public static function get_field_types()
    {
        return [
            "text" => __("Text"),
            "email" => __("Email"),
            "number" => __("Number"),
            "date" => __("Date"),
            "textarea" => __("Textarea"),
            "select" => __("Select"),
            // "image" => __("Image Upload"),
        ];
    }

    /**
     * Get column widths
     */
    public static function get_column_widths()
    {
        return [
            "full" => __("Full Width"),
            "half" => __("Half Width"),
            "third" => __("One Third"),
        ];
    }

    /**
     * Validate field
     */
    public function validate($value = null)
    {
        $value = $value !== null ? $value : $this->value;

        if ($this->required && empty(trim($value))) {
            return new WP_Error(
                "required",
                !empty($this->error_message)
                    ? $this->error_message
                    : sprintf(__("%s is required."), $this->label),
            );
        }

        return true;
    }

    /**
     * Convert to array
     */
    public function to_array()
    {
        return [
            "field_key" => $this->field_key,
            "label" => $this->label,
            "meta_key" => $this->meta_key,
            "field_type" => $this->field_type,
            "placeholder" => $this->placeholder,
            "column_width" => $this->column_width,
            "required" => $this->required,
            "error_message" => $this->error_message,
            "display_in_profile" => $this->display_in_profile,
            "display_in_edit" => $this->display_in_edit,
            "select_options" => $this->select_options,
        ];
    }

    /**
     * Get input HTML
     */
    public function get_input_html($value = "", $echo = true)
    {
        $this->value = $value;
        $required_attr = $this->required ? ' required="required"' : "";
        $placeholder_attr = !empty($this->placeholder)
            ? ' placeholder="' . esc_attr($this->placeholder) . '"'
            : "";
        $id = "tcf_field_" . $this->field_key;
        $name = "tcf_fields[" . $this->field_key . "]";

        // Column width class
        $width_class = "tcf-width-" . $this->column_width;

        $html = '<div class="tcf-field-wrap ' . esc_attr($width_class) . '">';
        $html .= '<label for="' . esc_attr($id) . '">' . esc_html($this->label);
        if ($this->required) {
            $html .= ' <span class="required">*</span>';
        }
        $html .= "</label>";

        switch ($this->field_type) {
            case "textarea":
                $html .=
                    '<textarea name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    $placeholder_attr .
                    ' class="regular-text">' .
                    esc_textarea($value) .
                    "</textarea>";
                break;

            case "select":
                $options = $this->parse_select_options();
                $html .=
                    '<select name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    ' class="regular-text">';
                $html .= '<option value="">' . __("Select...") . "</option>";
                foreach ($options as $opt_value => $opt_label) {
                    $selected = selected($value, $opt_value, false);
                    $html .=
                        '<option value="' .
                        esc_attr($opt_value) .
                        '"' .
                        $selected .
                        ">" .
                        esc_html($opt_label) .
                        "</option>";
                }
                $html .= "</select>";
                break;

            case "date":
                $html .=
                    '<input type="date" name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    $placeholder_attr .
                    ' value="' .
                    esc_attr($value) .
                    '" class="regular-text">';
                break;

            case "email":
                $html .=
                    '<input type="email" name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    $placeholder_attr .
                    ' value="' .
                    esc_attr($value) .
                    '" class="regular-text">';
                break;

            case "number":
                $html .=
                    '<input type="number" name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    $placeholder_attr .
                    ' value="' .
                    esc_attr($value) .
                    '" class="regular-text">';
                break;

            default:
                $html .=
                    '<input type="text" name="' .
                    esc_attr($name) .
                    '" id="' .
                    esc_attr($id) .
                    '"' .
                    $required_attr .
                    $placeholder_attr .
                    ' value="' .
                    esc_attr($value) .
                    '" class="regular-text">';
                break;
        }

        $html .= "</div>";

        if ($echo) {
            echo $html;
        }

        return $html;
    }

    /**
     * Parse select options from string
     */
    private function parse_select_options()
    {
        $options = [];
        $lines = explode("\n", $this->select_options);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (strpos($line, "=") !== false) {
                [$value, $label] = explode("=", $line, 2);
                $options[trim($value)] = trim($label);
            } else {
                $options[$line] = $line;
            }
        }

        return $options;
    }

    /**
     * Get display HTML for profile (read-only)
     */
    public function get_display_html($value = "", $echo = true)
    {
        if (empty($value)) {
            $value = __("Not set");
        }

        $html = '<div class="tcf-profile-field">';
        $html .= "<strong>" . esc_html($this->label) . ":</strong> ";
        $html .= esc_html($value);
        $html .= "</div>";

        if ($echo) {
            echo $html;
        }

        return $html;
    }
}
