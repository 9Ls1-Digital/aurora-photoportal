<?php
if (!defined('ABSPATH')) exit;

/**
 * Aurora Auth adapter for Fotoportal.
 *
 * Takeover checkpoint 1 registers Fotoportal with the shared Auth Capsule and
 * explicitly transfers the two public login entry routes to Aurora Auth.
 * Fotoportal retains its legacy route handlers as a runtime fallback whenever
 * Aurora Auth is unavailable or deactivated.
 */
final class NLS1_Aurora_Fotoportal_Auth_Adapter {
    const APP_ID = 'fotoportal';

    public function __construct() {
        add_action('aurora_auth_register_apps', [$this, 'register_app'], 10, 1);
    }

    public function register_app($registry = null) {
        if (!function_exists('aurora_auth_register_app')) return;

        aurora_auth_register_app(self::APP_ID, [
            'name' => 'Aurora Fotoportal',
            'contexts' => ['photographer', 'customer'],
            'routes' => [
                'photographer' => '/fotograf/',
                'customer' => '/fotograf/kunde/',
            ],
            // Frontend workspace is opt-in. Customer delivery remains on its
            // existing portal route in this checkpoint.
            'workspace_routes' => [
                'photographer' => '/fotograf/portal/',
                'customer' => '/fotograf/kunde/portal/',
            ],
            'workspace_renderer' => [$this, 'render_workspace'],
            // Aurora Auth v0.1.8-dev.3 session guard. Normal sessions expire after
            // 8 hours of inactivity; "Husk meg" sessions after 14 days.
            'session_timeout' => 8 * HOUR_IN_SECONDS,
            'remember_session_timeout' => 14 * DAY_IN_SECONDS,
            'redirect_wrong_context' => true,
            // Controlled takeover: Auth owns the registered public login entry routes.
            // If Aurora Auth is not active, Fotoportal's legacy handlers remain intact.
            'takeover_routes' => true,
            'identity_resolver' => [$this, 'resolve_identity'],
            'authorization_callback' => [$this, 'authorize'],
            'post_login_redirect' => [$this, 'post_login_redirect'],
            'logout_redirect' => [$this, 'logout_redirect'],
            'branding_callback' => [$this, 'branding'],
            'source' => 'aurora-fotoportal',
        ]);
    }

    public function resolve_identity($user, $context, $request = []) {
        if (!($user instanceof WP_User)) {
            return new WP_Error('aurora_fotoportal_identity', 'Fant ikke brukeridentiteten.');
        }

        $account_id = (int)get_user_meta($user->ID, 'aurora_fotoportal_account_id', true);
        if ($context === 'photographer') {
            $requested_account = absint($request['account_id'] ?? 0);
            if ($requested_account && $account_id !== $requested_account) {
                return new WP_Error('aurora_fotoportal_account_mismatch', 'Denne innloggingen har ikke tilgang til denne fotografkontoen.');
            }
            return [
                'user_id' => (int)$user->ID,
                'account_id' => $account_id,
                'subject_id' => $account_id,
            ];
        }

        if ($context === 'customer') {
            $client_id = (int)get_user_meta($user->ID, 'aurora_fotoportal_client_id', true);
            if (!$client_id || !$account_id) {
                return new WP_Error('aurora_fotoportal_customer_mapping', 'Denne innloggingen er ikke koblet til en Aurora Fotoportal-kunde.');
            }
            $client = NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id, $account_id);
            if (!$client || !NLS1_Fotoportal_Admin::repair_client_user_authorization($client, (int)$user->ID)) {
                return new WP_Error('aurora_fotoportal_customer_forbidden', 'Denne innloggingen er ikke koblet til en Aurora Fotoportal-kunde.');
            }
            return [
                'user_id' => (int)$user->ID,
                'account_id' => $account_id,
                'subject_id' => $client_id,
            ];
        }

