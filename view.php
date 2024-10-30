<?php
if (!defined('ABSPATH')) exit;

/********************************
Form submit
 ********************************/
if (isset($_POST['api_key'])){
    check_admin_referer('verifypass_update_nonce');
    $api_key = preg_replace('~[^a-zA-Z0-9]+~', '', $_POST['api_key']);
    update_option("verifypass_api_key", $api_key);
    $is_saved = true;
}
?>

<!--Main Display-->
<div class="wrap">

    <!--Tabs-->
    <h2 class="nav-tab-wrapper">
        <a href="?page=verifypass.php&tab=admin" class="nav-tab <?php echo !isset($_GET['tab']) || $_GET['tab'] == 'admin' ? 'nav-tab-active' : ''; ?>">Connect</a>
        <a href="?page=verifypass.php&tab=help" class="nav-tab <?php echo $_GET['tab'] == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
    </h2>

    <!--Saved Message-->
    <?=(isset($is_saved) && $is_saved ? '<div id="message" class="updated fade" style="margin-top:20px;"><p><b>Your settings have been saved.</b></p></div>' : '')?>

    <!--Tab: Connect -->
    <?php
    if (!isset($_GET['tab']) || $_GET['tab'] == 'admin'){
        ?>
        <h3>Step 1: Add your API Key</h3>
        <!-- API Key -->
        <table class="form-table">
            <tr>
                <th scope="row"><img src="<?=$this->logo_path();?>" style="float:left;width:19px;height:19px;" />&nbsp;&nbsp;API Key</th>
                <td>
                    <form method="post" action="admin.php?page=verifypass.php&tab=admin">
                        <?=wp_nonce_field('verifypass_update_nonce');?>
                        <input size="40" type="password" name="api_key" value="<?= get_option("verifypass_api_key") ? get_option("verifypass_api_key") : '' ?>" />
                        <?php
                        if (!get_option("verifypass_api_key")){
                            ?>
                            <ol>
                                <li>Create or login to your VerifyPass <a href="https://verifypass.com/account" target="_blank">Account</a>.</li>
                                <li>Visit Settings &rarr; <a href="https://verifypass.com/account/settings/api" target="_blank">API Keys</a> and copy your API Key above.</li>
                            </ol>
                            <?php
                        }
                        submit_button();
                        ?>
                    </form>
                </td>
            </tr>

        </table>

        <hr>

        <h3>
            <?php
            if (get_option("verifypass_api_key")) {
                ?>
                Step 2: Connect your VerifyPass Account
                <?php
            }
            else{
                ?>
                <strike>Step 2: Connect your VerifyPass Account</strike>
                <?php
            }
            ?>
        </h3>
        <ol>
            <li>After completing Step 1, visit VerifyPass &rarr; Account &rarr; Integrations &rarr; <a href="https://verifypass.com/account/connections/woocommerce">WooCommerce</a>.</li>
            <li>Copy the following URL: <b><?= get_site_url() ?></b></li>
            <li>Click <b>Connect</b>.</li>
        </ol>

        <hr>

        <h3>
            <?php
            if (get_option("verifypass_api_key")) {
                ?>
                Step 3: Complete setup
                <?php
            }
            else{
                ?>
                <strike>Step 3: Complete setup</strike>
                <?php
            }
            ?>
        </h3>
        <p>
            All configuration occurs from your VerifyPass account.
        </p>
        <ol>
            <li>Create a Widget and determine eligibility within <b>Widget</b> &rarr; <b>Pre-Verification</b>.</li>
            <li>Visit <b>Widget</b> &rarr; <b>Post-Verification</b> and select <b>Discount Code: Unique (WooCommerce)</b> to specify discount settings.</li>
            <li>Visit <b>Widget</b> &rarr; <b>Installation</b> to create a page to host your discounts.</li>
        </ol>

        <hr/>

        <p>
            <b>Important</b>: Be sure to leave this plugin installed while you are using VerifyPass. If the plugin is removed
            or deactivated, requests to this store (such as generating a discount code) will fail.
        </p>
        <?php
    }
    ?>

    <!--Tab: Help -->
    <?php
    if (isset($_GET['tab']) && $_GET['tab'] == 'help'){
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><img src="<?=$this->logo_path();?>" style="float:left;width:19px;height:19px;" />&nbsp;&nbsp;Support</th>
                <td>
                    partners@verifypass.com
                </td>
            </tr>
            <tr>
                <th scope="row"><img src="<?=$this->logo_path();?>" style="float:left;width:19px;height:19px;" />&nbsp;&nbsp;Account</th>
                <td>
                    <a href="https://verifypass.com/account" target="_blank">https://verifypass.com/account</a>
                </td>
            </tr>
        </table>
        <?php
    }
    ?>

</div>
