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
        $this->coalesce();
        return $this;
    }

    /**
     * Coalesce the subnet array
     */
    private function coalesce()
    {
        foreach ($this->subnets as $key1=>$s1)
        {
            foreach ($this->subnets as $key2=>&$s2)
            {
                if ($s1 == $s2)
                    continue;
                if (($s1[1] + 1) == $s2[0])
                {
                    $this->subnets[$key1][1] = $s2[1];
                    unset($this->subnets[$key2]);
                    continue;
                }
            }
        }
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