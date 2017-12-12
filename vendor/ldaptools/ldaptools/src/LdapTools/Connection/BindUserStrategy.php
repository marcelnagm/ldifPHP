<?php
/**
 * This file is part of the LdapTools package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LdapTools\Connection;

use LdapTools\Connection\AD\ADBindUserStrategy;
use LdapTools\DomainConfiguration;

/**
 * Determines how to format the username for a bind attempt.
 *
 * @author Chad Sikorra <Chad.Sikorra@gmail.com>
 */
class BindUserStrategy
{
    /**
     * @var string The default bind format.
     */
    protected $bindFormat = '%username%';

    /**
     * @var string[] The parameters to be replaced in the username string.
     */
    protected $params = [
        '/%username%/i',
        '/%domainname%/i',
    ];

    /**
     * @var DomainConfiguration
     */
    protected $config;

    /**
     * @param DomainConfiguration $config
     */
    public function __construct(DomainConfiguration $config)
    {
        $this->config = $config;

        if (!empty($config->getBindFormat())) {
            $this->bindFormat = $config->getBindFormat();
        }
    }

    /**
     * Given the LDAP type, determine the BindStrategy to use.
     *
     * @param DomainConfiguration $config
     * @return ADBindUserStrategy|BindUserStrategy
     */
    public static function getInstance(DomainConfiguration $config)
    {
        if (LdapConnection::TYPE_AD == $config->getLdapType()) {
            return new ADBindUserStrategy($config);
        } else {
            return new self($config);
        }
    }

    /**
     * Determine the format to use for a bind username going to LDAP.
     *
     * @param string $username
     * @return string
     */
    public function getUsername($username)
    {
        if ($this->isValidUserDn($username)) {
            return $username;
        }
        $replacements = [
            $username,
            $this->config->getDomainName(),
        ];

        return preg_replace($this->params, $replacements, $this->bindFormat);
    }

    /**
     * @param string $dn
     * @return bool
     */
    protected function isValidUserDn($dn)
    {
        return (($pieces = ldap_explode_dn($dn, 1)) && isset($pieces['count']) && $pieces['count'] > 2);
    }
}
