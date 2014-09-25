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
 * @todo Proper construct
 * @copyright Copyright (c) 2011, Tom Young
 */
class IP {
    
    private $ip;
    private $mask;

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
    
    public function getNetwork($integer = false)
    {
        $network = $this->ip & $this->mask;
        return ($integer) ? $network : long2ip($network);
    }
    
    public function getBroadcast($integer = false)
    {
        $bcast = $this->getNetwork(true)  | $this->getWildcard(true);
        return ($integer) ? $bcast : long2ip($bcast);
    }
    
    public function getWildcard($integer = false)
    {
        $wildcard = ~$this->mask & ip2long('255.255.255.255');
        return ($integer) ? $wildcard : long2ip($wildcard);
    }
    
    public function setCIDR($cidr)
    {
        if ($cidr < 0 || $cidr > 32) { throw new Exception('Invalid CIDR: ' . $cidr); }

        // The don't work on 64-bit machines
        //$this->mask = ~((1 << (32 - $cidr)) - 1);
        //$this->mask = -1 << (32 - $cidr);

        // 64 bit
        $this->mask = (-1 << (32 - $cidr)) & ip2long('255.255.255.255');

    }
    
    public function setIp($ip, $integer = false)
    {
        if (!IP::IPCheck($ip)) { throw new Exception('Invalid IP Address: ' . $ip); }
        return ($integer) ? $this->ip = $ip : $this->ip = ip2long($ip);
    }
    
    public function setMask($mask, $integer = false)
    {
        if (!IP::IPCheck($mask)) { throw new Exception('Invalid Mask: ' . $mask); }
        return ($integer) ? $this->mask = $mask : $this->mask = ip2long($mask);
    }
    
    public function getIP($integer = false)
    {
        return ($integer) ? $this->ip : long2ip($this->ip);
    }
    
    public function getMask($integer = false)
    {
        return ($integer) ? $this->mask : long2ip($this->mask);
    }
    
    public function getCIDR()
    {
        $maskbin = decbin($this->mask);
        return substr_count($maskbin, '1');
    }

    public function inNetwork(IP $network)
    {
        return (($this->getIP(true) >= $network->getNetwork(true)) && ($this->getIP(true) <= $network->getBroadcast(true)));
    }
    
}

?>
