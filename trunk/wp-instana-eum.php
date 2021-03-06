<?php
/*
Plugin Name: Instana EUM
Plugin URI:  https://github.com/instana/wp-instana-eum
Description: Instana End User Monitoring
Version:     1.0.4
Author:      Instana
Author URI:  http://instana.com
License:     Apache License 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0
*/

namespace Instana;

const SECTION_MENU = 'instana-eum';
const SECTION_INSTANA = 'instana_settings_section';

const INSTANA_API_KEY = 'instana_api_key';
const INSTANA_EUM_BASE_URL = 'instana_eum_base_url';
const INSTANA_EUM_USE_DEBUG = 'instana_eum_use_debug';
const INSTANA_ADVANCED_SETTINGS = 'instana_advanced_settings';

const DEFAULT_EUM_BASE_URL = '//eum.instana.io';
const X_INSTANA_T = 'X-INSTANA-T';

function get_eum_script() {
    $baseUrl = get_option(INSTANA_EUM_BASE_URL, DEFAULT_EUM_BASE_URL);
    $scriptName = boolval(get_option(INSTANA_EUM_USE_DEBUG, false)) ? 'eum.debug.js' : 'eum.min.js';
    $apiKey = get_option(INSTANA_API_KEY, '');

    if (empty($apiKey)) {
        return;
    }

    $js = '<!-- Instana End User Monitoring Beacon -->' . PHP_EOL . '<script type="text/javascript">' . PHP_EOL;

    $js .=  sprintf("(function(i,s,o,g,r,a,m){i['InstanaEumObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','%s/%s','ineum');%s", $baseUrl, $scriptName, PHP_EOL);

    $js .=  sprintf("ineum('apiKey', '%s');%s", $apiKey, PHP_EOL);

    if (isset($_SERVER[X_INSTANA_T])) {
        $js .= sprintf("ineum('traceId', '%s');%s", $_SERVER[X_INSTANA_T], PHP_EOL);
    }

    if ($baseUrl !== DEFAULT_EUM_BASE_URL) {
        $js .= sprintf("ineum('reportingUrl', '%s');%s", $baseUrl, PHP_EOL);
    }

    $js .= trim(get_option(INSTANA_ADVANCED_SETTINGS) . PHP_EOL);
    $js .= '</script>' . PHP_EOL;

    return $js;
}

add_action(
    'admin_menu',
    function() {
        add_options_page('Instana EUM Configuration', 'Instana EUM', 'manage_options', 'instana-eum', function() {
            ?>
            <div class="wrap">
                <h1>Instana EUM - Web End User Monitoring</h1>
                <form action="options.php" method="post">
                    <?php settings_fields(SECTION_MENU); ?>
                    <?php do_settings_sections(SECTION_MENU); ?>
                    <input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
                </form>
            </div>
            <?php
        });
    }
);

add_action(
    'admin_init',
    function() {
        register_setting(
            SECTION_MENU,
            INSTANA_API_KEY,
            array(
                'type' => 'string',
                'description' => 'The Monitoring key received from Instana',
                'sanitize_callback' => function($val) {
                    $val = trim($val);
                    preg_match('~[a-z0-9-_]+~i', $val, $match);
                    if (isset($match[0]) && $match[0] === $val) {
                        return $val;
                    }
                    return '';
                },
                'show_in_rest' => false,
                'default' => ''
            )
        );
        register_setting(
            SECTION_MENU,
            INSTANA_EUM_BASE_URL,
            array(
                'type' => 'string',
                'description' => 'Base URL to load script from and send beacons to',
                'sanitize_callback' => null,
                'show_in_rest' => false,
                'default' => DEFAULT_EUM_BASE_URL
            )
        );
        register_setting(
            SECTION_MENU,
            INSTANA_EUM_USE_DEBUG,
            array(
                'type' => 'boolean',
                'description' => 'Whether to use the unminified debug script',
                'sanitize_callback' => 'boolval',
                'show_in_rest' => false,
                'default' => false
            )
        );
        register_setting(
            SECTION_MENU,
            INSTANA_ADVANCED_SETTINGS,
            array(
                'type' => 'string',
                'description' => 'Additional JavaScript code',
                'sanitize_callback' => null,
                'show_in_rest' => false,
                'default' => ''
            )
        );
        add_settings_section(
            SECTION_INSTANA,
            'Configuration',
            function() {
                echo '<p>Configures the Instana End User Monitoring Beacon. Please refer to the <a href="https://docs.instana.io/products/website_monitoring" target="_blank" rel="noopener noreferrer">official documentation</a> for further details.</p>';
            },
            SECTION_MENU
        );
        add_settings_field(
            INSTANA_API_KEY,
            'Monitoring Key',
            function() {
                printf(
                    '<input type="text" name="%s" value="%s">',
                    INSTANA_API_KEY,
                    esc_attr(get_option(INSTANA_API_KEY, ''))
                );
                echo '<br><p class="description">Enter the Monitoring key you received from Instana';
            },
            SECTION_MENU,
            SECTION_INSTANA
        );
        add_settings_field(
            INSTANA_EUM_BASE_URL,
            'Base URL',
            function() {
                $baseUrl = get_option(INSTANA_EUM_BASE_URL, DEFAULT_EUM_BASE_URL);
                printf(
                    '<input type="text" name="%s" value="%s">',
                    INSTANA_EUM_BASE_URL,
                    esc_attr(empty($baseUrl) ? DEFAULT_EUM_BASE_URL : $baseUrl)
                );
                echo '<br><p class="description">Enter the Base URL to load script from and send beacons to';
            },
            SECTION_MENU,
            SECTION_INSTANA
        );
        add_settings_field(
            INSTANA_EUM_USE_DEBUG,
            "Use Debug Script",
            function() {
                $checked = boolval(get_option(INSTANA_EUM_USE_DEBUG, false));
                printf(
                    '<label><input type="checkbox" name="%s" %svalue="1"> Use the unminified debug script</label>',
                    INSTANA_EUM_USE_DEBUG,
                    $checked === true ? 'checked="checked"' : ''
                );
            },
            SECTION_MENU,
            SECTION_INSTANA
        );
        add_settings_field(
            INSTANA_ADVANCED_SETTINGS,
            'Advanced Settings',
            function() {
                printf(
                    '<textarea rows=5 cols=50 type="text" name="%s">%s</textarea>',
                    INSTANA_ADVANCED_SETTINGS,
                    esc_attr(get_option(INSTANA_ADVANCED_SETTINGS, ''))
                );
                echo '<br><p class="description">Any text in here is inserted verbatim into the beacon script block. You can use this to set additional EUM settings.<br><br>Example:</p><code>ineum("meta", "user", "stan@example.com");<br>ineum("ignoreUrls", [/.*\/api\/data.*/]);</code>';
            },
            SECTION_MENU,
            SECTION_INSTANA
        );
        add_settings_field(
            'preview',
            'Preview',
            function() {
                $beacon = nl2br(esc_html(get_eum_script()));
                if (empty($beacon)) {
                    $beacon = "No Monitoring key provided. Preview disabled.";
                }
                printf('<div style="whitespace: pre;">%s</div>', $beacon);
            },
            SECTION_MENU,
            SECTION_INSTANA
        );
    }
);

add_action('wp_head', function() { echo get_eum_script(); } );