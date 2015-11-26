<?php

namespace TestModule;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Response\Response;
use \Zepi\Web\Test\Exception;
use Zepi\Turbo\FrameworkInterface\SessionInterface;

class TestSession implements SessionInterface
{
    /**
     * Returns true if this session has access to the given
     * access level, return false otherwise.
     *
     * @access public
     * @param string $accessLevel
     * @return boolean
     */
    public function hasAccess($accessLevel)
    {
        return true;
    }
}
