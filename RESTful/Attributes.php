<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Usefull attributes for REST
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller {}

#[Attribute(Attribute::TARGET_CLASS)]
class RequestMapping {}

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class GetMapping {}

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class PostMapping {}

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class PutMapping {}

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class DeleteMapping {}

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam {}

#[Attribute(Attribute::TARGET_PARAMETER)]
class PathVariable {}

#[Attribute]
enum Validate {
    case INT;
    case FLOAT;
    case STRING;
}
