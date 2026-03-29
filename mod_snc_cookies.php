<?php
/**
 * SNC Cookies – Modul
 *
 * 2026-2027 Steven Naschold Computerservice
 * 
 *
 * @package     Joomla.Module
 * @subpackage  mod_snc_cookies
 * @license     GNU/GPLv3
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// ------------------------------------------------------------
// CSS EINBINDEN (WICHTIG!)
// ------------------------------------------------------------
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle(
    'mod_snc_cookies',
    'modules/mod_snc_cookies/assets/css/style.css'
);

// ------------------------------------------------------------
// Helper laden
// ------------------------------------------------------------
require_once __DIR__ . '/helper.php';

// Accept-Handler ausführen (setzt Cookie & redirect)
ModSncCookiesHelper::handleAccept();

// Cookie-Status abfragen (1 = akzeptiert, 0 = nicht akzeptiert)
$cookieAccepted = ModSncCookiesHelper::isAccepted();

// Alte EU-Cookie-Settings laden (falls benötigt)
list($settings, $eu_cookie_consent) = ModSncCookiesHelper::getSettings($params);

// Template laden
require ModuleHelper::getLayoutPath('mod_snc_cookies', $params->get('layout', 'default'));