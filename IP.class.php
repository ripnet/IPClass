<?php
/**
 * IP Class
 * 
 * This class can be used for help in IP calculation in PHP scripts.
 *
 * @package IPClass
 * @license http://www.eclipse.org/legal/epl-v10.html Eclipse Public License
 * @author Tom Young <ripnet@gmail.com>
 * @version 1.0
 * @copyright Copyright (c) 2011, Tom Young
 */
class IP {
    
    private $ip;
    private $mask;

    /**
     * @param $ip IP Address (with or without slash notation prefix)
     * @param null $mask Subnet Mask
     * @throws Exception
     */
    public function __construct($ip, $mask = null)
    {
        if ($mask === null)
        {
            list($ip, $cidr) = explode('/', $ip);
            if ($cidr != '')
            {
                $this->setCIDR($cidr);
            }
        } else {
            $this->setMask($mask);
        }

        $this->setIp($ip);
    }
    
    /**
     * Check to see if a given IP address is valid
     * 
     * @param type $ip IP Address
     * @return type boolean
     */
    public static function IPCheck($ip)
    {
        return ($ip == long2ip(ip2long($ip)));
    }

    /**
     * Return the first usable IP address in a subnet.
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return int|string
     */
    public function getFirstUsable($integer = false)
    {
      if ($this->mask <= ip2long("255.255.255.252"))
      {
          $first = $this->getNetwork(true) + 1;
      } else {
          $first = $this->getNetwork(true);
      }
      return ($integer) ? $first : long2ip($first);
    }

    /**
     * Return the last usable IP address in a subnet.
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return int|string
     */
    public function getLastUsable($integer = false)
    {
      if ($this->mask <= ip2long("255.255.255.252"))
      {
          $last = $this->getBroadcast(true) - 1;
      } else {
          $last = $this->getBroadcast(true);
      }
      return ($integer) ? $last : long2ip($last);
    }

    /**
     * Calculates the network address
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return int|string
     */
    public function getNetwork($integer = false)
    {
        $network = $this->ip & $this->mask;
        return ($integer) ? $network : long2ip($network);
    }

    /**
     * Calculates the broadcast address
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return int|string
     */
    public function getBroadcast($integer = false)
    {
        $bcast = $this->getNetwork(true)  | $this->getWildcard(true);
        return ($integer) ? $bcast : long2ip($bcast);
    }

    /**
     * Returns the wildcard mask
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return int|string
     */
    public function getWildcard($integer = false)
    {
        $wildcard = ~$this->mask & ip2long('255.255.255.255');
        return ($integer) ? $wildcard : long2ip($wildcard);
    }

    /**
     * Set the CIDR mask.
     *
     * @param $cidr Integer between 0 and 32 representing the IPv4 network bits
     * @throws Exception
     */
    public function setCIDR($cidr)
    {
        if ($cidr < 0 || $cidr > 32) { throw new Exception('Invalid CIDR: ' . $cidr); }

        // They don't work on 64-bit machines
        //$this->mask = ~((1 << (32 - $cidr)) - 1);
        //$this->mask = -1 << (32 - $cidr);

        // 64 bit
        $this->mask = (-1 << (32 - $cidr)) & ip2long('255.255.255.255');

    }

    /**
     * Set the IP address
     *
     * @param $ip
     * @param bool $integer
     * @return int
     * @throws Exception
     */
    public function setIp($ip, $integer = false)
    {
        if (!IP::IPCheck($ip)) { throw new Exception('Invalid IP Address: ' . $ip); }
        return ($integer) ? $this->ip = $ip : $this->ip = ip2long($ip);
    }

    /**
     * Set the subnet mask
     *
     * @param $mask
     * @param bool $integer
     * @return int
     * @throws Exception
     */
    public function setMask($mask, $integer = false)
    {
        if (!IP::IPCheck($mask)) { throw new Exception('Invalid Mask: ' . $mask); }
        return ($integer) ? $this->mask = $mask : $this->mask = ip2long($mask);
    }

    /**
     * Return the IP address
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return string
     */
    public function getIP($integer = false)
    {
        return ($integer) ? $this->ip : long2ip($this->ip);
    }

    /**
     * Returns the subnet mask
     *
     * @param bool $integer If TRUE, returns the integer representation.
     * @return string
     */
    public function getMask($integer = false)
    {
        return ($integer) ? $this->mask : long2ip($this->mask);
    }

    /**
     * Returns the CIDR (bits in the mask)
     *
     * @return int
     */
    public function getCIDR()
    {
        $maskbin = decbin($this->mask);
        return substr_count($maskbin, '1');
    }

    /**
     * Returns true if the IP address of this class is in the given network.
     *
     * @param IP $network
     * @return bool
     */
    public function inNetwork(IP $network)
    {
        return (($this->getIP(true) >= $network->getNetwork(true)) && ($this->getIP(true) <= $network->getBroadcast(true)));
    }

    public function networkToArray($integer = false)
    {
        $ips = array();
        for($i = $this->getNetwork(true); $i <= $this->getBroadcast(true); $i++)
        {
            $ips[] = ($integer ? $i : long2ip($i));
        }
        return $ips;
    }

    public function isRFC1918()
    {
        $i = $this->getNetwork(true);
        if (($i >= 167772160 && $i <= 184549375) || ($i >= 2886729728 && $i <= 2887778303) || ($i >= 3232235520 && $i <= 3232301055)) {
            return true;
        } else {
            return false;
        }
    }

}

?>
