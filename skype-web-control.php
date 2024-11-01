<?php
/*
 * Plugin Name: Skype Web Control - Free live chat & call
 * Plugin URI: https://dev.skype.com/webcontrol
 * Description: Have a free Skype chat and call control on your website to provide live customer support
 * Version: 1.0
 * Author: Skype
 * Author URI: https://www.skype.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

if (!defined('ABSPATH'))
    exit('Access restricted :(');

$skypewc_settingsPageId = 'skype-web-control';
$skypewc_settingsSectionName = 'skypewc_settings_section';
$skypewc_settingsGroupName = 'skypewc_settings';
$skypewc_settingsHookSuffix = '';
$skypewc_defaultMode = 'chat_and_calling';
$skypewc_defaultDataId = 'echo123';
$skypewc_defaultBubbleColor = '#00aff0';
$skypewc_defaultMessageColor = '#f1f1f4';

add_action('admin_menu', 'skypewc_add_settings');
add_action('admin_init', 'skypewc_settings_init');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'skypewc_add_settings_link');
add_action('wp_footer', 'skypewc_show_control');
register_uninstall_hook(__FILE__, 'skypewc_uninstall');


function skypewc_add_settings()
{
    global $skypewc_settingsPageId;
    global $skypewc_settingsHookSuffix;
    
    $skypewc_settingsHookSuffix = add_options_page(
        'Skype Web Control settings',
        'Skype Web Control',
        'manage_options',
        $skypewc_settingsPageId,
        'skypewc_settings_page');

    add_action('load-' . $skypewc_settingsHookSuffix, 'skypewc_load_settings_js');
}

function skypewc_add_settings_link($links)
{
    global $skypewc_settingsPageId;
    $settings_link = '<a href="' . admin_url('options-general.php?page=' . $skypewc_settingsPageId) . '">Settings</a>';

    array_push($links, $settings_link);
    return $links;
}

function skypewc_load_settings_js()
{
    add_action('admin_enqueue_scripts', 'skypewc_enqueue_settings_js');
}

function skypewc_enqueue_settings_js($hookSuffix)
{
    global $skypewc_settingsHookSuffix;

    if ($skypewc_settingsHookSuffix !== $hookSuffix || !is_admin())
        return;
    
    wp_enqueue_style('wp-color-picker');

    wp_enqueue_script(
        'skypewc_settings_js',
        plugins_url('settings.js', __FILE__ ),
        array('wp-color-picker'),
        false,
        true);
}

function skypewc_settings_page()
{
    global $skypewc_settingsPageId;
    global $skypewc_settingsGroupName;

    if (!current_user_can('manage_options'))
        return;

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields($skypewc_settingsGroupName);
            do_settings_sections($skypewc_settingsPageId);
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function skypewc_settings_init()
{
    global $skypewc_settingsPageId;
    global $skypewc_settingsSectionName;
    global $skypewc_settingsGroupName;

    add_settings_section($skypewc_settingsSectionName, '', null, $skypewc_settingsPageId);
    
    add_settings_field(
        'skypewc_mode',
        'Mode',
        'skypewc_mode_radio',
        $skypewc_settingsPageId,
        $skypewc_settingsSectionName);
    
    add_settings_field(
        'skypewc_data_id',
        'Skype ID/Microsoft App ID',
        'skypewc_data_id_input',
        $skypewc_settingsPageId,
        $skypewc_settingsSectionName);

    add_settings_field(
        'skypewc_bubble_bg_color',
        'Bubble background color',
        'skypewc_bubble_bg_color_input',
        $skypewc_settingsPageId,
        $skypewc_settingsSectionName);

    add_settings_field(
        'skypewc_message_bg_color',
        'Message background color',
        'skypewc_message_bg_color_input',
        $skypewc_settingsPageId,
        $skypewc_settingsSectionName);

    register_setting($skypewc_settingsGroupName, 'skypewc_mode');
    register_setting($skypewc_settingsGroupName, 'skypewc_data_id');
    register_setting($skypewc_settingsGroupName, 'skypewc_bubble_bg_color');
    register_setting($skypewc_settingsGroupName, 'skypewc_message_bg_color');
}

function skypewc_mode_radio()
{
    global $skypewc_defaultMode;
    $mode_value = esc_attr(get_option('skypewc_mode', $skypewc_defaultMode));

    ?>
    <p><input name="skypewc_mode"
        type="radio"
        value="chat_and_calling"
        <?php checked($mode_value, 'chat_and_calling'); ?>
    />Chat and calling</p>

    <p><input name="skypewc_mode"
        type="radio"
        value="chat_only"
        <?php checked($mode_value, 'chat_only'); ?>
    />Chat only</p>

    <p><input name="skypewc_mode"
        type="radio"
        value="calling_only"
        <?php checked($mode_value, 'calling_only'); ?>
    />Calling only</p>
    <?php
}

function skypewc_data_id_input()
{
    global $skypewc_defaultDataId;
    $data_id = esc_attr(get_option('skypewc_data_id', $skypewc_defaultDataId));

    ?>
    <input name="skypewc_data_id"
        type="text"
        value="<?php echo $data_id ?>"
    />
    <?php
}

function skypewc_bubble_bg_color_input()
{
    global $skypewc_defaultBubbleColor;
    $bubble_bg_color = esc_attr(get_option('skypewc_bubble_bg_color', $skypewc_defaultBubbleColor));

    ?>
    <input name="skypewc_bubble_bg_color"
        type="text"
        value="<?php echo $bubble_bg_color ?>"
        class="color-input"
        data-default-color="<?php echo $skypewc_defaultBubbleColor ?>"
    />
    <?php
}

function skypewc_message_bg_color_input()
{
    global $skypewc_defaultMessageColor;
    $message_bg_color = esc_attr(get_option('skypewc_message_bg_color', $skypewc_defaultMessageColor));

    ?>
    <input name="skypewc_message_bg_color"
        type="text"
        value="<?php echo $message_bg_color ?>"
        class="color-input"
        data-default-color="<?php echo $skypewc_defaultMessageColor ?>"
    />
    <?php
}

function skypewc_get_mode_attr()
{
    global $skypewc_defaultMode;

    $mode_value = esc_attr(get_option('skypewc_mode', $skypewc_defaultMode));

    if ($mode_value === 'chat_and_calling')
        return '';
    elseif ($mode_value === 'chat_only')
        return 'data-enable-calling="false"';
    else
        return 'data-calling-only="true"';
}

function skypewc_show_control()
{
    global $skypewc_defaultDataId;
    global $skypewc_defaultBubbleColor;
    global $skypewc_defaultMessageColor;

    $mode_attr = skypewc_get_mode_attr();
    
    $data_id = esc_attr(get_option('skypewc_data_id', $skypewc_defaultDataId));
    $bubble_color = esc_attr(get_option('skypewc_bubble_bg_color', $skypewc_defaultBubbleColor));
    $message_color = esc_attr(get_option('skypewc_message_bg_color', $skypewc_defaultMessageColor));

    $swcHTML = '<span class="skype-button bubble" data-id="' . $data_id . '" data-color="' . $bubble_color . '" data-provider="wordpress"></span>';
    $swcHTML .= '<span class="skype-chat" data-color-message="' . $message_color . '" ' . $mode_attr . ' data-provider="wordpress"></span>';

    echo $swcHTML;
    wp_enqueue_script('skypewc_script', 'https://swc.cdn.skype.com/sdk/v1/sdk.min.js');
}

function skypewc_uninstall()
{
    delete_option('skypewc_mode');
    delete_option('skypewc_data_id');
    delete_option('skypewc_bubble_bg_color');
    delete_option('skypewc_message_bg_color');
}
?>
