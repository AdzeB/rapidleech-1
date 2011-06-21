<?php
if (!defined('RAPIDLEECH')) {
    require_once ("index.html");
    exit ();
}

class filekeen_com extends DownloadClass {
    public function Download($link) {
        global $premium_acc;
            if (($_REQUEST['premium_acc'] == "on" && $_REQUEST['premium_user'] && $_REQUEST['premium_pass']) || ($_REQUEST['premium_acc'] == "on" && $premium_acc['filekeen_com'] ['user'] && $premium_acc['filekeen_com'] ['pass'])) {
                $this->DownloadPremium($link);
            } else {
                $this->DownloadFree($link);
            }
    }

    private function DownloadFree($link) {
        global $Referer;
            $page = $this->GetPage($link);
            is_present($page, "File Not Found", "Have you ever checked the link is valid or not? The file is not exist!");

            $id = cut_str($page, 'name="id" value="','"');
            $FileName = cut_str($page, 'name="fname" value="','"');

            $post = array();
            $post['op'] = "download1";
            $post['usr_login'] = "";
            $post['id'] = $id;
            $post['fname'] = $FileName;
            $post['referer'] = $link;
            $post['method_free'] = "Free Download";
            $page = $this->GetPage($link, 0, $post, $link);
            if (preg_match("#You have to wait (\d+) minutes, (\d+) seconds till next download#",$page,$message)){
                html_error($message[0]);
            }
            if (preg_match('#(\d+)</span> seconds#', $page, $wait)) {
                $this->CountDown($wait[1]);
            }
            if (preg_match_all("#<span style='[^\d]+(\d+)[^\d]+\d+\w+;'>\W+(\d+);</span>#", $page, $temp)) {
                for ($i=0;$i<count($temp[1])-1;$i++){
                    for ($j=$i+1;$j<count($temp[1]);$j++){
                        if ($temp[1][$i]>$temp[1][$j]){
                            $n=1;
                            do {
                                $tmp=$temp[$n][$i];
                                $temp[$n][$i]=$temp[$n][$j];
                                $temp[$n][$j]=$tmp;
                                $n++;
                            } while ($n<=2);
                        }
                    }
                }
                $captcha="";
                foreach($temp[2] as $value) {
                    $captcha.=chr($value);
                }
            }
            $rand = cut_str($page, 'name="rand" value="','"');
            unset ($post);
            $post['op'] = "download2";
            $post['id'] = $id;
            $post['rand'] = $rand;
            $post['referer'] = $link;
            $post['method_free'] = "Free Download";
            $post['method_premium'] = "";
            $post['code'] = $captcha;
            $post['down_script'] = "1";
            $page = $this->GetPage($link, 0, $post, $link);
            if (!preg_match('#(http:\/\/.+(:\d+)?\/d\/[^"]+)">#', $page, $dl)) {
                html_error("Sorry, Download link not found. Contact the author & give him the link which u have this error ;)");
            }
            $dlink = trim($dl[1]);
            $Url = parse_url($dlink);
            if (!$FileName) $FileName = basename($Url['path']);
            $this->RedirectDownload($dlink, $FileName, 0, 0, $link);
            exit();
    }

    private function DownloadPremium($link) {
        html_error("It doesn't support premium, read the post carefully!");
    }
}

//Filekeen free download plugin by Ruud v.Tony 21-06-2011, based on automatic captcha code by vdhdevil
?>
