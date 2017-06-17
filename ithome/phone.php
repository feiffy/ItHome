<?php
// ͳ��ithome�ֻ��ͺ�
header("Content-Type: text/html; charset=utf-8");

class ItHome {

    public function getPageHtml($url)
    {
        $html = "";

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);

        return $html;
    }

    // ��ȡ��ҳ��id
    public function getPageIds($html)
    {
        $ids = [];
        preg_match("/<div class=\"block new-list-1\"><ul>(.+?)<\/ul><\/div>/i", $html, $matches1);
        preg_match("/<div class=\"block new-list-2\" style=\"display:none;\"><ul>(.+?)<\/ul><\/div>/i", $html, $matches2);
        preg_match("/<div class=\"block new-list-3\" style=\"display:none;\"><ul>(.+?)<\/ul><\/div>/i", $html, $matches3);
        preg_match("/<div class=\"block new-list-4\" style=\"display:none;\"><ul>(.+?)<\/ul><\/div>/i", $html, $matches4);
        preg_match("/<div class=\"block new-list-5\" style=\"display:none;\"><ul>(.+?)<\/ul><\/div>/i", $html, $matches5);

        $matches = [
            explode("</li>", $matches1[1]),
            explode("</li>", $matches2[1]),
            explode("</li>", $matches3[1]),
            explode("</li>", $matches4[1]),
            explode("</li>", $matches5[1]),
        ];

        foreach ($matches as $lis) {
            foreach($lis as $li) {
                preg_match("/\/(\d+)\.htm\">/i", $li, $matches_id);
                if (!empty($matches_id[1])) {
                    $ids[] = $matches_id[1];
                }
            }
        }

        return $ids;
    }

    // ���ҳ�������
    public function getPageReview($newsId, $page)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://dyn.ithome.com/ithome/getajaxdata.aspx",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "newsID=$newsId&type=commentpage&page=$page&order=false",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "cookie: _ga=GA1.2.623524568.1494653006; ASP.NET_SessionId=kwr3mhc3mw03mcelecly5sfa; BEC=6C2E4C8E8E2AC0ADB817757E374F78E2|WUOHn|WUOG7; Hm_lvt_cfebe79b2c367c4b89b285f412bf9867=1497588628; Hm_lpvt_cfebe79b2c367c4b89b285f412bf9867=1497597850",
            "host: dyn.ithome.com",
            "origin: https://dyn.ithome.com",
            "postman-token: f97630bb-0698-a2c4-28b4-b6f43d9eb246",
            "referer: https://dyn.ithome.com/comment/$newsId",
            "user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.104 Safari/537.36",
            "x-requested-with: XMLHttpRequest"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            // echo "cURL Error #:" . $err;
            return false;
        } else {
            // echo $response;
            return $response;
        }
    }

    // ���ҳ����������
    public function getPageReviews($newsId)
    {
        $reviewsHtml = "";
        for ($page = 1; $page <= 5; $page++) {
            if ($html = $this->getPageReview($newsId, $page)) {
                $reviewsHtml .= $html;
            }
        }
        return $reviewsHtml;
    }

    // �������л�ȡ������Ϣ
    public function convertToInfo($reviewsHtml)
    {
        $info = [
            "mobile android"    => [],
            "mobile windows8"   => [],
            "mobile win10"      => [],
            "mobile watch"      => [],
            "mobile iphone"     => [],
            "mobile wp"         => [],
            "mobile ipad"       => [],
            "mobile itouch"     => [],
            "mobile macbook"    => [],
            "other"             => [],
        ];
        $reviews = preg_split("/(info rmp|re_info rmp)/", $reviewsHtml);
        array_pop(array_reverse($reviews));
        foreach($reviews as $review) {
            if (empty(trim($review))) {continue;}
            if (preg_match("/mobile android/i", $review)) {
                $flag = "mobile android";
            }
            else if (preg_match("/mobile windows8/i", $review)) {
                $flag = "mobile windows8";
            }
            else if (preg_match("/mobile win10/i", $review)) {
                $flag = "mobile win10";
            }
            else if (preg_match("/mobile watch/i", $review)) {
                $flag = "mobile watch";
            }
            else if (preg_match("/mobile iphone/i", $review)) {
                $flag = "mobile iphone";
            }
            else if (preg_match("/mobile ipad/i", $review)) {
                $flag = "mobile ipad";
            }
            else if (preg_match("/mobile itouch/i", $review)) {
                $flag = "mobile itouch";
            }
            else if (preg_match("/mobile wp/i", $review)) {
                $flag = "mobile wp";
            }
            else if (preg_match("/mobile macbook/i", $review)) {
                $flag = "mobile macbook";
            }
            else {
                $flag = "other";
            }

            preg_match("/href=\"http:\/\/m\.ithome\.com\/ithome\/download\/\">(.*?)<\/a>/i", $review, $match_phone);
            preg_match("/<strong class=\"nick\"><a title=\"��ýͨ��֤����ID��\d+\" target=\"_blank\" href=\"http:\/\/quan.ithome.com\/user\/\d+\">(.+?)<\/a><\/strong>/i", $review, $match_user);
            $phone = isset($match_phone[1]) ? $match_phone[1] : 0;
            $user = isset($match_user[1]) ? $match_user[1] : 0;
            $phone = iconv("utf-8", "gbk", $phone); // ʹ��gbk���룬�Ա��ں������������
            if (!empty($phone) && !empty($user)) {
                if (isset($info[$flag][$phone])) {
                    if (!in_array($user, $info[$flag][$phone]["user"])) {
                        $info[$flag][$phone]["user"][] = $user;
                    }
                } else {
                    $info[$flag][$phone]["user"][] = $user;
                }
            }
        }
        return $info;
    }

    public function mergeInfo($info, $info_new)
    {
        foreach($info_new as $key => $item) {
            foreach ($item as $key2 => $item2) {
                if (isset($info[$key][$key2])) {
                    $info[$key][$key2]["user"] += $item2["user"];
                } else {
                    $info[$key][$key2]["user"] = $item2["user"];
                }
            }
        }

        return $info;
    }

    public function start($pages = 1)
    {
        $html = $this->getPageHtml("https://www.ithome.com");
        $newsIds = $this->getPageIds($html);
        $info = [
            "mobile android"    => [],
            "mobile windows8"   => [],
            "mobile win10"      => [],
            "mobile watch"      => [],
            "mobile iphone"     => [],
            "mobile wp"         => [],
            "mobile ipad"       => [],
            "mobile itouch"     => [],
            "mobile macbook"    => [],
            "other"             => [],
        ];
        for($i = 1; $i <= $pages; $i++) {
            $reviewsHtml = $this->getPageReviews($newsIds[$i]);
            $info_new = $this->convertToInfo($reviewsHtml);
            $info = $this->mergeInfo($info, $info_new);
        }

        // $utf8_info = [];
        foreach($info as $key => $val) { // ����
            ksort($info[$key]);
            // if (!$val) {
            //     $utf8_info[$key] = $val;
            //     continue;
            // }
            // foreach($val as $key2 => $val2) {
            //     $utf8_key = iconv("gbk", "utf-8", $key2);
            //     $utf8_info[$key][$utf8_key] = $val2; // ������ת��Ϊutf-8���룬�Ա���ҳ����ʾ
            // }
        }

        // unset($info);
        // $info = $utf8_info;
        // unset($utf8_info);

        $total = []; // ͳ�Ƹ������ֻ�������
        foreach($info["mobile android"] as $phone => $phone_info) {
            if (strpos($phone, iconv("utf-8", "gbk", "С��")) === 0 || strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["С���ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "��Ϊ")) === 0 || strpos($phone, iconv("utf-8", "gbk", "��ҫ")) === 0) {
                $total["��Ϊ�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "OPPO") === 0) {
                $total["OPPO�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "vivo") === 0) {
                $total["vivo�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0 || strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "LG") === 0) {
                $total["LG�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "HTC") === 0) {
                $total["HTC�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "360") === 0) {
                $total["360�ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, "nubia") === 0 || strpos($phone, iconv("utf-8", "gbk", "Ŭ����")) === 0) {
                $total["Ŭ�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
            elseif (strpos($phone, iconv("utf-8", "gbk", "����")) === 0 || strpos($phone, "ZUK") === 0 || strpos($phone, "Moto") === 0) {
                $total["�����ֻ��ܼ�"] += count($phone_info["user"]);
            }
        }
        foreach ($info["mobile iphone"] as $phone => $phone_info) {
            $total["ƻ���ֻ��ܼ�"] += count($phone_info["user"]);
        }
        foreach ($info["mobile wp"] as $phone => $phone_info) {
            $total["WP�ֻ��ܼ�"] += count($phone_info);
        }
        foreach ($info["mobile win10"] as $phone => $phone_info) {
            $total["WP�ֻ��ܼ�"] += count($phone_info);
        }

        $title = "IT֮����������������ֻ�����ͳ�ƣ����" . $pages . "ƪ��";
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
    table,tr,th,td {
        border: 1px solid #ccc;
        border-collapse: collapse;
    }
    td {
        padding: 0 5px;
    }
    th {
        background-color: #CDF09F;
    }
    </style>
    <title><?php echo $title;?></title>
</head>
<body>
    <h1><?php echo $title;?></h1>
    <table>
        <tr>
            <th>����</th>
            <th>����</th>
        </tr>
<?php   foreach($total as $vendor => $number): ?>
        <tr>
            <td><?php echo $vendor;?></td>
            <td><?php echo $number;?></td>
        </tr>
<?php   endforeach; ?>
    </table>
    <table>
<?php   foreach($info as $key => $item): ?>
        <tr>
            <th>����</th>
            <th>����</th>
            <th>����</th>
            <th>�û���</th>
        </tr>
<?php       if (count($info[$key])): ?>
        <tr>
            <td rowspan="<?php echo count($info[$key]) + 1;?>"> <?php echo $key ?> (<?php echo count($info[$key]); ?>) </td>
        </tr>
<?php           foreach($info[$key] as $phone => $phone_info): ?>
        <tr>
            <td><?php echo iconv("gbk", "utf-8", $phone); ?></td>
            <td><?php echo count($phone_info["user"]); ?></td>
            <td><?php echo implode(", ", $phone_info["user"]); ?></td>
<?php           endforeach;?>
<?php       else:?>
        <tr>
            <td rowspan='1'><?php echo $key; ?> (0)</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
<?php       endif;?>
<?php endforeach;?>
    </table>
</body>
</html>
<?php
    }

}