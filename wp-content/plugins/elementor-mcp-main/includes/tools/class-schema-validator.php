<?php
/**
 * JSON Schema Validator
 *
 * Validates data against JSON schema definitions.
 * Uses justinrainbow/json-schema library for validation.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Schema Validator Class
 *
 * Provides JSON schema validation for tool inputs.
 * Wraps the justinrainbow/json-schema library with WordPress-friendly interface.
 *
 * @since 1.0.0
 */
class Schema_Validator {

	/**
	 * Validation errors.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $errors = array();

	/**
	 * JSON Schema Validator instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Validator|null
	 */
	private $validator = null;

	/**
	 * Constructor.
	 *
	 * Initialize the validator.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->errors = array();
	}

	/**
	 * Validate data against schema.
	 *
	 * Validates the provided data against a JSON schema.
	 * Stores any validation errors for retrieval.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $data   Data to validate.
	 * @param array $schema JSON schema to validate against.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate( $data, $schema ) {
		$this->errors = array();

		// Check if json-schema library is available
		if ( ! class_exists( 'JsonSchema\Validator' ) ) {
			return $this->fallback_validate( $data, $schema );
		}

		try {
			// Convert data to object for validation
			$data_object = json_decode( wp_json_encode( $data ) );
			$schema_object = json_decode( wp_json_encode( $schema ) );

			// Create validator
			$this->validator = new Validator();

			// Validate
			$this->validator->validate(
				$data_object,
				$schema_object,
				Constraint::CHECK_MODE_TYPE_CAST
			);

			// Check if valid
			if ( ! $this->validator->isValid() ) {
				foreach ( $this->validator->getErrors() as $error ) {
					$this->errors[] = array(
						'property' => $error['property'],
						'message'  => $error['message'],
						'constraint' => $error['constraint'] ?? '',
					);
				}
				return false;
			}

			return true;

		} catch ( \Exception $e ) {
			$this->errors[] = array(
				'property' => '',
				'message'  => 'Validation error: ' . $e->getMessage(),
				'constraint' => 'exception',
			);
			return false;
		}
	}

	/**
	 * Fallback validation when json-schema library is not available.
	 *
	 * Provides basic validation for common schema types.
	 * This is a simplified validator for when the library is not installed.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param mixed $data   Data to validate.
	 * @param array $schema JSON schema to validate against.
	 * @return bool True if valid, false otherwise.
	 */
	private function fallback_validate( $data, $schema ) {
		// Validate required properties
		if ( isset( $schema['required'] ) && is_array( $schema['required'] ) ) {
			foreach ( $schema['required'] as $required_field ) {
				if ( ! isset( $data[ $required_field ] ) ) {
					$this->errors[] = array(
						'property' => $required_field,
						'message'  => "Required property '{$required_field}' is missing",
						'constraint' => 'required',
					);
				}
			}
		}

		// Validate properties if present
		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $data as $key => $value ) {
				if ( ! isset( $schema['properties'][ $key ] ) ) {
					// Allow additional properties by default
					if ( isset( $schema['additionalProperties'] ) && false === $schema['additionalProperties'] ) {
						$this->errors[] = array(
							'property' => $key,
							'message'  => "Additional property '{$key}' is not allowed",
							'constraint' => 'additionalProperties',
						);
					}
					continue;
				}

				$property_schema = $schema['properties'][ $key ];
				$this->validate_property( $key, $value, $property_schema );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Validate a single property.
	 *
	 * Validates a property against its schema definition.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property value.
	 * @param array  $schema        Property schema.
	 */
	private function validate_property( $property_name, $value, $schema ) {
		// Type validation
		if ( isset( $schema['type'] ) ) {
			$valid_type = $this->validate_type( $value, $schema['type'] );
			if ( ! $valid_type ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be of type {$schema['type']}",
					'constraint' => 'type',
				);
				return;
			}
		}

		// Enum validation
		if ( isset( $schema['enum'] ) && is_array( $schema['enum'] ) ) {
			if ( ! in_array( $value, $schema['enum'], true ) ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be one of: " . implode( ', ', $schema['enum'] ),
					'constraint' => 'enum',
				);
			}
		}

		// String validations
		if ( 'string' === $schema['type'] ) {
			if ( isset( $schema['minLength'] ) && strlen( $value ) < $schema['minLength'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be at least {$schema['minLength']} characters",
					'constraint' => 'minLength',
				);
			}
			if ( isset( $schema['maxLength'] ) && strlen( $value ) > $schema['maxLength'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be at most {$schema['maxLength']} characters",
					'constraint' => 'maxLength',
				);
			}
			if ( isset( $schema['pattern'] ) && ! preg_match( '/' . $schema['pattern'] . '/', $value ) ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' does not match required pattern",
					'constraint' => 'pattern',
				);
			}
		}

		// Number validations
		if ( in_array( $schema['type'], array( 'integer', 'number' ), true ) ) {
			if ( isset( $schema['minimum'] ) && $value < $schema['minimum'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be at least {$schema['minimum']}",
					'constraint' => 'minimum',
				);
			}
			if ( isset( $schema['maximum'] ) && $value > $schema['maximum'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must be at most {$schema['maximum']}",
					'constraint' => 'maximum',
				);
			}
		}

		// Array validations
		if ( 'array' === $schema['type'] && is_array( $value ) ) {
			if ( isset( $schema['minItems'] ) && count( $value ) < $schema['minItems'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must have at least {$schema['minItems']} items",
					'constraint' => 'minItems',
				);
			}
			if ( isset( $schema['maxItems'] ) && count( $value ) > $schema['maxItems'] ) {
				$this->errors[] = array(
					'property' => $property_name,
					'message'  => "Property '{$property_name}' must have at most {$schema['maxItems']} items",
					'constraint' => 'maxItems',
				);
			}
		}
	}

	/**
	 * Validate type.
	 *
	 * Check if value matches the expected type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param mixed  $value Value to check.
	 * @param string $type  Expected type.
	 * @return bool True if type matches, false otherwise.
	 */
	private function validate_type( $value, $type ) {
		switch ( $type ) {
			case 'string':
				return is_string( $value );
			case 'integer':
				return is_int( $value );
			case 'number':
				return is_numeric( $value );
			case 'boolean':
				return is_bool( $value );
			case 'array':
				return is_array( $value );
			case 'object':
				return is_object( $value ) || is_array( $value );
			case 'null':
				return is_null( $value );
			default:
				return true;
		}
	}

	/**
	 * Get validation errors.
	 *
	 * Retrieve all validation errors from the last validation.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of validation errors.
	 */
	public function get_validation_errors() {
		return $this->errors;
	}

	/**
	 * Clear validation errors.
	 *
	 * Reset the errors array.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function clear_errors() {
		$this->errors = array();
	}

	/**
	 * Get formatted error message.
	 *
	 * Get a human-readable error message from validation errors.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Formatted error message.
	 */
	public function get_error_message() {
		if ( empty( $this->errors ) ) {
			return '';
		}

		$messages = array();
		foreach ( $this->errors as $error ) {
			if ( ! empty( $error['property'] ) ) {
				$messages[] = "{$error['property']}: {$error['message']}";
			} else {
				$messages[] = $error['message'];
			}
		}

		return implode( '; ', $messages );
	}
}
