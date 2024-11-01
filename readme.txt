=== Easy CRM ===
Tags: crm,leads,quotes,invoices,tasks
Donate link: https://www.paypal.com/donate/?hosted_button_id=73ZZVYDUTMHME
Requires at least: 5.9
Tested up to: 6.6.1
Stable tag: 1.0.24
Contributors: sunnysonic
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Collect new leads, manage clients, quotations, invoices, tasks and more for your entire team. 

== Description ==
Many CRM systems can seem overwhelming: We've created Easy CRM to include the most essential tools for you to be able to run your small and medium sized companies and manage your team efficiently.
Collect new leads, manage clients, quotations, invoices, tasks and more for your entire team. 
Watch the video to see the most basics on how it is used.

== Installation ==
Download the plugin, install and activate. Our CRM doesn't have any dependencies on other plugins.

== Frequently Asked Questions ==

= How to use the plugin? =

1. step - Add your clients (manually or through form on your website)
2. step - Assign tasks and log the activity pertaining to the client / your team
3. step - create and manage your quotes and invoices for the existing clients

= Can I create tasks independent from a client? =

Yes, tasks can be added through the task menu without a connection to the client. This will allow your team to plan work that does not directly pertain to a client.

= Do i need to use all parts of the plugin? =

No, feel free to only use parts of the plugin, like task management for your team.

= Help, I can't delete old logs / activity logs of a client? =

The system only allows to delete or edit activity logs that are not older than 1 hour. This will assure that nobody can delete client history, which often can have legal implications.
Those logs are deleted though once the entire client is deleted in the system if necessary.

= I would like to add some functionality? =

Feel free to contact us to let us know, what you'd like to have included.

== <a href="https://vimeo.com/804911326" target="_blank">Click here to watch our Part1 Video: First steps - Usage & Feature Presentation</a> ==
[vimeo https://vimeo.com/804911326]

== <a href="https://vimeo.com/804911367" target="_blank">Click here to watch our Part2 Video: First steps - Usage & Feature Presentation</a> ==
[vimeo https://vimeo.com/804911367]

== Screenshots ==

1. This screen shows the list of clients and client companies added to the system
2. Overview of open tasks and recent tasks of the entire team
3. Overview of all Quotations and Invoices in the system
4. Menu that shows a form that can be added to the front-end. Form submits will be added to the CRM as leads.
5. Settings menu to set standard currency, tax percentage, invoice/quote headers and footers etc
6. Client profile with all activity logs, tasks, quotes and invoices pertaining to the client

== Changelog ==

= 1.0.24 =
* fixed issue on saving date when marking invoice as paid
* hovering over invoice/quote name in client profile shows now line items of invoice/quote
* hovering over invoice/quote name in invoice list of main menu shows now line items of invoice/quote

= 1.0.23 =
* bug-fixes
* links in logs are now clickable
* client email(s) are now shown on quotes and invoices
* invoices can be marked as paid now directly from the client profile, without the need to go into the edit menu of the invoice. Option only shown when invoice is in status quote or invoice.

= 1.0.22 =
* tasks can now be marked as complete from the dashboard widget by clicking the green checkmark
* invoices can be marked as paid now directly from the client profile, without the need to go into the edit menu of the invoice

= 1.0.21 =
* invoice and quotations list now shows properly the region on top
* invoice and quotations list now includes buttons to filter by accounting region if the user has "general" set in the profile or is an admin

= 1.0.20 =
* open task widget adjusts now better
* symbol has been added next to name in client list to open the client profile in a new table
* if no tags have been created in the menu, it now shows a link to help create some in the client profile
* improved mobile style on client profile

= 1.0.19 =
* tested wordpress 6.4.3
* accounting regions can now be edited by the admin through the menu
* a tag system has been implemented 
* tags can be created through the stand-alone menu called "Tags" and applied to every client profile
* client list can be filtered by tags now
* open tasks dashboard widget now shows in two columns and can be filtered by due, started and all open tasks

= 1.0.18 =
* tested wordpress 6.4.3
* new design of open tasks widget on admin dashboard
* on task creating setting the start date now sets the end date at the same minimum
* task now adds to description who it was created by
* clientlist can now be exported for users with general access

= 1.0.17 =
* tested wordpress 6.4.3
* show more buttons in client profile now wider and with animation to not miss it
* my open tasks widget on the dashboard now showing start/end date and client name on button
* open task widget now shows tasks from oldest to newest

= 1.0.16 =
* tested wordpress 6.4.2
* Buttons added to filter by accounting region / country on clientlist if several are present for the users with general access

= 1.0.15 =
* tested wordpress 6.4.2
* mobile usability improved on taks and profile pages
* recent tasks now loads up to 3000 entries
* clientlist can now be sorted by action
* you can show now all entries in the client list

= 1.0.14 =
* tested wordpress 6.4.2
* mobile usability improved on all pages
* show more button in client profile made bigger and centered on overflowing content

= 1.0.13 =
* tested wordpress 6.4.2
* fixed error in action indication on client list
* error fix in user access of clients
* accounting region for clients has been improved. Regions currently have to be edited manually in table wp_eacraccountingregion (In every user that is an editor or shop-manager you can choose as admin by checkmark which regions to allow. Also always choose general in your own user first). ID 1 with regionname General should not be deleted.


= 1.0.12 =
* tested wordpress 6.4.2
* automatic redirect on editing client
* slashes properly escaped on client update and submit button now disable after adding / editing a client
* after adding a log you're being redirected now back to the client profile / clicking on submit now disables the button avoiding double submission / no slashes are added to special characters on logs anymore
* on client profile with more than eight entries in any section a "show more" button was implemented to not clutter up the first view of a client profile
* taks now save without added slashes and task submit button deactivates so no double submission is possible
* clients can now be filtered by status through a button above the list
* total of contacts is shown on the top right
* recent tasks are only 1000 now
* tasks are now searchable and sortable through datatables
* tasks can now be filtered by status through a button above the list
* slashes now properly escaped on quote/invoice items
* invoices can't be created anymore without items
* after saving a quote or invoice you're now redirected to its view to download it directly
* accounting region for clients has been added. Regions currently have to be edited manually in table wp_eacraccountingregion (In every user that is an editor or shop-manager you can choose as admin by checkmark which regions to allow)
* colour indicators have been added on the client list for suggested actions

= 1.0.11 =
* tested wordpress 6.2
* pdf generation for invoices improved
* dashboard widget includes formatting of task description now

= 1.0.10 =
* improved cron job performance
* tasks can now contain multimedia and html formatting, shown as well in overview list
* code preparations for extensions

= 1.0.9 =
* fixed cron jobs
* table design improved in lists
* captcha added to form
* tasks can now contain multimedia and html formatting

= 1.0.8 =
* fixed php8+ issues
* email reminder for due tasks added within 24hours before due time
* invoice layout improved

= 1.0.7 =
* Task notifications via email improved
* footer formatting adjusted in quotes and invoices

= 1.0.6 =
* quotes / invoices are now being converted to pdf and downloaded
* header and footer formatting fixed
* decimals in quote / invoice view fixed
* dashboard widget now available to all roles

= 1.0.5 =
* Added access to plugin for all roles with "edit_others_posts" capability

= 1.0.4 =
* Dashboard widget for open tasks added
* Total calculation of quotes and invoices now always has 2 decimals
* Task list improvement - If task belongs to a client, the task title is now clickable to get to client profile
* client source field problem solved on editing client, when value wasn't saved

= 1.0.3 =
* client profile improvements
* client list improvements

= 1.0.2 =
* problem fixed on saving Settings
* spanish translation added

= 1.0.1 =
* base version

