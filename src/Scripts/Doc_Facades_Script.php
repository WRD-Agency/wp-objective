<?php
/**
 * Contains the Doc_Facades_Script.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Scripts;

use ReflectionClass;
use ReflectionMethod;
use Wrd\WpObjective\Support\Facades\Facade;

/**
 * Script for generating doc comments for facades.
 *
 * @author Ardhana <ardzz@indoxploit.or.id>
 */
class Doc_Facades_Script {
	/**
	 * Generate the doc comment.
	 *
	 * @param string $class The class to generate the comment for.
	 *
	 * @return string
	 */
	public function get_method_comments( string $class ): string {
		$reflection = new ReflectionClass( $class );
		$methods    = $reflection->getMethods();
		$str        = '/**' . PHP_EOL;

		$str .= " * Facade for accessing the '$class' instance in the container." . PHP_EOL;
		$str .= ' *' . PHP_EOL;
		$str .= ' * @autodoc facade' . PHP_EOL;
		$str .= ' *' . PHP_EOL;

		foreach ( $methods as $method ) {
			if ( $method->isConstructor() ) {
				continue;
			}

			$str .= ' * @method static ';
			$str .= $this->format_return_type( $method ) . $method->getName() . $this->format_params( $method->getParameters() );
			$str .= PHP_EOL;
		}

		$str .= ' *' . PHP_EOL;
		$str .= ' * @see ' . $class . PHP_EOL;
		$str .= ' */';

		return $str;
	}

	/**
	 * Get the return type of a method.
	 *
	 * @param ReflectionMethod $method The method to get return types for.
	 *
	 * @return string|null
	 */
	protected function format_return_type( ReflectionMethod $method ): ?string {
		$type = $method->getReturnType();

		if ( is_null( $type ) ) {
			return '';
		} elseif ( class_exists( $type ) ) {
			return '\\' . $type . ' ';
		} else {
			return $type . ' ';
		}
	}

	/**
	 * Process an array of parameters into a string.
	 *
	 * @param ReflectionParameter[] $parameters The parameters.
	 *
	 * @return string
	 */
	protected function format_params( array $parameters ): string {
		$output = array();

		foreach ( $parameters as $parameter ) {
			if ( $parameter->isOptional() ) {
				if ( $parameter->isDefaultValueAvailable() ) {
					if ( $parameter->isDefaultValueConstant() ) {
						$output[] = (string) $parameter->getType() . ' $' . $parameter->getName() . ' = ' . $parameter->getDefaultValueConstantName();
					} else {
						$output[] = (string) $parameter->getType() . ' $' . $parameter->getName() . ' = ' . str_replace( array( "\r", "\n" ), ' ', var_export( $parameter->getDefaultValue(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- Used for formatting, not debug.
					}
				}
			} else {
				$output[] = (string) $parameter->getType() . ' $' . $parameter->getName();
			}
		}
		return '(' . implode( ', ', $output ) . ')';
	}

	/**
	 * Get the return type from the facade accessor.
	 *
	 * @param string $class The class to get the accessor for.
	 *
	 * @return string
	 */
	public function get_facade_accessor_return_type( string $class ): string {
		return $class::get_facade_accessor();
	}

	/**
	 * Get the namespace facades are placed in.
	 *
	 * @return string
	 */
	public function get_facades_namespace(): string {
		return join( '\\', array_slice( explode( '\\', Facade::class ), 0, -1 ) );
	}

	/**
	 * Get the PSR4 namespaces.
	 *
	 * @return array<string, string>
	 */
	public function get_psr4_namespaces() {
		$path     = getcwd() . '/composer.json';
		$json     = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- File reading.
		$composer = json_decode( $json );

		return $composer->autoload->{'psr-4'};
	}

	/**
	 * Get a list of the facade classes.
	 *
	 * @return string[]
	 */
	public function get_facades(): array {
		$namespace = $this->get_facades_namespace();
		$psr4      = $this->get_psr4_namespaces();

		$dir = $namespace;

		foreach ( $psr4 as $ns => $path ) {
			$dir = str_replace( '\\', '/', str_replace( $ns, $path, $namespace ) );
		}

		$map   = array();
		$files = array_diff( scandir( $dir ), array( '..', '.' ) );

		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;

			if ( ! str_ends_with( $path, '.php' ) ) {
				continue;
			}

			$class_name = $namespace . '\\' . str_replace( '.php', '', $file );

			if ( Facade::class === $class_name ) {
				// Don't include base class.
				continue;
			}

			$map[ $path ] = $class_name;
		}

		return $map;
	}

	/**
	 * Replaces a comment in PHP code by searching for a comment with the given target phrase.
	 *
	 * @param string $code Code to search in.
	 *
	 * @param string $target The target to identify the comment.
	 *
	 * @param string $comment The new comment.
	 *
	 * @return string
	 */
	public function replace_comment( string $code, string $target, string $comment ): string {
		$tokens         = token_get_all( $code );
		$comment_tokens = array( T_DOC_COMMENT, T_COMMENT );

		$new_code = '';

		foreach ( $tokens as $token ) {
			if ( ! is_array( $token ) ) {
				// Not a comment.
				$new_code .= $token;
				continue;
			}

			if ( ! in_array( $token[0], $comment_tokens, true ) ) {
				// Not a comment.
				$new_code .= $token[1];
				continue;
			}

			if ( ! str_contains( $token[1], $target ) ) {
				// Not the target.
				$new_code .= $token[1];
				continue;
			}

			$new_code .= $comment;
		}

		return $new_code;
	}

	/**
	 * Run the script.
	 *
	 * @return void
	 */
	public static function handle() {
		$generator = new static();
		$facades   = $generator->get_facades( '' );

		foreach ( $facades as $file_path => $facade_class_name ) {
			$instance_type = $generator->get_facade_accessor_return_type( $facade_class_name );

			if ( ! class_exists( $instance_type ) ) {
				continue;
			}

			$target      = '@autodoc facade';
			$new_comment = $generator->get_method_comments( $instance_type );
			$old_code    = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- File reading.

			$new_code = $generator->replace_comment( $old_code, $target, $new_comment );

			if ( $new_code === $old_code ) {
				echo 'Unchanged: ' . $file_path . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Console.
				continue;
			}

			$success = file_put_contents( $file_path, $new_code ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- File reading.

			if ( $success > 0 ) {
				echo 'Updated: ' . $file_path . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Console.
			} else {
				echo 'Failed: ' . $file_path . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Console.
			}
		}
	}
}
