<?php
/**
 * SNC Cookies
 * @package     Joomla.Module
 * @subpackage  mod_snc_cookies
 * @author      Steven Naschold Computerservice
 * @license     GNU/GPLv3
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class ModSncCookiesHelper
{
    /**
     * Einstiegspunkt: Baut alle Einstellungen + Consent-Status.
     */
    public static function getSettings(object $params): array
    {
        $language = Factory::getLanguage();
        $langTag  = $language->getTag();
        $langKey  = strtoupper(substr($langTag, 0, 2) ?: 'DE');

        $settings           = new \stdClass();
        $settings->basics   = self::getBasicSettings($params, $langKey);
        $settings->captions = self::getCaptions($params);
        $settings->css      = self::getCss($params);
        $settings->buttons  = self::getButtons($params);
        $settings->links    = self::getLinks($params);
        $settings->cookies  = self::getCategories($params, $langKey);

        // Consent verarbeiten (POST + Cookie lesen + JSON bauen)
        $consent = self::handleConsent($settings->basics, $settings->cookies);

        return [$settings, $consent];
    }

    /**
     * Consent-Handling: POST auswerten, JSON-Cookie setzen, Consent-Objekt zurückgeben.
     */
    public static function handleConsent(object $basics, array &$cookies): ?\stdClass
    {
        $app    = Factory::getApplication();
        $input  = $app->input;
        $action = $input->post->getString('snc_action', null);

        $cookieName = $basics->cookie_ref;
        $lifetime   = (int) $basics->lifetime;

        // Bestehenden Consent aus Cookie lesen
        $existing = null;
        if (!empty($_COOKIE[$cookieName])) {
            $decoded = json_decode($_COOKIE[$cookieName], true);
            if (is_array($decoded) && !empty($decoded['categories'])) {
                $existing = $decoded;
            }
        }

        // Wenn keine Aktion → nur bestehenden Consent zurückgeben
        if ($action === null) {
            if ($existing) {
                self::applyConsentToCookies($cookies, $existing['categories']);
                return (object) $existing;
            }
            return null;
        }

        // Kategorien aus POST
        $postedCategories = $input->post->get('snc_categories', [], 'array');

        // Neue Consent-Matrix aufbauen
        $categoriesConsent = [];

        foreach ($cookies as $cookie) {
            $name       = $cookie->name;
            $isRequired = !empty($cookie->required);
            $readonly   = !empty($cookie->readonly);

            switch ($action) {
                case 'accept':
                    // Nur notwendige + Default-Einstellungen
                    $agree = $isRequired ? 1 : (!empty($cookie->default) ? 1 : 0);
                    break;

                case 'accept-all':
                    $agree = 1;
                    break;

                case 'deny-all':
                    $agree = $isRequired ? 1 : 0;
                    break;

                case 'save':
                    $agree = isset($postedCategories[$name]) ? 1 : 0;
                    break;

                default:
                    $agree = $isRequired ? 1 : 0;
                    break;
            }

            if ($readonly && !$isRequired) {
                // Readonly-Kategorien nicht durch Aktion überschreiben
                $agree = !empty($cookie->default) ? 1 : 0;
            }

            $categoriesConsent[$name] = (int) $agree;
            $cookie->checked          = (int) $agree;
        }

        $consent = [
            'time'       => time(),
            'categories' => $categoriesConsent,
        ];

        // JSON-Cookie setzen
        setcookie(
            $cookieName,
            json_encode($consent),
            [
                'expires'  => time() + ($lifetime * 86400),
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => false,
                'samesite' => 'Lax',
            ]
        );

        return (object) $consent;
    }

    /**
     * Consent auf Kategorien anwenden (für Anzeige).
     */
    private static function applyConsentToCookies(array &$cookies, array $categories): void
    {
        foreach ($cookies as $cookie) {
            $name = $cookie->name;
            if (isset($categories[$name])) {
                $cookie->checked = (int) $categories[$name];
            }
        }
    }

    /**
     * Basis-Einstellungen: Position, Opazität, Schrift, Cookie-Name, Laufzeit.
     */
    private static function getBasicSettings(object $params, string $langKey): \stdClass
    {
        $settings = new \stdClass();

        // Cookie-Name & Laufzeit (in Tagen)
        $settings->cookie_ref = $params->get('cookie_ref', 'snc_consent');
        $settings->lifetime   = (int) $params->get('cookie_lifetime', 12);

        // Sprache (nur als Info)
        $settings->language_code = $langKey;

        // Position des Banners (top/bottom)
        $settings->position = $params->get('cookie_position', 'bottom');

        // Overlay / Opazität (0–100)
        $settings->opacity = (int) $params->get('cookie_opacity', 85);

        // Icon-Position (z. B. left/right)
        $settings->icon_position = $params->get('icon_position', 'right');

        return $settings;
    }

    /**
     * Captions (Überschriften) für Übersicht & Details.
     * Texte kommen aus Sprachdateien, hier nur Steuerung & Styling.
     */
    private static function getCaptions(object $params): array
    {
        $captions = [
            'overview' => null,
            'details'  => null,
        ];

        // Übersicht
        $overview              = new \stdClass();
        $overview->show        = (int) $params->get('caption_overview_show', 1);
        $overview->htmltag     = $params->get('caption_overview_htmltag', 'h2');
        $overview->font_size   = (int) $params->get('caption_overview_font_size', 18);
        $overview->font_family = $params->get('caption_overview_font_family', 'inherit');
        $overview->font_color  = $params->get('caption_overview_font_color', '#ffffff');
        $captions['overview']  = $overview;

        // Details
        $details              = new \stdClass();
        $details->show        = (int) $params->get('caption_details_show', 1);
        $details->htmltag     = $params->get('caption_details_htmltag', 'h3');
        $details->font_size   = (int) $params->get('caption_details_font_size', 16);
        $details->font_family = $params->get('caption_details_font_family', 'inherit');
        $details->font_color  = $params->get('caption_details_font_color', '#ffffff');
        $captions['details']  = $details;

        return $captions;
    }

    /**
     * CSS-Klassen & Styles für Banner, Buttons, Captions, Icon.
     */
    private static function getCss(object $params): array
    {
        $css     = ['classes' => null, 'styles' => null];
        $classes = new \stdClass();
        $styles  = new \stdClass();

        // Klassen
        $classes->position = $params->get('cookie_position', 'bottom');
        $classes->module   = 'snc-cookies-module';
        $css['classes']    = $classes;

        // Banner-Styles
        $styles->opacity     = (int) $params->get('cookie_opacity', 85);
        $styles->bgcolor     = $params->get('cookie_bgcolor', '#000000');
        $styles->font_size   = (int) $params->get('cookie_font_size', 14);
        $styles->font_family = $params->get('cookie_font_family', 'inherit');
        $styles->font_color  = $params->get('cookie_font_color', '#ffffff');

        // Captions-Styles
        $styles->captions = [];

        $ov              = new \stdClass();
        $ov->font_size   = (int) $params->get('caption_overview_font_size', 18);
        $ov->font_family = $params->get('caption_overview_font_family', 'inherit');
        $ov->font_color  = $params->get('caption_overview_font_color', '#ffffff');
        $styles->captions['overview'] = $ov;

        $dt              = new \stdClass();
        $dt->font_size   = (int) $params->get('caption_details_font_size', 16);
        $dt->font_family = $params->get('caption_details_font_family', 'inherit');
        $dt->font_color  = $params->get('caption_details_font_color', '#ffffff');
        $styles->captions['details'] = $dt;

        // Buttons-Styles (nur Farben/Schrift, Klassen kommen aus getButtons)
        $styles->buttons = [];

        $accept              = new \stdClass();
        $accept->bgcolor     = $params->get('button_accept_bgcolor', '#28a745');
        $accept->bordercolor = $params->get('button_accept_bordercolor', '#28a745');
        $accept->font_size   = (int) $params->get('button_accept_font_size', 14);
        $accept->font_family = $params->get('button_accept_font_family', 'inherit');
        $accept->font_color  = $params->get('button_accept_font_color', '#ffffff');
        $styles->buttons['accept'] = $accept;

        $settingsBtn              = new \stdClass();
        $settingsBtn->bgcolor     = $params->get('button_settings_bgcolor', '#007bff');
        $settingsBtn->bordercolor = $params->get('button_settings_bordercolor', '#007bff');
        $settingsBtn->font_size   = (int) $params->get('button_settings_font_size', 14);
        $settingsBtn->font_family = $params->get('button_settings_font_family', 'inherit');
        $settingsBtn->font_color  = $params->get('button_settings_font_color', '#ffffff');
        $styles->buttons['settings'] = $settingsBtn;

        $more              = new \stdClass();
        $more->bgcolor     = $params->get('button_more_info_bgcolor', '#ffc107');
        $more->bordercolor = $params->get('button_more_info_bordercolor', '#ffc107');
        $more->font_size   = (int) $params->get('button_more_info_font_size', 14);
        $more->font_family = $params->get('button_more_info_font_family', 'inherit');
        $more->font_color  = $params->get('button_more_info_font_color', '#000000');
        $styles->buttons['more'] = $more;

        $save              = new \stdClass();
        $save->bgcolor     = $params->get('button_save_bgcolor', '#28a745');
        $save->bordercolor = $params->get('button_save_bordercolor', '#28a745');
        $save->font_size   = (int) $params->get('button_save_font_size', 14);
        $save->font_family = $params->get('button_save_font_family', 'inherit');
        $save->font_color  = $params->get('button_save_font_color', '#ffffff');
        $styles->buttons['save'] = $save;

        $close             = new \stdClass();
        $close->font_color = $params->get('button_close_font_color', '#ffffff');
        $styles->buttons['close'] = $close;

        // Icon-Styles
        $styles->icons = [];
        $icon          = new \stdClass();
        $icon->position = $params->get('icon_position', 'right');
        $styles->icons['icon'] = $icon;

        $css['styles'] = $styles;

        return $css;
    }

    /**
     * Buttons: Sichtbarkeit, HTML-Tag, Verhalten, Klassen, Links.
     * Texte kommen aus Sprachdateien.
     */
    public static function getButtons(object $params): array
    {
        $buttons = [
            'accept'      => null,
            'settings'    => null,
            'more'        => null,
            'save'        => null,
            'close'       => null,
        ];

        // Accept
        $accept           = new \stdClass();
        $accept->show     = (int) $params->get('button_accept_show', 1);
        $accept->htmltag  = $params->get('button_accept_htmltag', 'button');
        $accept->behavior = (int) $params->get('button_accept_behavior', 0); // 0 = accept, 1 = accept-all
        $accept->class    = $params->get('button_accept_class', '');
        $buttons['accept'] = $accept;

        // Settings
        $settingsBtn          = new \stdClass();
        $settingsBtn->show    = (int) $params->get('button_settings_show', 1);
        $settingsBtn->htmltag = $params->get('button_settings_htmltag', 'button');
        $settingsBtn->class   = $params->get('button_settings_class', '');
        $buttons['settings']  = $settingsBtn;

        // More Info
        $more          = new \stdClass();
        $more->show    = (int) $params->get('button_more_info_show', 1);
        $more->htmltag = $params->get('button_more_info_htmltag', 'button');
        $more->class   = $params->get('button_more_info_class', '');
        $more->url     = $params->get('button_more_info_url', '');
        $more->target  = $params->get('button_more_info_target', '_blank');
        $buttons['more'] = $more;

        // Save
        $save          = new \stdClass();
        $save->show    = (int) $params->get('button_save_show', 1);
        $save->htmltag = $params->get('button_save_htmltag', 'button');
        $save->class   = $params->get('button_save_class', '');
        $buttons['save'] = $save;

        // Close
        $close        = new \stdClass();
        $close->show  = (int) $params->get('button_close_show', 1);
        $close->class = $params->get('button_close_class', '');
        $buttons['close'] = $close;

        return $buttons;
    }

    /**
     * Links (Privacy, Impressum, About Cookies).
     */
    public static function getLinks(object $params): array
    {
        $links = [
            'privacy'     => null,
            'impressum'   => null,
            'aboutcookies'=> null,
        ];

        $privacy         = new \stdClass();
        $privacy->show   = (int) $params->get('link_privacy_show', 1);
        $privacy->url    = $params->get('link_privacy_url', '');
        $privacy->target = $params->get('link_privacy_target', '_blank');
        $links['privacy'] = $privacy;

        $impressum         = new \stdClass();
        $impressum->show   = (int) $params->get('link_impressum_show', 1);
        $impressum->url    = $params->get('link_impressum_url', '');
        $impressum->target = $params->get('link_impressum_target', '_blank');
        $links['impressum'] = $impressum;

        $about         = new \stdClass();
        $about->show   = (int) $params->get('link_aboutcookies_show', 1);
        $about->url    = $params->get('link_aboutcookies_url', '');
        $about->target = $params->get('link_aboutcookies_target', '_blank');
        $links['aboutcookies'] = $about;

        return $links;
    }

    /**
     * Kategorien (Cookie-Gruppen) – fix: essential, preferences, statistics, marketing, media.
     * Titel & Beschreibungen kommen aus Sprachdateien.
     */
    public static function getCategories(object $params, string $langKey): array
    {
        $cookies = [];

        // Helper für Kategorie-Erzeugung
        $addCategory = function (
            string $name,
            string $titleKey,
            string $descKey,
            string $paramPrefix,
            bool $defaultShow,
            bool $defaultRequired,
            bool $defaultDefault,
            bool $defaultOpen
        ) use (&$cookies, $params) {
            $cat              = new \stdClass();
            $cat->name        = $name;
            $cat->title       = Text::_($titleKey);
            $cat->description = Text::_($descKey);

            $cat->show     = (int) $params->get($paramPrefix . '_show', $defaultShow ? 1 : 0);
            $cat->required = (int) $params->get($paramPrefix . '_required', $defaultRequired ? 1 : 0);
            $cat->default  = (int) $params->get($paramPrefix . '_default', $defaultDefault ? 1 : 0);
            $cat->readonly = $cat->required ? 1 : 0;

            $cat->scripts = $params->get($paramPrefix . '_scripts', '');

            // Accordion-Status: nur "essential" standardmäßig geöffnet
            $cat->open    = $defaultOpen ? 1 : 0;

            // Checkbox-Status initial = default
            $cat->checked = $cat->default ? 1 : ($cat->required ? 1 : 0);

            $cookies[] = $cat;
        };

        // Notwendig (essential) – immer vorhanden, required, open
        $addCategory(
            'essential',
            'MOD_SNC_COOKIES_CAT_ESSENTIAL',
            'MOD_SNC_COOKIES_CAT_ESSENTIAL_DESC',
            'category_essential',
            true,
            true,
            true,
            true
        );

        // Präferenzen
        $addCategory(
            'preferences',
            'MOD_SNC_COOKIES_CAT_PREFERENCES',
            'MOD_SNC_COOKIES_CAT_PREFERENCES_DESC',
            'category_preferences',
            true,
            false,
            false,
            false
        );

        // Statistiken
        $addCategory(
            'statistics',
            'MOD_SNC_COOKIES_CAT_STATISTICS',
            'MOD_SNC_COOKIES_CAT_STATISTICS_DESC',
            'category_statistics',
            true,
            false,
            false,
            false
        );

        // Marketing
        $addCategory(
            'marketing',
            'MOD_SNC_COOKIES_CAT_MARKETING',
            'MOD_SNC_COOKIES_CAT_MARKETING_DESC',
            'category_marketing',
            true,
            false,
            false,
            false
        );

        // Externe Medien
        $addCategory(
            'media',
            'MOD_SNC_COOKIES_CAT_MEDIA',
            'MOD_SNC_COOKIES_CAT_MEDIA_DESC',
            'category_media',
            true,
            false,
            false,
            false
        );

        return $cookies;
    }

      /** 
     * HEX → RGB (aktuell nicht zwingend benötigt, aber nützlich für spätere Erweiterungen).
     */
    public static function hexValueToRgb(string $hexString): array
    {
        $hexString = str_replace('#', '', $hexString);
        if (strlen($hexString) !== 6 || !preg_match('/[A-Fa-f0-9]{6}/', $hexString)) {
            return ['red' => 255, 'green' => 255, 'blue' => 255];
        }

        $hexdecValue = hexdec($hexString);

        return [
            'red'   => 0xFF & ($hexdecValue >> 16),
            'green' => 0xFF & ($hexdecValue >> 8),
            'blue'  => 0xFF & $hexdecValue,
        ];
    }

    /**
     * Prüft, ob der User bereits zugestimmt hat (Popup-Steuerung)
     */
    public static function isAccepted(): bool
    {
        return isset($_COOKIE['snc_cookie_accepted']) && $_COOKIE['snc_cookie_accepted'] == '1';
    }

    /**
     * Setzt das einfache Popup-Cookie, wenn der User akzeptiert
     */
    public static function handleAccept(): void
    {
        if (isset($_GET['snc_accept'])) {

            setcookie(
                'snc_cookie_accepted',
                '1',
                time() + 31536000,
                '/'
            );

            // Seite neu laden, Query entfernen
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
            exit;
        }
    }
}

// Accept-Handler aktivieren
ModSncCookiesHelper::handleAccept();