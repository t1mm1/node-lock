# Module Node Lock

### Description
Module provide:
- Lock and unlock content nodes
- Configure per content type
- View lock details directly in Content Views

Enable the module and navigate to the settings page:

**Configuration → Content authoring → Node lock settings**

```
admin/config/content/node-lock
```
Enable the service and configure it for specific node types.

In the node form sidebar, the Node lock section allows you to check lock information.

To view all lock nodes, go to **Structure → Node lock**:

```
admin/structure/node-lock
```
On this page you can review all locked nodes and remove their "lock" mark directly from the list.


### Media

Settings form

<img src="assets/settings_form.png" alt="Settings form">

Node edit form (advanced section)

<img src="assets/node_edit_form_advanced.png" alt="Node edit form">

Node edit form (buttons)

<img src="assets/node_edit_form_buttons.png" alt="Node edit form">

Node lock form

<img src="assets/lock_form.png" alt="Node lock form">

Node unlock form

<img src="assets/unlock_form.png" alt="Node unlock form">

Content view expiration details

<img src="assets/content_view_expiration_details.png" alt="Content view expiration details">

List of locks

<img src="assets/list.png" alt="List of locks">

### Permissions
Module provide permissions *node lock bypass unlock* and *administer node lock configuration*:
- node lock bypass unlock: allow to unlock any node
- administer node lock configuration: allow to change default settings for each node type

### Install
Dependencies:
```
dependencies:
- drupal:node
- drupal:user
```

Install this module using the standard Drupal module installation process.

### Important

Supports D11+ only.
