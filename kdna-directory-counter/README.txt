=== KDNA Directory Counter ===
Contributors: Krull Design & Advertising
Tags: elementor, jetengine, directory, counter, badge
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A single Elementor widget that displays a styleable stats badge (e.g.
"32 Offices") with full position and styling controls. Designed to overlay
on a JetEngine Map Listing or sit anywhere else on the page.

== Description ==

KDNA Directory Counter is a focused Elementor widget that displays a number
plus a label. The count can come from:

* A static number typed into the widget.
* The total published posts in a chosen custom post type (cached for 15 minutes).
* A JetSmartFilters query, with live AJAX updates whenever the user filters
  the directory.

Bundled features:

* Position controls with four corner presets plus a Custom preset, responsive
  offsets, and z-index. The widget can be injected into any target element by
  CSS ID and overlaid in absolute position.
* Count-up animation powered by a bundled CountUp.js v2 compatible
  implementation, triggered on viewport entry via IntersectionObserver, with
  configurable duration and easing.
* Optional icon (Font Awesome or SVG) with three position options.
* Full Style tab with container background and gradient, padding, border,
  radius, box shadow, hover states, transitions, typography for the number
  and label, icon styling, alignment, and responsive layout.
* Atomic markup compatible (single wrapper div, no .elementor-widget-container
  selectors).
* CSS and JS only loaded on pages where the widget is actually rendered.

== Requirements ==

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Elementor (free) installed and active
* JetEngine (optional, only required for the JetSmartFilters source)

== Installation ==

1. Download the kdna-directory-counter.zip file.
2. In WordPress admin, go to Plugins, Add New, Upload Plugin.
3. Choose the zip file and click Install Now.
4. Click Activate Plugin.
5. Make sure Elementor is also installed and active.

== How to use ==

1. Edit any page with Elementor. Search the widget panel for "KDNA Directory
   Counter" and drag it onto the page.
2. Open the Counter Source section and pick a source:
   * Static number, then type the number you want shown.
   * CPT total, then choose the custom post type. The count is cached for
     15 minutes per CPT slug.
   * JetSmartFilters query, then enter the Query ID set on your JetEngine
     Listing Grid or Map Listing. The same Query ID must be set on every
     JetSmartFilters filter widget that targets the directory.
3. Set the Singular label and Plural label, e.g. "Office" and "Offices". The
   widget switches between them automatically based on the current count.
4. To overlay on a Map Listing, edit the Map Listing widget and set a CSS ID
   in its Advanced tab, e.g. directory-map. Then in the Counter widget open
   the Position section:
   * Set Target element ID to directory-map (no # prefix).
   * Turn Overlay on target ON.
   * Choose a position preset (e.g. Top right) and adjust offsets.
   * Optionally set the z-index if other elements stack above the counter.
5. Open the Animation section to enable or disable the count-up animation
   and tune the duration and easing. The animation fires when the widget
   enters the viewport. For JetSmartFilters sources the count re-animates
   from the previous value to the new value on every filter change.
6. Open the Icon section to add an optional icon and choose whether it sits
   before, after, or above the number.
7. Use the Style tab to fine tune the container, number, label, icon, and
   alignment. All controls are responsive (desktop / tablet / mobile).

== File listing ==

kdna-directory-counter/
- kdna-directory-counter.php (main plugin file)
- includes/class-kdna-directory-counter-widget.php (widget class)
- assets/css/kdna-directory-counter.css (front-end styles)
- assets/js/kdna-directory-counter.js (front-end behaviour)
- assets/js/countup.min.js (bundled CountUp.js v2 compatible, MIT licensed)
- README.txt

== Changelog ==

= 1.0.0 =
* Stage 1: Plugin scaffold, Elementor widget registration, Counter Source
  controls, static / CPT total / JetSmartFilters initial count sources,
  conditional asset enqueue.
* Stage 2: Position section (target element ID, absolute overlay toggle,
  four position presets plus Custom, responsive offsets, z-index),
  zero-flicker overlay injection, JetSmartFilters live count via AJAX
  with nonce validation, JSF re-injection safety net, Elementor editor
  placeholder badge.
* Stage 3: Animation section (enable toggle, duration, easing) with
  bundled CountUp.js v2 compatible library and viewport detection via
  IntersectionObserver. Icon section (icons picker plus before / after /
  above position). Full Style tab (container background and gradient,
  padding, border, radius, box shadow, hover states, transition duration,
  number typography and colour with hover, text shadow, label typography
  and spacing, icon size, colour, and spacing, text alignment, stacked
  vs inline layout). All Style controls responsive.
