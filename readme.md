# WP-Objective

> :warning: This package is currently experimental.

Objective is a batteries-included framework for building consistent WordPress plugins.

It includes setup for many common plugin requirements - such as Custom Post Types, Admin Actions, HTTP Clients, Rest API Development, Migrations - all wrapped in a Laravel inspired API.

Created by Kyle Cooper at [WRD Studio](https://wrd.studio).

## Installation

This project is available for installation via Composer.

```bash
composer require wrd/wp-objective
```

## Getting Started

Objective uses a global plugin which serves as a container to manage global objects. You can use the `plugin()` utility to grab the current instance.

The plugin is a container responsible for instantiating objects & injecting their dependencies, using it's bindings to do so.

### Bindings

You can also register bindings, allowing every part of Objective to use the correct object/class when looking for it. Objective comes with a range a default bindings to provide sensible defaults and will also look for unbound classes directly when needed.

You can use this functionality override the default `Plugin` instance or any other piece of functionality. For example, by switching the `Logging_Manager` to store to the database.

> :warning: You should always bind your own extension of the base `Plugin` class. This allows Objective to keep track of your plugin file/directory.

### Service Providers

You can add in your service providers which come with convenient methods for the WordPress plugin lifecycle. Your service providers allow ways for separate 'components' too hook into the plugin lifecycle without needing to define loose hooks.

### Configuration

You can configure your plugin's bindings & service providers by create an extension of the `Plugin` class, like below. In your plugin's entrypoint file you can then create a global instance and boot it.

```php
// my-plugin/src/Foundation/My_Plugin.php
use Wrd\WpObjective\Foundation\Plugin;

class My_Plugin extends Plugin {
	/**
	 * Files to include when the plugin is loaded.
	 *
	 * @var string[]
	 */
	public array $files = array(
		// Any files you want to include outside of your typical autoloading.
	);

	/**
	 * Bindings to bind upon boot.
	 *
	 * @var array<string, class-string<Service_Provider>|Service_Provider>
	 */
	public array $bindings = array(
		// Any bindings you want to configure.
		// For example, changing the defaut logger:
		Log_Manager::class => My_Log_Manager::class,
	);

	/**
	 * Service providers to register upon boot.
	 *
	 * @var (class-string<Service_Provider>|Service_Provider)[]
	 */
	public array $providers = array(
		// Any service providers you want to provide.
		My_Post_State::class,
	);
}
```

```php
// my-plugin/my-plugin.php
use MyVendor\MyPlugin\Foundation\My_Plugin;

require_once 'vendor/autoload.php';

My_Plugin::create_global( __FILE__, __DIR__ )->boot();
```

### Plugin Class

Your plugin should include your own extension of the base `Plugin` class. This is the only item that is required be bound in your config.

The plugin class is primarily responsible for storing your plugin file, directory and version.

It also comes with a range of pre-built hooks for you enqueue assets or spin off any effects need (though it's recommended to use service providers if possible).

```php
// my-plugin/src/Plugin.php

namespace MyPlugin;

use Wrd\WpObjective\Foundation\Plugin;

/**
 * Base implementation for a plugin.
 */
class My_Plugin extends Plugin {
	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	public string $version = '1.0.0';

	/**
	 * Files to include when the plugin is loaded.
	 *
	 * @var string[]
	 */
	public array $files = array(
		// ...
	);

	/**
	 * Load in any additional files.
	 *
	 * @return void
	 */
	public function includes() {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the load.
	 *
	 * @return void
	 */
	public function boot(): void {
		// You should call parent::boot to ensure 'includes' is called and any files in your 'files' property are included.
		parent::boot();

		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		// You should call parent::init to ensure the 'admin', 'api' and 'public' methods get called.
		parent::init();

		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook in an admin screen.
	 *
	 * @return void
	 */
	public function admin(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'rest_api_init' hook in the rest API.
	 *
	 * @return void
	 */
	public function api(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook in an public screen.
	 *
	 * @return void
	 */
	public function public(): void {
		// This page left intentionally blank.
	}
}
```

### Facades

Many of the Plugin's bindings can be easily accessed via Laravel-style Facades. Simply call these statically and your call will be mapped to the function in the current instance.

```php
Plugin::version();

Flash::success( __('Changes saved successfully.') );

Log::add( message: "Action initiated" );

Migration::add_migration( My_Migration::class );

Settings::set( 'admin_email', 'hello@wrd.agency' );
```

## Deeper Dive

### Actions

It's common for plugins to need to allow their users to perform actions that can be triggered from WordPress. Objective includes the 'Action' class to make this easier.

A permissions callback is required to ensure the user is allowed to perform the action.

You can provide the URL the user should be redirected to upon successful completion.

You can provide arguments, in the same format as the REST API. These will be sanitized & validated before they react your `handle` function.

Define your action like so,

```php
/**
 * Action for toggling between dark & light theme.
 */
class Toggle_Theme_Action extends Action {
	/**
	 * Called to check if the current user can undertake this action.
	 *
	 * @return WP_Error|bool
	 */
	public function permissions_callback(): WP_Error | bool {
		return current_user_can( "manage_options" );
	}

	/**
	 * Get the redirection URL upon success.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return string
	 */
	public function get_destination( array $args ): string {
		return admin_url();
	}

	/**
	 * Get the arguments for the action.
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return array(
			'theme' => [
				'type' => 'enum',
				'enum' => array(
					'light',
					'dark',
					'context'
				),
			]
		);
	}

	/**
	 * Execute the action.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return WP_Error|null
	 */
	public function handle( array $args ): WP_Error|null {
		Settings::set( $t )
	}
}
```

You'll need to make sure you either bind or provide your class. Objects that inherit from the `Service_Provider` class (which Action does) will automatically be provided when they're bound.

```php
class My_Plugin extends Plugin {
	public $providers = [
		Toggle_Theme_Action::class
	]
}

// OR

plugin()->provide( Toggle_Theme_Action::class );
```

### Collections

Many of Objective's methods will return a Collection object, rather than an array. This is a class which includes many utility functions for manipulating the data in a fluent API.

You can create your own collection like so,

```php

new Collection( array( 1, 2, 3 ) );

// OR

Collection::from( array( 1, 2, 3 ) );

```

If you need to access the raw array underneath, you can always call `$collection->all()`.

### Migrations

Migrations allow you to lay out the steps your plugin needs to make for every new version. For example, you might want to create a database table in version 1.0.0 and then you may need to add an additional column to the table in version 1.1.0.

You simply define a migration for each new plugin version that needs to be migrated and Objective will handle running through them upon activation and storing the version currently migrated to.

```php
/**
 * Contains the Migration class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation\Migrate;

use Wrd\WpObjective\Database\Database_Manager;

/**
 * Migrates to version 1.0.0
 */
class Migration_1_0_0 {
	/**
	 * Get version of the plugin this migration sets up for.
	 *
	 * @return string
	 */
	public function get_version(): string{
		return '1.0.0';
	}

	/**
	 * Run the migration.
	 *
	 * @return void
	 */
	public function up(){
		// Create your custom database table, etc.
	}
}
```

### Learn More

More documentation is coming soon. In the meantime dive into the code to learn more about,

- Post States
- Flashes
- List Tables
- Metaboxes
- Notices
- API Routes
- API Endpoints
- API Objects
- Emails
- HTTP Requests, Responses & Clients
- Money & Currency
- Post Types
- Posts
- Templates
- Settings
- HTML Building
