=== Minutes, Agendas, Newsletters ===
Contributors: brendaegeland
Tags: upload, minutes, agendas, newsletters
Stable tag: 1.0.0
Requires at least: 3.0.1
Tested up to: 3.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upload and manage pdfs of minutes, agendas and newsletters for your organization.

== Description ==

Upload and manage pdfs of minutes, agendas and newsletters for your organization.

Provides editor functions and shortcodes to display a listing or link to
individuals files. The uploaded files are stored in a user-named folder in the uploads directory
and are *not* indexed in the database. This allows users to mass-upload 
existing files through an ftp program if necessary.

More information can be found at [the plugin website](http://www.redletterdesign.net/wp/minutes-agendas-newsletters/)

= Current Features =

* Upload pdfs of minutes, agendas and newsletters
* Display a table of minutes, agendas and/or newsletters by month and year
* Link to individual files

= Shortcodes =

[minagnews-table show="show-value" months_order="ASC|DESC" omit_empty=false|true id="id" class="class" attr="attr"]

*	__show__: which types of documents to show, defined by the 'slug' for each document type in the plugin settings, or 'all' for all document types. For multiple document types, use a comma separated list (e.g., show=minutes,agendas).

*	__months_order__: within a given year in the table, show the months in ascending order (ASC) or descending order (DESC)

*	__omit_empty__: if a month in a given year has no documents, should that month be omitted from the table? Defaults to false.

*	__year__: which years to display. You can enter a specific year, a comma-separated list of years, a range, or use the keywords 'current' for the current year or 'previous' for the previous year. Valid examples:year=2008

	year=2005-2009

	year=2005,2007,2011

	year=2004,2007-2008
	
	year=current
	
	year=previous,current
 	
	year=all
	
*	__id__: the id to be applied to the table

*	__class__: the class to be applied to the table, default is minagnews_tbl

*	__attr__: additional attributes to be applied, e.g., "width='100%'". This string will be added as is to the table tag. The table defaults to cellspacing='0' cellpadding='4' width='100%' unless overridden with attr="attr"

[minagnews-link doctype="doctype" date="date" id="id" class="class" ]

* __doctype__: the document type slug, as specified in the plugin settings

* __date__: the date of the document, e.g., 2012-04-12, or 'latest' for the most current document of the given type

* __id__: id to be applied to the link

*	__class__: class to be applied to the link

If invalid data is provided in the shortcode, a link will not be created. However, the document does not need to exist in order for the shortcode to be created (unless 'latest' is selected as the date).

== Installation ==

1. Download and unzip the latest release zip file
1. Upload the entire minutes-agendas-newsletters directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Adjust settings in Settings->Minutes, Agendas, Newsletters
1. If you have a large number of existing documents, named as specified in your settings, you can manually upload them to the directory (specified in Settings) and they will be recognized by the plugin.

= Using =

1. Activate plugin.
1. Set settings to match your naming conventions and document types.
1. Go to Media -> Minutes, Agendas Newsletters to upload files.
1. Creates link to files in your pages/posts using the button in the Visual Editor, or by shortcode
1. Create tabular display of uploaded documents in your pages/posts using the button in the Visual Editor, or by shortcode
1. More information can be found at [the plugin website](http://www.redletterdesign.net/wp/minutes-agendas-newsletters/)

== Screenshots ==

1. The Upload Files page.
2. The Visual Editor button.
3. The popup screen in the Visual Editor.
4. The Settings page.
5. Example of table display.

== Frequently Asked Questions ==

= Can this handle something other than pdf documents? =

Not at this time.

= Can this handle more than one document for a given date for a given document type? =

Sorry, no. The file naming convention is based on no more than one document per date for each document type.

= Can I change how the table looks? I don't like the headings, etc. =

These are all controlled by CSS. The default class for the table is minagnews_tbl.

= The links for documents within the same month should be on separate lines. =

You can do this via CSS in your style.css file, e.g., table.minagnews_tbl td a { display: block; }

== Requirements ==

* PHP 5+
* WordPress 3.0+

== Changelog ==

= 1.0.0 =
2012-07-04 - New features for shortcodes. Some general code cleanup.

= 0.2.1 =
2011-10-11 - Trying to get this readme file right!

= 0.2 =
2011-10-11 - Added shortcode options to select only certain years and subsets of document types, and a number of bug fixes.
Thanks to Ethan Piliavin for finding and correction a problem with document order, and to Tim Carey for ideas on handling shortcode options
for selecting only certain years.

= 0.1 =
2011-03-02 - Initial development version

== Upgrade Notice ==

= 0.2.1 =

New table shortcode features to select only certain years or document types plus bug fixes.

== More Information ==

For more information about this plugin and its development, contact Brenda Egeland, brenda@redletterdesign.net