<?php

namespace Lib;

use Exception;

class Wifi
{
    /** @var string path to wifi db file */
    private $CONFIG_FOLDER;
    /** @var string path to wifi db file */
    private $CONFIG_FIlE;
    /** @var array containing data */
    private $wifiConfig = [];

    public function __construct()
    {
        $this->CONFIG_FOLDER = __DIR__.'/../config/';
        $this->CONFIG_FIlE = $this->CONFIG_FOLDER.'wifiDB.json';

        if (!file_exists($this->CONFIG_FOLDER)) {
            mkdir($this->CONFIG_FOLDER, 777);
        }

        if (!file_exists($this->CONFIG_FIlE)) {
            self::saveFile();
        } else {
            self::loadFile();
        }
    }

    private function saveFile()
    {
        file_put_contents($this->CONFIG_FIlE, json_encode($this->wifiConfig));
    }

    private function loadFile()
    {
        $data = file_get_contents(__DIR__.'/../config/wifiDB.json');
        $this->wifiConfig = json_decode($data, true);
    }

    public static function decodePassword($password, $salt)
    {
        $saltedpw = base64_decode($password);
        $password = str_replace($salt, '', $saltedpw);

        return $password;
    }

    private static function compare($i, $j)
    {
        $a = isset($i['signal']) ? $i['signal'] : 0;
        $b = isset($j['signal']) ? $j['signal'] : 0;

        return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
    }

    public function getNetworkByEssid($essid)
    {
        if (isset($this->wifiConfig[$essid])) {
            return $this->wifiConfig[$essid];
        } else {
            return;
        }
    }

    public function saveNetwork($ssid, $protocol, $encryption, $encryption_type, $password)
    {
        $this->updateNetwork([
            'ESSID'           => $ssid,
            'Protocol'        => $protocol,
            'encryption'      => $encryption,
            'encryption_type' => $encryption_type,
            'password'        => $password,
        ]);
    }

    /**
     * Adds or update a network.
     *
     * @param $network array network associative array that should contain the password
     */
    public function updateNetwork($network)
    {
        $essid = $network['ESSID'];

        unset($network['signal'], $network['connected'], $network['saved']);

        $salt = self::createSalt($essid);

        $network['salt'] = $salt;

        $network['password'] = self::encodePassword($network['password'], $salt);

        $this->wifiConfig[$essid] = $network;

        $this->saveFile();
    }

    private static function createSalt($essid)
    {
        $salt = base64_encode(sha1(microtime().md5($essid)));

        return $salt;
    }

    private static function encodePassword($password, $salt)
    {
        $password = base64_encode($password.$salt);

        return $password;
    }

    public function forgetNetwork($essid)
    {
        unset($this->wifiConfig[$essid]);

        $this->saveFile();
    }

    public function wifiScan()
    {
        $interface = self::getInterface();

        $cmd = __DIR__.'/../cmd/wifi_scan.sh';

        if (!file_exists($cmd)) {
            throw new Exception('wifi_scan.sh not found!');
        }

        $mega_command = shell_exec("bash $cmd $interface");

        if ($mega_command == '') {
            return;
        }

        $wifi_array = explode("\n", trim($mega_command));

        $wifiNetworks = array_keys($this->wifiConfig);

        $networks = [];

        $network_index = false;

        $mega_regex1 = '/\s*([^[:|=]*)[\:|=]\s*(.*)/';

        foreach ($wifi_array as $key => $wifi) {
            $wifi = trim($wifi);

            preg_match($mega_regex1, $wifi, $matches);

            if (isset($matches[1], $matches[2])) {
                $match_key = trim($matches[1], '"');
                $match_value = trim($matches[2], '"');
            } else {
                continue;
            }

            if ($match_key == 'ESSID' && $match_value != $network_index) {
                // ESSID
                $network_index = $match_value;

                if (in_array($network_index, $wifiNetworks)) {
                    $networks[$network_index]['saved'] = true;
                }
            } elseif ($match_key == 'Quality') {
                // Signal
                $match_value = str_replace('/', '', substr($match_value, 0, 3));
                $match_key = 'signal';
            } elseif ($match_key == 'Encryption key') {
                // Encryption

                if ($match_value == 'off') {
                    $match_value = 'open';
                }
                $match_key = 'encryption';
            } elseif ($match_key == 'IE') {
                // Encryption Type

                if (self::has($match_value, 'WPA2')) {
                    $match_value = 'WPA2';
                } elseif (self::has($match_value, 'WPA')) {
                    $match_value = 'WPA';
                } elseif (isset($networks[$network_index]['encryption']) && $networks[$network_index]['encryption'] == 'open') {
                    continue;
                } else {
                    $match_value = 'WEP';
                }
                $match_key = 'encryption_type';
            }

            // Prevents values overriding
            if (isset($networks[$network_index][$match_key])) {
                continue;
            }

            // Finally adds the value to the array
            if (!empty($network_index)) {
                $networks[$network_index][$match_key] = $match_value;
            }
        }

        $conn = $this->getConnectedNetwork();

        uasort($networks, ['self', 'compare']);

        if ($conn !== null) {
            $conn_essid = $conn['ESSID'];

            if (isset($networks[$conn_essid])) {
                $networks[$conn_essid]['connected'] = true;
            } else {
                $networks[$conn_essid] = $conn;
            }

            $networks = [$conn_essid => $networks[$conn_essid]] + $networks;
        }

        return $networks;
    }

    // ENCRYPTION

    /**
     * @return string The current wifi interface
     */
    public static function getInterface()
    {
        return trim(shell_exec("ls /sys/class/net | grep -e 'wlan*' | xargs"), "\n ");
    }

    private static function has($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    public function getConnectedNetwork()
    {
        $interface = self::getInterface();

        $output = shell_exec('sudo iwconfig '.$interface);

        preg_match('/ESSID:"(.*)" /', $output, $matches);

        if (!isset($matches[1])) {
            return;
        }

        $essid = $matches[1];

        $matches = [];

        preg_match('/Access Point: (.*)   /', $output, $matches);

        $AP = $matches[1];

        $matches = [];

        preg_match("/\w*Security mode:([^\:]*)\n/", $output, $matches);

        $encryption = @$matches[1];

        $matches = [];

        preg_match('/Link Quality=(.*)\\//', $output, $matches);

        $quality = $matches[1];

        return ['ESSID' => $essid, 'encryption' => $encryption, 'signal' => $quality, 'AP' => $AP, 'connected' => true];
    }
}

//function getNetworkPassword($essid)
//{
//    $network = getNetworkEssid($essid);
//
//    $password = decodePassword($network['password'], $network['salt']);
//
//    return $password;
//}
