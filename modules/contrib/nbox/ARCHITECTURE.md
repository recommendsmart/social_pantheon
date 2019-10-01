# Nbox Architecture

## Fields
The module defines a custom field type "Recipient", which is a user reference
field but allows to set the visibility of the field to "To", "Cc" and "Bcc".

## Entities
All entities will not be revisionable and will not be translatable, as there is
no clear use case for either within the message / mail context.

### Nbox
These are the actual messages being sent.
This entity is fiedable.
There will be 1 bundle shipped with the contrib module "message". This could be
extended with other bundles like "bulk message".
Bundles are handled through the `NboxType` entity.

### NboxThread
This entity mainly serves as an ID that messages and metadata can reference to
track the message thread.

### NboxMetadata
Contains the thread metadata per user needed to build mailboxes.
It tracks attributes like 'read status', 'starred', 'last message', etc.
The user ID / thread ID combination should always be unique and is validated in
`UniqueThreadUserValidator`.

### NboxFolder
Part of the nbox_folder submodule, personal folders that users can move their
message threads into.
This entity is fiedable, but default there are no defined fields.

## Plugins

### Mailbox
The `Mailbox` plugin defines the various mailboxes (inbox, sent, trash, etc).
The plugin primary provides building blocks (in the form of `MailboxRule` value
objects) to create queries in order to build a mailbox.
