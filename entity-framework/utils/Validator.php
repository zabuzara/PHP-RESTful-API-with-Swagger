<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Simplae AttributeValidator class
 * for validate attributes with given values
 */
class AttributeValidator {
    /**
     * Validates if #[NOT_NULL] attribute is set
     * and property can't be null
     *
     * @param string $entity_name
     * @param string $property_name
     * @return void
     */
    static function validate_entity_property(string $entity_name, string $property_name) {
        $reflection = new ReflectionProperty($entity_name, $property_name);
        $nullable = $reflection->getType()->allowsNull();

        foreach ($reflection->getAttributes() as $attr) {
            $attr_name = $attr->getName();
            if ($attr_name === NOT_NULL::class && $nullable) {
                echo('property "'.$property_name.'" not matched to NOT_NULL attribute, check it');
            }
        }
    }

    /**
     * Validates if #[NOT_NULL] attribute is set
     * and property can't be null. other attributes
     * will be checks if value is valid depend on
     * attributes. As an example:
     * #[VARCHAR(10)] allows just maximal value length
     * of 10 characters.
     *
     * @param string $entity_name
     * @param string $property_name
     * @param mixed $value
     * @return void
     */
    static function validate_value_depend_on_attribute (string $entity_name, string $property_name, mixed $value) {
        $reflection = new ReflectionProperty($entity_name, $property_name);
        $nullable = $reflection->getType()->allowsNull();

        $attrs = $reflection->getAttributes();
        $attr_name = array_map(fn($attr) => $attr->getName(), $attrs);
        $attr_args = array_map(fn($attr) => $attr->getArguments(), $attrs);

        for ($i = 0; $i < count($attrs); $i++) {
            if ($attr_name[$i] === NOT_NULL::class && $nullable) {
                echo ('property "'.$property_name.'" not matched to NOT_NULL attribute, check it');
            } else if ($attr_name[$i] === \Types\String\T_ENUM::class) {
                $attr_args = array_map(fn($item) => '"'.$item.'"', $attr_args[0]);
                if (!in_array($value, $attr_args)) {
                    echo ('property "'.$property_name.'" must be on of this values ("'.join('", "', $attr_args).'"), check it');
                }
            } else {
                if (!empty($attr_args[$i]) && strlen($value ?? 0) > intval($attr_args[$i][0])) 
                    echo('invalid character length '.strlen($value ?? 0).' of value for property "'.$property_name.'". Maximal allowed value length is '. $attr_args[$i][0].' characters.');
            }
        }
    }
}