<?php
date_default_timezone_set("Europe/Helsinki");
class Tilannehuone {
    private $options = array("base_url" => "http://www.tilannehuone.fi/", "alert_map" => "halytysmap.php", "mission_url" => "tehtava.php?hash={{hash}}");

    public function setOption($key, $value) {
        if(array_key_exists($key, $this->options)) {
            $options[$key] = $value;
        } else {
            die('[ERROR] ' . $key . ' is not a valid option.');
        }
    }

    public function fetch() {
        $rtn = array();
        $data = file_get_contents($this->options['base_url'] . $this->options['alert_map']);
        preg_match_all('/var image \= new google\.maps\.MarkerImage(.*?)ikonit/', str_replace(PHP_EOL, "", $data), $matches);
        $i = 0;
        foreach($matches[1] as $match) {
            preg_match('/contentString = \"(.*?)\";/', $match, $match_f);
            preg_match('/myLatLng = new google\.maps\.LatLng\((.*?)\);/', $match, $match_s);
            $match_f = $match_f[1];
            $match_s = $match_s[1];
            preg_match('/<br><strong>(.*?)<\/strong><br>/', $match_f, $match2);
            preg_match('/<\/strong><br>(.*?)<br><\/td><\/tr><\/table>/', $match_f, $match3);
            preg_match('/halytys_info\.php\?hash\=(.*?)\'\);/', $match_f, $match4);
            preg_match('/kyna\.gif\\\"><\/a>(.*?)<br>/', $match_f, $match5);
            $rtn[$i]['place'] = $match2[1];
            $rtn[$i]['description'] = $match3[1];
            $rtn[$i]['hash'] = $match4[1];
            $rtn[$i]['time'] = strtotime($match5[1]);
            $rtn[$i]['coordinates'] = $match_s;
            $i++;
        }
        return $rtn;
    }

    public function getInfo($hash) {
        $rtn = array();
        $data = file_get_contents($this->options['base_url'] . str_replace("{{hash}}", $hash, $this->options['mission_url']));
        if(stripos("<title>Tilannehuone.fi - 01.01.1970 02:00 : </title>", $data) !== false) {
            die('[ERROR] Mission ' . $hash . ' not found.');
        } else {
            preg_match('/<div class\=\"infotxt\"(.*?)<\/div>/', str_replace(PHP_EOL, "", $data), $match);
            if($match[1] == "Hälytyksestä ei ole tarkempaa tietoa.") return false;
            preg_match('/>(.*?)<br><br>/', $match[1], $match2);
            preg_match('/<a href=\"(.*?)\" target=\"_new\">(.*?)<\/a>/', $match[1], $match3);
            preg_match('/<span style="color: #999999;">Lisätty: (.*?), päivitetty: (.*?)<\/span>/', $match[1], $match4);
            if(!isset($match2[1]) || empty($match2[1])) {
                $rtn['ext_description'] = false;
            } else {
                $rtn['ext_description'] = str_replace(array("<br/>", "<br />", "<br>"), PHP_EOL, $match2[1]);
            }
            if(!isset($match3[1]) || empty($match3[1])) {
                $rtn['external_links'] = false;
            } else {
                $rtn['external_links'][$match3[2]] = $match3[1];
            }
            if(!isset($match4[1]) || empty($match4[1])) {
                $rtn['added'] = false;
                $rtn['updated'] = false;
            } else {
                $rtn['added'] = $match4[1];
                $rtn['updated'] = $match4[2];
            }
        }
        return $rtn;
    }
}
