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
            return NLS1_Photographer_Workspace::url('dashboard', ['account_id' => $account_id]);
        }

        if ($context === 'customer') {
            $client_id = absint($identity['subject_id'] ?? 0);
            $account_id = absint($identity['account_id'] ?? 0);
            $client = ($client_id && $account_id) ? NLS1_Fotoportal_Admin::get_public_client_by_id_account($client_id, $account_id) : null;
            if ($client && !empty($client->portal_token)) {
                return add_query_arg(['fotoportal_customer' => 1, 'token' => rawurlencode((string)$client->portal_token)], home_url('/'));
            }
        }

        return home_url('/');
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
