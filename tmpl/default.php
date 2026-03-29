<?php
/**
 * SNC Cookies
 * @package     Joomla.Module
 * @subpackage  mod_snc_cookies
 * @author      Steven Naschold Computerservice
 * @license     GNU/GPLv3
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// ------------------------------------------------------------
// Daten aus Helper
// ------------------------------------------------------------
$basics     = $settings->basics;
$captions   = $settings->captions;
$css        = $settings->css;
$buttons    = $settings->buttons;
$links      = $settings->links;
$categories = $settings->cookies;

$moduleId   = 'snc-cookies-' . $module->id;

// Popup-Status (aus Helper)
$activeClass = $cookieAccepted ? '' : 'active';
?>

<link rel="stylesheet" href="modules/mod_snc_cookies/assets/css/snc-cookies.css">

<!-- Overlay -->
<div class="snc-overlay <?php echo $activeClass; ?>"></div>

<!-- Popup (Hauptcontainer für Overview + Settings) -->
<div class="snc-cookies-popup <?php echo $activeClass; ?>" id="<?php echo $moduleId; ?>">

    <!-- OVERVIEW BOX -->
    <div class="snc-cookies-box">
        <div class="snc-cookies-content">

            <!-- Close Button -->
            <?php if (!empty($buttons['close']->show)) : ?>
                <a href="?snc_accept=1" class="snc-cookies-close">&times;</a>
            <?php endif; ?>

            <!-- Overview Caption -->
            <?php if (!empty($captions['overview']->show)) : ?>
                <<?php echo $captions['overview']->htmltag; ?>
                    class="snc-cookies-caption"
                    style="color:<?php echo $captions['overview']->font_color; ?>;
                           font-size:<?php echo $captions['overview']->font_size; ?>px;
                           font-family:<?php echo $captions['overview']->font_family; ?>;">
                    <?php echo Text::_('MOD_SNC_COOKIES_CAPTION_OVERVIEW'); ?>
                </<?php echo $captions['overview']->htmltag; ?>>
            <?php endif; ?>

            <!-- Intro Message -->
            <div class="snc-cookies-message">
                <?php echo Text::_('MOD_SNC_COOKIES_INTRO_MESSAGE'); ?>
            </div>

            <!-- Buttons -->
            <div class="snc-cookies-actions">

                <!-- Accept -->
                <a href="?snc_accept=1"
                   class="snc-btn snc-btn-accept <?php echo htmlspecialchars($buttons['accept']->class); ?>">
                    <?php echo Text::_('MOD_SNC_COOKIES_BUTTON_ACCEPT'); ?>
                </a>

                <!-- Decline -->
                <a href="?snc_accept=0"
                   class="snc-btn snc-btn-decline <?php echo htmlspecialchars($buttons['decline']->class ?? ''); ?>">
                    <?php echo Text::_('MOD_SNC_COOKIES_BUTTON_DECLINE'); ?>
                </a>

                <!-- Settings -->
                <?php if (!empty($buttons['settings']->show)) : ?>
                    <a href="#<?php echo $moduleId; ?>-details"
                       class="snc-btn snc-btn-settings <?php echo htmlspecialchars($buttons['settings']->class); ?>">
                        <?php echo Text::_('MOD_SNC_COOKIES_BUTTON_SETTINGS'); ?>
                    </a>
                <?php endif; ?>

                <!-- More Info -->
                <?php if (!empty($buttons['more']->show) && !empty($buttons['more']->url)) : ?>
                    <a href="<?php echo $buttons['more']->url; ?>"
                       target="<?php echo $buttons['more']->target; ?>"
                       class="snc-btn snc-btn-more <?php echo htmlspecialchars($buttons['more']->class); ?>">
                        <?php echo Text::_('MOD_SNC_COOKIES_BUTTON_MORE_INFO'); ?>
                    </a>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <!-- SETTINGS POPUP (jetzt korrekt im Popup-Container!) -->
    <div class="snc-cookies-details snc-hidden" id="<?php echo $moduleId; ?>-details">

        <div class="snc-cookies-details-box">

            <h3><?php echo Text::_('MOD_SNC_COOKIES_SETTINGS_TITLE'); ?></h3>

            <p><?php echo Text::_('MOD_SNC_COOKIES_SETTINGS_INTRO'); ?></p>

            <!-- Kategorie: Notwendig -->
            <div class="snc-cookie-category">
                <div class="snc-cookie-category-info">
                    <strong>Notwendige Cookies</strong>
                    <p>Diese Cookies sind für den Betrieb der Website erforderlich.</p>
                </div>

                <label class="snc-switch">
                    <input type="checkbox" checked disabled>
                    <span class="snc-slider"></span>
                </label>
            </div>

            <!-- Kategorie: Statistik -->
            <div class="snc-cookie-category">
                <div class="snc-cookie-category-info">
                    <strong>Statistik</strong>
                    <p>Hilft uns zu verstehen, wie Besucher die Website nutzen.</p>
                </div>

                <label class="snc-switch">
                    <input type="checkbox" id="snc-statistics">
                    <span class="snc-slider"></span>
                </label>
            </div>

            <!-- Kategorie: Marketing -->
            <div class="snc-cookie-category">
                <div class="snc-cookie-category-info">
                    <strong>Marketing</strong>
                    <p>Diese Cookies werden verwendet, um Werbung zu personalisieren.</p>
                </div>

                <label class="snc-switch">
                    <input type="checkbox" id="snc-marketing">
                    <span class="snc-slider"></span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="snc-cookies-details-actions">

                <button class="snc-btn snc-btn-save" id="snc-save-settings">
                    Einstellungen speichern
                </button>

                <a href="#" class="snc-btn snc-btn-back">
                    Zurück
                </a>

            </div>

        </div>

    </div>

</div> <!-- Popup Ende -->


<!-- ========================================================= -->
<!-- JAVASCRIPT FÜR EINSTELLUNGEN -->
<!-- ========================================================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const popup = document.querySelector(".snc-cookies-popup");
    const details = document.querySelector(".snc-cookies-details");
    const settingsBtn = document.querySelector(".snc-btn-settings");
    const backBtn = document.querySelector(".snc-btn-back");

    if (settingsBtn) {
        settingsBtn.addEventListener("click", function (e) {
            e.preventDefault();
            popup.classList.add("snc-show-details");
            details.classList.remove("snc-hidden");
        });
    }

    if (backBtn) {
        backBtn.addEventListener("click", function (e) {
            e.preventDefault();
            details.classList.add("snc-hidden");
            popup.classList.remove("snc-show-details");
        });
    }

});
</script>