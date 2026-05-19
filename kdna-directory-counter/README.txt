=== KDNA Directory Counter ===
Contributors: Krull Design & Advertising
Tags: elementor, jetengine, directory, counter, badge
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A small companion plugin for JetEngine directories. Adds a single Elementor
widget that displays a styleable stats badge (e.g. "32 Offices") with full
position and styling controls.

== Description ==

KDNA Directory Counter is a focused Elementor widget that displays a number
plus a label, suitable for overlaying on a JetEngine Map Listing or sitting
anywhere else on the page.

The count can come from:

* A static number typed into the widget.
* The total published posts in a chosen custom post type (cached for 15 minutes).
* A JetSmartFilters query (initial server-side count in Stage 1, live AJAX
  updates added in Stage 2).

This is the Stage 1 build. Position controls, overlay behaviour, live JSF
updates, animation, icons, and full styling controls are added in Stages 2
and 3.

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
6. Edit any page with Elementor. Search the widget panel for "KDNA Directory Counter"
   and drag it onto the page.
7. Open the Counter Source section, set the source, choose your singular and plural
   labels, then preview the page.

== Quick test after activation ==

1. Open a page in Elementor.
2. Drag in the KDNA Directory Counter widget.
3. Set Source to "Static number".
4. Set the static number to 32.
5. Leave Singular label as "Office" and Plural label as "Offices".
6. Update the page and view on the front end. You should see "32 Offices".

== Changelog ==

= 1.0.0 =
* Stage 1 release. Plugin scaffold, Elementor widget registration, Counter
  Source controls, static / CPT total / JetSmartFilters initial count sources,
  base CSS, conditional asset enqueue.

== Roadmap ==

* Stage 2: Position section (Target Element ID, absolute toggle, four position
  presets, responsive offsets, z-index), overlay injection behaviour,
  JetSmartFilters live count updates via AJAX, Elementor editor placeholder.
* Stage 3: Count-up animation with CountUp.js and viewport detection, icon
  support, full Style tab (container, number, label, icon, alignment), hover
  states, responsive polish.
