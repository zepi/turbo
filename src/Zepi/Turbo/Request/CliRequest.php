<?php
/**
 * The CliRequest representates a CLI framework request.s
 * 
 * @package Zepi\Turbo\Request
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Request;

/**
 * The CliRequest representates a CLI framework request.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class CliRequest extends RequestAbstract
{
    /**
     * Returns the delimitier, which is used to split the route
     * into parts.
     * The delimiter for the cli request is a space.
     * 
     * @access public
     * @return string
     */
    public function getRouteDelimiter()
    {
        return ' ';
    }
}
