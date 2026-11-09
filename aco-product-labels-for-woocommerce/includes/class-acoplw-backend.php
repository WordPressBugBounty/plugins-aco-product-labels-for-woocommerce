<?php

if (!defined('ABSPATH'))
    exit;

class ACOPLW_Backend
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    public $hook_suffix = array();
    public $plugin_slug;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        $this->_version = $version;
        $this->_token = ACOPLW_TOKEN;
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->plugin_slug = 'abc';

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';


        register_activation_hook($this->file, array($this, 'install'));
        // register_deactivation_hook($this->file, array($this, 'deactivation'));
        add_action('save_post', array($this, 'delete_transient'), 1);
        add_action('edited_term', array($this, 'delete_transient'));
        add_action('delete_term', array($this, 'delete_transient'));
        add_action('created_term', array($this, 'delete_transient'));

        add_action('admin_menu', array($this, 'register_root_page'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);

        $plugin = plugin_basename($this->file);
        add_filter("plugin_action_links_$plugin", array($this, 'add_settings_link'));

        // Deactivation feedback popup (Acowebs feedback API)
        add_action('admin_footer-plugins.php', array($this, 'acoplw_deactivation_modal'));
        add_action('wp_ajax_acoplw_deactivation_feedback', array($this, 'acoplw_deactivation_feedback'));

    }

    /**
     * Deactivation feedback popup (Acowebs feedback UI kit), printed on plugins.php only.
     * Replaces the old deactivation survey form.
     */
    public function acoplw_deactivation_modal() {
        if ( ! class_exists('Acowebs_Feedback_Client') || ! current_user_can('activate_plugins') ) {
            return;
        }

        $td      = 'aco-product-labels-for-woocommerce';
        $user    = wp_get_current_user();
        $reasons = array(
            array( 'value' => "I couldn't get it to work", 'label' => __("I couldn't get it to work", $td), 'placeholder' => __('What went wrong? Our support team can help.', $td) ),
            array( 'value' => 'I found a better plugin', 'label' => __('I found a better plugin', $td), 'placeholder' => __('Which plugin, and what does it do better?', $td) ),
            array( 'value' => "It's missing a feature I need", 'label' => __("It's missing a feature I need", $td), 'placeholder' => __('Which feature do you need?', $td) ),
            array( 'value' => 'It broke my site or conflicts with another plugin', 'label' => __('It broke my site or conflicts with another plugin', $td), 'placeholder' => __('What happened, and which plugin or theme conflicts?', $td) ),
            array( 'value' => 'I only needed it for a short time', 'label' => __('I only needed it for a short time', $td), 'placeholder' => __('Anything we could improve? (optional)', $td) ),
            array( 'value' => 'Other', 'label' => __('Other', $td), 'placeholder' => __('Please tell us more.', $td) ),
        );
        ?>
        <div class="acofb acofb-overlay" id="acoplw-deactivation" hidden>
            <div class="acofb-modal" role="dialog" aria-modal="true" aria-labelledby="acoplw-deactivation-title" aria-describedby="acoplw-deactivation-sub">
                <form class="acofb-modal-form">
                    <div class="acofb-modal-head">
                        <span class="acofb-badge" aria-hidden="true">
                            <svg viewBox="-8 -1.5 86 86" focusable="false"><path fill="#00DFB1" fill-rule="evenodd" d="M2.85642 22.0022C6.72119 18.0569 13.0525 17.9916 16.9978 21.8564L41.4978 45.8564C43.4052 47.7249 44.4861 50.2785 44.4999 52.9485C44.5136 55.6185 43.4591 58.1831 41.5711 60.0711L21.5711 80.0711C17.6658 83.9763 11.3342 83.9763 7.42894 80.0711C3.52369 76.1658 3.52369 69.8342 7.42894 65.9289L20.2846 53.0733L3.0022 36.1436C-0.943088 32.2788 -1.00836 25.9475 2.85642 22.0022Z"></path><path fill="#00B2FF" fill-rule="evenodd" d="M60.1791 3.124C64.5289 7.36505 64.6171 14.3293 60.376 18.6791L48.4585 30.9022L65.7782 48.2218C70.0739 52.5176 70.0739 59.4824 65.7782 63.7782C61.4824 68.0739 54.5176 68.0739 50.2218 63.7782L25.2218 38.7782C20.9645 34.5208 20.9209 27.6318 25.124 23.3209L44.624 3.3209C48.8651 -1.02889 55.8293 -1.11705 60.1791 3.124Z"></path><path fill="#0A84FF" fill-rule="evenodd" d="M43.5968 57.1543L25.2218 38.7786C22.1371 35.6937 21.2645 31.2271 22.6168 27.3607L41.4978 45.8572C43.4052 47.7257 44.4861 50.2794 44.4999 52.9495C44.5074 54.4143 44.1935 55.8473 43.5968 57.1543Z"></path></svg>
                        </span>
                        <div>
                            <h2 class="acofb-title" id="acoplw-deactivation-title"><?php echo esc_html( sprintf( __('Quick question before you deactivate %s', $td), ACOPLW_PLUGIN_NAME ) ); ?></h2>
                            <p class="acofb-sub" id="acoplw-deactivation-sub"><?php esc_html_e('If you have a moment, tell us why. The Acowebs team reads every answer and uses it to improve the plugin.', $td); ?></p>
                        </div>
                    </div>

                    <div class="acofb-modal-body">
                        <fieldset class="acofb-reasons">
                            <legend class="acofb-sr"><?php esc_html_e('Why are you deactivating?', $td); ?></legend>
                            <?php foreach ( $reasons as $i => $reason ) : ?>
                                <div class="acofb-reason">
                                    <input class="acofb-radio" type="radio" id="acoplw-reason-<?php echo (int) $i; ?>" name="reason" value="<?php echo esc_attr( $reason['value'] ); ?>" required>
                                    <label class="acofb-reason-card" for="acoplw-reason-<?php echo (int) $i; ?>"><?php echo esc_html( $reason['label'] ); ?></label>
                                    <div class="acofb-details">
                                        <label class="acofb-sr" for="acoplw-reason-details-<?php echo (int) $i; ?>"><?php esc_html_e('Details', $td); ?></label>
                                        <textarea class="acofb-textarea" id="acoplw-reason-details-<?php echo (int) $i; ?>" name="reason_details" rows="3" maxlength="5000" placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>"></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </fieldset>

                        <div class="acofb-contact">
                            <input class="acofb-check" type="checkbox" id="acoplw-deactivation-consent" name="consent" value="1" checked>
                            <label class="acofb-check-label" for="acoplw-deactivation-consent"><?php esc_html_e('Acowebs can contact me about this feedback', $td); ?></label>
                            <div class="acofb-contact-fields">
                                <div class="acofb-field">
                                    <label class="acofb-label" for="acoplw-deactivation-name"><?php esc_html_e('Name', $td); ?></label>
                                    <input class="acofb-input" type="text" id="acoplw-deactivation-name" name="name" value="<?php echo esc_attr( $user->display_name ); ?>" maxlength="255" autocomplete="name">
                                </div>
                                <div class="acofb-field">
                                    <label class="acofb-label" for="acoplw-deactivation-email"><?php esc_html_e('Email', $td); ?></label>
                                    <input class="acofb-input" type="email" id="acoplw-deactivation-email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" maxlength="320" autocomplete="email">
                                </div>
                            </div>
                        </div>

                        <p class="acofb-privacy">
                            <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8 1a3.5 3.5 0 0 0-3.5 3.5V6H4a1.5 1.5 0 0 0-1.5 1.5v6A1.5 1.5 0 0 0 4 15h8a1.5 1.5 0 0 0 1.5-1.5v-6A1.5 1.5 0 0 0 12 6h-.5V4.5A3.5 3.5 0 0 0 8 1Zm2 5V4.5a2 2 0 1 0-4 0V6h4Z"></path></svg>
                            <span><?php esc_html_e('We only send this if you click Send: your answer, your site address, name and language, the plugin version and your WordPress, WooCommerce and PHP versions. Your name and email are included only while the box above is ticked.', $td); ?> <a class="acofb-link" href="<?php echo esc_url('https://acowebs.com/privacy-policy/'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Privacy policy', $td); ?><span class="acofb-sr"> <?php esc_html_e('(opens in a new tab)', $td); ?></span></a></span>
                        </p>
                    </div>

                    <div class="acofb-modal-foot">
                        <button type="submit" class="acofb-btn acofb-btn--primary acofb-btn--send" data-acofb-action="send"><?php esc_html_e('Send & deactivate', $td); ?></button>
                        <button type="button" class="acofb-btn acofb-btn--text" data-acofb-action="skip"><?php esc_html_e('Skip & deactivate', $td); ?></button>
                        <button type="button" class="acofb-btn acofb-btn--text acofb-btn--cancel" data-acofb-action="cancel"><?php esc_html_e('Cancel', $td); ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: send the deactivation feedback to the Acowebs feedback API (non-blocking).
     */
    public function acoplw_deactivation_feedback() {
        check_ajax_referer('acoplw_deactivation_feedback', 'nonce');

        if ( ! current_user_can('activate_plugins') ) {
            wp_send_json_error(null, 403);
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
        $consent = isset($_POST['consent']) ? rest_sanitize_boolean(wp_unslash($_POST['consent'])) : false;

        $args = array(
            'plugin'         => ACOPLW_FEEDBACK_PLUGIN,
            'edition'        => ACOPLW_EDITION,
            'plugin_slug'    => dirname(plugin_basename($this->file)),
            'plugin_version' => ACOPLW_VERSION,
            'reason'         => isset($_POST['reason']) ? sanitize_text_field(wp_unslash($_POST['reason'])) : '',
            'reason_details' => isset($_POST['reason_details']) ? sanitize_textarea_field(wp_unslash($_POST['reason_details'])) : '',
            'consent'        => $consent,
        );

        // Name and email only while "Acowebs can contact me" is ticked.
        if ( $consent ) {
            $args['name']  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
            $args['email'] = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        }
        // phpcs:enable

        if ( class_exists('Acowebs_Feedback_Client') ) {
            Acowebs_Feedback_Client::send_deactivation($args);
        }

        wp_send_json_success();
    }

    

    /**
     *
     *
     * Ensures only one instance of WCPA is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WCPA instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    public function register_root_page()
    {
        $this->hook_suffix[] = add_menu_page(
            __('Badges', 'aco-product-labels-for-woocommerce'), __('Badges', 'aco-product-labels-for-woocommerce'), 'edit_products', 'acoplw_badges_ui', array($this, 'admin_ui'), esc_url($this->assets_url) . '/images/icon.png', 25);
        $this->hook_suffix[] = add_submenu_page(
            'acoplw_badges_ui', __('Product Lists', 'aco-product-labels-for-woocommerce'), __('Product Lists', 'aco-product-labels-for-woocommerce'), 'edit_products', 'acoplw_product_lists_ui', array($this, 'admin_ui_pro_lists'));
        $this->hook_suffix[] = add_submenu_page(
            'acoplw_badges_ui', __('Settings', 'aco-product-labels-for-woocommerce'), __('Settings', 'aco-product-labels-for-woocommerce'), 'edit_products', 'acoplw_settings_ui', array($this, 'admin_ui_settings'));
    }

    public function admin_ui()
    {
        ACOPLW_Backend::view('admin-root', []);
    }

    public function add_settings_link($links)
    {
        $settings = '<a href="' . admin_url('admin.php?page=acoplw_badges_ui#/') . '">' . __('Badges','aco-product-labels-for-woocommerce') . '</a>';
        $products = '<a href="' . admin_url('admin.php?page=acoplw_product_lists_ui#/') . '">' . __('Product Lists','aco-product-labels-for-woocommerce') . '</a>';
        $upgrade = '<a href="https://acowebs.com/woocommerce-product-labels/" target="_blank" style="font-weight:600;color:#6D71F9;">' . __('Upgrade to PRO','aco-product-labels-for-woocommerce') . '</a>';
        array_push($links, $settings);
        array_push($links, $products);
        array_push($links, $upgrade);
        return $links;
    }

    /**
     *    Create post type forms
     */

     static function view($view, $data = array())
    {
        extract($data);
        include(plugin_dir_path(__FILE__) . 'views/' . $view . '.php');
    }

    // End admin_enqueue_styles ()

    public function admin_ui_pro_lists()
    {
        ACOPLW_Backend::view('admin-lists', []);
    }

    public function admin_ui_settings()
    {
        ACOPLW_Backend::view('admin-settings', []);
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {
                
        $currentScreen = get_current_screen();
        $screenID = $currentScreen->id; //
        if (strpos($screenID, 'acoplw_') !== false) {

            wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-admin');
            
        }
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {

        $currentScreen  = get_current_screen();
        $screenID       = $currentScreen->id;
        $defaultLang    = ''; 
        $currentLang    = ''; 
        $pricingrules   = [];

        if (strpos($screenID, 'acoplw_') !== false) {

            if (!isset($this->hook_suffix) || empty($this->hook_suffix)) {
                return;
            }

            // All Categories
            $categories = get_terms ( 'product_cat', ['taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'id=>name'] );

            // Product List
            if ( get_option ('acoplw_dp_list_status') && get_option ('acoplw_dp_list_status') == 1 && function_exists('AWDP') ) {
                $acoplwList = get_posts ( array ( 'fields' => 'ids', 'numberposts' => -1, 'post_type' => array ( ACOPLW_PRODUCT_LIST, ACOPLW_DP_PRODUCT_LIST ), 'orderby' => 'title', 'order' => 'ASC' ) );
            } else {
                $acoplwList = get_posts ( array ( 'fields' => 'ids', 'numberposts' => -1, 'post_type' => ACOPLW_PRODUCT_LIST, 'orderby' => 'title', 'order' => 'ASC') );
            }

            $acoplwList     = array_map ( function ($v) {
                                return ['id' => $v, 'name' => get_the_title($v)];
                            }, $acoplwList ); 

            $previewImage   = plugin_dir_url(__FILE__). '../assets/images/preview-product.jpg';
            $taglist        = get_terms(array('hide_empty' => false, 'taxonomy' => 'product_tag'));
            $screen         = get_current_screen();

            // Language Parameters
            $defaultLang    = call_user_func ( array ( new ACOPLW_ML(), 'default_language' ), '' );
            $currentLang    = call_user_func ( array ( new ACOPLW_ML(), 'current_language' ), '' );

            $acoplwBaseurl  = ACOPLW_URL;

            $permaStruc     = get_option( 'permalink_structure' );

            // Dynamic Pricing Active Rules
            if ( function_exists('AWDP') ) {
                $pricingrules = get_posts ( array ( 'fields' => 'ids', 'numberposts' => -1, 'post_type' => array ( AWDP_POST_TYPE ), 'orderby' => 'title', 'order' => 'ASC', 'meta_query' => array ( 'relation' => 'AND', array ( 'key' => 'discount_status', 'value' => 1, 'compare' => '=', 'type' => 'NUMERIC' ) ) ) );
            } 
            $pricingrules   = !empty ( $pricingrules ) ? array_map ( function ($v) {
                return ['id' => $v, 'name' => get_the_title($v)];
            }, $pricingrules ) : ''; 

            wp_enqueue_script('jquery');

            if ( in_array ( $screen->id, $this->hook_suffix ) ) {

                if ( !wp_script_is ( 'wp-i18n', 'registered' ) ) {
                    wp_register_script ( 'wp-i18n', esc_url($this->assets_url) . 'js/i18n.min.js', array('jquery'), $this->_version, true );
                }

                wp_enqueue_script ( $this->_token . '-backend-script', esc_url($this->assets_url) . 'js/backend.js', array('jquery', 'wp-i18n'), $this->_version, true );
                wp_localize_script ( $this->_token . '-backend-script', 'acoplw_object', array(
                        'api_nonce'     => wp_create_nonce('wp_rest'),
                        'root'          => rest_url('acoplw/v1/'),
                        'cats'          => (array)$categories,
                        'tags'          => (array)$taglist,
                        'productlist'   => (array)$acoplwList,
                        'previewImage'  => $previewImage,
                        'defaultLang'   => $defaultLang,
                        'currentLang'   => $currentLang,
                        'acoplwBaseurl' => $acoplwBaseurl,
                        'pricingrules'  => $pricingrules,
                        'permalink'     => $permaStruc
                    )
                );

                $plugin_rel_path = (dirname($this->file)) . '\languages'; /* Relative to WP_PLUGIN_DIR */
                if ( ACOPLW_Wordpress_Version >= 5 ) {
                    wp_set_script_translations ( ACOPLW_TOKEN . '-backend-script', 'aco-product-labels-for-woocommerce', $plugin_rel_path );
                }

            }

        }

        // Deactivation feedback popup (Acowebs feedback UI kit)
        if ( $screenID == 'plugins' && class_exists('Acowebs_Feedback_Client') && current_user_can('activate_plugins') ) {
            wp_enqueue_style('acowebs-feedback-ui', esc_url($this->assets_url).'acowebs-ui-kit/acowebs-feedback-ui.css', array(), $this->_version);
            wp_enqueue_script('acoplw-deactivation-message', esc_url($this->assets_url).'js/message.js', array(), $this->_version, true);
            wp_localize_script('acoplw-deactivation-message', 'acoplwDeactivation', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('acoplw_deactivation_feedback'),
                'slug'    => ACOPLW_SLUG,
                'plugin'  => plugin_basename($this->file), // Row fallback when data-slug differs (no wordpress.org data).
            ));
        }

    }


    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function install()
    {
        $this->_log_version_number();

    }

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    }

    public function delete_transient($arg = false)
    {
        if ($arg) {
            in_array(get_post_type($arg), ['product', ACOPLW_POST_TYPE, ACOPLW_PRODUCT_LIST]) && delete_transient(ACOPLW_PRODUCTS_TRANSIENT_KEY);
        } else {
            delete_transient(ACOPLW_PRODUCTS_TRANSIENT_KEY);
        }

    }

}
