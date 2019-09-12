#####################################################################
##                  Views Any Route Button README                  ##
#####################################################################

### INTRODUCTION ###

Views Any Route generates buttons in Views using Drupal's routing
system, in which a user enters the route and parameters, and Views
tokenization can provide parameter values.

The main use case for this module is to leverage Drupal's route
access checking, so that buttons show or hide based on access
conditions. While Views Add Button focused exclusively on creating
entities in such a way that a user did not need to understand Drupal
routes, Views Any Route lets a developer leverage every possible
route in Drupal.

### REQUIREMENTS ###

 - Views
 - Token

### INSTALLATION ###

Views Any Route Button installs like most Drupal modules:

# Composer

 - Go to your project root, and require drupal/views_any_route
   - composer require drupal/views_any_route
 - Go to your modules page (Extend) and enable. No further setup is
   needed

# Download

 - Download the tar or zip file to your modules/contrib directory,
   and extract
 - Go to your modules page (Extend) and enable. No further setup is
   needed


### CONFIGURATION ###

Once installed, in the Views header, footer, No Results, and fields
an option called "Global: Any Route Button" will be made available.
The button has the following options:

# Route
Here, you may enter the Drupal route from which we will build the
link.

# Route Parameters
You may enter one parameter per line, in the format "key=value" .
If the route's path is /node/{node}/edit for example, the key would
be "node" , and the value would be a node ID. So you would enter
the following in the box to edit a node with nid 3: node=3

# Access Plugin
Which plugin to use for access checking.

# URL Plugin
Which plugin to use for generating the URL.

# Link Plugin
Which plugin to use for generating the link.

# Button Text
The text to be shown for the generated link.

# Button Classes
The CSS classes to be used for styling the generated link.

# Query String
A query string to add to the generated URL. Do not add the '?' to
the string.

# Button Options
This will populate the $options array when the system calls
URL::fromRoute(). Enter one option per line, in a key=value format.
Note that $options['query'] is handled by the Query String field.

# Button Attributes
This will populate the $options['attributes'] array for the URL.
Enter in key=value format.
Note that $options['attributes']['class'] is handled by the

# More options
Options common to other Views area/field plugins (destination
parameter, enabling the use of tokens, etc.) are also available for
the Any Route Button.

### Creating a Plugin ###

Please review the Node, Taxonomy, and User plugins found under
/src/Plugin/views_any_route to supplement this README.

# Placement
Views Any Route Button classes should go in /src/Plugin/views_any_route

# Annotation

Views Any Route Plugins are annotated as @ViewsAnyRoute, and have
these parameters:

 - id: textual ID of the plugin, usually views_any_route_[entity_type]
 - label = a translated (@Translation()) string of a human-readable
   label for the plugin

# Class Functions
Your plugin class should have the following four functions:
 - description: Provides a description of the plugin.
 - checkAccess: Return TRUE/FALSE regarding whether the user has
    access to use the button.
 - generateUrl: Generate a Drupal\Core\Url that points to the button
    path.
 - generateLink: Generates the button as a Drupal\Core\Link object.

Note that checkAccess, generateUrl, and generateLink should be static
functions.
