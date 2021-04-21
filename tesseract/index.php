<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (http://localhost/texton/register). It should, with no or few adaptations
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
ToolID         : Tesseract-OCR
PassWord       : 
Version        : 4.1.0
Title          : Tesseract-OCRv4
Path in URL    : tesseract	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : GitHub
ContentProvider: GitHub
Creator        : Ray Smith a.o.
InfoAbout      : https://github.com/tesseract-ocr/tesseract/wiki
Description    : Tesseract Open Source OCR Engine. Tesseract 4 adds a new neural net (LSTM) based OCR engine which is focused on line recognition, but also still supports the legacy Tesseract OCR engine of Tesseract 3 which works by recognizing character patterns.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/TesseractOCR.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();


function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    return;
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
    return;
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
        logit("requestFile({$requestParm})");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . urlencode($item);
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
                $tempfilename = tempFileName("TesseractOCR_{$requestParm}_");
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

    function do_TesseractOCR()
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
        $Iappgot = false;	/* Appearance in input is blackletter (gotisk) if true */
        $Iappgotd = false;	/* Appearance in input is blackletter w. ø (gotisk m. ø) if true */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iapprom = false;	/* Appearance in input is roman (roman) if true */
        $Ifacettxt = false;	/* Type of content in input is text (Ingen annotation) if true */
        $Iformatimg = false;	/* Format in input is image (billede) if true */
        $Iformatpdf = false;	/* Format in input is PDF if true */
        $Ilangaf = false;	/* Language in input is Afrikaans (afrikaans) if true */
        $Ilangbr = false;	/* Language in input is Breton (bretonsk) if true */
        $Ilangbs = false;	/* Language in input is Bosnian (bosnisk) if true */
        $Ilangca = false;	/* Language in input is Catalan (katalansk) if true */
        $Ilangco = false;	/* Language in input is Corsican (korsikansk) if true */
        $Ilangcs = false;	/* Language in input is Czech (tjekkisk) if true */
        $Ilangcy = false;	/* Language in input is Welsh (walisisk) if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangde = false;	/* Language in input is German (tysk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ilangeo = false;	/* Language in input is Esperanto (esperanto) if true */
        $Ilanges = false;	/* Language in input is Spanish (spansk) if true */
        $Ilanget = false;	/* Language in input is Estonian (estisk) if true */
        $Ilangeu = false;	/* Language in input is Basque (baskisk) if true */
        $Ilangfi = false;	/* Language in input is Finnish (finsk) if true */
        $Ilangfo = false;	/* Language in input is Faroese (færøsk) if true */
        $Ilangfr = false;	/* Language in input is French (fransk) if true */
        $Ilangga = false;	/* Language in input is Irish (irsk) if true */
        $Ilanggl = false;	/* Language in input is Galician (galicisk) if true */
        $Ilanghr = false;	/* Language in input is Croatian (kroatisk) if true */
        $Ilanght = false;	/* Language in input is Haitian (haitisk kreolsk) if true */
        $Ilanghu = false;	/* Language in input is Hungarian (ungarsk) if true */
        $Ilangid = false;	/* Language in input is Indonesian (indonesisk) if true */
        $Ilangis = false;	/* Language in input is Icelandic (islandsk) if true */
        $Ilangit = false;	/* Language in input is Italian (italiensk) if true */
        $Ilangiu = false;	/* Language in input is Inuktitut (inuittisk) if true */
        $Ilangjv = false;	/* Language in input is Javanese (javanesisk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Ilanglb = false;	/* Language in input is Luxembourgish (letzeburgsk) if true */
        $Ilanglt = false;	/* Language in input is Lithuanian (litauisk) if true */
        $Ilanglv = false;	/* Language in input is Latvian (lettisk) if true */
        $Ilangms = false;	/* Language in input is Malay (malajisk) if true */
        $Ilangmt = false;	/* Language in input is Maltese (maltesisk) if true */
        $Ilangnb = false;	/* Language in input is Norwegian Bokmål (norsk bokmål) if true */
        $Ilangnl = false;	/* Language in input is Dutch (nederlandsk) if true */
        $Ilangnn = false;	/* Language in input is Norwegian Nynorsk (nynorsk) if true */
        $Ilangoc = false;	/* Language in input is Occitan (occitansk) if true */
        $Ilangpl = false;	/* Language in input is Polish (polsk) if true */
        $Ilangpt = false;	/* Language in input is Portuguese (portugisisk) if true */
        $Ilangro = false;	/* Language in input is Romanian (rumænsk) if true */
        $Ilangsk = false;	/* Language in input is Slovak (slovakisk) if true */
        $Ilangsl = false;	/* Language in input is Slovene (slovensk) if true */
        $Ilangsq = false;	/* Language in input is Albanian (albansk) if true */
        $Ilangsr = false;	/* Language in input is Serbian (serbisk) if true */
        $Ilangsv = false;	/* Language in input is Swedish (svensk) if true */
        $Ilangsw = false;	/* Language in input is Swahili (swahili) if true */
        $Ilangtr = false;	/* Language in input is Turkish (tyrkisk) if true */
        $Ilanguz = false;	/* Language in input is Uzbek (usbekisk) if true */
        $Ilangvi = false;	/* Language in input is Vietnamese (vietnamesisk) if true */
        $Ilangyi = false;	/* Language in input is Yiddish (jiddisch) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Ismlsml = false;	/* Smell in input is any smell (lugt) if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappocr = false;	/* Appearance in output is OCR if true */
        $Ofacettxt = false;	/* Type of content in output is text (Ingen annotation) if true */
        $Oformatflat = false;	/* Format in output is plain (flad) if true */
        $Olangaf = false;	/* Language in output is Afrikaans (afrikaans) if true */
        $Olangbr = false;	/* Language in output is Breton (bretonsk) if true */
        $Olangbs = false;	/* Language in output is Bosnian (bosnisk) if true */
        $Olangca = false;	/* Language in output is Catalan (katalansk) if true */
        $Olangco = false;	/* Language in output is Corsican (korsikansk) if true */
        $Olangcs = false;	/* Language in output is Czech (tjekkisk) if true */
        $Olangcy = false;	/* Language in output is Welsh (walisisk) if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangde = false;	/* Language in output is German (tysk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Olangeo = false;	/* Language in output is Esperanto (esperanto) if true */
        $Olanges = false;	/* Language in output is Spanish (spansk) if true */
        $Olanget = false;	/* Language in output is Estonian (estisk) if true */
        $Olangeu = false;	/* Language in output is Basque (baskisk) if true */
        $Olangfi = false;	/* Language in output is Finnish (finsk) if true */
        $Olangfo = false;	/* Language in output is Faroese (færøsk) if true */
        $Olangfr = false;	/* Language in output is French (fransk) if true */
        $Olangga = false;	/* Language in output is Irish (irsk) if true */
        $Olanggl = false;	/* Language in output is Galician (galicisk) if true */
        $Olanghr = false;	/* Language in output is Croatian (kroatisk) if true */
        $Olanght = false;	/* Language in output is Haitian (haitisk kreolsk) if true */
        $Olanghu = false;	/* Language in output is Hungarian (ungarsk) if true */
        $Olangid = false;	/* Language in output is Indonesian (indonesisk) if true */
        $Olangis = false;	/* Language in output is Icelandic (islandsk) if true */
        $Olangit = false;	/* Language in output is Italian (italiensk) if true */
        $Olangiu = false;	/* Language in output is Inuktitut (inuittisk) if true */
        $Olangjv = false;	/* Language in output is Javanese (javanesisk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Olanglb = false;	/* Language in output is Luxembourgish (letzeburgsk) if true */
        $Olanglt = false;	/* Language in output is Lithuanian (litauisk) if true */
        $Olanglv = false;	/* Language in output is Latvian (lettisk) if true */
        $Olangms = false;	/* Language in output is Malay (malajisk) if true */
        $Olangmt = false;	/* Language in output is Maltese (maltesisk) if true */
        $Olangnb = false;	/* Language in output is Norwegian Bokmål (norsk bokmål) if true */
        $Olangnl = false;	/* Language in output is Dutch (nederlandsk) if true */
        $Olangnn = false;	/* Language in output is Norwegian Nynorsk (nynorsk) if true */
        $Olangoc = false;	/* Language in output is Occitan (occitansk) if true */
        $Olangpl = false;	/* Language in output is Polish (polsk) if true */
        $Olangpt = false;	/* Language in output is Portuguese (portugisisk) if true */
        $Olangro = false;	/* Language in output is Romanian (rumænsk) if true */
        $Olangsk = false;	/* Language in output is Slovak (slovakisk) if true */
        $Olangsl = false;	/* Language in output is Slovene (slovensk) if true */
        $Olangsq = false;	/* Language in output is Albanian (albansk) if true */
        $Olangsr = false;	/* Language in output is Serbian (serbisk) if true */
        $Olangsv = false;	/* Language in output is Swedish (svensk) if true */
        $Olangsw = false;	/* Language in output is Swahili (swahili) if true */
        $Olangtr = false;	/* Language in output is Turkish (tyrkisk) if true */
        $Olanguz = false;	/* Language in output is Uzbek (usbekisk) if true */
        $Olangvi = false;	/* Language in output is Vietnamese (vietnamesisk) if true */
        $Olangyi = false;	/* Language in output is Yiddish (jiddisch) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $Osmlnsl = false;	/* Smell in output is new smell (ny lugt) if true */
        $Iformatimggif = false;	/* Style of format image (billede) in input is 0 if true */
        $Iformatimgjpeg = false;	/* Style of format image (billede) in input is 0 if true */
        $Iformatimgpdf = false;	/* Style of format image (billede) in input is 0 if true */
        $Iformatimgpjpeg = false;	/* Style of format image (billede) in input is 0 if true */
        $Iformatimgpng = false;	/* Style of format image (billede) in input is 0 if true */
        $Iformatimgtiff = false;	/* Style of format image (billede) in input is 0 if true */

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
            $Iappgot = existsArgumentWithValue("Iapp", "got");
            $Iappgotd = existsArgumentWithValue("Iapp", "gotd");
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iapprom = existsArgumentWithValue("Iapp", "rom");
            $echos = $echos . "Iappgot=$Iappgot " . "Iappgotd=$Iappgotd " . "Iappnrm=$Iappnrm " . "Iapprom=$Iapprom ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacettxt=$Ifacettxt ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatimg = existsArgumentWithValue("Iformat", "img");
            $Iformatpdf = existsArgumentWithValue("Iformat", "pdf");
            $echos = $echos . "Iformatimg=$Iformatimg " . "Iformatpdf=$Iformatpdf ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangaf = existsArgumentWithValue("Ilang", "af");
            $Ilangbr = existsArgumentWithValue("Ilang", "br");
            $Ilangbs = existsArgumentWithValue("Ilang", "bs");
            $Ilangca = existsArgumentWithValue("Ilang", "ca");
            $Ilangco = existsArgumentWithValue("Ilang", "co");
            $Ilangcs = existsArgumentWithValue("Ilang", "cs");
            $Ilangcy = existsArgumentWithValue("Ilang", "cy");
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangde = existsArgumentWithValue("Ilang", "de");
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $Ilangeo = existsArgumentWithValue("Ilang", "eo");
            $Ilanges = existsArgumentWithValue("Ilang", "es");
            $Ilanget = existsArgumentWithValue("Ilang", "et");
            $Ilangeu = existsArgumentWithValue("Ilang", "eu");
            $Ilangfi = existsArgumentWithValue("Ilang", "fi");
            $Ilangfo = existsArgumentWithValue("Ilang", "fo");
            $Ilangfr = existsArgumentWithValue("Ilang", "fr");
            $Ilangga = existsArgumentWithValue("Ilang", "ga");
            $Ilanggl = existsArgumentWithValue("Ilang", "gl");
            $Ilanghr = existsArgumentWithValue("Ilang", "hr");
            $Ilanght = existsArgumentWithValue("Ilang", "ht");
            $Ilanghu = existsArgumentWithValue("Ilang", "hu");
            $Ilangid = existsArgumentWithValue("Ilang", "id");
            $Ilangis = existsArgumentWithValue("Ilang", "is");
            $Ilangit = existsArgumentWithValue("Ilang", "it");
            $Ilangiu = existsArgumentWithValue("Ilang", "iu");
            $Ilangjv = existsArgumentWithValue("Ilang", "jv");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $Ilanglb = existsArgumentWithValue("Ilang", "lb");
            $Ilanglt = existsArgumentWithValue("Ilang", "lt");
            $Ilanglv = existsArgumentWithValue("Ilang", "lv");
            $Ilangms = existsArgumentWithValue("Ilang", "ms");
            $Ilangmt = existsArgumentWithValue("Ilang", "mt");
            $Ilangnb = existsArgumentWithValue("Ilang", "nb");
            $Ilangnl = existsArgumentWithValue("Ilang", "nl");
            $Ilangnn = existsArgumentWithValue("Ilang", "nn");
            $Ilangoc = existsArgumentWithValue("Ilang", "oc");
            $Ilangpl = existsArgumentWithValue("Ilang", "pl");
            $Ilangpt = existsArgumentWithValue("Ilang", "pt");
            $Ilangro = existsArgumentWithValue("Ilang", "ro");
            $Ilangsk = existsArgumentWithValue("Ilang", "sk");
            $Ilangsl = existsArgumentWithValue("Ilang", "sl");
            $Ilangsq = existsArgumentWithValue("Ilang", "sq");
            $Ilangsr = existsArgumentWithValue("Ilang", "sr");
            $Ilangsv = existsArgumentWithValue("Ilang", "sv");
            $Ilangsw = existsArgumentWithValue("Ilang", "sw");
            $Ilangtr = existsArgumentWithValue("Ilang", "tr");
            $Ilanguz = existsArgumentWithValue("Ilang", "uz");
            $Ilangvi = existsArgumentWithValue("Ilang", "vi");
            $Ilangyi = existsArgumentWithValue("Ilang", "yi");
            $echos = $echos . "Ilangaf=$Ilangaf " . "Ilangbr=$Ilangbr " . "Ilangbs=$Ilangbs " . "Ilangca=$Ilangca " . "Ilangco=$Ilangco " . "Ilangcs=$Ilangcs " . "Ilangcy=$Ilangcy " . "Ilangda=$Ilangda " . "Ilangde=$Ilangde " . "Ilangen=$Ilangen " . "Ilangeo=$Ilangeo " . "Ilanges=$Ilanges " . "Ilanget=$Ilanget " . "Ilangeu=$Ilangeu " . "Ilangfi=$Ilangfi " . "Ilangfo=$Ilangfo " . "Ilangfr=$Ilangfr " . "Ilangga=$Ilangga " . "Ilanggl=$Ilanggl " . "Ilanghr=$Ilanghr " . "Ilanght=$Ilanght " . "Ilanghu=$Ilanghu " . "Ilangid=$Ilangid " . "Ilangis=$Ilangis " . "Ilangit=$Ilangit " . "Ilangiu=$Ilangiu " . "Ilangjv=$Ilangjv " . "Ilangla=$Ilangla " . "Ilanglb=$Ilanglb " . "Ilanglt=$Ilanglt " . "Ilanglv=$Ilanglv " . "Ilangms=$Ilangms " . "Ilangmt=$Ilangmt " . "Ilangnb=$Ilangnb " . "Ilangnl=$Ilangnl " . "Ilangnn=$Ilangnn " . "Ilangoc=$Ilangoc " . "Ilangpl=$Ilangpl " . "Ilangpt=$Ilangpt " . "Ilangro=$Ilangro " . "Ilangsk=$Ilangsk " . "Ilangsl=$Ilangsl " . "Ilangsq=$Ilangsq " . "Ilangsr=$Ilangsr " . "Ilangsv=$Ilangsv " . "Ilangsw=$Ilangsw " . "Ilangtr=$Ilangtr " . "Ilanguz=$Ilanguz " . "Ilangvi=$Ilangvi " . "Ilangyi=$Ilangyi ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Isml") )
            {
            $Ismlsml = existsArgumentWithValue("Isml", "sml");
            $echos = $echos . "Ismlsml=$Ismlsml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappocr = existsArgumentWithValue("Oapp", "ocr");
            $echos = $echos . "Oappocr=$Oappocr ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacettxt = existsArgumentWithValue("Ofacet", "txt");
            $echos = $echos . "Ofacettxt=$Ofacettxt ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $echos = $echos . "Oformatflat=$Oformatflat ";
            }
        if( hasArgument("Olang") )
            {
            $Olangaf = existsArgumentWithValue("Olang", "af");
            $Olangbr = existsArgumentWithValue("Olang", "br");
            $Olangbs = existsArgumentWithValue("Olang", "bs");
            $Olangca = existsArgumentWithValue("Olang", "ca");
            $Olangco = existsArgumentWithValue("Olang", "co");
            $Olangcs = existsArgumentWithValue("Olang", "cs");
            $Olangcy = existsArgumentWithValue("Olang", "cy");
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangde = existsArgumentWithValue("Olang", "de");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $Olangeo = existsArgumentWithValue("Olang", "eo");
            $Olanges = existsArgumentWithValue("Olang", "es");
            $Olanget = existsArgumentWithValue("Olang", "et");
            $Olangeu = existsArgumentWithValue("Olang", "eu");
            $Olangfi = existsArgumentWithValue("Olang", "fi");
            $Olangfo = existsArgumentWithValue("Olang", "fo");
            $Olangfr = existsArgumentWithValue("Olang", "fr");
            $Olangga = existsArgumentWithValue("Olang", "ga");
            $Olanggl = existsArgumentWithValue("Olang", "gl");
            $Olanghr = existsArgumentWithValue("Olang", "hr");
            $Olanght = existsArgumentWithValue("Olang", "ht");
            $Olanghu = existsArgumentWithValue("Olang", "hu");
            $Olangid = existsArgumentWithValue("Olang", "id");
            $Olangis = existsArgumentWithValue("Olang", "is");
            $Olangit = existsArgumentWithValue("Olang", "it");
            $Olangiu = existsArgumentWithValue("Olang", "iu");
            $Olangjv = existsArgumentWithValue("Olang", "jv");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $Olanglb = existsArgumentWithValue("Olang", "lb");
            $Olanglt = existsArgumentWithValue("Olang", "lt");
            $Olanglv = existsArgumentWithValue("Olang", "lv");
            $Olangms = existsArgumentWithValue("Olang", "ms");
            $Olangmt = existsArgumentWithValue("Olang", "mt");
            $Olangnb = existsArgumentWithValue("Olang", "nb");
            $Olangnl = existsArgumentWithValue("Olang", "nl");
            $Olangnn = existsArgumentWithValue("Olang", "nn");
            $Olangoc = existsArgumentWithValue("Olang", "oc");
            $Olangpl = existsArgumentWithValue("Olang", "pl");
            $Olangpt = existsArgumentWithValue("Olang", "pt");
            $Olangro = existsArgumentWithValue("Olang", "ro");
            $Olangsk = existsArgumentWithValue("Olang", "sk");
            $Olangsl = existsArgumentWithValue("Olang", "sl");
            $Olangsq = existsArgumentWithValue("Olang", "sq");
            $Olangsr = existsArgumentWithValue("Olang", "sr");
            $Olangsv = existsArgumentWithValue("Olang", "sv");
            $Olangsw = existsArgumentWithValue("Olang", "sw");
            $Olangtr = existsArgumentWithValue("Olang", "tr");
            $Olanguz = existsArgumentWithValue("Olang", "uz");
            $Olangvi = existsArgumentWithValue("Olang", "vi");
            $Olangyi = existsArgumentWithValue("Olang", "yi");
            $echos = $echos . "Olangaf=$Olangaf " . "Olangbr=$Olangbr " . "Olangbs=$Olangbs " . "Olangca=$Olangca " . "Olangco=$Olangco " . "Olangcs=$Olangcs " . "Olangcy=$Olangcy " . "Olangda=$Olangda " . "Olangde=$Olangde " . "Olangen=$Olangen " . "Olangeo=$Olangeo " . "Olanges=$Olanges " . "Olanget=$Olanget " . "Olangeu=$Olangeu " . "Olangfi=$Olangfi " . "Olangfo=$Olangfo " . "Olangfr=$Olangfr " . "Olangga=$Olangga " . "Olanggl=$Olanggl " . "Olanghr=$Olanghr " . "Olanght=$Olanght " . "Olanghu=$Olanghu " . "Olangid=$Olangid " . "Olangis=$Olangis " . "Olangit=$Olangit " . "Olangiu=$Olangiu " . "Olangjv=$Olangjv " . "Olangla=$Olangla " . "Olanglb=$Olanglb " . "Olanglt=$Olanglt " . "Olanglv=$Olanglv " . "Olangms=$Olangms " . "Olangmt=$Olangmt " . "Olangnb=$Olangnb " . "Olangnl=$Olangnl " . "Olangnn=$Olangnn " . "Olangoc=$Olangoc " . "Olangpl=$Olangpl " . "Olangpt=$Olangpt " . "Olangro=$Olangro " . "Olangsk=$Olangsk " . "Olangsl=$Olangsl " . "Olangsq=$Olangsq " . "Olangsr=$Olangsr " . "Olangsv=$Olangsv " . "Olangsw=$Olangsw " . "Olangtr=$Olangtr " . "Olanguz=$Olanguz " . "Olangvi=$Olangvi " . "Olangyi=$Olangyi ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }
        if( hasArgument("Osml") )
            {
            $Osmlnsl = existsArgumentWithValue("Osml", "nsl");
            $echos = $echos . "Osmlnsl=$Osmlnsl ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Iformatimg") )
            {
            $Iformatimggif = existsArgumentWithValue("Iformatimg", "gif");
            $Iformatimgjpeg = existsArgumentWithValue("Iformatimg", "jpeg");
            $Iformatimgpdf = existsArgumentWithValue("Iformatimg", "pdf");
            $Iformatimgpjpeg = existsArgumentWithValue("Iformatimg", "pjpeg");
            $Iformatimgpng = existsArgumentWithValue("Iformatimg", "png");
            $Iformatimgtiff = existsArgumentWithValue("Iformatimg", "tiff");
            $echos = $echos . "Iformatimggif=$Iformatimggif " . "Iformatimgjpeg=$Iformatimgjpeg " . "Iformatimgpdf=$Iformatimgpdf " . "Iformatimgpjpeg=$Iformatimgpjpeg " . "Iformatimgpng=$Iformatimgpng " . "Iformatimgtiff=$Iformatimgtiff ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $TesseractOCRfile = tempFileName("TesseractOCR-results");
        $command = "echo $echos >> $TesseractOCRfile";
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
        if($Iappgot)
            {
            $lang = "Fraktur";
	        $script = "tessdata_best/script";
            }
        else if($Iappgotd)
            {
            $lang = "dan_frak";
            $script = "tesseract-dan-fraktur/dan_frak";
            }
        else
            {
            if     ($Olangaf) $lang ="afr";
            else if($Olangbr) $lang ="bre";
            else if($Olangbs) $lang ="bos";
            else if($Olangca) $lang ="cat";
            else if($Olangco) $lang ="cos";
            else if($Olangcs) $lang ="ces";
            else if($Olangcy) $lang ="cym";
            else if($Olangda) $lang ="dan";
            else if($Olangde) $lang ="deu";
            else if($Olangen) $lang ="eng";
            else if($Olangeo) $lang ="epo";
            else if($Olanges) $lang ="spa";
            else if($Olanget) $lang ="est";
            else if($Olangeu) $lang ="eus";
            else if($Olangfi) $lang ="fin";
            else if($Olangfo) $lang ="fao";
            else if($Olangfr) $lang ="fra";
            else if($Olangga) $lang ="gle";
            else if($Olanggl) $lang ="glg";
            else if($Olanghr) $lang ="hrv";
            else if($Olanght) $lang ="hat";
            else if($Olanghu) $lang ="hun";
            else if($Olangid) $lang ="ind";
            else if($Olangis) $lang ="isl";
            else if($Olangit) $lang ="ita";
            else if($Olangiu) $lang ="iku";
            else if($Olangjv) $lang ="jav";
            else if($Olangla) $lang ="lat";
            else if($Olanglb) $lang ="ltz";
            else if($Olanglt) $lang ="lit";
            else if($Olanglv) $lang ="lav";
            else if($Olangms) $lang ="msa";
            else if($Olangmt) $lang ="mlt";
            else if($Olangnb) $lang ="nor";
            else if($Olangnl) $lang ="nld";
            else if($Olangnn) $lang ="nor";
            else if($Olangoc) $lang ="oci";
            else if($Olangpl) $lang ="pol";
            else if($Olangpt) $lang ="por";
            else if($Olangro) $lang ="ron";
            else if($Olangsk) $lang ="slk";
            else if($Olangsl) $lang ="slv";
            else if($Olangsq) $lang ="sqi";
            else if($Olangsr) $lang ="srp";
            else if($Olangsv) $lang ="swe";
            else if($Olangsw) $lang ="swa";
            else if($Olangtr) $lang ="tur";
            else if($Olanguz) $lang ="uzb";
            else if($Olangvi) $lang ="vie";
            else if($Olangyi) $lang ="yid";
            else              $lang ="eng";
            $script = "tessdata_best";
            }

        $TesseractOCRfile = tempFileName("tesseract-results");
        if($Iformatimgpdf || $Iformatpdf)
            {
            $command = "./ocr.sh $F $lang /opt/texton/tesseract/$script $TesseractOCRfile";
	    /*
            $TIFF = tempFileName("convert-results");
	    // See /etc/ImageMagick-6/policy.xml
            $command = "convert -density 300 $F -depth 8 -strip -background white -alpha off tiff64:$TIFF.tiff";
            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
                {
                throw new SystemExit(); // instead of exit()
                }

            while($read = fgets($cmd))
                {
                }

            pclose($cmd);
            $command = "tesseract --tessdata-dir tessdata_best$script -l $lang  ../log/tempFileName.tiff stdout > $TesseractOCRfile";
            */
            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
                {
                throw new SystemExit(); // instead of exit()
                }

            while($read = fgets($cmd))
                {
                }

            pclose($cmd);
            //rename($TIFF.".tiff",$TIFF);
            }
        else
            {
            $command = "tesseract --tessdata-dir $script -l $lang $F stdout > $TesseractOCRfile";
            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
                {
                throw new SystemExit(); // instead of exit()
                }

            while($read = fgets($cmd))
                {
                }

            pclose($cmd);
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $TesseractOCRfile
//*/
        $tmpf = fopen($TesseractOCRfile,'r');

        if($tmpf)
            {
            //logit('output from TesseractOCR:');
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
    do_TesseractOCR();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

