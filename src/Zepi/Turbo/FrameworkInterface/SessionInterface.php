<?php
/**
 * This interface defines a session. The request will accept 
 * sessions which are implementing this interface.
 * 
 * @package Zepi\Turbo\FrameworkInterface
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\FrameworkInterface;

/**
 * This interface defines a session. The request will accept 
 * sessions which are implementing this interface.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
interface SessionInterface
{
    /**
     * Returns true if this session has access to the given 
     * access level, return false otherwise.
     *
     * @access public
     * @param string $accessLevel
     * @return boolean
     */
    public function hasAccess($accessLevel);
}