        return new WP_Error('aurora_fotoportal_context', 'Ukjent Fotoportal-kontekst.');
    }

    public function authorize($user, $context, $identity, $request = []) {
        if (!($user instanceof WP_User)) return false;

        if ($context === 'photographer') {
            $is_photographer = in_array('aurora_photographer', (array)$user->roles, true)
                || $user->has_cap('aurora_fotoportal_photographer')
                || get_user_meta($user->ID, 'aurora_fotoportal_role', true) === 'photographer_owner';
            if (!$is_photographer || empty($identity['account_id'])) return false;
            return (bool)NLS1_Aurora_Account_Platform::get_account((int)$identity['account_id']);
        }

        if ($context === 'customer') {
            if (empty($identity['account_id']) || empty($identity['subject_id'])) return false;
            $client = NLS1_Fotoportal_Admin::get_public_client_by_id_account((int)$identity['subject_id'], (int)$identity['account_id']);
            return $client ? NLS1_Fotoportal_Admin::repair_client_user_authorization($client, (int)$user->ID) : false;
        }

        return false;
    }

    public function post_login_redirect($user, $context, $identity) {
        if ($context === 'photographer') {
            $account_id = absint($identity['account_id'] ?? 0);
            if ($account_id) NLS1_Aurora_Account_Platform::mark_account_active($account_id);
            if (function_exists('aurora_auth_workspace_url')) {
                return aurora_auth_workspace_url(self::APP_ID, 'photographer');
            }
            // Hard fallback preserves the proven wp-admin workspace when Auth
            // is older/unavailable; login itself is deliberately untouched.
            return NLS1_Photographer_Workspace::admin_url('dashboard', ['account_id' => $account_id]);
        }

        if ($context === 'customer') {
            $client_id = absint($identity['subject_id'] ?? 0);
            $account_id = absint($identity['account_id'] ?? 0);
            $client = ($client_id && $account_id) ? NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id, $account_id) : null;
            if ($client && function_exists('aurora_auth_workspace_url')) {
                return aurora_auth_workspace_url(self::APP_ID, 'customer');
            }
            // Compatibility fallback for older Aurora Auth installations.
            if ($client && !empty($client->portal_token)) {
                return add_query_arg(['fotoportal_customer' => 1, 'token' => rawurlencode((string)$client->portal_token)], home_url('/'));
            }
        }

        return home_url('/');
    }


    /**
     * Render the photographer workspace on Aurora Auth's authenticated
     * frontend route. Auth has already resolved identity and authorization.
     */
    public function render_workspace($user, $context, $identity, $match = []) {
        if ($context === 'photographer') {
            $workspace = new NLS1_Photographer_Workspace(false);
            $workspace->render_frontend($identity);
            return;
        }

        if ($context === 'customer') {
            $client_id = absint($identity['subject_id'] ?? 0);
            $account_id = absint($identity['account_id'] ?? 0);
            $client = ($client_id && $account_id)
                ? NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id, $account_id)
                : null;

            if (!$client || !NLS1_Fotoportal_Admin::client_user_authorized($client)) {
                wp_safe_redirect(home_url('/fotograf/kunde/'));
                exit;
            }

            // Reuse the proven customer portal renderer. The token is supplied
            // internally for backwards compatibility and is never exposed in
            // the authenticated workspace URL.
            $frontend = new NLS1_Fotoportal_Frontend(false);
            $frontend->render_authenticated_customer_portal($client);
            return;
        }

        wp_safe_redirect(home_url('/'));
        exit;
    }

    public function logout_redirect($context) {
        if ($context === 'customer') return home_url('/fotograf/kunde/');
        if ($context === 'photographer') return home_url('/fotograf/');
        return home_url('/');
    }

    public function branding($context) {
        $platform = NLS1_Aurora_Account_Platform::platform_branding();
        $accent = sanitize_hex_color($platform['accent'] ?? ($platform['accent_color'] ?? '')) ?: '#6f4bf2';

        // Read the persisted platform options directly as the authoritative login
        // background source. This keeps the public Auth shell in sync with the
        // exact images already used by Fotoportal's legacy login screens.
        $default_bg = NLS1_FOTOPORTAL_PLUGIN_URL . 'assets/aurora-login-background.png';
        $photographer_desktop = esc_url_raw((string)get_option('9ls1_aurora_photographer_login_bg_desktop', ''));
        if (!$photographer_desktop) $photographer_desktop = !empty($platform['photographer_login_bg_desktop']) ? esc_url_raw($platform['photographer_login_bg_desktop']) : $default_bg;
        $photographer_mobile = esc_url_raw((string)get_option('9ls1_aurora_photographer_login_bg_mobile', ''));
        if (!$photographer_mobile) $photographer_mobile = !empty($platform['photographer_login_bg_mobile']) ? esc_url_raw($platform['photographer_login_bg_mobile']) : $photographer_desktop;

        if ($context === 'customer') {
            $customer_desktop = esc_url_raw((string)get_option('9ls1_aurora_customer_login_bg_desktop', ''));
            if (!$customer_desktop) $customer_desktop = !empty($platform['customer_login_bg_desktop']) ? esc_url_raw($platform['customer_login_bg_desktop']) : $photographer_desktop;
            $customer_mobile = esc_url_raw((string)get_option('9ls1_aurora_customer_login_bg_mobile', ''));
            if (!$customer_mobile) $customer_mobile = !empty($platform['customer_login_bg_mobile']) ? esc_url_raw($platform['customer_login_bg_mobile']) : $customer_desktop;
            $background_desktop = $customer_desktop;
            $background_mobile = $customer_mobile;
            $overlay = 'linear-gradient(180deg,rgba(25,20,28,.12),rgba(25,20,28,.34))';
        } else {
            $background_desktop = $photographer_desktop;
            $background_mobile = $photographer_mobile;
            $overlay = 'linear-gradient(180deg,rgba(18,12,22,.18),rgba(18,12,22,.42))';
        }

        return [
            'title' => 'AURORA',
            'subtitle' => 'Intelligent Business Platform',
            'product' => 'Aurora Fotoportal',
            'accent' => $accent,
            'logo_url' => !empty($platform['logo_url']) ? $platform['logo_url'] : '',
            'background_desktop' => $background_desktop,
            'background_mobile' => $background_mobile,
            'background_position' => 'center center',
            'background_overlay' => $overlay,
            'card_mode' => 'glass',
        ];
    }
}
