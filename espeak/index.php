<?php
header("Content-type:text/plain; charset=UTF-8");

       /* This index.php file illustrates how input data that is POSTed to a 
	* web service can be fetched and used. 
	*/

$toollog = '../log/';
$ERROR = "";
$dodelete = true;
$tobedeleted = array();

function logit($straeng)
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog . 'espeak.log','a');
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
                $tempfilename = fname("espeak_$requestParm_");
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

    function findInputFile($key,$parms)
        {
        logit("key $key");
        if(array_key_exists($key , $parms))
            {
            $input = $parms[$key];
            if(is_array($input))
                {
                foreach($input as $value)
                    {
                    $ret = findInputFile($key . $value, $parms);
                    if($ret != '')
                        return $ret;
                    }
                }
            else
                {
                logit("InputIs $input");
                if($input == 'F')
                    return $key . 'F';
                if(input != '')
                    return findInputFile($key . $input, $parms);
                }
            }
        return 'F';
        }


/*
-v:

af  
bs  
cs  
da  
en  
es     
et  
fr     
hi  
hu  
hy-west  
is  
ka  
ku  
lv  
mk  
nl  
pl  
pt-pt  
ru  
sq  
sv  
ta    
tr  
vi  
zh-yue
bg  
ca  
cy  
de  
el       
eo  
es-la  
fi  
fr-be  
hr  
hy  
id       
it  
kn  
la  
mb  
ml  
no  
pt  
ro     
sk  
sr  
sw  
zh
*/
    function espeak($filename)
        {
        logit("espeak($filename)");

        $language = isset($_POST["language"]) ? $_POST["language"] 
                  : (isset($_REQUEST["Ilang"])? $_REQUEST["Ilang"]
                    : (isset($_REQUEST["Olang"])? $_REQUEST["Olang"]
                      :""
                      )
                    );

        $speakfile = fname("espeak-results");
        $command = "espeak -s 140 -b1 -v $language -f $filename -w $speakfile";
        
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
        return $speakfile;
        }

//  Create a log file from scratch.
    unlink($toollog . 'espeak.log');

    $uploadfileParm = findInputFile('I',php_fix_raw_query());
    
    $uploadfile = requestFile($uploadfileParm);
    if($uploadfile == '')
        {
        header("HTTP/1.0 404 Input text not found (F parameter). ");
        return;
        }
        
    $filename = espeak($uploadfile);
    $tmpf = fopen($filename,"r");

    if($tmpf)
        {
        while($line = fgets($tmpf))
            {
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
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

