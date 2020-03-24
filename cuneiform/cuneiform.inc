<?php
$toollog = '../log/';
$ERROR = "";

//  Set $dodelete to true if temporary files in /tmp should be deleted before returning.
$dodelete = true;
$tobedeleted = array();

function logit($straeng)
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog . 'cuneiform.log','a');
    if($ftemp)
        {
        fwrite($ftemp,$straeng . "\n");
        fclose($ftemp);
        }
    }

class SystemExit extends Exception {}
try {
    header("Content-type: application/xml");


    function php_fix_raw_query() 
        {
        logit(" php_fix_raw_query");
        $post = '';
        
        // Try globals array
        if (!$post && isset($_GLOBALS) && isset($_GLOBALS["HTTP_RAW_POST_DATA"]))
            $post = $_GLOBALS["HTTP_RAW_POST_DATA"];
        // Try globals variable
        if (!$post && isset($HTTP_RAW_POST_DATA))
            $post = $HTTP_RAW_POST_DATA;
        // Try stream
        if (!$post) 
            {
            if (!function_exists('file_get_contents')) 
                {
                $fp = fopen("php://input", "r");
                if ($fp) 
                    {
                    $post = '';
                    
                    while (!feof($fp))
                        $post = fread($fp, 1024);
                    
                    fclose($fp);
                    }
                } 
            else 
                {
                $post = "" . file_get_contents("php://input");
                }
            }
        $raw = !empty($_SERVER['QUERY_STRING']) ? sprintf('%s&%s', $_SERVER['QUERY_STRING'], $post) : $post;
       
        logit("raw=".$raw);
       
        $arr = array();
        $pairs = explode('&', $raw);
        foreach($pairs as $i) 
            {
            if(!empty($i)) 
                {
                list($name, $value) = explode('=', $i, 2);
                if (isset($arr[$name]) ) 
                    {
                    if (is_array($arr[$name]) ) 
                        {
                        $arr[$name] = array_merge($arr[$name], array($value));
                        }
                    else 
                        {
                        $arr[$name] = array($arr[$name], $value);
                        }
                    }
                else 
                    {
                    $arr[$name] = $value;
                    }
                }
            }
            $_REQUEST = $arr;
        # optionally return result array
            return $arr;
        }

    function fname($suff)
        {
        global $dodelete;
        global $tobedeleted;

        $tmpno = tempnam("/tmp", $suff);
        if($dodelete)
            $tobedeleted[$tmpno] = true;
        return $tmpno;
        }

    function requestFile($requestParm) // e.g. "IfacettokF"
        {
        logit("requestFile(" . $requestParm . ")");

        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();    
        logit("_REQUEST:$dump");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "https://infra.clarin.dk/toolsdata/";

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
                $tempfilename = fname("cuneiform_$requestParm_");
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

    function cuneiform($filename)
        {
        logit("cuneiform($filename)");

        $language = isset($_POST["language"]) ? $_POST["language"] 
                  : (isset($_REQUEST["Ilang"])? $_REQUEST["Ilang"]
                    : (isset($_REQUEST["Olang"])? $_REQUEST["Olang"]
                      :""
                      )
                    );

        $format = ( isset($_REQUEST["Oformat"])? $_REQUEST["Oformat"]
                  : ""
                  );
        logit("format: $format");
        if($format != 'rtf')
	     $format = 'text';
            
        $langs = array(
                   "bg" => "bul", //Bulgarian
                   "cs" => "cze", //Czech
                   "da" => "dan", //Danish
                   "nl" => "dut", //Dutch
                   "en" => "eng", //English
                   "et" => "est", //Estonian
                   "fr" => "fra", //French
                   "de" => "ger", //German
                   "hr" => "hrv", //Croatian
                   "hu" => "hun", //Hungarian
                   "it" => "ita", //Italian
                   "lv" => "lav", //Latvian
                   "lt" => "lit", //Lithuanian
                   "pl" => "pol", //Polish
                   "pt" => "por", //Portugese
                   "ro" => "rum", //Romanian
                   "ru" => "rus", //Russian
                   //"ru" => "ruseng",   //mixed Russian/English
                   "sl" => "slv", //Slovenian
                   "sp" => "spa", //Spanish
                   "sr" => "srp", //Serbian
                   "sv" => "swe", //Swedish
                   "tr" => "tur", //Turkish
                   "uk" => "ukr", //Ukrainian
                   );

	 $lang = $langs[$language];
        logit("lang $lang");
	 
        $cunefile = fname("cune-results");
        $command = "cuneiform -f $format -l $lang -o $cunefile $filename 2> ../log/cuneiform.log";
    	
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
        return $cunefile;
        }

//  Use currdir and toolres if tool uses local resources
//    $currdir = getcwd();
//    $toolres = "/opt/texton/cuneiform";
//    chdir($toolres);



//  Create a log file from scratch.
    unlink($toollog . 'cuneiform.log');

/*  $parms = php_fix_raw_query();
    ob_start();
    var_dump($parms);
    $dump = ob_get_clean();
    logit("parms:$dump");*/

    $uploadfile = requestFile("IformatimgF");
    if($uploadfile == '')
        $uploadfile = requestFile("F");

    if($uploadfile == '')
        {
        header("HTTP/1.0 404 Input image not found (F or IformatimgF parameter). ");
        return;
        }
        
    $filename = cuneiform($uploadfile);
    $tmpf = fopen($filename,"r");

    if($tmpf)
        {
        #logit('output from cuneiform:');
        while($line = fgets($tmpf))
            {
          #  logit($line);
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


//    chdir($currdir);
    }
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>
