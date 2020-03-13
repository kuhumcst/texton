<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (https://clarin.dk/texton/register). It should, with no or few adaptations
 * work out of the box as a dummy for your web service. The output returned
 * to the CLARIN-DK workflow manager is just a listing of the HTTP parameters
 * received by this web service from the CLARIN-DK workflow manager, and not
 * the output proper. For that you have to add your code to this script and
 * deactivate the dummy functionality. (The comments near the end of this
 * script explain how that is done.)
 *
 * Places in this script that require your attention are marked 'TODO'.
 */
/*
ToolID         : cbftok
PassWord       : 
Version        : 1.0
Title          : CBF-Tokenizer
Path in URL    : CBF-Tokenizer	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : From Clarin Base Format enriched with token and segment attributes, extract tokens and their offset in the input.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/cbftok.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();


function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'w');
    if($ftemp)
        {
        fwrite($ftemp,$toollog . "\n");
        fclose($ftemp);
        }
    }
    
function logit($str) /* TODO You can use this function to write strings to the log file. */
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
    if($ftemp)
        {
        fwrite($ftemp,$str . "\n");
        fclose($ftemp);
        }
    }
    
class SystemExit extends Exception {}
try {
    function hasArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]);
        }

    function getArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]) ? $_REQUEST["$parameterName"] : "";
        }

    function existsArgumentWithValue ($parameterName, $parameterValue)
        {
        /* Check whether there is an argument <parameterName> that has value 
           <parameterValue>. 
           There may be any number of arguments with name <parameterName> !
        */
        $query  = explode('&', $_SERVER['QUERY_STRING']);

        foreach( $query as $param )
            {
            list($name, $value) = explode('=', $param);
            if($parameterName == urldecode($name) && $parameterValue == urldecode($value))
                return true;
            }
        return false;
        }

    function tempFileName($suff) /* TODO Use this to create temporary files, if needed. */
        {
        global $dodelete;
        global $tobedeleted;
        $tmpno = tempnam('/tmp', $suff);
        if($dodelete)
            $tobedeleted[$tmpno] = true;
        return $tmpno;
        }
        
    function requestFile($requestParm) // e.g. "IfacettokF"
        {
        logit("requestFile(" . $requestParm . ")");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . $item;
            logit("requestParm:$requestParm");
            logit("urlbase:$urlbase");
            logit("item:$item");
            logit("url[$url]");

            $handle = fopen($url, "r");
            if($handle == false)
                {
                logit("Cannot open url[$url]");
                return "";
                }
            else
                {
                $tempfilename = tempFileName("cbftok_$requestParm_");
                $temp_fh = fopen($tempfilename, 'w');
                if($temp_fh == false)
                    {
                    fclose($handle);
                    logit("handle closed. Cannot open $tempfilename");
                    return "";
                    }
                else
                    {
                    while (!feof($handle)) 
                        {
                        $read = fread($handle, 8192);
                        fwrite($temp_fh, $read);    
                        }
                    fclose($temp_fh);
                    fclose($handle);
                    return $tempfilename;
                    }
                }
            }
        logit("empty");
        return "";
        }    

    function do_cbftok()
        {
        global $dodelete;
        global $tobedeleted;
/***************
* declarations *
***************/

/*
 * TODO Use the variables defined below to configure your tool for the right 
 * input files and the right settings.
 * The input files are local files that your tool can open and close like any
 * other file.
 * If your tool needs to create temporary files, use the tempFileName() 
 * function. It can mark the temporary files for deletion when the webservice
 * is done. (See the global dodelete variable.)
 */
        $base = "";	/* URL from where this web service downloads input. The generated script takes care of that, so you can ignore this variable. */
        $job = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must have the name of the job. */
        $post2 = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must be posted to this URL. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacetseto = false;	/* Type of content in input is segments,tokens (S�tningssegmenter,tokens) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangaf = false;	/* Language in input is Afrikaans (afrikaans) if true */
        $Ilangbg = false;	/* Language in input is Bulgarian (bulgarsk) if true */
        $Ilangbs = false;	/* Language in input is Bosnian (bosnisk) if true */
        $Ilangca = false;	/* Language in input is Catalan (katalansk) if true */
        $Ilangcs = false;	/* Language in input is Czech (tjekkisk) if true */
        $Ilangcy = false;	/* Language in input is Welsh (walisisk) if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangde = false;	/* Language in input is German (tysk) if true */
        $Ilangel = false;	/* Language in input is Greek (gr�sk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ilangeo = false;	/* Language in input is Esperanto (esperanto) if true */
        $Ilanges = false;	/* Language in input is Spanish (spansk) if true */
        $Ilanget = false;	/* Language in input is Estonian (estisk) if true */
        $Ilangfa = false;	/* Language in input is Persian (persisk) if true */
        $Ilangfi = false;	/* Language in input is Finnish (finsk) if true */
        $Ilangfr = false;	/* Language in input is French (fransk) if true */
        $Ilanghi = false;	/* Language in input is Hindi (hindi) if true */
        $Ilanghr = false;	/* Language in input is Croatian (kroatisk) if true */
        $Ilanghu = false;	/* Language in input is Hungarian (ungarsk) if true */
        $Ilanghy = false;	/* Language in input is Armenian (armensk) if true */
        $Ilangid = false;	/* Language in input is Indonesian (indonesisk) if true */
        $Ilangis = false;	/* Language in input is Icelandic (islandsk) if true */
        $Ilangit = false;	/* Language in input is Italian (italiensk) if true */
        $Ilangka = false;	/* Language in input is Georgian (georgisk) if true */
        $Ilangkn = false;	/* Language in input is Kannada (kannada) if true */
        $Ilangku = false;	/* Language in input is Kurdish (kurdisk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Ilanglv = false;	/* Language in input is Latvian (lettisk) if true */
        $Ilangmk = false;	/* Language in input is Macedonian (makedonsk) if true */
        $Ilangml = false;	/* Language in input is Malayalam (malayalam) if true */
        $Ilangnl = false;	/* Language in input is Dutch (nederlandsk) if true */
        $Ilangno = false;	/* Language in input is Norwegian (norsk) if true */
        $Ilangpl = false;	/* Language in input is Polish (polsk) if true */
        $Ilangpt = false;	/* Language in input is Portuguese (portugisisk) if true */
        $Ilangro = false;	/* Language in input is Romanian (rum�nsk) if true */
        $Ilangru = false;	/* Language in input is Russian (russisk) if true */
        $Ilangsk = false;	/* Language in input is Slovak (slovakisk) if true */
        $Ilangsl = false;	/* Language in input is Slovene (slovensk) if true */
        $Ilangsq = false;	/* Language in input is Albanian (albansk) if true */
        $Ilangsr = false;	/* Language in input is Serbian (serbisk) if true */
        $Ilangsv = false;	/* Language in input is Swedish (svensk) if true */
        $Ilangsw = false;	/* Language in input is Swahili (swahili) if true */
        $Ilangta = false;	/* Language in input is Tamil (tamilsk) if true */
        $Ilangtr = false;	/* Language in input is Turkish (tyrkisk) if true */
        $Ilanguk = false;	/* Language in input is Ukrainian (ukrainsk) if true */
        $Ilangvi = false;	/* Language in input is Vietnamese (vietnamesisk) if true */
        $Ilangzh = false;	/* Language in input is Chinese (kinesisk) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc20 = false;	/* Historical period in input is late modern (moderne tid) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Oappunn = false;	/* Appearance in output is unnormalised (ikke-normaliseret) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (Tokens) if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangaf = false;	/* Language in output is Afrikaans (afrikaans) if true */
        $Olangbg = false;	/* Language in output is Bulgarian (bulgarsk) if true */
        $Olangbs = false;	/* Language in output is Bosnian (bosnisk) if true */
        $Olangca = false;	/* Language in output is Catalan (katalansk) if true */
        $Olangcs = false;	/* Language in output is Czech (tjekkisk) if true */
        $Olangcy = false;	/* Language in output is Welsh (walisisk) if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangde = false;	/* Language in output is German (tysk) if true */
        $Olangel = false;	/* Language in output is Greek (gr�sk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Olangeo = false;	/* Language in output is Esperanto (esperanto) if true */
        $Olanges = false;	/* Language in output is Spanish (spansk) if true */
        $Olanget = false;	/* Language in output is Estonian (estisk) if true */
        $Olangfa = false;	/* Language in output is Persian (persisk) if true */
        $Olangfi = false;	/* Language in output is Finnish (finsk) if true */
        $Olangfr = false;	/* Language in output is French (fransk) if true */
        $Olanghi = false;	/* Language in output is Hindi (hindi) if true */
        $Olanghr = false;	/* Language in output is Croatian (kroatisk) if true */
        $Olanghu = false;	/* Language in output is Hungarian (ungarsk) if true */
        $Olanghy = false;	/* Language in output is Armenian (armensk) if true */
        $Olangid = false;	/* Language in output is Indonesian (indonesisk) if true */
        $Olangis = false;	/* Language in output is Icelandic (islandsk) if true */
        $Olangit = false;	/* Language in output is Italian (italiensk) if true */
        $Olangka = false;	/* Language in output is Georgian (georgisk) if true */
        $Olangkn = false;	/* Language in output is Kannada (kannada) if true */
        $Olangku = false;	/* Language in output is Kurdish (kurdisk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Olanglv = false;	/* Language in output is Latvian (lettisk) if true */
        $Olangmk = false;	/* Language in output is Macedonian (makedonsk) if true */
        $Olangml = false;	/* Language in output is Malayalam (malayalam) if true */
        $Olangnl = false;	/* Language in output is Dutch (nederlandsk) if true */
        $Olangno = false;	/* Language in output is Norwegian (norsk) if true */
        $Olangpl = false;	/* Language in output is Polish (polsk) if true */
        $Olangpt = false;	/* Language in output is Portuguese (portugisisk) if true */
        $Olangro = false;	/* Language in output is Romanian (rum�nsk) if true */
        $Olangru = false;	/* Language in output is Russian (russisk) if true */
        $Olangsk = false;	/* Language in output is Slovak (slovakisk) if true */
        $Olangsl = false;	/* Language in output is Slovene (slovensk) if true */
        $Olangsq = false;	/* Language in output is Albanian (albansk) if true */
        $Olangsr = false;	/* Language in output is Serbian (serbisk) if true */
        $Olangsv = false;	/* Language in output is Swedish (svensk) if true */
        $Olangsw = false;	/* Language in output is Swahili (swahili) if true */
        $Olangta = false;	/* Language in output is Tamil (tamilsk) if true */
        $Olangtr = false;	/* Language in output is Turkish (tyrkisk) if true */
        $Olanguk = false;	/* Language in output is Ukrainian (ukrainsk) if true */
        $Olangvi = false;	/* Language in output is Vietnamese (vietnamesisk) if true */
        $Olangzh = false;	/* Language in output is Chinese (kinesisk) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc20 = false;	/* Historical period in output is late modern (moderne tid) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Presentation in output is normal if true */

        if( hasArgument("base") )
            {
            $base = getArgument("base");
            }
        if( hasArgument("job") )
            {
            $job = getArgument("job");
            }
        if( hasArgument("post2") )
            {
            $post2 = getArgument("post2");
            }
        $echos = "base=$base job=$job post2=$post2 ";

/*********
* input  *
*********/
        if( hasArgument("F") )
            {        
            $F = requestFile("F");
            if($F == '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            }
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappnrm=$Iappnrm " . "Iappunn=$Iappunn ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseto = existsArgumentWithValue("Ifacet", "seto");
            $echos = $echos . "Ifacetseto=$Ifacetseto ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangaf = existsArgumentWithValue("Ilang", "af");
            $Ilangbg = existsArgumentWithValue("Ilang", "bg");
            $Ilangbs = existsArgumentWithValue("Ilang", "bs");
            $Ilangca = existsArgumentWithValue("Ilang", "ca");
            $Ilangcs = existsArgumentWithValue("Ilang", "cs");
            $Ilangcy = existsArgumentWithValue("Ilang", "cy");
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangde = existsArgumentWithValue("Ilang", "de");
            $Ilangel = existsArgumentWithValue("Ilang", "el");
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $Ilangeo = existsArgumentWithValue("Ilang", "eo");
            $Ilanges = existsArgumentWithValue("Ilang", "es");
            $Ilanget = existsArgumentWithValue("Ilang", "et");
            $Ilangfa = existsArgumentWithValue("Ilang", "fa");
            $Ilangfi = existsArgumentWithValue("Ilang", "fi");
            $Ilangfr = existsArgumentWithValue("Ilang", "fr");
            $Ilanghi = existsArgumentWithValue("Ilang", "hi");
            $Ilanghr = existsArgumentWithValue("Ilang", "hr");
            $Ilanghu = existsArgumentWithValue("Ilang", "hu");
            $Ilanghy = existsArgumentWithValue("Ilang", "hy");
            $Ilangid = existsArgumentWithValue("Ilang", "id");
            $Ilangis = existsArgumentWithValue("Ilang", "is");
            $Ilangit = existsArgumentWithValue("Ilang", "it");
            $Ilangka = existsArgumentWithValue("Ilang", "ka");
            $Ilangkn = existsArgumentWithValue("Ilang", "kn");
            $Ilangku = existsArgumentWithValue("Ilang", "ku");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $Ilanglv = existsArgumentWithValue("Ilang", "lv");
            $Ilangmk = existsArgumentWithValue("Ilang", "mk");
            $Ilangml = existsArgumentWithValue("Ilang", "ml");
            $Ilangnl = existsArgumentWithValue("Ilang", "nl");
            $Ilangno = existsArgumentWithValue("Ilang", "no");
            $Ilangpl = existsArgumentWithValue("Ilang", "pl");
            $Ilangpt = existsArgumentWithValue("Ilang", "pt");
            $Ilangro = existsArgumentWithValue("Ilang", "ro");
            $Ilangru = existsArgumentWithValue("Ilang", "ru");
            $Ilangsk = existsArgumentWithValue("Ilang", "sk");
            $Ilangsl = existsArgumentWithValue("Ilang", "sl");
            $Ilangsq = existsArgumentWithValue("Ilang", "sq");
            $Ilangsr = existsArgumentWithValue("Ilang", "sr");
            $Ilangsv = existsArgumentWithValue("Ilang", "sv");
            $Ilangsw = existsArgumentWithValue("Ilang", "sw");
            $Ilangta = existsArgumentWithValue("Ilang", "ta");
            $Ilangtr = existsArgumentWithValue("Ilang", "tr");
            $Ilanguk = existsArgumentWithValue("Ilang", "uk");
            $Ilangvi = existsArgumentWithValue("Ilang", "vi");
            $Ilangzh = existsArgumentWithValue("Ilang", "zh");
            $echos = $echos . "Ilangaf=$Ilangaf " . "Ilangbg=$Ilangbg " . "Ilangbs=$Ilangbs " . "Ilangca=$Ilangca " . "Ilangcs=$Ilangcs " . "Ilangcy=$Ilangcy " . "Ilangda=$Ilangda " . "Ilangde=$Ilangde " . "Ilangel=$Ilangel " . "Ilangen=$Ilangen " . "Ilangeo=$Ilangeo " . "Ilanges=$Ilanges " . "Ilanget=$Ilanget " . "Ilangfa=$Ilangfa " . "Ilangfi=$Ilangfi " . "Ilangfr=$Ilangfr " . "Ilanghi=$Ilanghi " . "Ilanghr=$Ilanghr " . "Ilanghu=$Ilanghu " . "Ilanghy=$Ilanghy " . "Ilangid=$Ilangid " . "Ilangis=$Ilangis " . "Ilangit=$Ilangit " . "Ilangka=$Ilangka " . "Ilangkn=$Ilangkn " . "Ilangku=$Ilangku " . "Ilangla=$Ilangla " . "Ilanglv=$Ilanglv " . "Ilangmk=$Ilangmk " . "Ilangml=$Ilangml " . "Ilangnl=$Ilangnl " . "Ilangno=$Ilangno " . "Ilangpl=$Ilangpl " . "Ilangpt=$Ilangpt " . "Ilangro=$Ilangro " . "Ilangru=$Ilangru " . "Ilangsk=$Ilangsk " . "Ilangsl=$Ilangsl " . "Ilangsq=$Ilangsq " . "Ilangsr=$Ilangsr " . "Ilangsv=$Ilangsv " . "Ilangsw=$Ilangsw " . "Ilangta=$Ilangta " . "Ilangtr=$Ilangtr " . "Ilanguk=$Ilanguk " . "Ilangvi=$Ilangvi " . "Ilangzh=$Ilangzh ";
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $Iperiodc20 = existsArgumentWithValue("Iperiod", "c20");
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc13=$Iperiodc13 " . "Iperiodc20=$Iperiodc20 " . "Iperiodc21=$Iperiodc21 ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $Oappunn = existsArgumentWithValue("Oapp", "unn");
            $echos = $echos . "Oappnrm=$Oappnrm " . "Oappunn=$Oappunn ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacettok=$Ofacettok ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformattxtann=$Oformattxtann ";
            }
        if( hasArgument("Olang") )
            {
            $Olangaf = existsArgumentWithValue("Olang", "af");
            $Olangbg = existsArgumentWithValue("Olang", "bg");
            $Olangbs = existsArgumentWithValue("Olang", "bs");
            $Olangca = existsArgumentWithValue("Olang", "ca");
            $Olangcs = existsArgumentWithValue("Olang", "cs");
            $Olangcy = existsArgumentWithValue("Olang", "cy");
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangde = existsArgumentWithValue("Olang", "de");
            $Olangel = existsArgumentWithValue("Olang", "el");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $Olangeo = existsArgumentWithValue("Olang", "eo");
            $Olanges = existsArgumentWithValue("Olang", "es");
            $Olanget = existsArgumentWithValue("Olang", "et");
            $Olangfa = existsArgumentWithValue("Olang", "fa");
            $Olangfi = existsArgumentWithValue("Olang", "fi");
            $Olangfr = existsArgumentWithValue("Olang", "fr");
            $Olanghi = existsArgumentWithValue("Olang", "hi");
            $Olanghr = existsArgumentWithValue("Olang", "hr");
            $Olanghu = existsArgumentWithValue("Olang", "hu");
            $Olanghy = existsArgumentWithValue("Olang", "hy");
            $Olangid = existsArgumentWithValue("Olang", "id");
            $Olangis = existsArgumentWithValue("Olang", "is");
            $Olangit = existsArgumentWithValue("Olang", "it");
            $Olangka = existsArgumentWithValue("Olang", "ka");
            $Olangkn = existsArgumentWithValue("Olang", "kn");
            $Olangku = existsArgumentWithValue("Olang", "ku");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $Olanglv = existsArgumentWithValue("Olang", "lv");
            $Olangmk = existsArgumentWithValue("Olang", "mk");
            $Olangml = existsArgumentWithValue("Olang", "ml");
            $Olangnl = existsArgumentWithValue("Olang", "nl");
            $Olangno = existsArgumentWithValue("Olang", "no");
            $Olangpl = existsArgumentWithValue("Olang", "pl");
            $Olangpt = existsArgumentWithValue("Olang", "pt");
            $Olangro = existsArgumentWithValue("Olang", "ro");
            $Olangru = existsArgumentWithValue("Olang", "ru");
            $Olangsk = existsArgumentWithValue("Olang", "sk");
            $Olangsl = existsArgumentWithValue("Olang", "sl");
            $Olangsq = existsArgumentWithValue("Olang", "sq");
            $Olangsr = existsArgumentWithValue("Olang", "sr");
            $Olangsv = existsArgumentWithValue("Olang", "sv");
            $Olangsw = existsArgumentWithValue("Olang", "sw");
            $Olangta = existsArgumentWithValue("Olang", "ta");
            $Olangtr = existsArgumentWithValue("Olang", "tr");
            $Olanguk = existsArgumentWithValue("Olang", "uk");
            $Olangvi = existsArgumentWithValue("Olang", "vi");
            $Olangzh = existsArgumentWithValue("Olang", "zh");
            $echos = $echos . "Olangaf=$Olangaf " . "Olangbg=$Olangbg " . "Olangbs=$Olangbs " . "Olangca=$Olangca " . "Olangcs=$Olangcs " . "Olangcy=$Olangcy " . "Olangda=$Olangda " . "Olangde=$Olangde " . "Olangel=$Olangel " . "Olangen=$Olangen " . "Olangeo=$Olangeo " . "Olanges=$Olanges " . "Olanget=$Olanget " . "Olangfa=$Olangfa " . "Olangfi=$Olangfi " . "Olangfr=$Olangfr " . "Olanghi=$Olanghi " . "Olanghr=$Olanghr " . "Olanghu=$Olanghu " . "Olanghy=$Olanghy " . "Olangid=$Olangid " . "Olangis=$Olangis " . "Olangit=$Olangit " . "Olangka=$Olangka " . "Olangkn=$Olangkn " . "Olangku=$Olangku " . "Olangla=$Olangla " . "Olanglv=$Olanglv " . "Olangmk=$Olangmk " . "Olangml=$Olangml " . "Olangnl=$Olangnl " . "Olangno=$Olangno " . "Olangpl=$Olangpl " . "Olangpt=$Olangpt " . "Olangro=$Olangro " . "Olangru=$Olangru " . "Olangsk=$Olangsk " . "Olangsl=$Olangsl " . "Olangsq=$Olangsq " . "Olangsr=$Olangsr " . "Olangsv=$Olangsv " . "Olangsw=$Olangsw " . "Olangta=$Olangta " . "Olangtr=$Olangtr " . "Olanguk=$Olanguk " . "Olangvi=$Olangvi " . "Olangzh=$Olangzh ";
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $Operiodc20 = existsArgumentWithValue("Operiod", "c20");
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc13=$Operiodc13 " . "Operiodc20=$Operiodc20 " . "Operiodc21=$Operiodc21 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $cbftokfile = tempFileName("cbftok-results");
        $command = "echo $echos >> $cbftokfile";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
/*/
// YOUR CODE STARTS HERE.
//        TODO your code!
        logit($echos);
        $cbftokfile = tempFileName("cbftok-results");
        if($Ilangda && $Iperiodc21)
            $convertaa = 'y';
        else
            $convertaa = 'n';
        $command = "../bin/bracmat \"get'\\\"cbftok.bra\\\"\" $F $cbftokfile $convertaa";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $cbftokfile
//*/
        $tmpf = fopen($cbftokfile,'r');

        if($tmpf)
            {
            //logit('output from cbftok:');
            while($line = fgets($tmpf))
                {
                //logit($line);
                print $line;
                }
            fclose($tmpf);
            }

        if($dodelete)
            {
            foreach ($tobedeleted as $filename => $dot) 
                {
                if($dot)
                    unlink($filename);
                }
            unset($tobedeleted);
            }
        }
    loginit();
    do_cbftok();
    }
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

