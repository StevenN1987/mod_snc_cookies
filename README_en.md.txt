SNC Cookies – GDPR‑Compliant Cookie and Script Blocking Module

SNC Cookies is a lightweight, GDPR‑compliant cookie module for Joomla 6.
It is conditionally compatible with Joomla 5, but has not yet been fully tested.
The module displays a cookie notice, allows blocking of optional categories, and provides a detailed settings window.

Features
• 	Clean and modern cookie popup with custom design
• 	Detailed settings panel (Statistics, Marketing, Essential)
• 	GDPR‑compliant structure
• 	Fully multilingual (includes de-DE & en-GB)
• 	Responsive layout
• 	No external libraries required
• 	Clean HTML structure, easy to customize
• 	Simple integration into any template

Installation
1. 	Install the ZIP package via the Joomla backend
2. 	Activate the module “SNC Cookies”
3. 	Assign it to a module position such as debug or bottom
4. 	Set the module to display on all pages
5. 	Done

Configuration
The module provides the following options:
• 	Buttons (Accept, Decline, Settings, More Info)
• 	Colors & Layout
• 	Texts & Headings
• 	Links to Privacy Policy & Imprint
• 	Cookie Categories

Structure
The module consists of:
mod_snc_cookies/
│
├── tmpl/
│   └── default.php
│
├── assets/
│   ├── css/
│   │   └── snc-cookies.css
│   └── js/
│       └── snc-cookies.js (optional)
│
├── language/
│   ├── de-DE/
│   │   └── de-DE.mod_snc_cookies.ini
│   └── en-GB/
│       └── en-GB.mod_snc_cookies.ini
│
└── mod_snc_cookies.xml

Multilingual Support
Included languages:
• 	German (de-DE)
• 	English (en-GB)

Additional languages can be added easily.

GDPR Notice
This module only stores:
• 	Consent accepted (1)
• 	Consent declined (0)
• 	Category settings (JSON)

No personal data is stored.

Support
Premium features and extensions are already in development and will be released after successful testing.
Requests for individual customizations can be sent at any time to:
joomlaextensions@stevnacom.com
