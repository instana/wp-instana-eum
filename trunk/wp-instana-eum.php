<?php
/*
Plugin Name: Instana EUM
Plugin URI:  https://github.com/instana/wp-instana-eum
Description: Instana End User Monitoring
Version:     0.1
Author:      Instana
Author URI:  http://instana.com
License:     MIT
License URI: https://opensource.org/licenses/MIT
*/
add_action('admin_init', function() {
    register_setting(
        'general',
        'instana_api_key',
        array(
            'type' => 'string',
            'description' => 'The API Key received from Instana',
            'sanitize_callback' => function($val) {
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
        'general',
        'instana_eum_base_url',
        array(
            'type' => 'string',
            'description' => 'Base URL to load script from and send beacons to',
            'sanitize_callback' => null,
            'show_in_rest' => false,
            'default' => '//eum.instana.io'
        )
    );

    register_setting(
        'general',
        'instana_eum_use_debug',
        array(
            'type' => 'boolean',
            'description' => 'Whether to use the unminified debug script',
            'sanitize_callback' => 'boolval',
            'show_in_rest' => false,
            'default' => false
        )
    );

    register_setting(
        'general',
        'instana_advanced_settings',
        array(
            'type' => 'string',
            'description' => 'Additional JavaScript code',
            'sanitize_callback' => null,
            'show_in_rest' => false,
            'default' => ''
        )
    );

    add_settings_section(
        'instana_settings_section',
        'Instana EUM',
        function() {
            echo '<p>Configures the Instana End User Monitoring Beacon. Please refer to the <a href="https://instana.atlassian.net/wiki/display/DOCS/Web+End-User+Monitoring" target="_blank" rel="noopener noreferrer">official documentation</a> for further details.</p>';
        },
        'general'
    );

    add_settings_field(
        'instana_api_key',
        'API Key',
        function() {
            printf(
                '<input type="text" name="instana_api_key" value="%s">',
                esc_attr(get_option('instana_api_key', ''))
            );
            echo '<br><p class="description">Enter the API key you received from Instana';
        },
        'general',
        'instana_settings_section'
    );

    add_settings_field(
        'instana_eum_base_url',
        'Base URL',
        function() {
            printf(
                '<input type="text" name="instana_eum_base_url" value="%s">',
                esc_attr(get_option('instana_eum_base_url', ''))
            );
            echo '<br><p class="description">Enter the Base URL to load script from and send beacons to';
        },
        'general',
        'instana_settings_section'
    );

    add_settings_field(
        "instana_eum_use_debug",
        "Use Debug Script",
        function() {
            $checked = get_option('instana_eum_use_debug', false);
            printf(
                '<input type="checkbox" name="instana_eum_use_debug" %svalue="1">',
                $checked === true ? 'checked="checked"' : ''
            );
            echo '<br><p class="description">Whether to use the unminified debug script';
        },
        "general",
        "instana_settings_section"
    );

    add_settings_field(
        'instana_advanced_settings',
        'Advanced Settings',
        function() {
            printf(
                '<textarea rows=5 cols=50 type="text" name="instana_advanced_settings">%s</textarea>',
                esc_attr(get_option('instana_advanced_settings', ''))
            );
    	    echo '<br><p class="description">Any text in here is inserted verbatim into the beacon script block. You can use this to set additional EUM settings.<br><br>Example:</p><code>ineum("meta", "user", "stan@example.com");<br>ineum("ignoreUrls", [/.*\/api\/data.*/]);</code>';
        },
        'general',
        'instana_settings_section'
    );

});

add_action(
    'wp_head',
    function() {
        if (!isset($_SERVER['X-INSTANA-T'])) {
            return;
        }

        $baseUrl = get_option('instana_eum_base_url', '//eum.instana.io');
        $scriptName = boolval(get_option('instana_eum_use_debug', false)) ? 'eum.debug.js' : 'eum.min.js';

        echo '<!-- Instana End User Monitoring Beacon -->', PHP_EOL;
        echo '<script>';
        printf("(function(i,s,o,g,r,a,m){i['InstanaEumObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','%s/%s','ineum');", $baseUrl, $scriptName);
        printf("ineum('apiKey', '%s');", get_option('instana_api_key', ''));
        printf("ineum('traceId', '%s');", $_SERVER['X-INSTANA-T']);

        if ($baseUrl !== '//eum.instana.io') {
            printf("ineum('reportingUrl', '%s');", $baseUrl);
        }

        echo get_option('instana_advanced_settings');
        echo '</script>';
    }
);
