# Nbox
The Nbox module provides an full fledged internal mail/messaging system.

## Modules
The this module ships with the following modules:
### Nbox
Contains the core functionalities and entities for sending and receiving
messages, working with threads and all mailbox functions.
The module is build as an API first module, you will need to enable at least one
other module that provides an interface.
### Nbox UI
Provides a user interface and provides standard Drupal front-end functionalities
such as:
* Views integration (mailbox, threads)
* Blocks
* Forms
* Twig templates
### Nbox folders
Contains the core functionalities for personal folders and defines the folder
entity.

## Installation
Todo...

## Uninstall
Because of a [core bug](https://www.drupal.org/project/drupal/issues/2871486) 
you can't uninstall a module that defines a field type and an entity that 
implements this field type.
To delete this module, delete all entities, delete the "message" nbox bunlde,
uninstall.
Todo: automate this process.

## Architecture
[ARCHITECTURE.md](ARCHITECTURE.md) Contains the technical documentation on
fields, entities and plugins.
