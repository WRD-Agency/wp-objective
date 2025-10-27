<?php
/**
 * Contains the Doc_Facades_Script.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Scripts;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionType;
use Wrd\WpObjective\Support\Facades\Facade;

/**
 * Script for generating doc comments for facades.
 */
class Doc_Facades_Script {
	/**
	 * Generate the doc comment.
	 *
	 * @param class-string<Facade> $class_name The class to generate the comment for.
	 *
	 * @return string
	 */
	public function get_method_comments( $class_name ): string {
		$reflection = new ReflectionClass( $class_name );
		$methods    = $reflection->getMethods();
		$str        = '/**' . PHP_EOL;

		$str .= " * Facade for accessing the '$class_name' instance in the plugin." . PHP_EOL;
		$str .= ' *' . PHP_EOL;
		$str .= ' * @autodoc facade' . PHP_EOL;
		$str .= ' *' . PHP_EOL;

		foreach ( $methods as $method ) {
			if ( $method->isConstructor() ) {
				continue;
			}

			$comment   = $method->getDocComment();
			$factory   = DocBlockFactory::createInstance();
			$doc_block = $comment ? $factory->create( $comment ) : null;

			$str .= ' * @method static ';
			$str .= $this->get_return_type( $doc_block, $method->getReturnType() ) . ' ' . $method->getName() . '(' . $this->get_params( $doc_block, $method->getParameters() ) . ') ' . $doc_block?->getSummary();
			$str .= PHP_EOL;
		}

		$str .= ' *' . PHP_EOL;
		$str .= ' * @see ' . $class_name . PHP_EOL;
		$str .= ' */';

		return $str;
	}

	/**
	 * Get the return type of a method.
	 *
	 * @param ?DocBlock       $doc_block The doc comment, if available.
	 *
	 * @param ?ReflectionType $type The return type as a fallback.
	 *
	 * @return string
	 */
	protected function get_return_type( ?DocBlock $doc_block, ?ReflectionType $type ): ?string {
		if ( $doc_block && $doc_block->hasTag( 'return' ) ) {
			/**
			 * The return tag.
			 *
			 * @var \phpDocumentor\Reflection\DocBlock\Tags\Return_
			 */
			$return_tag = $doc_block->getTagsByName( 'return' )[0];
			return (string) $return_tag->getType();
		} elseif ( $type ) {
			if ( class_exists( $type ) ) {
				return '\\' . $type;
			} else {
				return $type;
			}
		} else {
			return 'mixed';
		}
	}

	/**
	 * Get the method's parameters as a string.
	 *
	 * @param ?DocBlock             $doc_block The doc comment, if available.
	 *
	 * @param ReflectionParameter[] $parameters Paremeters to use as a fallback.
	 *
	 * @return string
	 */
	protected function get_params( ?DocBlock $doc_block, array $parameters ): string {
		$output = array();

		if ( $doc_block && $doc_block->hasTag( 'param' ) ) {
			foreach ( $doc_block->getTagsByName( 'param' ) as $tag ) {
				/**
				 * The parameter.
				 *
				 * @var \phpDocumentor\Reflection\DocBlock\Tags\Param
				 */
				$param = $tag;

				$output[] = (string) $param->getType() . ' $' . $param->getVariableName();
			}
		} elseif ( $parameters ) {
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
		}

		return implode( ', ', $output );
	}

	/**
	 * Get the return type from the facade accessor.
	 *
	 * @param class-string<Facade> $class_name The class to get the accessor for.
	 *
	 * @return string
	 */
	public function get_facade_accessor_return_type( $class_name ): string {
		return $class_name::get_facade_accessor();
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
			if ( str_starts_with( $namespace, $ns ) ) {
				$dir = str_replace( '\\', '/', str_replace( $ns, $path, $namespace ) );
				break;
			}
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

			$success = file_put_contents( $file_path, $new_code ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- File reading.

			if ( $success > 0 ) {
				echo 'Updated: ' . $file_path . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Console.
			} else {
				echo 'Failed: ' . $file_path . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Console.
			}
		}
	}
}
