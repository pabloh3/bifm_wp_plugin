=== Build It For Me ===
Contributors: pablobifm
Tags: widgets, elementor, AI, automation, management
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Billy is a WordPress copilot. Ask a bot for help navigating and modifying your site, ask it to build blog posts or to create new Elementor widgets.

== Description ==

Build It For Me Widget Manager allows WordPress users to efficiently manage their website using Artificial Intelligence.
Billy guides users through making changes to their site.
Our blog creator allows you to create blog posts with a single prompt. The Widget Builder codes elementor widgets from scratch.
Ideal for developers, content creators, and site managers looking to streamline their WordPress workflow.
The plugin connects with Build It For Me's API to facilitate the generation and management of content and code.

https://www.youtube.com/watch?v=SXsSv5by0X8

Features:
* **Just ask a bot**: The bot will answer questions about your site. You can grant it access to your site's configuration and content.
* **Chat on any page**: Hit the "B" button on the top bar to open up your most recent conversation with Billy on any admin page.
* **Create custom widgets for your page**: Ask a bot to code a new widget, and use elementor's editor to drag the widget into your page.
* **Blog creator**: The bot will build any blog posts to target a specific keyphrase, and deliver them in draft state for your review.
* **Write in bulk**: Request multiple blog posts at once, go to lunch, and come back to find your new posts created and ready for your approval.
* **Help your clients**: Are you a WordPress developer? Give Billy a user manual so it can guide your clients on how to manage their website.


== Installation ==

1. Install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to the 'Build It For Me' menu in your dashboard to chat with Billy.
4. If you want to use the blog creator, configure your blog creator on the "Writer Settings". If you're not an admin, you'll need an "app password" to configure the writer.
5. You can modify how Billy answers on the "Chat Settings" button. Including providing custom docs.


== Frequently Asked Questions ==

= What kind of questions can I ask Billy? =
You can ask Billy to be your WordPress assistant. Ask about Wordpress or questions specific to your site. 
You can also request to write new content or to create a new widget for you to use on your site.

= What information does Billy know about my site? =
Billy has access to your site's theme, template, editor and WordPress version. 
Billy can access your site's content and configuration, but will ask for your approval when the information is private.

= How do I create a new AI-generated widget? =
Navigate to 'Build It For Me' in your WordPress dashboard and click on the 'Widget Builder' submenu to generate a new widget. Ask a bot to build the widget you want.
Start with something simple and you can continue asking the bot to do changes until you're pleased with the result.
If at some point you don't like what the bot did, use the UNDO button. It's better to not complicate your project too much.
Click 'Reset' to start over.

= What kind of widgets can I create? =
Billy builds exclusively Elementor widgets, so you will need the Elementor plugin installed to use them, even if you don't usually edit your pages with Elementor.
We recommend sticking to building widgets that work only on the front-end.
Some examples we've seen: A Solar System animation, a refinancing calculator, a pricing estimator.

= Can I customize my widget? =
Yes, in two ways. You can ask the Builder to code the widget however you want it.
You can also click "Add Elementor Controls" to ask the bot to add controls so that, once you save the widget, you can customize it each time you pull it into a page.

= What are Elementor Controls? =
Elementor controls allow you to modify things like text and colors when you use the widget in a page.

= Why is the bot not doing what I ask it to do? =
The UNDO button is your best friend. The bot can sometimes fail to understand what you're asking, try using the UNDO button and ask with different words.
If the button didn't do what you expected it's always best to use the UNDO button than to continue asking for changes.

= Is there a limit to how many widgets I can create? =
This might depend on your API limits with Build It For Me. Please refer to their official documentation for more details.

= How do I use a widget I created? =
Once you like the widget you've developed, hit 'Save', provide a name for your widget (it can't start with a number, can't contain special characters). Then you'll see the widget in your website under the name you saved it with.

= How do I restart the Builder from scratch? =
In the 'Create widget' page click the broom icon 'Reset' to start over.

= Can I edit a widget? =
You can iteratively work on your widget by asking the bot to do changes to it. But once you hit the reset button to create a new widget, you can't go back to edit an old widget.

= Who can use the plugin? =
Your wordpress users with editor permissions and higher.

= How can I get help? =
We will answer your questions and appreciate any feedback at pablo@builditforme.ai

= How to setup the blog generator =
Step 1: Access your wordpress environment, on the left hand side select “Build It For Me”.
Step 2: Go to the “Writer Settings” tab and fill out the settings for your blog post
Author’s username: You need to decide which user will be credited for what Billy writes. We recommend you create a new user as “Author” to use for this purpose.
Describe your website / company: These are the instructions for the bot that generates text. Explain to it what your company does, share any links you want it to promote and the tone you want to use. Ideally, match the language you want to generate the posts in.
Describe the image style: These are the instructions for the bot that generates images. Specify the style of the images. Because this bot doesn’t generate text, the language is less relevant.
Step 3: Hit UPDATE
*Settings are configured at the user level. Two users in the same company can have different settings.*
*If you are not an admin, you'll have to ask an admin to create an "application password" for the account you want to use as the author.*

= How to create a post =
1. Log in to Wordpress with your own account. If you haven’t configured the “Writer Settings” section, configure it as indicated here.
2. Go to the Build It For Me plugin (left hand side bar). And click "Writer bot" -> Create New Blog Post.
Keyphrase: Fill out the field with the text that you think users will google, and you want to target.
Category: Select the blog category it will be generated in
3. Hit "Generate Blog Posts" 
4. In the table at the bottom you'll see the status of your request. Hit refresh to see how it's doing.
*To generate many posts at once, hit "Add more +". Select the category for each.*
*Wait at least 3 minutes for each line that you included. Once each is ready, you'll be able to preview them in the "glasses" button.*


== Screenshots ==

1. Screenshot_1.png: Billy's interface. Ask a bot for help on WordPress.
2. Screenshot_2.png: Writer Bot interface, ask the bot to write blog posts.
3. Screenshot_3.png: Widget Builder. Ask a bot to code custom Elementor widgets. 
4. Screenshot_4.png: Pop out chat. Can be opened in any admin page of WordPress.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1 =
Fixed widget saving issues.

= 1.2 =
Billy can now read site content to provide more accurate assistance.

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

== Acknowledgments ==

A special thanks to the Build It For Me team for their API, which powers the AI generation.
Thank you to Elementor for building a flexible platform that allows building your own widgets.
