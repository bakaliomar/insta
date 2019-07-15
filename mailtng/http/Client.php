<?php namespace ma\mailtng\http
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author          MailTng Team
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\core\Base as Base;
    use ma\mailtng\globals\Server as Server;
    /**
     * @name            Client.class 
     * @description     It's a class that deals with Client request infos
     * @package		ma\mailtng\http
     * @category        HTTP Class
     * @author		MailTng Team			
     */
    class Client extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_agent = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_apiKey = '3ba9c6c13b2292634ac85f9a9b558833d3da934b6222898a922eedda8640713b';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_deviceName = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_ip = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_countryCode = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_country = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_region = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_city = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_language = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_hostname = '';

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_browserName = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_browserVersion = '';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_os = '';

        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_isMobile = 'false';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_isTablet = 'false';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_isRobot = 'false';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_isFacebook = 'false';
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_isComputer = 'false';

        # Constants 
        
        const DEVICE_UNKNOWN = 'Phone';
        const OS_UNKNOWN = 'OS';
        const BROWSER_UNKNOWN = 'unknown';
        const VERSION_UNKNOWN = 'unknown';

        const BROWSER_OPERA = 'Opera';
        const BROWSER_OPERA_MINI = 'Opera Mini';
        const BROWSER_WEBTV = 'WebTV';
        const BROWSER_EDGE = 'Edge';
        const BROWSER_IE = 'Internet Explorer';
        const BROWSER_POCKET_IE = 'Pocket Internet Explorer';
        const BROWSER_KONQUEROR = 'Konqueror';
        const BROWSER_ICAB = 'iCab';
        const BROWSER_OMNIWEB = 'OmniWeb';
        const BROWSER_FIREBIRD = 'Firebird';
        const BROWSER_FIREFOX = 'Firefox';
        const BROWSER_ICEWEASEL = 'Iceweasel';
        const BROWSER_SHIRETOKO = 'Shiretoko';
        const BROWSER_MOZILLA = 'Mozilla';
        const BROWSER_AMAYA = 'Amaya';
        const BROWSER_LYNX = 'Lynx';
        const BROWSER_SAFARI = 'Safari';
        const BROWSER_IPHONE = 'iPhone';
        const BROWSER_IPOD = 'iPod';
        const BROWSER_IPAD = 'iPad';
        const BROWSER_CHROME = 'Chrome';
        const BROWSER_ANDROID = 'Android';
        const BROWSER_GOOGLEBOT = 'GoogleBot';
        const BROWSER_SLURP = 'Yahoo! Slurp';
        const BROWSER_W3CVALIDATOR = 'W3C Validator';
        const BROWSER_BLACKBERRY = 'BlackBerry';
        const BROWSER_ICECAT = 'IceCat';
        const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser';
        const BROWSER_NOKIA = 'Nokia Browser';
        const BROWSER_MSN = 'MSN Browser';
        const BROWSER_MSNBOT = 'MSN Bot';
        const BROWSER_BINGBOT = 'Bing Bot';
        const BROWSER_VIVALDI = 'Vivalidi';

        const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator'; # (DEPRECATED)
        const BROWSER_GALEON = 'Galeon'; # (DEPRECATED)
        const BROWSER_NETPOSITIVE = 'NetPositive'; # (DEPRECATED)
        const BROWSER_PHOENIX = 'Phoenix'; # (DEPRECATED)
        const BROWSER_PLAYSTATION = "PlayStation";

        
        const OS_WINDOWS = 'Windows';
        const OS_WINDOWS_CE = 'Windows CE';
        const OS_APPLE = 'MAC OS X';
        const OS_LINUX = 'Linux';
        const OS_OS2 = 'OS/2';
        const OS_BEOS = 'BeOS';
        const OS_IPHONE = 'iPhone';
        const OS_IPOD = 'iPod';
        const OS_IPAD = 'iPad';
        const OS_BLACKBERRY = 'BlackBerry';
        const OS_NOKIA = 'Nokia';
        const OS_FREEBSD = 'FreeBSD';
        const OS_OPENBSD = 'OpenBSD';
        const OS_NETBSD = 'NetBSD';
        const OS_SUNOS = 'SunOS';
        const OS_OPENSOLARIS = 'OpenSolaris';
        const OS_ANDROID = 'Android';
        const OS_PLAYSTATION = "Sony PlayStation";   
        
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_phoneDevices = array(
            'iPhone' => '\biPhone\b|\biPod\b',
            'BlackBerry' => 'BlackBerry|\bBB10\b|rim[0-9]+',
            'HTC' => 'HTC|HTC.*(Sensation|Evo|Vision|Explorer|6800|8100|8900|A7272|S510e|C110e|Legend|Desire|T8282)|APX515CKT|Qtek9090|APA9292KT|HD_mini|Sensation.*Z710e|PG86100|Z715e|Desire.*(A8181|HD)|ADR6200|ADR6400L|ADR6425|001HT|Inspire 4G|Android.*\bEVO\b|T-Mobile G1|Z520m',
            'Nexus' => 'Nexus One|Nexus S|Galaxy.*Nexus|Android.*Nexus.*Mobile|Nexus 4|Nexus 5|Nexus 6',
            'Dell' => 'Dell.*Streak|Dell.*Aero|Dell.*Venue|DELL.*Venue Pro|Dell Flash|Dell Smoke|Dell Mini 3iX|XCD28|XCD35|\b001DL\b|\b101DL\b|\bGS01\b',
            'Motorola' => 'Motorola|DROIDX|DROID BIONIC|\bDroid\b.*Build|Android.*Xoom|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT901|XT907|XT909|XT910|XT912|XT928|XT926|XT915|XT919|XT925|XT1021|\bMoto E\b',
            'Samsung' => 'Samsung|SM-G9250|GT-19300|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9082|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9305|GT-I9500|GT-I9505|GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S7710|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-I959|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100|GT-N7105|SCH-I535|SM-N900A|SGH-I317|SGH-T999L|GT-S5360B|GT-I8262|GT-S6802|GT-S6312|GT-S6310|GT-S5312|GT-S5310|GT-I9105|GT-I8510|GT-S6790N|SM-G7105|SM-N9005|GT-S5301|GT-I9295|GT-I9195|SM-C101|GT-S7392|GT-S7560|GT-B7610|GT-I5510|GT-S7580|GT-S7582|GT-S7530E|GT-I8750|SM-G9006V|SM-G9008V|SM-G9009D|SM-G900A|SM-G900D|SM-G900F|SM-G900H|SM-G900I|SM-G900J|SM-G900K|SM-G900L|SM-G900M|SM-G900P|SM-G900R4|SM-G900S|SM-G900T|SM-G900V|SM-G900W8|SHV-E160K|SCH-P709|SCH-P729|SM-T2558|GT-I9205|SM-G9350|SM-J120F',
            'LG' => '\bLG\b;|LG[- ]?(C800|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|730|855|L160|LS740|LS840|LS970|LU6200|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P500|P509|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999|E612|D955|D802|MS323)',
            'Sony' => 'SonyST|SonyLT|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i|LT28h|LT26w|SonyEricssonMT27i|C5303|C6902|C6903|C6906|C6943|D2533|D6503',
            'Asus' => 'Asus.*Galaxy|PadFone.*Mobile',
            'NokiaLumia' => 'Lumia [0-9]{3,4}',
            'Micromax' => 'Micromax.*\b(A210|A92|A88|A72|A111|A110Q|A115|A116|A110|A90S|A26|A51|A35|A54|A25|A27|A89|A68|A65|A57|A90)\b',
            'Palm' => 'PalmSource|Palm',
            'Vertu' => 'Vertu|Vertu.*Ltd|Vertu.*Ascent|Vertu.*Ayxta|Vertu.*Constellation(F|Quest)?|Vertu.*Monika|Vertu.*Signature',
            'Pantech' => 'PANTECH|IM-A850S|IM-A840S|IM-A830L|IM-A830K|IM-A830S|IM-A820L|IM-A810K|IM-A810S|IM-A800S|IM-T100K|IM-A725L|IM-A780L|IM-A775C|IM-A770K|IM-A760S|IM-A750K|IM-A740S|IM-A730S|IM-A720L|IM-A710K|IM-A690L|IM-A690S|IM-A650S|IM-A630K|IM-A600S|VEGA PTL21|PT003|P8010|ADR910L|P6030|P6020|P9070|P4100|P9060|P5000|CDM8992|TXT8045|ADR8995|IS11PT|P2030|P6010|P8000|PT002|IS06|CDM8999|P9050|PT001|TXT8040|P2020|P9020|P2000|P7040|P7000|C790',
            'Fly' => 'IQ230|IQ444|IQ450|IQ440|IQ442|IQ441|IQ245|IQ256|IQ236|IQ255|IQ235|IQ245|IQ275|IQ240|IQ285|IQ280|IQ270|IQ260|IQ250',
            'Wiko' => 'KITE 4G|HIGHWAY|GETAWAY|STAIRWAY|DARKSIDE|DARKFULL|DARKNIGHT|DARKMOON|SLIDE|WAX 4G|RAINBOW|BLOOM|SUNSET|GOA(?!nna)|LENNY|BARRY|IGGY|OZZY|CINK FIVE|CINK PEAX|CINK PEAX 2|CINK SLIM|CINK SLIM 2|CINK +|CINK KING|CINK PEAX|CINK SLIM|SUBLIM',
            'iMobile' => 'i-mobile (IQ|i-STYLE|idea|ZAA|Hitz)',
            'SimValley' => '\b(SP-80|XT-930|SX-340|XT-930|SX-310|SP-360|SP60|SPT-800|SP-120|SPT-800|SP-140|SPX-5|SPX-8|SP-100|SPX-8|SPX-12)\b',
            'Wolfgang' => 'AT-B24D|AT-AS50HD|AT-AS40W|AT-AS55HD|AT-AS45q2|AT-B26D|AT-AS50Q',
            'Alcatel' => 'Alcatel',
            'Nintendo' => 'Nintendo 3DS',
            'Amoi' => 'Amoi',
            'INQ' => 'INQ',
            'Phone' => 'Tapatalk|PDA;|SAGEM|\bmmp\b|pocket|\bpsp\b|symbian|Smartphone|smartfon|treo|up.browser|up.link|vodafone|\bwap\b|nokia|Series40|Series60|S60|SonyEricsson|N900|MAUI.*WAP.*Browser',
        );

        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_tabletDevices = array(
            'iPad' => 'iPad|iPad.*Mobile',
            'NexusTablet' => 'Android.*Nexus[\s]+(7|9|10)',
            'SamsungTablet' => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5105|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925|GT-I9200|GT-P5200|GT-P5210|GT-P5210X|SM-T311|SM-T310|SM-T310X|SM-T210|SM-T210R|SM-T211|SM-P600|SM-P601|SM-P605|SM-P900|SM-P901|SM-T217|SM-T217A|SM-T217S|SM-P6000|SM-T3100|SGH-I467|XE500|SM-T110|GT-P5220|GT-I9200X|GT-N5110X|GT-N5120|SM-P905|SM-T111|SM-T2105|SM-T315|SM-T320|SM-T320X|SM-T321|SM-T520|SM-T525|SM-T530NU|SM-T230NU|SM-T330NU|SM-T900|XE500T1C|SM-P605V|SM-P905V|SM-T337V|SM-T537V|SM-T707V|SM-T807V|SM-P600X|SM-P900X|SM-T210X|SM-T230|SM-T230X|SM-T325|GT-P7503|SM-T531|SM-T330|SM-T530|SM-T705|SM-T705C|SM-T535|SM-T331|SM-T800|SM-T700|SM-T537|SM-T807|SM-P907A|SM-T337A|SM-T537A|SM-T707A|SM-T807A|SM-T237|SM-T807P|SM-P607T|SM-T217T|SM-T337T|SM-T807T|SM-T116NQ|SM-P550|SM-T350|SM-T550|SM-T9000|SM-P9000|SM-T705Y|SM-T805|GT-P3113|SM-T710|SM-T810|SM-T815|SM-T360|SM-T533|SM-T113|SM-T335|SM-T715|SM-T560|SM-T670|SM-T677|SM-T377|SM-T567|SM-T357T|SM-T555|SM-T561', // SCH-P709|SCH-P729|SM-T2558|GT-I9205 - Samsung Mega - treat them like a regular phone.
            'Kindle' => 'Kindle|Silk.*Accelerated|Android.*\b(KFOT|KFTT|KFJWI|KFJWA|KFOTE|KFSOWI|KFTHWI|KFTHWA|KFAPWI|KFAPWA|WFJWAE|KFSAWA|KFSAWI|KFASWI|KFARWI)\b',
            'SurfaceTablet' => 'Windows NT [0-9.]+; ARM;.*(Tablet|ARMBJS)',
            'HPTablet' => 'HP Slate (7|8|10)|HP ElitePad 900|hp-tablet|EliteBook.*Touch|HP 8|Slate 21|HP SlateBook 10',
            'AsusTablet' => '^.*PadFone((?!Mobile).)*$|Transformer|TF101|TF101G|TF300T|TF300TG|TF300TL|TF700T|TF700KL|TF701T|TF810C|ME171|ME301T|ME302C|ME371MG|ME370T|ME372MG|ME172V|ME173X|ME400C|Slider SL101|\bK00F\b|\bK00C\b|\bK00E\b|\bK00L\b|TX201LA|ME176C|ME102A|\bM80TA\b|ME372CL|ME560CG|ME372CG|ME302KL| K010 | K017 |ME572C|ME103K|ME170C|ME171C|\bME70C\b|ME581C|ME581CL|ME8510C|ME181C|P01Y|PO1MA',
            'BlackBerryTablet' => 'PlayBook|RIM Tablet',
            'HTCtablet' => 'HTC_Flyer_P512|HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200|PG09410',
            'MotorolaTablet' => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
            'NookTablet' => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|BNTV400|BNTV600|LogicPD Zoom2',
            'AcerTablet' => 'Android.*; \b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71|B1-710|B1-711|A1-810|A1-811|A1-830)\b|W3-810|\bA3-A10\b|\bA3-A11\b|\bA3-A20\b|\bA3-A30',
            'ToshibaTablet' => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
            'LGTablet' => '\bL-06C|LG-V909|LG-V900|LG-V700|LG-V510|LG-V500|LG-V410|LG-V400|LG-VK810\b',
            'FujitsuTablet' => 'Android.*\b(F-01D|F-02F|F-05E|F-10D|M532|Q572)\b',
            'PrestigioTablet' => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280C3G|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474|PMP5097CPRO|PMP5097|PMP7380D|PMP5297C|PMP5297C_QUAD|PMP812E|PMP812E3G|PMP812F|PMP810E|PMP880TD|PMT3017|PMT3037|PMT3047|PMT3057|PMT7008|PMT5887|PMT5001|PMT5002',
            'LenovoTablet' => 'Lenovo TAB|Idea(Tab|Pad)( A1|A10| K1|)|ThinkPad([ ]+)?Tablet|YT3-X90L|YT3-X90F|YT3-X90X|Lenovo.*(S2109|S2110|S5000|S6000|K3011|A3000|A3500|A1000|A2107|A2109|A1107|A5500|A7600|B6000|B8000|B8080)(-|)(FL|F|HV|H|)',
            'DellTablet' => 'Venue 11|Venue 8|Venue 7|Dell Streak 10|Dell Streak 7',
            'YarvikTablet' => 'Android.*\b(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468|TAB07-100|TAB07-101|TAB07-150|TAB07-151|TAB07-152|TAB07-200|TAB07-201-3G|TAB07-210|TAB07-211|TAB07-212|TAB07-214|TAB07-220|TAB07-400|TAB07-485|TAB08-150|TAB08-200|TAB08-201-3G|TAB08-201-30|TAB09-100|TAB09-211|TAB09-410|TAB10-150|TAB10-201|TAB10-211|TAB10-400|TAB10-410|TAB13-201|TAB274EUK|TAB275EUK|TAB374EUK|TAB462EUK|TAB474EUK|TAB9-200)\b',
            'MedionTablet' => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
            'ArnovaTablet' => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT|AN9G2',
            'IntensoTablet' => 'INM8002KP|INM1010FP|INM805ND|Intenso Tab|TAB1004',
            'IRUTablet' => 'M702pro',
            'MegafonTablet' => 'MegaFon V9|\bZTE V9\b|Android.*\bMT7A\b',
            'EbodaTablet' => 'E-Boda (Supreme|Impresspeed|Izzycomm|Essential)',
            'AllViewTablet' => 'Allview.*(Viva|Alldro|City|Speed|All TV|Frenzy|Quasar|Shine|TX1|AX1|AX2)',
            'ArchosTablet' => '\b(101G9|80G9|A101IT)\b|Qilive 97R|Archos5|\bARCHOS (70|79|80|90|97|101|FAMILYPAD|)(b|)(G10| Cobalt| TITANIUM(HD|)| Xenon| Neon|XSK| 2| XS 2| PLATINUM| CARBON|GAMEPAD)\b',
            'AinolTablet' => 'NOVO7|NOVO8|NOVO10|Novo7Aurora|Novo7Basic|NOVO7PALADIN|novo9-Spark',
            'NokiaLumiaTablet' => 'Lumia 2520',
            'SonyTablet' => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT13|SGPT114|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT131|SGPT132|SGPT133|SGPT211|SGPT212|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201|SGP351|SGP341|SGP511|SGP512|SGP521|SGP541|SGP551|SGP621|SGP612|SOT31',
            'PhilipsTablet' => '\b(PI2010|PI3000|PI3100|PI3105|PI3110|PI3205|PI3210|PI3900|PI4010|PI7000|PI7100)\b',
            'CubeTablet' => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
            'CobyTablet' => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
            'MIDTablet' => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733|MID4X10',
            'MSITablet' => 'MSI \b(Primo 73K|Primo 73L|Primo 81L|Primo 77|Primo 93|Primo 75|Primo 76|Primo 73|Primo 81|Primo 91|Primo 90|Enjoy 71|Enjoy 7|Enjoy 10)\b',
            'SMiTTablet' => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
            'RockChipTablet' => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
            'FlyTablet' => 'IQ310|Fly Vision',
            'bqTablet' => 'Android.*(bq)?.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant|Aquaris E10)|Maxwell.*Lite|Maxwell.*Plus',
            'HuaweiTablet' => 'MediaPad|MediaPad 7 Youth|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
            'NecTablet' => '\bN-06D|\bN-08D',
            'PantechTablet' => 'Pantech.*P4100',
            'BronchoTablet' => 'Broncho.*(N701|N708|N802|a710)',
            'VersusTablet' => 'TOUCHPAD.*[78910]|\bTOUCHTAB\b',
            'ZyncTablet' => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
            'PositivoTablet' => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
            'NabiTablet' => 'Android.*\bNabi',
            'KoboTablet' => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
            'DanewTablet' => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
            'TexetTablet' => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
            'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
            'TrekstorTablet' => 'ST10416-1|VT10416-1|ST70408-1|ST702xx-1|ST702xx-2|ST80208|ST97216|ST70104-2|VT10416-2|ST10216-2A|SurfTab',
            'PyleAudioTablet' => '\b(PTBL10CEU|PTBL10C|PTBL72BC|PTBL72BCEU|PTBL7CEU|PTBL7C|PTBL92BC|PTBL92BCEU|PTBL9CEU|PTBL9CUK|PTBL9C)\b',
            'AdvanTablet' => 'Android.* \b(E3A|T3X|T5C|T5B|T3E|T3C|T3B|T1J|T1F|T2A|T1H|T1i|E1C|T1-E|T5-A|T4|E1-B|T2Ci|T1-B|T1-D|O1-A|E1-A|T1-A|T3A|T4i)\b ',
            'DanyTechTablet' => 'Genius Tab G3|Genius Tab S2|Genius Tab Q3|Genius Tab G4|Genius Tab Q4|Genius Tab G-II|Genius TAB GII|Genius TAB GIII|Genius Tab S1',
            'GalapadTablet' => 'Android.*\bG1\b',
            'MicromaxTablet' => 'Funbook|Micromax.*\b(P250|P560|P360|P362|P600|P300|P350|P500|P275)\b',
            'KarbonnTablet' => 'Android.*\b(A39|A37|A34|ST8|ST10|ST7|Smart Tab3|Smart Tab2)\b',
            'AllFineTablet' => 'Fine7 Genius|Fine7 Shine|Fine7 Air|Fine8 Style|Fine9 More|Fine10 Joy|Fine11 Wide',
            'PROSCANTablet' => '\b(PEM63|PLT1023G|PLT1041|PLT1044|PLT1044G|PLT1091|PLT4311|PLT4311PL|PLT4315|PLT7030|PLT7033|PLT7033D|PLT7035|PLT7035D|PLT7044K|PLT7045K|PLT7045KB|PLT7071KG|PLT7072|PLT7223G|PLT7225G|PLT7777G|PLT7810K|PLT7849G|PLT7851G|PLT7852G|PLT8015|PLT8031|PLT8034|PLT8036|PLT8080K|PLT8082|PLT8088|PLT8223G|PLT8234G|PLT8235G|PLT8816K|PLT9011|PLT9045K|PLT9233G|PLT9735|PLT9760G|PLT9770G)\b',
            'YONESTablet' => 'BQ1078|BC1003|BC1077|RK9702|BC9730|BC9001|IT9001|BC7008|BC7010|BC708|BC728|BC7012|BC7030|BC7027|BC7026',
            'ChangJiaTablet' => 'TPC7102|TPC7103|TPC7105|TPC7106|TPC7107|TPC7201|TPC7203|TPC7205|TPC7210|TPC7708|TPC7709|TPC7712|TPC7110|TPC8101|TPC8103|TPC8105|TPC8106|TPC8203|TPC8205|TPC8503|TPC9106|TPC9701|TPC97101|TPC97103|TPC97105|TPC97106|TPC97111|TPC97113|TPC97203|TPC97603|TPC97809|TPC97205|TPC10101|TPC10103|TPC10106|TPC10111|TPC10203|TPC10205|TPC10503',
            'GUTablet' => 'TX-A1301|TX-M9002|Q702|kf026',
            'PointOfViewTablet' => 'TAB-P506|TAB-navi-7-3G-M|TAB-P517|TAB-P-527|TAB-P701|TAB-P703|TAB-P721|TAB-P731N|TAB-P741|TAB-P825|TAB-P905|TAB-P925|TAB-PR945|TAB-PL1015|TAB-P1025|TAB-PI1045|TAB-P1325|TAB-PROTAB[0-9]+|TAB-PROTAB25|TAB-PROTAB26|TAB-PROTAB27|TAB-PROTAB26XL|TAB-PROTAB2-IPS9|TAB-PROTAB30-IPS9|TAB-PROTAB25XXL|TAB-PROTAB26-IPS10|TAB-PROTAB30-IPS10',
            'OvermaxTablet' => 'OV-(SteelCore|NewBase|Basecore|Baseone|Exellen|Quattor|EduTab|Solution|ACTION|BasicTab|TeddyTab|MagicTab|Stream|TB-08|TB-09)',
            'HCLTablet' => 'HCL.*Tablet|Connect-3G-2.0|Connect-2G-2.0|ME Tablet U1|ME Tablet U2|ME Tablet G1|ME Tablet X1|ME Tablet Y2|ME Tablet Sync',
            'DPSTablet' => 'DPS Dream 9|DPS Dual 7',
            'VistureTablet' => 'V97 HD|i75 3G|Visture V4( HD)?|Visture V5( HD)?|Visture V10',
            'CrestaTablet' => 'CTP(-)?810|CTP(-)?818|CTP(-)?828|CTP(-)?838|CTP(-)?888|CTP(-)?978|CTP(-)?980|CTP(-)?987|CTP(-)?988|CTP(-)?989',
            'MediatekTablet' => '\bMT8125|MT8389|MT8135|MT8377\b',
            'ConcordeTablet' => 'Concorde([ ]+)?Tab|ConCorde ReadMan',
            'GoCleverTablet' => 'GOCLEVER TAB|A7GOCLEVER|M1042|M7841|M742|R1042BK|R1041|TAB A975|TAB A7842|TAB A741|TAB A741L|TAB M723G|TAB M721|TAB A1021|TAB I921|TAB R721|TAB I720|TAB T76|TAB R70|TAB R76.2|TAB R106|TAB R83.2|TAB M813G|TAB I721|GCTA722|TAB I70|TAB I71|TAB S73|TAB R73|TAB R74|TAB R93|TAB R75|TAB R76.1|TAB A73|TAB A93|TAB A93.2|TAB T72|TAB R83|TAB R974|TAB R973|TAB A101|TAB A103|TAB A104|TAB A104.2|R105BK|M713G|A972BK|TAB A971|TAB R974.2|TAB R104|TAB R83.3|TAB A1042',
            'ModecomTablet' => 'FreeTAB 9000|FreeTAB 7.4|FreeTAB 7004|FreeTAB 7800|FreeTAB 2096|FreeTAB 7.5|FreeTAB 1014|FreeTAB 1001 |FreeTAB 8001|FreeTAB 9706|FreeTAB 9702|FreeTAB 7003|FreeTAB 7002|FreeTAB 1002|FreeTAB 7801|FreeTAB 1331|FreeTAB 1004|FreeTAB 8002|FreeTAB 8014|FreeTAB 9704|FreeTAB 1003',
            'VoninoTablet' => '\b(Argus[ _]?S|Diamond[ _]?79HD|Emerald[ _]?78E|Luna[ _]?70C|Onyx[ _]?S|Onyx[ _]?Z|Orin[ _]?HD|Orin[ _]?S|Otis[ _]?S|SpeedStar[ _]?S|Magnet[ _]?M9|Primus[ _]?94[ _]?3G|Primus[ _]?94HD|Primus[ _]?QS|Android.*\bQ8\b|Sirius[ _]?EVO[ _]?QS|Sirius[ _]?QS|Spirit[ _]?S)\b',
            'ECSTablet' => 'V07OT2|TM105A|S10OT1|TR10CS1',
            'StorexTablet' => 'eZee[_\']?(Tab|Go)[0-9]+|TabLC7|Looney Tunes Tab',
            'VodafoneTablet' => 'SmartTab([ ]+)?[0-9]+|SmartTabII10|SmartTabII7|VF-1497',
            'EssentielBTablet' => 'Smart[ \']?TAB[ ]+?[0-9]+|Family[ \']?TAB2',
            'RossMoorTablet' => 'RM-790|RM-997|RMD-878G|RMD-974R|RMT-705A|RMT-701|RME-601|RMT-501|RMT-711',
            'iMobileTablet' => 'i-mobile i-note',
            'TolinoTablet' => 'tolino tab [0-9.]+|tolino shine',
            'AudioSonicTablet' => '\bC-22Q|T7-QC|T-17B|T-17P\b',
            'AMPETablet' => 'Android.* A78 ',
            'SkkTablet' => 'Android.* (SKYPAD|PHOENIX|CYCLOPS)',
            'TecnoTablet' => 'TECNO P9',
            'JXDTablet' => 'Android.* \b(F3000|A3300|JXD5000|JXD3000|JXD2000|JXD300B|JXD300|S5800|S7800|S602b|S5110b|S7300|S5300|S602|S603|S5100|S5110|S601|S7100a|P3000F|P3000s|P101|P200s|P1000m|P200m|P9100|P1000s|S6600b|S908|P1000|P300|S18|S6600|S9100)\b',
            'iJoyTablet' => 'Tablet (Spirit 7|Essentia|Galatea|Fusion|Onix 7|Landa|Titan|Scooby|Deox|Stella|Themis|Argon|Unique 7|Sygnus|Hexen|Finity 7|Cream|Cream X2|Jade|Neon 7|Neron 7|Kandy|Scape|Saphyr 7|Rebel|Biox|Rebel|Rebel 8GB|Myst|Draco 7|Myst|Tab7-004|Myst|Tadeo Jones|Tablet Boing|Arrow|Draco Dual Cam|Aurix|Mint|Amity|Revolution|Finity 9|Neon 9|T9w|Amity 4GB Dual Cam|Stone 4GB|Stone 8GB|Andromeda|Silken|X2|Andromeda II|Halley|Flame|Saphyr 9,7|Touch 8|Planet|Triton|Unique 10|Hexen 10|Memphis 4GB|Memphis 8GB|Onix 10)',
            'FX2Tablet' => 'FX2 PAD7|FX2 PAD10',
            'XoroTablet' => 'KidsPAD 701|PAD[ ]?712|PAD[ ]?714|PAD[ ]?716|PAD[ ]?717|PAD[ ]?718|PAD[ ]?720|PAD[ ]?721|PAD[ ]?722|PAD[ ]?790|PAD[ ]?792|PAD[ ]?900|PAD[ ]?9715D|PAD[ ]?9716DR|PAD[ ]?9718DR|PAD[ ]?9719QR|PAD[ ]?9720QR|TelePAD1030|Telepad1032|TelePAD730|TelePAD731|TelePAD732|TelePAD735Q|TelePAD830|TelePAD9730|TelePAD795|MegaPAD 1331|MegaPAD 1851|MegaPAD 2151',
            'ViewsonicTablet' => 'ViewPad 10pi|ViewPad 10e|ViewPad 10s|ViewPad E72|ViewPad7|ViewPad E100|ViewPad 7e|ViewSonic VB733|VB100a',
            'OdysTablet' => 'LOOX|XENO10|ODYS[ -](Space|EVO|Xpress|NOON)|\bXELIO\b|Xelio10Pro|XELIO7PHONETAB|XELIO10EXTREME|XELIOPT2|NEO_QUAD10',
            'CaptivaTablet' => 'CAPTIVA PAD',
            'IconbitTablet' => 'NetTAB|NT-3702|NT-3702S|NT-3702S|NT-3603P|NT-3603P|NT-0704S|NT-0704S|NT-3805C|NT-3805C|NT-0806C|NT-0806C|NT-0909T|NT-0909T|NT-0907S|NT-0907S|NT-0902S|NT-0902S',
            'TeclastTablet' => 'T98 4G|\bP80\b|\bX90HD\b|X98 Air|X98 Air 3G|\bX89\b|P80 3G|\bX80h\b|P98 Air|\bX89HD\b|P98 3G|\bP90HD\b|P89 3G|X98 3G|\bP70h\b|P79HD 3G|G18d 3G|\bP79HD\b|\bP89s\b|\bA88\b|\bP10HD\b|\bP19HD\b|G18 3G|\bP78HD\b|\bA78\b|\bP75\b|G17s 3G|G17h 3G|\bP85t\b|\bP90\b|\bP11\b|\bP98t\b|\bP98HD\b|\bG18d\b|\bP85s\b|\bP11HD\b|\bP88s\b|\bA80HD\b|\bA80se\b|\bA10h\b|\bP89\b|\bP78s\b|\bG18\b|\bP85\b|\bA70h\b|\bA70\b|\bG17\b|\bP18\b|\bA80s\b|\bA11s\b|\bP88HD\b|\bA80h\b|\bP76s\b|\bP76h\b|\bP98\b|\bA10HD\b|\bP78\b|\bP88\b|\bA11\b|\bA10t\b|\bP76a\b|\bP76t\b|\bP76e\b|\bP85HD\b|\bP85a\b|\bP86\b|\bP75HD\b|\bP76v\b|\bA12\b|\bP75a\b|\bA15\b|\bP76Ti\b|\bP81HD\b|\bA10\b|\bT760VE\b|\bT720HD\b|\bP76\b|\bP73\b|\bP71\b|\bP72\b|\bT720SE\b|\bC520Ti\b|\bT760\b|\bT720VE\b|T720-3GE|T720-WiFi',
            'OndaTablet' => '\b(V975i|Vi30|VX530|V701|Vi60|V701s|Vi50|V801s|V719|Vx610w|VX610W|V819i|Vi10|VX580W|Vi10|V711s|V813|V811|V820w|V820|Vi20|V711|VI30W|V712|V891w|V972|V819w|V820w|Vi60|V820w|V711|V813s|V801|V819|V975s|V801|V819|V819|V818|V811|V712|V975m|V101w|V961w|V812|V818|V971|V971s|V919|V989|V116w|V102w|V973|Vi40)\b[\s]+',
            'JaytechTablet' => 'TPC-PA762',
            'BlaupunktTablet' => 'Endeavour 800NG|Endeavour 1010',
            'DigmaTablet' => '\b(iDx10|iDx9|iDx8|iDx7|iDxD7|iDxD8|iDsQ8|iDsQ7|iDsQ8|iDsD10|iDnD7|3TS804H|iDsQ11|iDj7|iDs10)\b',
            'EvolioTablet' => 'ARIA_Mini_wifi|Aria[ _]Mini|Evolio X10|Evolio X7|Evolio X8|\bEvotab\b|\bNeura\b',
            'LavaTablet' => 'QPAD E704|\bIvoryS\b|E-TAB IVORY|\bE-TAB\b',
            'AocTablet' => 'MW0811|MW0812|MW0922|MTK8382|MW1031|MW0831|MW0821|MW0931|MW0712',
            'MpmanTablet' => 'MP11 OCTA|MP10 OCTA|MPQC1114|MPQC1004|MPQC994|MPQC974|MPQC973|MPQC804|MPQC784|MPQC780|\bMPG7\b|MPDCG75|MPDCG71|MPDC1006|MP101DC|MPDC9000|MPDC905|MPDC706HD|MPDC706|MPDC705|MPDC110|MPDC100|MPDC99|MPDC97|MPDC88|MPDC8|MPDC77|MP709|MID701|MID711|MID170|MPDC703|MPQC1010',
            'CelkonTablet' => 'CT695|CT888|CT[\s]?910|CT7 Tab|CT9 Tab|CT3 Tab|CT2 Tab|CT1 Tab|C820|C720|\bCT-1\b',
            'WolderTablet' => 'miTab \b(DIAMOND|SPACE|BROOKLYN|NEO|FLY|MANHATTAN|FUNK|EVOLUTION|SKY|GOCAR|IRON|GENIUS|POP|MINT|EPSILON|BROADWAY|JUMP|HOP|LEGEND|NEW AGE|LINE|ADVANCE|FEEL|FOLLOW|LIKE|LINK|LIVE|THINK|FREEDOM|CHICAGO|CLEVELAND|BALTIMORE-GH|IOWA|BOSTON|SEATTLE|PHOENIX|DALLAS|IN 101|MasterChef)\b',
            'MiTablet' => '\bMI PAD\b|\bHM NOTE 1W\b',
            'NibiruTablet' => 'Nibiru M1|Nibiru Jupiter One',
            'NexoTablet' => 'NEXO NOVA|NEXO 10|NEXO AVIO|NEXO FREE|NEXO GO|NEXO EVO|NEXO 3G|NEXO SMART|NEXO KIDDO|NEXO MOBI',
            'LeaderTablet' => 'TBLT10Q|TBLT10I|TBL-10WDKB|TBL-10WDKBO2013|TBL-W230V2|TBL-W450|TBL-W500|SV572|TBLT7I|TBA-AC7-8G|TBLT79|TBL-8W16|TBL-10W32|TBL-10WKB|TBL-W100',
            'UbislateTablet' => 'UbiSlate[\s]?7C',
            'PocketBookTablet' => 'Pocketbook',
            'KocasoTablet' => '\b(TB-1207)\b',
            'Hudl' => 'Hudl HT7S3|Hudl 2',
            'TelstraTablet' => 'T-Hub2',
            'Tablet' => 'Android.*\b97D\b|Tablet(?!.*PC)|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|rk30sdk|\bEVOTAB\b|M758A|ET904|ALUMIUM10|Smartfren Tab|Endeavour 1010|Tablet-PC-4|Tagi Tab|\bM6pro\b|CT1020W|arc 10HD|\bJolla\b|\bTP750\b'
        );

        /**
         * @access static 
         * @var array
         */
        public static $_countries = array(
            'AF' => 'AFGHANISTAN',
            'AL' => 'ALBANIA',
            'DZ' => 'ALGERIA',
            'AS' => 'AMERICAN SAMOA',
            'AD' => 'ANDORRA',
            'AO' => 'ANGOLA',
            'AI' => 'ANGUILLA',
            'AQ' => 'ANTARCTICA',
            'AG' => 'ANTIGUA AND BARBUDA',
            'AR' => 'ARGENTINA',
            'AM' => 'ARMENIA',
            'AW' => 'ARUBA',
            'AU' => 'AUSTRALIA',
            'AT' => 'AUSTRIA',
            'AZ' => 'AZERBAIJAN',
            'BS' => 'BAHAMAS',
            'BH' => 'BAHRAIN',
            'BD' => 'BANGLADESH',
            'BB' => 'BARBADOS',
            'BY' => 'BELARUS',
            'BE' => 'BELGIUM',
            'BZ' => 'BELIZE',
            'BJ' => 'BENIN',
            'BM' => 'BERMUDA',
            'BT' => 'BHUTAN',
            'BO' => 'BOLIVIA',
            'BA' => 'BOSNIA AND HERZEGOVINA',
            'BW' => 'BOTSWANA',
            'BV' => 'BOUVET ISLAND',
            'BR' => 'BRAZIL',
            'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
            'BN' => 'BRUNEI DARUSSALAM',
            'BG' => 'BULGARIA',
            'BF' => 'BURKINA FASO',
            'BI' => 'BURUNDI',
            'KH' => 'CAMBODIA',
            'CM' => 'CAMEROON',
            'CA' => 'CANADA',
            'CV' => 'CAPE VERDE',
            'KY' => 'CAYMAN ISLANDS',
            'CF' => 'CENTRAL AFRICAN REPUBLIC',
            'TD' => 'CHAD',
            'CL' => 'CHILE',
            'CN' => 'CHINA',
            'CX' => 'CHRISTMAS ISLAND',
            'CC' => 'COCOS (KEELING) ISLANDS',
            'CO' => 'COLOMBIA',
            'KM' => 'COMOROS',
            'CG' => 'CONGO',
            'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
            'CK' => 'COOK ISLANDS',
            'CR' => 'COSTA RICA',
            'CI' => 'COTE D IVOIRE',
            'HR' => 'CROATIA',
            'CU' => 'CUBA',
            'CY' => 'CYPRUS',
            'CZ' => 'CZECH REPUBLIC',
            'DK' => 'DENMARK',
            'DJ' => 'DJIBOUTI',
            'DM' => 'DOMINICA',
            'DO' => 'DOMINICAN REPUBLIC',
            'TP' => 'EAST TIMOR',
            'EC' => 'ECUADOR',
            'EG' => 'EGYPT',
            'SV' => 'EL SALVADOR',
            'GQ' => 'EQUATORIAL GUINEA',
            'ER' => 'ERITREA',
            'EE' => 'ESTONIA',
            'ET' => 'ETHIOPIA',
            'FK' => 'FALKLAND ISLANDS (MALVINAS)',
            'FO' => 'FAROE ISLANDS',
            'FJ' => 'FIJI',
            'FI' => 'FINLAND',
            'FR' => 'FRANCE',
            'GF' => 'FRENCH GUIANA',
            'PF' => 'FRENCH POLYNESIA',
            'TF' => 'FRENCH SOUTHERN TERRITORIES',
            'GA' => 'GABON',
            'GM' => 'GAMBIA',
            'GE' => 'GEORGIA',
            'DE' => 'GERMANY',
            'GH' => 'GHANA',
            'GI' => 'GIBRALTAR',
            'GR' => 'GREECE',
            'GL' => 'GREENLAND',
            'GD' => 'GRENADA',
            'GP' => 'GUADELOUPE',
            'GU' => 'GUAM',
            'GT' => 'GUATEMALA',
            'GN' => 'GUINEA',
            'GW' => 'GUINEA-BISSAU',
            'GY' => 'GUYANA',
            'HT' => 'HAITI',
            'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
            'VA' => 'HOLY SEE (VATICAN CITY STATE)',
            'HN' => 'HONDURAS',
            'HK' => 'HONG KONG',
            'HU' => 'HUNGARY',
            'IS' => 'ICELAND',
            'IN' => 'INDIA',
            'ID' => 'INDONESIA',
            'IR' => 'IRAN, ISLAMIC REPUBLIC OF',
            'IQ' => 'IRAQ',
            'IE' => 'IRELAND',
            'IL' => 'ISRAEL',
            'IT' => 'ITALY',
            'JM' => 'JAMAICA',
            'JP' => 'JAPAN',
            'JO' => 'JORDAN',
            'KZ' => 'KAZAKSTAN',
            'KE' => 'KENYA',
            'KI' => 'KIRIBATI',
            'KP' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
            'KR' => 'KOREA REPUBLIC OF',
            'KW' => 'KUWAIT',
            'KG' => 'KYRGYZSTAN',
            'LA' => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
            'LV' => 'LATVIA',
            'LB' => 'LEBANON',
            'LS' => 'LESOTHO',
            'LR' => 'LIBERIA',
            'LY' => 'LIBYAN ARAB JAMAHIRIYA',
            'LI' => 'LIECHTENSTEIN',
            'LT' => 'LITHUANIA',
            'LU' => 'LUXEMBOURG',
            'MO' => 'MACAU',
            'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
            'MG' => 'MADAGASCAR',
            'MW' => 'MALAWI',
            'MY' => 'MALAYSIA',
            'MV' => 'MALDIVES',
            'ML' => 'MALI',
            'MT' => 'MALTA',
            'MH' => 'MARSHALL ISLANDS',
            'MQ' => 'MARTINIQUE',
            'MR' => 'MAURITANIA',
            'MU' => 'MAURITIUS',
            'YT' => 'MAYOTTE',
            'MX' => 'MEXICO',
            'FM' => 'MICRONESIA, FEDERATED STATES OF',
            'MD' => 'MOLDOVA, REPUBLIC OF',
            'MC' => 'MONACO',
            'MN' => 'MONGOLIA',
            'MS' => 'MONTSERRAT',
            'MA' => 'MOROCCO',
            'MZ' => 'MOZAMBIQUE',
            'MM' => 'MYANMAR',
            'NA' => 'NAMIBIA',
            'NR' => 'NAURU',
            'NP' => 'NEPAL',
            'NL' => 'NETHERLANDS',
            'AN' => 'NETHERLANDS ANTILLES',
            'NC' => 'NEW CALEDONIA',
            'NZ' => 'NEW ZEALAND',
            'NI' => 'NICARAGUA',
            'NE' => 'NIGER',
            'NG' => 'NIGERIA',
            'NU' => 'NIUE',
            'NF' => 'NORFOLK ISLAND',
            'MP' => 'NORTHERN MARIANA ISLANDS',
            'NO' => 'NORWAY',
            'OM' => 'OMAN',
            'PK' => 'PAKISTAN',
            'PW' => 'PALAU',
            'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
            'PA' => 'PANAMA',
            'PG' => 'PAPUA NEW GUINEA',
            'PY' => 'PARAGUAY',
            'PE' => 'PERU',
            'PH' => 'PHILIPPINES',
            'PN' => 'PITCAIRN',
            'PL' => 'POLAND',
            'PT' => 'PORTUGAL',
            'PR' => 'PUERTO RICO',
            'QA' => 'QATAR',
            'RE' => 'REUNION',
            'RO' => 'ROMANIA',
            'RU' => 'RUSSIAN FEDERATION',
            'RW' => 'RWANDA',
            'SH' => 'SAINT HELENA',
            'KN' => 'SAINT KITTS AND NEVIS',
            'LC' => 'SAINT LUCIA',
            'PM' => 'SAINT PIERRE AND MIQUELON',
            'VC' => 'SAINT VINCENT AND THE GRENADINES',
            'WS' => 'SAMOA',
            'SM' => 'SAN MARINO',
            'ST' => 'SAO TOME AND PRINCIPE',
            'SA' => 'SAUDI ARABIA',
            'SN' => 'SENEGAL',
            'SC' => 'SEYCHELLES',
            'SL' => 'SIERRA LEONE',
            'SG' => 'SINGAPORE',
            'SK' => 'SLOVAKIA',
            'SI' => 'SLOVENIA',
            'SB' => 'SOLOMON ISLANDS',
            'SO' => 'SOMALIA',
            'ZA' => 'SOUTH AFRICA',
            'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
            'ES' => 'SPAIN',
            'LK' => 'SRI LANKA',
            'SD' => 'SUDAN',
            'SR' => 'SURINAME',
            'SJ' => 'SVALBARD AND JAN MAYEN',
            'SZ' => 'SWAZILAND',
            'SE' => 'SWEDEN',
            'CH' => 'SWITZERLAND',
            'SY' => 'SYRIAN ARAB REPUBLIC',
            'TW' => 'TAIWAN, PROVINCE OF CHINA',
            'TJ' => 'TAJIKISTAN',
            'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
            'TH' => 'THAILAND',
            'TG' => 'TOGO',
            'TK' => 'TOKELAU',
            'TO' => 'TONGA',
            'TT' => 'TRINIDAD AND TOBAGO',
            'TN' => 'TUNISIA',
            'TR' => 'TURKEY',
            'TM' => 'TURKMENISTAN',
            'TC' => 'TURKS AND CAICOS ISLANDS',
            'TV' => 'TUVALU',
            'UG' => 'UGANDA',
            'UA' => 'UKRAINE',
            'AE' => 'UNITED ARAB EMIRATES',
            'GB' => 'UNITED KINGDOM',
            'US' => 'UNITED STATES',
            'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
            'UY' => 'URUGUAY',
            'UZ' => 'UZBEKISTAN',
            'VU' => 'VANUATU',
            'VE' => 'VENEZUELA',
            'VN' => 'VIET NAM',
            'VG' => 'VIRGIN ISLANDS, BRITISH',
            'VI' => 'VIRGIN ISLANDS, U.S.',
            'WF' => 'WALLIS AND FUTUNA',
            'EH' => 'WESTERN SAHARA',
            'YE' => 'YEMEN',
            'YU' => 'YUGOSLAVIA',
            'ZM' => 'ZAMBIA',
            'ZW' => 'ZIMBABWE',
        );

        /**
         * @name __construct
         * @description the class constructor
         * @access public
         * @param array $options
         * @return Client
         */
        public function __construct($options = array())
        {
            parent::__construct($options);
        }
        
        /**
         * @name reset
         * @description reset all properties
         * @access public
         * @return
         */
        public function retreiveInfo()
        {
            $deviceType = '';
            
            if($this->isComputer() == 'true')
            {
                $deviceType = strpos($this->_os,self::OS_APPLE) > -1 ? 'Macintosh' : 'Computer';
            }
            else if($this->isMobile() == 'true')
            {
                $deviceType = 'Mobile Phone';
            }
            else if($this->isTablet() == 'true')
            {
                $deviceType = 'Tablet';
            }
            else if($this->isFacebook() == 'true')
            {
                $deviceType = 'Facebook Request';
            }
            else if($this->isRobot() == 'true')
            {
                $deviceType = 'Robot';
            }
            
            return array(
                'agent' => $this->_agent,
                'ip' => $this->_ip,
                'country-code' => $this->_countryCode == 'GB' ? 'UK' : $this->_countryCode,
                'country' => $this->_country,
                'region' => $this->_region,
                'city' => $this->_city,
                'language' => $this->_language,
                'device-name' => $this->_deviceName,
                'device-type' => $deviceType,
                'os' => $this->_os,
                'browser-name' => $this->_browserName,
                'browser-version' => $this->_browserVersion
            );
        }
        
        /**
         * @name reset
         * @description reset all properties
         * @access public
         * @return
         */
        public function reset()
        {
            $this->_agent = Server::get('HTTP_USER_AGENT');
            $this->_ip = '127.0.0.1';
            $this->_deviceName = self::DEVICE_UNKNOWN;
            $this->_browserName = self::BROWSER_UNKNOWN;
            $this->_browserVersion = self::VERSION_UNKNOWN;
            $this->_os = self::OS_UNKNOWN;
            $this->_isMobile = 'false';
            $this->_isTablet = 'false';
            $this->_isRobot = 'false';
            $this->_isFacebook = 'false';
            $this->_isComputer = 'true';
        }
        
        /**
         * @name isBrowser
         * @description check to see if the specific browser is valid
         * @access public
         * @return
         */
        public function isBrowser($browserName)
        {
            return (0 == strcasecmp($this->_browserName, trim($browserName)));
        }

        /**
         * @name getBrowser
         * @description get the name of the browser. all return types are from the class contants
         * @access public
         * @return string name of the browser
         */
        public function getBrowser()
        {
            return $this->_browserName;
        }

        /**
         * @name setBrowser
         * @description set the name of the browser
         * @access public
         * @return
         */
        public function setBrowser($browser)
        {
            $this->_browserName = $browser;
        }

        /**
         * @name getOS
         * @description get the name of the OS. all return types are from the class contants
         * @access public
         * @return string name of the OS
         */
        public function getOS()
        {
            return $this->_os;
        }

        /**
         * @name setOS
         * @description set the name of the OS
         * @access public
         * @return
         */
        public function setOS($os)
        {
            $this->_os = $os;
        }

        /**
         * @name getVersion
         * @description get the version of the browser
         * @access public
         * @return string Version of the browser (will only contain alpha-numeric characters and a period)
         */
        public function getVersion()
        {
            return $this->_browserVersion;
        }

        /**
         * @name setVersion
         * @description set the version of the browser
         * @access public
         * @return
         */
        public function setVersion($version)
        {
            $this->_browserVersion = preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);
        }

        /**
         * @name isComputer
         * @description check if the browser from a computer?
         * @access public
         * @return boolean true if the browser is from a computer otherwise false
         */
        public function isComputer()
        {
            return $this->_isComputer;
        }
        
        /**
         * @name isMobile
         * @description check if the browser from a mobile device?
         * @access public
         * @return boolean true if the browser is from a mobile device otherwise false
         */
        public function isMobile()
        {
            return $this->_isMobile;
        }

        /**
         * @name isTablet
         * @description check if the browser from a tablet device?
         * @access public
         * @return boolean true if the browser is from a tablet device otherwise false
         */
        public function isTablet()
        {
            return $this->_isTablet;
        }

        /**
         * @name isRobot
         * @description check if the browser from a robot (ex Slurp,GoogleBot)?
         * @access public
         * @return boolean true if the browser is from a robot device otherwise false
         */
        public function isRobot()
        {
            return $this->_isRobot;
        }

        /**
         * @name isFacebook
         * @description check if the browser from facebook?
         * @access public
         * @return boolean true if the browser is from facebook otherwise false
         */
        public function isFacebook()
        {
            return $this->_isFacebook;
        }

        /**
         * @name setComputer
         * @description set the browser to be computer
         * @access protected
         * @return
         */
        protected function setComputer($value = true)
        {
            $this->_isComputer = ($value) ? 'true' : 'false';
        }
        
        /**
         * @name setMobile
         * @description set the browser to be mobile
         * @access protected
         * @return
         */
        protected function setMobile($value = true)
        {
            $this->_isMobile = ($value) ? 'true' : 'false';
            
            if($value == true)
            {
                $this->setComputer(false);
            }
            else
            {
                $this->setComputer(true);
            }
        }

        /**
         * @name setTablet
         * @description set the browser to be tablet
         * @access protected
         * @return
         */
        protected function setTablet($value = true)
        {
            $this->_isTablet = ($value) ? 'true' : 'false';
            
            if($value == true)
            {
                $this->setComputer(false);
            }
            else
            {
                $this->setComputer(true);
            }
        }

        /**
         * @name setRobot
         * @description set the browser to be a robot
         * @access protected
         * @return
         */
        protected function setRobot($value = true)
        {
            $this->_isRobot = ($value) ? 'true' : 'false';
        }

        /**
         * @name setFacebook
         * @description set the browser to be a facebook request
         * @access protected
         * @return
         */
        protected function setFacebook($value = true)
        {
            $this->_isFacebook = ($value) ? 'true' : 'false';
        }

        /**
         * @name getUserAgent
         * @description get the user agent value in use to determine the browser
         * @access public
         * @return string The user agent from the HTTP header
         */
        public function getUserAgent()
        {
            return $this->_agent;
        }

        /**
         * @name setUserAgent
         * @description set the user agent value (the construction will use the HTTP header value - this will overwrite it)
         * @access public
         * @return
         */
        public function setUserAgent($userAgent)
        {
            $this->reset();
            $this->_agent = $userAgent;
            $this->determine();
        }

        /**
         * @name isChromeFrame
         * @description check if the browser is actually "chromeframe"
         * @access public
         * @since 1.7
         * @return boolean true if the browser is using chromeframe
         */
        public function isChromeFrame()
        {
            return (strpos($this->_agent, "chromeframe") !== false);
        }
       
        /**
         * @name determine
         * @description a routine to calculate and determine what the browser is in use (including OS)
         * @access protected
         * @return
         */
        public function determine()
        {
            $this->checkIp();
            $this->checkIpMetaInformation();
            $this->checkOS();
            $this->checkBrowsers();
            $this->checkDeviceName();
        }

        /**
         * @name checkIp
         * @description a routine to determine the client IP
         * @access protected
         * @return
         */
        public function checkIp()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
            {
                $this->_ip = $_SERVER['HTTP_CLIENT_IP'];
            } 
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $this->_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } 
            else 
            {
                $this->_ip = $_SERVER['REMOTE_ADDR'];
            }
            
            if(filter_var($this->_ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))
            {
                $ipv4 = hexdec(substr($this->_ip, 0, 2)). "." . hexdec(substr($this->_ip, 2, 2)). "." . hexdec(substr($this->_ip, 5, 2)). "." . hexdec(substr($this->_ip, 7, 2));
                $this->_ip = $ipv4;
            }
            
            if(!filter_var($this->_ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
            {
                $match = array();
                
                if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$this->_ip, $match)) 
                {
                    $this->_ip = count($match) > 0 && filter_var($match[0],FILTER_VALIDATE_IP) ? $match[0] : "";
                }
            }
        }
        
        /**
         * @name getIp
         * @description a routine to determine the client IP
         * @access static
         * @return
         */
        public static function getIp()
        {
            $ip = "";
            
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
            {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } 
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } 
            else 
            {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            
            if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))
            {
                $ipv4 = hexdec(substr($ip, 0, 2)). "." . hexdec(substr($ip, 2, 2)). "." . hexdec(substr($ip, 5, 2)). "." . hexdec(substr($ip, 7, 2));
                $ip = $ipv4;
            }
            
            if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
            {
                $match = array();
                
                if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$ip, $match)) 
                {
                    $ip = count($match) > 0 && filter_var($match[0],FILTER_VALIDATE_IP) ? $match[0] : "";
                }
            }
            
            return $ip;
        }
        
        /**
         * @name checkIpMetaInformation
         * @description a routine to determine the client IP meta info
         * @access protected
         * @return
         */
        public function checkIpMetaInformation()
        {
            if(filter_var($this->_ip,FILTER_VALIDATE_IP))
            {              
                $geoResult = json_decode(file_get_contents("http://api.ipinfodb.com/v3/ip-city/?key={$this->_apiKey}&ip={$this->_ip}&format=json"),true);
                $this->_region = $geoResult['regionName'] != null ? $geoResult['regionName'] : "";
                $this->_city = $geoResult['cityName'] != null ? $geoResult['cityName'] : "";
                $this->_countryCode = key_exists('countryCode',$geoResult) && strlen($geoResult['countryCode']) == 2 ? strtoupper($geoResult['countryCode']) : 'US';
                $this->_country = key_exists($this->_countryCode,self::$_countries) ? ucfirst(strtolower(self::$_countries[$this->_countryCode])) : $geoResult['countryName'];
                $this->_language = ucfirst(substr(Server::get('HTTP_ACCEPT_LANGUAGE'), 0, 2));
            }
        }
        
        /**
         * @name checkDeviceName
         * @description set the device name
         * @access protected
         * @return
         */
        public function checkDeviceName()
        {
            if($this->_isMobile == true || $this->_isTablet == true)
            {
                $devices = ($this->_isMobile == 'true') ? $this->_phoneDevices : $this->_tabletDevices;
             
                # check device name 
                foreach ($devices as $name => $regex) 
                {
                    $matches = array();
                    $match = (bool) preg_match(sprintf('#%s#is', $regex),$this->_agent, $matches);
                    
                    if ($match) 
                    {
                        $this->_deviceName = $name;
                    }
                } 
            }
            
            # get full name 
            preg_match_all('/\((.*?)\)/',$this->_agent,$matches);

            if(count($matches) && count($matches[1]))
            {
                $build = explode(';',$matches[1][0]);

                if(count($build))
                {
                    if($this->_isComputer == 'true')
                    {
                        $this->_deviceName = strpos($this->_os,self::OS_APPLE) > -1 ? 'MAC' : 'PC';
                    }
                    else
                    {
                        if(count($build) > 2)
                        {
                            $this->_deviceName .= ' ( ' . trim($build[2]) . ' )'; 
                        }
                    }
                }
            }
        }
        
        /**
         * @name checkBrowsers
         * @description a routine to determine the browser type
         * @access protected
         * @return
         */
        public function checkBrowsers()
        {
            return (

                $this->checkBrowserWebTv() ||
                $this->checkBrowserEdge() ||
                $this->checkBrowserInternetExplorer() ||
                $this->checkBrowserOpera() ||
                $this->checkBrowserGaleon() ||
                $this->checkBrowserNetscapeNavigator9Plus() ||
                $this->checkBrowserVivaldi() ||
                $this->checkBrowserFirefox() ||
                $this->checkBrowserChrome() ||
                $this->checkBrowserOmniWeb() ||

                # common mobile
                $this->checkBrowserAndroid() ||
                $this->checkBrowseriPad() ||
                $this->checkBrowseriPod() ||
                $this->checkBrowseriPhone() ||
                $this->checkBrowserBlackBerry() ||
                $this->checkBrowserNokia() ||

                # common bots
                $this->checkBrowserGoogleBot() ||
                $this->checkBrowserMSNBot() ||
                $this->checkBrowserBingBot() ||
                $this->checkBrowserSlurp() ||

                # check for facebook external hit when loading URL
                $this->checkFacebookExternalHit() ||

                # WebKit base check (post mobile and others)
                $this->checkBrowserSafari() ||

                # everyone else
                $this->checkBrowserNetPositive() ||
                $this->checkBrowserFirebird() ||
                $this->checkBrowserKonqueror() ||
                $this->checkBrowserIcab() ||
                $this->checkBrowserPhoenix() ||
                $this->checkBrowserAmaya() ||
                $this->checkBrowserLynx() ||
                $this->checkBrowserShiretoko() ||
                $this->checkBrowserIceCat() ||
                $this->checkBrowserIceweasel() ||
                $this->checkBrowserW3CValidator() ||
                $this->checkBrowserPlayStation() ||
                $this->checkBrowserMozilla() 
            );
        }

        /**
         * @name checkBrowserBlackBerry
         * @description determine if the user is using a BlackBerry
         * @access protected
         * @return boolean true if the browser is the BlackBerry browser otherwise false
         */
        public function checkBrowserBlackBerry()
        {
            if (stripos($this->_agent, 'blackberry') !== false) 
            {
                $result = explode("/", stristr($this->_agent, "BlackBerry"));

                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->_browserName = self::BROWSER_BLACKBERRY;
                    $this->setMobile(true);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserGoogleBot
         * @description determine if the browser is the GoogleBot or not
         * @access protected
         * @return boolean true if the browser is the GoogletBot otherwise false
         */
        public function checkBrowserGoogleBot()
        {
            if (stripos($this->_agent, 'googlebot') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'googlebot'));

                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion(str_replace(';', '', $version[0]));
                    $this->_browserName = self::BROWSER_GOOGLEBOT;
                    $this->setRobot(true);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserMSNBot
         * @description determine if the browser is the MSNBot or not
         * @access protected
         * @return boolean true if the browser is the MSNBot otherwise false
         */
        protected function checkBrowserMSNBot()
        {
            if (stripos($this->_agent, "msnbot") !== false) 
            {
                $result = explode("/", stristr($this->_agent, "msnbot"));

                if (isset($result[1])) 
                {
                    $version = explode(" ", $result[1]);
                    $this->setVersion(str_replace(";", "", $version[0]));
                    $this->_browserName = self::BROWSER_MSNBOT;
                    $this->setRobot(true);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserBingBot
         * @description determine if the browser is the BingBot or not
         * @access protected
         * @return boolean true if the browser is the BingBot otherwise false
         */
        protected function checkBrowserBingBot()
        {
            if (stripos($this->_agent, "bingbot") !== false) 
            {
                $result = explode("/", stristr($this->_agent, "bingbot"));

                if (isset($result[1])) 
                {
                    $version = explode(" ", $result[1]);
                    $this->setVersion(str_replace(";", "", $version[0]));
                    $this->_browserName = self::BROWSER_BINGBOT;
                    $this->setRobot(true);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserW3CValidator
         * @description determine if the browser is the W3C Validator or not
         * @access protected
         * @return boolean true if the browser is the W3C Validator otherwise false
         */
        protected function checkBrowserW3CValidator()
        {
            if (stripos($this->_agent, 'W3C-checklink') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'W3C-checklink'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->_browserName = self::BROWSER_W3CVALIDATOR;
                    
                    return true;
                }
            } 
            else if (stripos($this->_agent, 'W3C_Validator') !== false) 
            {
                $ua = str_replace("W3C_Validator ", "W3C_Validator/", $this->_agent);
                $result = explode('/', stristr($ua, 'W3C_Validator'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->_browserName = self::BROWSER_W3CVALIDATOR;
                    
                    return true;
                }
            } 
            else if (stripos($this->_agent, 'W3C-mobileOK') !== false) 
            {
                $this->_browserName = self::BROWSER_W3CVALIDATOR;
                $this->setMobile(true);
                
                return true;
            }

            return false;
        }

        /**
         * @name checkBrowserSlurp
         * @description determine if the browser is the Yahoo! Slurp Robot or not
         * @access protected
         * @return boolean true if the browser is the Yahoo! Slurp Robot otherwise false
         */
        protected function checkBrowserSlurp()
        {
            if (stripos($this->_agent, 'slurp') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Slurp'));

                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->_browserName = self::BROWSER_SLURP;
                    $this->setRobot(true);
                    $this->setMobile(false);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserEdge
         * @description determine if the browser is Edge or not
         * @access protected
         * @return boolean true if the browser is Edge otherwise false
         */
        protected function checkBrowserEdge()
        {
            if( stripos($this->_agent,'Edge/') !== false ) 
            {
                $result = explode('/', stristr($this->_agent, 'Edge'));

                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_EDGE);

                    if(stripos($this->_agent, 'Windows Phone') !== false || stripos($this->_agent, 'Android') !== false) 
                    {
                        $this->setMobile(true);
                    }

                    return true;
                }
            }

            return false;
        }

        /**
         * @name checkBrowserInternetExplorer
         * @description determine if the browser is Internet Explorer or not
         * @access protected
         * @return boolean true if the browser is Internet Explorer otherwise false
         */
        protected function checkBrowserInternetExplorer()
        {
            if( stripos($this->_agent,'Trident/7.0; rv:11.0') !== false ) 
            {
                $this->setBrowser(self::BROWSER_IE);
                $this->setVersion('11.0');
                
                return true;
            }
            else if (stripos($this->_agent, 'microsoft internet explorer') !== false) 
            {
                $this->setBrowser(self::BROWSER_IE);
                $this->setVersion('1.0');
                $result = stristr($this->_agent, '/');
                
                if (preg_match('/308|425|426|474|0b1/i', $result)) 
                {
                    $this->setVersion('1.5');
                }
                
                return true;
            } 
            else if (stripos($this->_agent, 'msie') !== false && stripos($this->_agent, 'opera') === false) 
            {
                if (stripos($this->_agent, 'msnb') !== false) 
                {
                    $result = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'MSN'));
                    
                    if (isset($result[1])) 
                    {
                        $this->setBrowser(self::BROWSER_MSN);
                        $this->setVersion(str_replace(array('(', ')', ';'), '', $result[1]));
                        
                        return true;
                    }
                }
                
                $result = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'msie'));
                
                if (isset($result[1])) 
                {
                    $this->setBrowser(self::BROWSER_IE);
                    $this->setVersion(str_replace(array('(', ')', ';'), '', $result[1]));
                    
                    if(stripos($this->_agent, 'IEMobile') !== false) 
                    {
                        $this->setBrowser(self::BROWSER_POCKET_IE);
                        $this->setMobile(true);
                    }
                    
                    return true;
                }
            }
                else if(stripos($this->_agent, 'trident') !== false) 
                {
                    $this->setBrowser(self::BROWSER_IE);
                    $result = explode('rv:', $this->_agent);
                    
                    if (isset($result[1])) 
                    {
                        $this->setVersion(preg_replace('/[^0-9.]+/', '', $result[1]));
                        $this->_agent = str_replace(array("Mozilla", "Gecko"), "MSIE", $this->_agent);
                    }
                }
                else if (stripos($this->_agent, 'mspie') !== false || stripos($this->_agent, 'pocket') !== false) 
                {
                    $result = explode(' ', stristr($this->_agent, 'mspie'));
                    
                    if (isset($result[1])) 
                    {
                        $this->setOS(self::OS_WINDOWS_CE);
                        $this->setBrowser(self::BROWSER_POCKET_IE);
                        $this->setMobile(true);

                        if (stripos($this->_agent, 'mspie') !== false) 
                        {
                            $this->setVersion($result[1]);
                        } 
                        else 
                        {
                            $version = explode('/', $this->_agent);
                            
                            if (isset($version[1])) 
                            {
                                $this->setVersion($version[1]);
                            }
                        }
                        
                        return true;
                    }
                }
                
            return false;
        }

        /**
         * @name checkBrowserOpera
         * @description determine if the browser is Opera or not
         * @access protected
         * @return boolean true if the browser is Opera otherwise false
         */
        protected function checkBrowserOpera()
        {
            if (stripos($this->_agent, 'opera mini') !== false) 
            {
                $globalResult = stristr($this->_agent, 'opera mini');
                
                if (preg_match('/\//', $globalResult)) 
                {
                    $result = explode('/', $globalResult);
                    
                    if (isset($result[1])) 
                    {
                        $version = explode(' ', $result[1]);
                        $this->setVersion($version[0]);
                    }
                } 
                else 
                {
                    $version = explode(' ', stristr($globalResult, 'opera mini'));
                    
                    if (isset($version[1])) 
                    {
                        $this->setVersion($version[1]);
                    }
                }
                
                $this->_browserName = self::BROWSER_OPERA_MINI;
                $this->setMobile(true);
                
                return true;
            } 
            else if (stripos($this->_agent, 'opera') !== false) 
            {
                $globalResult = stristr($this->_agent, 'opera');
                $matches = array();
                
                if (preg_match('/Version\/(1*.*)$/', $globalResult, $matches)) 
                {
                    $this->setVersion($matches[1]);
                } 
                else if (preg_match('/\//', $globalResult)) 
                {
                    $result = explode('/', str_replace("(", " ", $globalResult));
                    
                    if (isset($result[1])) 
                    {
                        $version = explode(' ', $result[1]);
                        $this->setVersion($version[0]);
                    }
                } 
                else
                {
                    $version = explode(' ', stristr($globalResult, 'opera'));
                    $this->setVersion(isset($version[1]) ? $version[1] : "");
                }
                
                if (stripos($this->_agent, 'Opera Mobi') !== false) 
                {
                    $this->setMobile(true);
                }
                
                $this->_browserName = self::BROWSER_OPERA;
                
                return true;
            } 
            else if (stripos($this->_agent, 'OPR') !== false) 
            {
                $globalResult = stristr($this->_agent, 'OPR');
                
                if (preg_match('/\//', $globalResult)) 
                {
                    $result = explode('/', str_replace("(", " ", $globalResult));
                    
                    if (isset($result[1])) 
                    {
                        $version = explode(' ', $result[1]);
                        $this->setVersion($version[0]);
                    }
                }
                
                if (stripos($this->_agent, 'Mobile') !== false) 
                {
                    $this->setMobile(true);
                }
                
                $this->_browserName = self::BROWSER_OPERA;
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserChrome
         * @description determine if the browser is Chrome or not
         * @access protected
         * @return boolean true if the browser is Chrome otherwise false
         */
        protected function checkBrowserChrome()
        {
            if (stripos($this->_agent, 'Chrome') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Chrome'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_CHROME);

                    if (stripos($this->_agent, 'Android') !== false) 
                    {
                        if (stripos($this->_agent, 'Mobile') !== false) 
                        {
                            $this->setMobile(true);
                        } 
                        else 
                        {
                            $this->setTablet(true);
                        }
                    }
                    
                    return true;
                }
            }
            return false;
        }

        /**
         * @name checkBrowserWebTv
         * @description determine if the browser is WebTv or not
         * @access protected
         * @return boolean true if the browser is WebTv otherwise false
         */
        protected function checkBrowserWebTv()
        {
            if (stripos($this->_agent, 'webtv') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'webtv'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_WEBTV);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserNetPositive
         * @description determine if the browser is NetPositive or not
         * @access protected
         * @return boolean true if the browser is NetPositive otherwise false
         */
        protected function checkBrowserNetPositive()
        {
            if (stripos($this->_agent, 'NetPositive') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'NetPositive'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion(str_replace(array('(', ')', ';'), '', $version[0]));
                    $this->setBrowser(self::BROWSER_NETPOSITIVE);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserGaleon
         * @description determine if the browser is Galeon or not
         * @access protected
         * @return boolean true if the browser is Galeon otherwise false
         */
        protected function checkBrowserGaleon()
        {
            if (stripos($this->_agent, 'galeon') !== false) 
            {
                $result = explode(' ', stristr($this->_agent, 'galeon'));
                $version = explode('/', $result[0]);
                
                if (isset($version[1])) 
                {
                    $this->setVersion($version[1]);
                    $this->setBrowser(self::BROWSER_GALEON);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserKonqueror
         * @description determine if the browser is Konqueror or not
         * @access protected
         * @return boolean true if the browser is Konqueror otherwise false
         */
        protected function checkBrowserKonqueror()
        {
            if (stripos($this->_agent, 'Konqueror') !== false) 
            {
                $result = explode(' ', stristr($this->_agent, 'Konqueror'));
                $version = explode('/', $result[0]);
                
                if (isset($version[1])) 
                {
                    $this->setVersion($version[1]);
                    $this->setBrowser(self::BROWSER_KONQUEROR);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserIcab
         * @description determine if the browser is iCab or not
         * @access protected
         * @return boolean true if the browser is iCab otherwise false
         */
        protected function checkBrowserIcab()
        {
            if (stripos($this->_agent, 'icab') !== false) 
            {
                $version = explode(' ', stristr(str_replace('/', ' ', $this->_agent), 'icab'));
                
                if (isset($version[1])) 
                {
                    $this->setVersion($version[1]);
                    $this->setBrowser(self::BROWSER_ICAB);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserOmniWeb
         * @description determine if the browser is OmniWeb or not
         * @access protected
         * @return boolean true if the browser is OmniWeb otherwise false
         */
        protected function checkBrowserOmniWeb()
        {
            if (stripos($this->_agent, 'omniweb') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'omniweb'));
                $version = explode(' ', isset($result[1]) ? $result[1] : "");
                $this->setVersion($version[0]);
                $this->setBrowser(self::BROWSER_OMNIWEB);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserPhoenix
         * @description determine if the browser is Phoenix or not
         * @access protected
         * @return boolean true if the browser is Phoenix otherwise false
         */
        protected function checkBrowserPhoenix()
        {
            if (stripos($this->_agent, 'Phoenix') !== false) 
            {
                $version = explode('/', stristr($this->_agent, 'Phoenix'));
                
                if (isset($version[1])) 
                {
                    $this->setVersion($version[1]);
                    $this->setBrowser(self::BROWSER_PHOENIX);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserFirebird
         * @description determine if the browser is Firebird or not
         * @access protected
         * @return boolean true if the browser is Firebird otherwise false
         */
        protected function checkBrowserFirebird()
        {
            if (stripos($this->_agent, 'Firebird') !== false) 
            {
                $version = explode('/', stristr($this->_agent, 'Firebird'));
                
                if (isset($version[1])) 
                {
                    $this->setVersion($version[1]);
                    $this->setBrowser(self::BROWSER_FIREBIRD);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserFirebird
         * @description determine if the browser is Netscape Navigator 9+ or not
         * @access protected
         * @return boolean true if the browser is Netscape Navigator 9+ otherwise false
         */
        protected function checkBrowserNetscapeNavigator9Plus()
        {
            $matches = array();
            
            if (stripos($this->_agent, 'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i', $this->_agent, $matches))
            {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
                
                return true;
            } 
            else if (stripos($this->_agent, 'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i', $this->_agent, $matches)) 
            {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserShiretoko
         * @description determine if the browser is Shiretoko or not
         * @access protected
         * @return boolean true if the browser is Shiretoko otherwise false
         */
        protected function checkBrowserShiretoko()
        {
            $matches = array();

            if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i', $this->_agent, $matches)) 
            {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_SHIRETOKO);

                return true;
            }

            return false;
        }

        /**
         * @name checkBrowserIceCat
         * @description determine if the browser is Ice Cat or not
         * @access protected
         * @return boolean true if the browser is Ice Cat otherwise false
         */
        protected function checkBrowserIceCat()
        {
            $matches = array();
            
            if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i', $this->_agent, $matches)) 
            {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_ICECAT);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserNokia
         * @description determine if the browser is Nokia or not
         * @access protected
         * @return boolean true if the browser is Nokia otherwise false
         */
        protected function checkBrowserNokia()
        {
            $matches = array();
            
            if (preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->_agent, $matches)) 
            {
                $this->setVersion($matches[2]);
                
                if (stripos($this->_agent, 'Series60') !== false || strpos($this->_agent, 'S60') !== false) 
                {
                    $this->setBrowser(self::BROWSER_NOKIA_S60);
                } 
                else 
                {
                    $this->setBrowser(self::BROWSER_NOKIA);
                }
                
                $this->setMobile(true);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserFirefox
         * @description determine if the browser is Firefox or not
         * @access protected
         * @return boolean true if the browser is Firefox otherwise false
         */
        protected function checkBrowserFirefox()
        {
            $matches = array();

            if (stripos($this->_agent, 'safari') === false)
            {
                if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", $this->_agent, $matches)) 
                {
                    $this->setVersion($matches[1]);
                    $this->setBrowser(self::BROWSER_FIREFOX);

                    #Firefox on Android
                    if (stripos($this->_agent, 'Android') !== false) 
                    {
                        if (stripos($this->_agent, 'Mobile') !== false)
                        {
                            $this->setMobile(true);
                        } 
                        else 
                        {
                            $this->setTablet(true);
                        }
                    }

                    return true;
                } 
                else if (preg_match("/Firefox$/i", $this->_agent, $matches)) 
                {
                    $this->setVersion("");
                    $this->setBrowser(self::BROWSER_FIREFOX);

                    return true;
                }
            }

            return false;
        }

        /**
         * @name checkBrowserIceweasel
         * @description determine if the browser is Iceweasel or not
         * @access protected
         * @return boolean true if the browser is Iceweasel otherwise false
         */
        protected function checkBrowserIceweasel()
        {
            if (stripos($this->_agent, 'Iceweasel') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Iceweasel'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_ICEWEASEL);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserMozilla
         * @description determine if the browser is Mozilla or not
         * @access protected
         * @return boolean true if the browser is Mozilla otherwise false
         */
        protected function checkBrowserMozilla()
        {
            $matches = array();
            
            if (stripos($this->_agent, 'mozilla') !== false && preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent) && stripos($this->_agent, 'netscape') === false) 
            {
                $version = explode(' ', stristr($this->_agent, 'rv:'));
                preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent, $version);
                $this->setVersion(str_replace('rv:', '', $version[0]));
                $this->setBrowser(self::BROWSER_MOZILLA);
                
                return true;
            } 
            else if (stripos($this->_agent, 'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i', $this->_agent) && stripos($this->_agent, 'netscape') === false)
            {
                $version = explode('', stristr($this->_agent, 'rv:'));
                $this->setVersion(str_replace('rv:', '', $version[0]));
                $this->setBrowser(self::BROWSER_MOZILLA);
                
                return true;
            } 
            else if (stripos($this->_agent, 'mozilla') !== false && preg_match('/mozilla\/([^ ]*)/i', $this->_agent, $matches) && stripos($this->_agent, 'netscape') === false)
            {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_MOZILLA);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserLynx
         * @description determine if the browser is Lynx or not
         * @access protected
         * @return boolean true if the browser is Lynx otherwise false
         */
        protected function checkBrowserLynx()
        {
            if (stripos($this->_agent, 'lynx') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Lynx'));
                $version = explode(' ', (isset($result[1]) ? $result[1] : ""));
                $this->setVersion($version[0]);
                $this->setBrowser(self::BROWSER_LYNX);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserAmaya
         * @description determine if the browser is Amaya or not
         * @access protected
         * @return boolean true if the browser is Amaya otherwise false
         */
        protected function checkBrowserAmaya()
        {
            if (stripos($this->_agent, 'amaya') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Amaya'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_AMAYA);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserSafari
         * @description determine if the browser is Safari or not
         * @access protected
         * @return boolean true if the browser is Safari otherwise false
         */
        protected function checkBrowserSafari()
        {
            if (stripos($this->_agent, 'Safari') !== false && stripos($this->_agent, 'iPhone') === false && stripos($this->_agent, 'iPod') === false) 
            {
                $result = explode('/', stristr($this->_agent, 'Version'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                } 
                else 
                {
                    $this->setVersion(self::VERSION_UNKNOWN);
                }
                
                $this->setBrowser(self::BROWSER_SAFARI);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkFacebookExternalHit
         * @description determine if URL is loaded from FacebookExternalHit
         * @access protected
         * @return boolean true if it detects FacebookExternalHit otherwise false
         */
        protected function checkFacebookExternalHit()
        {
            if(stristr($this->_agent,'FacebookExternalHit'))
            {
                $this->setRobot(true);
                $this->setFacebook(true);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkForFacebookIos
         * @description determine if URL is being loaded from internal Facebook browser
         * @access protected
         * @return boolean true if it detects internal Facebook browser otherwise false
         */
        protected function checkForFacebookIos()
        {
            if(stristr($this->_agent,'FBIOS'))
            {
                $this->setFacebook(true);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name getSafariVersionOnIos
         * @description determine version for the Safari browser on iOS devices
         * @access protected
         * @return boolean true if it detects the version correctly otherwise false
         */
        protected function getSafariVersionOnIos() 
        {
            $result = explode('/',stristr($this->_agent,'Version'));
            
            if( isset($result[1]) )
            {
                $version = explode(' ',$result[1]);
                $this->setVersion($version[0]);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name getChromeVersionOnIos
         * @description determine version for the Chrome browser on iOS devices
         * @access protected
         * @return boolean true if it detects the version correctly otherwise false
         */
        protected function getChromeVersionOnIos()
        {
            $result = explode('/',stristr($this->_agent,'CriOS'));
            
            if( isset($result[1]) )
            {
                $version = explode(' ',$result[1]);
                $this->setVersion($version[0]);
                $this->setBrowser(self::BROWSER_CHROME);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowseriPhone
         * @description determine if the browser is iPhone or not
         * @access protected
         * @return boolean true if the browser is iPhone otherwise false
         */
        protected function checkBrowseriPhone() 
        {
            if( stripos($this->_agent,'iPhone') !== false ) 
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setBrowser(self::BROWSER_IPHONE);
                $this->getSafariVersionOnIos();
                $this->getChromeVersionOnIos();
                $this->checkForFacebookIos();
                $this->setMobile(true);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowseriPad
         * @description determine if the browser is iPad or not
         * @access protected
         * @return boolean true if the browser is iPad otherwise false
         */
        protected function checkBrowseriPad() 
        {
            if( stripos($this->_agent,'iPad') !== false ) 
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setBrowser(self::BROWSER_IPAD);
                $this->getSafariVersionOnIos();
                $this->getChromeVersionOnIos();
                $this->checkForFacebookIos();
                $this->setTablet(true);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowseriPad
         * @description determine if the browser is iPod or not
         * @access protected
         * @return boolean true if the browser is iPod otherwise false
         */
        protected function checkBrowseriPod() 
        {
            if( stripos($this->_agent,'iPod') !== false ) 
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setBrowser(self::BROWSER_IPOD);
                $this->getSafariVersionOnIos();
                $this->getChromeVersionOnIos();
                $this->checkForFacebookIos();
                $this->setMobile(true);

                return true;
            }

            return false;
        }

        /**
         * @name checkBrowserAndroid
         * @description determine if the browser is Android or not
         * @access protected
         * @return boolean true if the browser is Android otherwise false
         */
        protected function checkBrowserAndroid()
        {
            if (stripos($this->_agent, 'Android') !== false) 
            {
                $result = explode(' ', stristr($this->_agent, 'Android'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                } 
                else 
                {
                    $this->setVersion(self::VERSION_UNKNOWN);
                }
                
                if (stripos($this->_agent, 'Mobile') !== false) 
                {
                    $this->setMobile(true);
                } 
                else 
                {
                    $this->setTablet(true);
                }
                
                $this->setBrowser(self::BROWSER_ANDROID);
                
                return true;
            }
            
            return false;
        }

        /**
         * @name checkBrowserVivaldi
         * @description determine if the browser is Vivaldi or not
         * @access protected
         * @return boolean true if the browser is Vivaldi otherwise false
         */
        protected function checkBrowserVivaldi()
        {
            if (stripos($this->_agent, 'Vivaldi') !== false) 
            {
                $result = explode('/', stristr($this->_agent, 'Vivaldi'));
                
                if (isset($result[1])) 
                {
                    $version = explode(' ', $result[1]);
                    $this->setVersion($version[0]);
                    $this->setBrowser(self::BROWSER_VIVALDI);
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkBrowserPlayStation
         * @description determine if the browser is PlayStation or not
         * @access protected
         * @return boolean true if the browser is PlayStation otherwise false
         */
        protected function checkBrowserPlayStation()
        {
            if (stripos($this->_agent, 'PlayStation ') !== false) 
            {
                $result = explode(' ', stristr($this->_agent, 'PlayStation '));
                $this->setBrowser(self::BROWSER_PLAYSTATION);
                
                if (isset($result[0])) 
                {
                    $version = explode(')', $result[2]);
                    $this->setVersion($version[0]);
                    
                    if (stripos($this->_agent, 'Portable)') !== false || stripos($this->_agent, 'Vita') !== false) 
                    {
                        $this->setMobile(true);
                    }
                    
                    return true;
                }
            }
            
            return false;
        }

        /**
         * @name checkOS
         * @description determine the user's OS
         * @access protected
         * @return 
         */
        public function checkOS()
        {
            if (stripos($this->_agent, 'windows') !== false)
            {
                $this->_os = self::OS_WINDOWS;
            }
            else if (stripos($this->_agent, 'iPad') !== false)
            {
                $this->_os = self::OS_IPAD;
            }
            else if (stripos($this->_agent, 'iPod') !== false)
            {
                $this->_os = self::OS_IPOD;
            }
            else if (stripos($this->_agent, 'iPhone') !== false)
            {
                $this->_os = self::OS_IPHONE;
            }
            elseif (stripos($this->_agent, 'mac') !== false)
            {
                $this->_os = self::OS_APPLE;
            }
            elseif (stripos($this->_agent, 'android') !== false)
            {
                $this->_os = self::OS_ANDROID;
            }
            elseif (stripos($this->_agent, 'linux') !== false)
            {
                $this->_os = self::OS_LINUX;
            }
            else if (stripos($this->_agent, 'Nokia') !== false)
            {
                $this->_os = self::OS_NOKIA;
            }
            else if (stripos($this->_agent, 'BlackBerry') !== false)
            {
                $this->_os = self::OS_BLACKBERRY;
            }
            elseif (stripos($this->_agent, 'FreeBSD') !== false)
            {
                $this->_os = self::OS_FREEBSD;
            }
            elseif (stripos($this->_agent, 'OpenBSD') !== false)
            {
                $this->_os = self::OS_OPENBSD;
            }
            elseif (stripos($this->_agent, 'NetBSD') !== false)
            {
                $this->_os = self::OS_NETBSD;
            }
            elseif (stripos($this->_agent, 'OpenSolaris') !== false)
            {
                $this->_os = self::OS_OPENSOLARIS;
            }
            elseif (stripos($this->_agent, 'SunOS') !== false)
            {
                $this->_os = self::OS_SUNOS;
            }
            elseif (stripos($this->_agent, 'OS\/2') !== false)
            {
                $this->_os = self::OS_OS2;
            }
            elseif (stripos($this->_agent, 'BeOS') !== false)
            {
                $this->_os = self::OS_BEOS;
            }
            elseif (stripos($this->_agent, 'win') !== false)
            {
                $this->_os = self::OS_WINDOWS;
            }
            elseif (stripos($this->_agent, 'Playstation') !== false)
            {
                $this->_os = self::OS_PLAYSTATION;
            }
            
            # get full name 
            $matches = array();
            preg_match_all('/\((.*?)\)/',$this->_agent,$matches);
            
            if(count($matches) && count($matches[1]))
            {
                $build = explode(';',$matches[1][0]);

                if(count($build))
                {
                    $value = ($this->_os == self::OS_ANDROID || $this->_os == self::OS_APPLE) ? $build[1] : $build[0];
                    
                    preg_match('/((?:\d+)(?:\.\d*)?)/',$value,$matches);
                    
                    if(count($matches))
                    {
                        $this->_os .= ' ' . trim($matches[0]);
                    }
                }
            }
        }
    }
}