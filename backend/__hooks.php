<?php

if (!defined('ABSPATH')) {
    die;
}

add_action('admin_menu', 'conexpro_metals_menu');
add_shortcode("conexpro-metal", "conexpro_metals_shortcode_func");

/**
 * @function
 * Display output of shortcode with provided attributes
 * 
 * @atts can be an array with following attributes
	    "type"      => "gold",
        "unit"      => "Ounce"
 */
function conexpro_metals_shortcode_func($atts = array())
{
    $attribs = shortcode_atts(array("type" => "", "unit" => "Please set unit type in shortcode. ie. unit='Lbs' "), $atts);

    //type is required
    if (trim($attribs["type"]) == "") {
        return wpautop("<b>Missing required attribute 'type' i.e: type='gold'</b>");
    }
    $url = "https://tradingeconomics.com/commodity/" . strtolower($attribs['type']);
    $metal_info = fetch_metal_info($url);

    if (isset($metal_info)) {
        $output  = get_option('conexpro_metal_success_display');

        if (isset($output) && trim($output) != "") {
            // replace [currency] placeholder
            $output  = str_replace("[currency]", 'USD', $output);
            // replace [currency_symbol] placeholder
            include_once(CNXSMETALSPATH . '/backend/currency_symbols.php');
            $output  = str_replace("[currency_symbol]", $currency_symbols['USD'], $output);
        }
        // replace [unit] placeholder
        if (isset($attribs["unit"])) {
            $output  = str_replace("[unit]", $attribs["unit"], $output);
        }
        // replace [price] placeholder
        if (isset($metal_info["value"])) {
            $output  = str_replace("[price]", $metal_info["value"], $output);
        }
        // replace [name] placeholder
        if (isset($metal_info["full_name"])) {
            $output  = str_replace("[name]", $metal_info["full_name"], $output);
        }
        // replace [ticker] placeholder
        if (isset($metal_info["ticker"])) {
            $output  = str_replace("[ticker]", $metal_info["ticker"], $output);
        }
        update_option('conexpro_metal_error_found', 'Working');
        update_option('conexpro_metal_last_ran', time());
    } else {
        $output  = get_option('conexpro_metal_error_display');
        update_option('conexpro_metal_error_found', 'stopped');
        wp_mail(
            get_option('admin_email'),
            "Heads up: Fething Metal Prices is not working",
            "System have found that url: $url did not return the expected result."
        );
    }
    
    return stripslashes($output);
}


function conexpro_metals_menu()
{
    add_menu_page('ConexPro Metals Settings', 'ConexPro Metals', 'administrator', 'conexpro-metal-main', 'conexpro_metals_admin_settings_page', 'dashicons-money-alt');
    add_submenu_page('conexpro-metal-main', 'ConexPro Metals Settings', 'Settings', 'administrator', 'conexpro-metal-settings', 'conexpro_metals_admin_settings_page');
    add_submenu_page('conexpro-metal-main', 'Learn Shortcode', 'Shortcode', 'administrator', 'conexpro-metal-info', 'conexpro_metals_admin_info_page');
    remove_submenu_page("conexpro-metal-main", "conexpro-metal-main");
}


function conexpro_metals_admin_main_page()
{
}

function conexpro_metals_admin_settings_page()
{

    if (isset($_POST['conexpro_metal_settings_update'])) {
        if (!isset($_POST['conexpro_metals_nonce']) || !wp_verify_nonce($_POST['conexpro_metals_nonce'])) {
            wp_die("Invalid Nonce. Reload the page and try again!");
        }

        if (isset($_POST['conexpro_metal_success_display'])) {
            /* allow HTML tags to be saved as a part of shortcode layout */
            $metal_success_display = wp_kses_post($_POST['conexpro_metal_success_display']);
            update_option('conexpro_metal_success_display', $metal_success_display);
        }

        if (isset($_POST['conexpro_metal_error_display'])) {
            /* allow HTML tags to be saved as a part of shortcode layout */
            $conexpro_metal_error_display = wp_kses_post($_POST['conexpro_metal_error_display']);
            update_option('conexpro_metal_error_display', $conexpro_metal_error_display);
        }
    }

    $last_ran_at = get_option("conexpro_metal_last_ran");
    $conexpro_status = get_option('conexpro_metal_error_found');

?><div class="wrap conexpro-metal">
        <h1>Conexus Solutions Metals Settings</h1>
        <hr>
        <form class="" method="post">
            <?php wp_nonce_field(-1, "conexpro_metals_nonce"); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>Conexus Metal Status</th>
                        <td>
                            <p class="description">Fetch status: <span><?= isset($conexpro_status) && trim($conexpro_status) != "" ? $conexpro_status : "Not connected!"; ?></span></p>
                            <p class="description">You can get the information about the metals you are quering by visiting <a href="https://tradingeconomics.com" target="_blank">Trading Economics</a></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h3>Shortcode results</h3>
            <p>Use below controls to print your desired content in the short code. API can return with success or no data (error). So please fill up your desired information for the two cases. You can also use below provided codes to display dynamic content.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><span class="success">Success</span></th>
                        <td>
                            <?php wp_editor(
                                stripslashes(get_option("conexpro_metal_success_display")),
                                "conexpro_metal_success_display",
                                array(
                                    "textarea_name" => "conexpro_metal_success_display",
                                    "textarea_rows" => 4
                                )
                            );
                            ?>
                            <p class="description">You can use [price], [currency], [name], [ticker], [unit], [currency_symbol] in above textarea.</br>
                                <b>* [unit] only outputs the input attribute <b>unit</b> of the shortcode and is not fetched from the API.</b>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th><span class="error">No Data</span></th>
                        <td>
                            <?php wp_editor(
                                stripslashes(get_option("conexpro_metal_error_display")),
                                "conexpro_metal_error_display",
                                array(
                                    "textarea_name" => "conexpro_metal_error_display",
                                    "textarea_rows" =>  4
                                )
                            );
                            ?>
                            <p class="description">Use this to display a message on the front end when no data can be fetched.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <button type="submit" class="button button-primary" name="conexpro_metal_settings_update">Submit</button>
            </p>
        </form>
    </div>
<?php
}


function conexpro_metals_admin_info_page()
{
?><style>
        .conexpro-metal.wrap ul>li {
            margin-left: 15px;
        }
    </style>
    <div class="wrap conexpro-metal">
        <h2>Short code details</h2>
        <div>
            <p>Use shortcode <code>[conexpro-metal]</code> to display metal rates on your wp website.</p>
            <p>Following are the params that you can pass to display your desired shortcode output!</p>
            <ul style="list-style: circle;">
                <li>
                    <strong>type</strong>
                    <p><b>(Required)</b> The metal type you want to display. Possible value can be: gold, silver, platinum, palladium, rhodium, ruthenium, copper.</p>
                    <p>Example: <code>[conexpro-metal type="gold"]</code></p>
                </li>
                <li>
                    <strong>unit</strong>
                    <p><b>(Optional)</b> If this is set the [unit] placeholder is replaced with it's value.</br>
                        <b>* This does not change the unit the metal info is fetched in. It only replaces placeholder in the output.</b>
                    </p>
                    <p>Example: <code>[conexpro-metal type="copper" unit="Lbs"]</code></p>
                </li>
            </ul>
        </div>
        <hr>
        <h3>Full shortcode example</h3>
        <code>[conexpro-metal type="copper" unit="Lbs"]</code>
    </div>

<?php
}
