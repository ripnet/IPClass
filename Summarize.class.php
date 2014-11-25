<?php

class Summarize
{
    /**
     * @var Array of subnets, in the form [start,end], using integer notation
     */
    private $subnets = array();

    public function __construct($subnets)
    {
        //$this->subnets = array();
        foreach ($subnets as $subnet)
        {
            $sn = new IP($subnet);
            foreach ($this->subnets as &$s)
            {
                if ($sn->getNetwork(true) == ($s[1] + 1))
                {
                    $s[1] = $sn->getBroadcast(true);
                    continue 2;
                } elseif ($sn->getBroadcast(true) == ($s[0] - 1))
                {
                    $s[0] = $sn->getNetwork(true);
                    continue 2;
                } elseif ($sn->getNetwork(true) >= $s[0] && $sn->getBroadcast(true) <= $s[1])
                {
                    // overlap
                    continue 2;
                }

            }
            $this->subnets[] = array($sn->getNetwork(true), $sn->getBroadcast(true));
        }
        return $this;
    }

    public function getSubnets()
    {
        $new = array();

        foreach ($this->subnets as $key=>&$s)
        {
            while ($s[0] <= $s[1])
            {
                $length = $s[1] - $s[0] + 1;
                for ($i = (32 - floor(log($length, 2))); $i <= 32; $i++)
                {
                    $test_ip = long2ip($s[0]) . '/' . $i;
                    $test = new IP($test_ip);
                    if ($test->getNetwork(true) == $s[0] && $test->getBroadcast(true) <= $s[1])
                    {
                        $s[0] = $test->getBroadcast(true) + 1;
                        $new[$test->getIP(true)] = $test->getIP() . "/" . $test->getCIDR();
                        break;
                    }
                }
            }
        }
        ksort($new);
        return $new;
    }


}