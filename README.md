# summit-event
Assessment


Description

This plugin provides a structured way to manage "Summit" events without relying on a specific theme. It was built to ensure data portability and strict adherence to WordPress coding standards.

**Key Features:**
* **Custom Post Type ('summit'):** Separates event content from standard blog posts.
* **Custom Metaboxes:** Provides a structured interface for entering Event Dates, Locations, Topic Highlights, and "Why Attend" details.
* **Frontend Templating:** Uses the `the_content` filter to automatically inject a "Hero Banner" and "Topic Grid" layout, ensuring the design works on any active theme.
* **Security:** Implements Nonces for form verification and strict Sanitization (`sanitize_text_field`, `wp_kses_post`) for all inputs.

Installation

1.  Upload the `summit-event` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  A new menu item **"Summits"** will appear in the dashboard sidebar.

Usage

1.  Navigate to **Summits > Add New**.
2.  Enter the **Title** (e.g., "4th Annual Future Banks Summit").
3.  Enter the **Overview** in the main content editor.
4.  Scroll down to **Event Configuration** to add the specific details (Date, Location, etc.).
5.  **Important:** Set a **Featured Image**. This image is used dynamically to generate the dark background for the Hero Banner.

Technical Decisions & Tools

* **Architecture:** Chosen as a Plugin (vs. Theme) to decouple functionality from design.
* **Development:** Developed locally using **LocalWP**.
* **Code Editing:** Written in **VS Code** following WordPress Coding Standards.
* **Testing:** Verified on WordPress 6.4 using **Chrome DevTools** to ensure responsive grid layout for the topics section.
