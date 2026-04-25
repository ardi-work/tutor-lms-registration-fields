<?php
/**
 * Admin Page
 * 
 * @package TutorCustomFields
 */

/**
 * Register admin menu
 */
function tcf_register_admin_menu() {
    add_submenu_page(
        'tutor',
        __('Custom Fields', 'tutor-custom-registration-fields'),
        __('Custom Fields', 'tutor-custom-registration-fields'),
        'manage_options',
        'tutor-custom-fields',
        'tcf_render_admin_page'
    );
}
add_action('admin_menu', 'tcf_register_admin_menu');

/**
 * Render admin page
 */
function tcf_render_admin_page() {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tcf_action'])) {
        if ($_POST['tcf_action'] === 'add_field') {
            check_admin_referer('tcf_add_field');

            $result = tcf_create_field($_POST);

            if (is_wp_error($result)) {
                $message = '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Field added successfully!', 'tutor-custom-registration-fields') . '</p></div>';
            }
        } elseif ($_POST['tcf_action'] === 'delete_field' && isset($_POST['field_key'])) {
            $field_key = sanitize_key(wp_unslash($_POST['field_key']));
            check_admin_referer('tcf_delete_' . $field_key);

            $result = tcf_delete_field($field_key);

            if (is_wp_error($result)) {
                $message = '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Field deleted successfully!', 'tutor-custom-registration-fields') . '</p></div>';
            }
        }
    }
    
    $fields = tcf_get_fields();
    $field_types = TCFField::get_field_types();
    $column_widths = TCFField::get_column_widths();
    ?>
    <div class="wrap">
        <h1><?php _e('Custom Fields', 'tutor-custom-registration-fields'); ?></h1>
        
        <?php if (isset($message)) echo $message; ?>
        
        <div class="tcf-admin-wrapper">
            <!-- Left Column: Add New Field Form -->
            <div class="tcf-admin-left">
                <div class="tcf-card">
                    <h2><?php _e('Add New Field', 'tutor-custom-registration-fields'); ?></h2>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('tcf_add_field'); ?>
                        <input type="hidden" name="tcf_action" value="add_field">
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="field_key"><?php _e('Field Key', 'tutor-custom-registration-fields'); ?></label>
                                    <span class="required">*</span>
                                </th>
                                <td>
                                    <input type="text" name="field_key" id="field_key" class="regular-text" required pattern="[a-z0-9_]+" title="<?php esc_attr_e('Lowercase letters, numbers, and underscores only'); ?>">
                                    <p class="description"><?php _e('Unique identifier (lowercase, numbers, underscore only). Cannot be changed after creation.', 'tutor-custom-registration-fields'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="label"><?php _e('Label', 'tutor-custom-registration-fields'); ?></label>
                                    <span class="required">*</span>
                                </th>
                                <td>
                                    <input type="text" name="label" id="label" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="meta_key"><?php _e('Meta Key', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="meta_key" id="meta_key" class="regular-text">
                                    <p class="description"><?php _e('Database key (optional, defaults to field key)', 'tutor-custom-registration-fields'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="field_type"><?php _e('Field Type', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <select name="field_type" id="field_type" class="regular-text">
                                        <?php foreach ($field_types as $value => $label): ?>
                                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="placeholder"><?php _e('Placeholder', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="placeholder" id="placeholder" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="column_width"><?php _e('Column Width', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <select name="column_width" id="column_width" class="regular-text">
                                        <?php foreach ($column_widths as $value => $label): ?>
                                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="error_message"><?php _e('Error Message', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="error_message" id="error_message" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Required', 'tutor-custom-registration-fields'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="required" value="1">
                                            <?php _e('Make this field required', 'tutor-custom-registration-fields'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Display Options', 'tutor-custom-registration-fields'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" name="display_in_profile" value="1" checked>
                                            <?php _e('Show in student profile', 'tutor-custom-registration-fields'); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="display_in_edit" value="1" checked>
                                            <?php _e('Show in profile edit', 'tutor-custom-registration-fields'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr class="tcf-select-options-row" style="display:none;">
                                <th scope="row">
                                    <label for="select_options"><?php _e('Select Options', 'tutor-custom-registration-fields'); ?></label>
                                </th>
                                <td>
                                    <textarea name="select_options" id="select_options" class="large-text code" rows="5" placeholder="value=Label&#10;option1=Option 1&#10;option2=Option 2"></textarea>
                                    <p class="description"><?php _e('One option per line in format: value=Label', 'tutor-custom-registration-fields'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Add Field', 'tutor-custom-registration-fields')); ?>
                    </form>
                </div>
            </div>
            
            <!-- Right Column: Existing Fields Table -->
            <div class="tcf-admin-right">
                <div class="tcf-card">
                    <h2><?php _e('Existing Fields', 'tutor-custom-registration-fields'); ?></h2>
                    
                    <?php if (empty($fields)): ?>
                        <p class="tcf-empty"><?php _e('No custom fields yet. Create your first field using the form on the left.', 'tutor-custom-registration-fields'); ?></p>
                    <?php else: ?>
                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Label', 'tutor-custom-registration-fields'); ?></th>
                                    <th><?php _e('Key', 'tutor-custom-registration-fields'); ?></th>
                                    <th><?php _e('Type', 'tutor-custom-registration-fields'); ?></th>
                                    <th><?php _e('Required', 'tutor-custom-registration-fields'); ?></th>
                                    <th><?php _e('Actions', 'tutor-custom-registration-fields'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $key => $field): ?>
                                    <tr>
                                        <td><?php echo esc_html($field['label']); ?></td>
                                        <td><code><?php echo esc_html($key); ?></code></td>
                                        <td><?php echo esc_html(isset($field_types[$field['field_type']]) ? $field_types[$field['field_type']] : $field['field_type']); ?></td>
                                        <td><?php echo !empty($field['required']) ? '<span class="dashicons dashicons-yes-alt tcf-yes"></span>' : '-'; ?></td>
                                        <td>
                                            <form method="post" action="" style="display:inline;">
                                                <?php wp_nonce_field('tcf_delete_' . $key); ?>
                                                <input type="hidden" name="tcf_action" value="delete_field">
                                                <input type="hidden" name="field_key" value="<?php echo esc_attr($key); ?>">
                                                <button type="submit" class="button button-small tcf-delete-btn" data-field="<?php echo esc_attr($key); ?>">
                                                    <?php esc_html_e('Delete', 'tutor-custom-registration-fields'); ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
            .tcf-admin-wrapper {
                display: flex;
                gap: 20px;
                margin-top: 20px;
            }
            .tcf-admin-left, .tcf-admin-right {
                flex: 1;
                min-width: 300px;
            }
            .tcf-admin-right {
                flex: 1.5;
            }
            .tcf-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 20px;
                box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            }
            .tcf-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #f0f0f1;
            }
            .tcf-empty {
                color: #646970;
                font-style: italic;
            }
            .tcf-yes {
                color: #2ecc71;
            }
            .required {
                color: #d63638;
            }
            .tcf-delete-btn {
                color: #d63638;
            }
            .tcf-delete-btn:hover {
                background: #f0c6c6;
                border-color: #d63638;
            }
            @media (max-width: 768px) {
                .tcf-admin-wrapper {
                    flex-direction: column;
                }
            }
        </style>
        
        <script>
            jQuery(document).ready(function($) {
                // Show/hide select options based on field type
                $('#field_type').change(function() {
                    if ($(this).val() === 'select') {
                        $('.tcf-select-options-row').show();
                    } else {
                        $('.tcf-select-options-row').hide();
                    }
                });
                
                // Delete confirmation
                $('.tcf-delete-btn').click(function(e) {
                    var field = $(this).data('field');
                    if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this field?', 'tutor-custom-registration-fields')); ?>')) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
    </div>
    <?php
}