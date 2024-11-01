=== User Registration Approval for Formidable Forms ===
Contributors: tentenbiz
Donate link: https://wproot.dev/tips-and-donation/
Tags: approve, admin, user, user approval, user management, user registration, registration, register, sign up, formidable, formidable forms, formidable registration, forms, form
Requires at least: 4.5
Tested up to: 5.1.1
Requires PHP: 5.3
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Allows admin to easily approve or reject user registrations that use forms built with Formidable Forms.

== Description ==

This plugin is for those who want to use or have already been using Formidable Forms for user registration and want a simple way to hold the registrations for admin approval.

**Tested with Formidable Forms up to:** Version 3.06.05
**Tested with Formidable Registration up to:** Version 2.02.01

**The Flow**

1. Admin creates user registration application form using Formidable Forms plugin.

2. User registers an account using the above form.

3. If admin turns on approval for the above form, user who registers will automatically have the role of *pending*, and they cannot login until admin approves their registration.

4. If admin approves a user registration, that user's role will change from *pending* to what is already set on the form setting. The user will be notified by email to let them know their registration has been approved.

5. Likewise, if admin rejects a user registration, the user will be notified by email to let them know their registration has been rejected, and the user will be deleted.
*(Note: You can still see rejected user's details in Entries section if you enable entries for your form, even after user has been deleted. Disable entries to not store user details after user is deleted.)*

**Plugin Dependency**

This plugin is an add-on for Formidable Forms. The following plugins are required for it to work:

* [Formidable Forms](https://wordpress.org/plugins/formidable/) (free or PRO, but you usually have to get PRO to get Formidable Registration plugin too)
* [Formidable Registration](https://formidableforms.com/downloads/user-registration/) (also called WordPress User Registration add-on for Formidable Forms)

**Support Questions, Bug Reports**

Support questions should ideally be posted here on WordPress.org forum. Other types of questions, bug reports, inquiry relating to the plugin, should also be posted on the WordPress.org forum. 

**Plugin Customization**

If you need further plugin customization to suit your website, please [reach out to the developer directly](https://wproot.dev/contact/).

== Installation ==

1.) You must install and activate the following plugins first (if you haven't already):

* [Formidable Forms](https://wordpress.org/plugins/formidable/) (free or PRO, but you usually have to get PRO to get Formidable Registration plugin too)
* [Formidable Registration](https://formidableforms.com/downloads/user-registration/) (also called WordPress User Registration add-on for Formidable Forms)

2.) Upload `wp-ohs-formidable-forms-user-approval` plugin folder to the `/wp-content/plugins/` directory, or go to `Dashboard>Plugins>Add New` and type in the plugin name in the search field.

3.) Install and activate.

4.) Go to `Formidable>Forms>Settings>General` and scroll down until you see the setting under User Registration Approval Setting.

5.) Set accordingly.

6.) You can review pending users under `Users>Pending Approvals`.

== Frequently Asked Questions ==

= What if a user is added manually via WordPress dashboard by admin, will this user get listed under pending approvals too? =

No. Only users that register using forms built with Formidable Forms and with approval mode turned on will need admin approval.

= If I don't use form builder for my user registration and I have this plugin active, will the users still need to be approved? =

No. Only users that register using forms built with Formidable Forms and with approval mode turned on will need admin approval.

= Is this plugin compatible with Gravity Forms, Ninja Forms, or any other form builder plugins? =

Not right now. This plugin was made specifically for Formidable Forms. 

= How can I customize email notifications sent to users? =

A plan to have this feature in the next update is in the works. If you can't wait for the next update, please [contact me directly](https://wproot.dev/contact/) so that I can customize it for your site. 

= The feature I need for my site is not in this plugin, how can I request for it? =

You can post it on WordPress.org forum. 

== Screenshots ==

1. Pending users will be listed under Users>Pending Approvals.
2. Admin clicks a button to approve or reject user. Admin can also see which form was used by user to submit the registration (in the case that a site might have more than one registration application forms).
3. This is the setting where admin enables user registration approval for a form. 
4. A clear view of the General setting menu and the new option that the plugin adds. 
5. This screenshot demonstrates pending user not being able to login before their account is approved by admin.
6. Example of notification email sent to user after their account has been approved.

== Changelog ==

= 1.0.1 =
* Maintenance release.
* Function and variable prefix change, in-plugin texts change. 

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
* Maintenance release.

= 1.0.0 =
* Initial release.