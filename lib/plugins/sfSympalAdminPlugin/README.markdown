sfSympalAdminPlugin
===================

This plugin adds various admin-functionalities including:

 * A custom admin theme (`sympal_admin`)
 * An admin dashboard (admin homepage)
 * A configurable admin menu while logged in
 * An admin "theme", which can be configured to be used automatically
   on any "admin" module (requires `sfSympalMenuPlugin`)
 * An configurable admin module for modifying `app.yml` config beneath
   the `sympal_config` key

Configuration
-------------

 * `config_form_class`

    The class name of the form that that allows for editing of configuration
    (`app.yml`) values.
    
    _Default_: `sfSympalConfigForm`

 * `admin_theme`

    The name of the theme that will be automatically applied to any module
    listed in the `admin_modules` config array.
   
    _Default_: `admin`

 * `admin_modules`

    An array of module names that should automatically use the admin theme
    defined in the `admin_theme` config.

    _Example_:

        admin_modules:
          my_module:   *

 * `admin_module_web_dir`

    The path to the web directory to use as the root directory for images
    and css used by the admin generator. This value replaces the
    `sf_admin_module_web_dir` symfony config value;
    
    _Default_: `/sfSympalAdminPlugin/admin`

 * `default_admin_generator_theme`

    The admin generator theme to use for all admin generated modules.
    This works because the `generator.yml` admin generation files reference
    this config value instead of hardcoding a theme name.
    
    _Default_: `sympal_admin`

 * `admin_generator_class`

    The generator class to use when generating admin modules.
    
    _Default_: `sfSympalDoctrineGenerator`

Events
------

 * sympal.load_admin_menu
 * sympal.load_config_form
 * sympal.pre_save_config_form
 * sympal.post_save_config_form
 * admin.pre_execute
 * admin.build_query
 * admin.delete_object
 * admin.save_object