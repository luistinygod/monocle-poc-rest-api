# Monocle Blocks library

This is a WordPress plugin that modifies the WP REST API to add custom endpoints for the Monocle theme. It disables the default WP REST API endpoints for non-editors/admins and adds new ones that are more specific to the Monocle project.

## Installation

This is a WordPress plugin. To install it, download the zip file and upload it to your WordPress site. Then activate the plugin.

## Usage

Please note that this plugin completely disables the default WP REST API endpoints for non-editors/admins. This is done to prevent unauthorized access to the site's data. If you want to enable the default WP REST API endpoints, you can do so by modifying the plugin code.

The plugin adds three custom endpoints to the WP REST API:

- `/wp-json/monocle/v1/posts` - Returns a list of posts with the following fields: ID, title, date, excerpt, and featured image URL.
- `/wp-json/monocle/v1/posts/{id}` - Returns a single post with the following fields: ID, title, date, content, and featured image URL. The content field is the full post content separated block by block (using the `parse_blocks` function and with some modification to remove some HTML tags and add more context to some blocks, including the Monocle Related Articles custom block).
- `/wp-json/monocle/v1/whoami` - Returns information about the current user, including their ID, email, and subscription status (for example).
