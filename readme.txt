=== Build It For Me - Widget creator ===
Contributors: pablobifm
Donate link: https://www.builditforme.ai/donate
Tags: widgets, elementor, AI, automation, management
Requires at least: 5.0
Tested up to: 6.3.1
Requires PHP: 7.0
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ask a bot to build Elementor widgets for you. See the changes in real time. Save your widgets and include them in any of your pages with the Elementor editor.

== Description ==

BIFM Widget Manager allows WordPress users to efficiently manage and view AI-generated widgets for Elementor. The plugin connects with Build It For Me's API to facilitate the generation and management of these widgets.

Features:
* **Just ask a bot**: The bot will build any front end widget you want. Ask for interactive widgets!
* **Add the widget to a page**: Save the widget, and use elementor's editor to drag the widget into your page.
* **Simple Management Interface**: Easily view and manage AI-generated widgets directly from your WordPress dashboard.



== Installation ==

1. Install the plugin through the WordPress plugins screen directly, or upload the plugin files to the `/wp-content/plugins/bifm-widget-manager` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to the 'Build It For Me' menu in your dashboard to create and manage AI-generated widgets.

== Frequently Asked Questions ==

= How do I create a new AI-generated widget? =
Navigate to 'Build It For Me' in your WordPress dashboard and click on the 'Create Widget' submenu to generate a new widget. Ask a bot to build the widget you want.
Start with something simple and you can continue asking the bot to do changes until you're pleased with the result.
If at some point you don't like what the bot did, use the UNDO button. It's better to not complicate your project too much.
Click 'Reset' to start over.

= Why is the bot not doing what I ask it to do? =
The UNDO button is your best friend. The bot can sometimes fail to understand what you're asking, try using the UNDO button and ask with different words.
If the button didn't do what you expected it's always best to use the UNDO button than to continue asking for changes.

= Is there a limit to how many widgets I can create? =
This might depend on your API limits with Build It For Me. Please refer to their official documentation for more details.

= How do I use a widget I created? =
Once you like the widget you've developed, hit 'Save', provide a name for your widget (it can't start with a number, can't contain special characters). Then you'll see the widget in your website under the name you saved it with.

= How do I restart from scratch? =
In the 'Create widget' page click 'Reset' to start over.

= Can I edit a widget? =
You can iteratively work on your widget by asking the bot to do changes to it. But once you hit the reset button to create a new widget, you can't go back to edit an old widget.

= Who can use the plugin? =
Your wordpress users with editor permissions and higher.

= How can I get help? =
We appreciate any feedback at pablo@builditforme.ai


== Screenshots ==

1. Main management interface showcasing all AI-generated widgets.
2. The 'Create Widget' interface where users can generate new widgets using AI.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1 =
Fixed widget saving issues.

== External API Usage ==

The BIFM Widget Manager relies on the Build It For Me (BIFM) external API to generate and manage AI-powered widgets. This integration is essential for the functionality of our plugin, enabling users to create, customize, and manage widgets through AI-driven processes.

### API Dependency Details:
- **Service Provider**: The plugin uses services provided by Build It For Me.
- **Service Usage**: The plugin communicates with the Build It For Me API to request widget generation and management tasks.

### Important Data Safety Notice:
- **Protection of PII**: Users should never share any Personally Identifiable Information (PII) through the plugin. Ensuring the privacy and security of your data is paramount. Avoid entering any sensitive personal details into the widget creation process.

### Terms of Service and Privacy:
- We highly recommend reviewing Build It For Me's Terms and Conditions to understand the usage policies and data handling practices. 
- For detailed information, please visit [Build It For Me Terms and Conditions](https://www.builditforme.ai/terms-and-conditions).

### User Consent:
- By using the BIFM Widget Manager, you agree to the terms and conditions set forth by Build It For Me. It is important to be aware of these terms as they govern the use of the API and the services provided through our plugin.

### Data Handling:
- The plugin does not store personal data but may transmit data necessary for widget generation to Build It For Me's API. This data is subject to Build It For Me's privacy policy.

This integration is crucial for providing our users with a seamless and efficient widget creation experience. Should you have any concerns or questions regarding the API integration, please feel free to reach out to us.

== Arbitrary section ==

A special thanks to the Build It For Me team for their API, which powers the AI widget generation.



Reviewed:
admin-page.php 
bifm_action_hooks.php
blog-creator-page.php
blog-manager.php
chat.php
client.php
readme.txt
widget-manager.php
blog-creator-script.js