<?php 
function getUid(){
   return substr(md5(uniqid (rand (),true)),1,4);
}
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('X-XSS-Protection: 0');


$uid = getUid();

$maxDocLen = 240000; 
$cDocument=@$_POST["txtOrigHTMLCode"];

if (strlen($cDocument)>$maxDocLen){    
    die("Max doc len exceeded.");
}

function randomColor() {
$str = '#';
for ($i = 0; $i < 6; $i++) {
    $randNum = rand(0, 15);
    switch ($randNum) {
        case 10: $randNum = 'A';
            break;
        case 11: $randNum = 'B';
            break;
        case 12: $randNum = 'C';
            break;
        case 13: $randNum = 'D';
            break;
        case 14: $randNum = 'E';
            break;
        case 15: $randNum = 'F';
            break;
    }
    $str .= $randNum;
}
return $str;}

function unMaskExpression($p_obj,$doc){
  $arr=$p_obj->replaces;
  foreach($arr as $key=>$val){
    $doc=str_replace($key,$val,$doc);
  }
  return $doc;
}
function maskExpression($expr, $doc){
  $rpl = Array();
  
  while(preg_match($expr,$doc, $m, PREG_OFFSET_CAPTURE))
  {
     $matchText = $m[0][0];
     $startMatch = $m[0][1];
     $endMatch=$startMatch+strlen($matchText);
       $docStart = substr($doc,0,$startMatch);
       $docEnd = substr($doc,$endMatch);
     $tmpId=getUid();
     $tmpId="<".$tmpId.">";
     $rpl[$tmpId]=$matchText;
     $doc=$docStart.$tmpId.$docEnd;
  }
  return (object) array('replaces' => $rpl, 'doc' => $doc);
}
if(get_magic_quotes_gpc()){
  $cDocument = stripslashes($cDocument);
  // strip off the slashes if they are magically added.
}
$txtOrigHTMLCode=$cDocument;
                                  
$obj=maskExpression("/([<][?].+[?][>])/isU",$cDocument); $cDocument=$obj->doc;
$obj2=maskExpression("/([<]script.+[<]\/script[>])/isU",$cDocument); $cDocument=$obj2->doc;
$obj3=maskExpression("/([<]style.+[<]\/style[>])/isU",$cDocument); $cDocument=$obj3->doc;
//$obj4=maskExpression("/(\/\*.+\*\/)/isU",$cDocument); $cDocument=$obj4->doc;
$cAdStyle="";
$styleNodeRegex="/[<](\w+)\s+[^>]*(style\s*=\s*(['\"])([^>]+)\g{-2})[^>]*[>]/isU";
$idNodeRegex="/id\s*=\s*(['\"])([^>]+)\g{-2}/isU";
if ($cDocument){
    while(preg_match($styleNodeRegex,$cDocument, $sMatches, PREG_OFFSET_CAPTURE)){
        $cElement=$sMatches[1][0];
        $cNode=$sMatches[0][0];
        $cStyleStart=$sMatches[2][1];
        $cStyleEnd=$cStyleStart+strlen($sMatches[2][0]);
        $cStyle=$sMatches[4][0];
        $cId=null;
        $newId="";
        $highId=-1;
        //lets capture original id
        if(preg_match($idNodeRegex,$cNode, $idMatches, PREG_OFFSET_CAPTURE)){
            $cId=$idMatches[2][0];
            //id found so lets use it
        }else{
            //lets get unique id
            $nodeRegex="/".$cElement."[_]".$uid."[_]([0-9]+)/";
            //find highest number
            if (preg_match_all($nodeRegex, $cDocument, $uidMatches)){
                foreach($uidMatches[1] as $id){
                    if ($id>$highId){
                        $highId=$id;
                    }
                }
            }
            $highId++;
            $newId=$cElement."_".$uid."_".$highId;
            $cId=$newId;
            $newId="id=\"$newId\"";
        }
        //lets replace style with new id
        
        $docStart = substr($cDocument,0,$cStyleStart);
        $docEnd = substr($cDocument,$cStyleEnd);
        
        $cDocument = $docStart.$newId.$docEnd;
        $cAdStyle.="#".$cId."{".$cStyle."}"."\n";
        
        
    }
  if($cAdStyle==""){
    $cAdStyle="//No inline styles found.";
  }
}
  $cDocument=unMaskExpression($obj,$cDocument);
  $cDocument=unMaskExpression($obj2,$cDocument);
  $cDocument=unMaskExpression($obj3,$cDocument);
//  $cDocument=unMaskExpression($obj4,$cDocument);
  
  $cAdStyle=unMaskExpression($obj,$cAdStyle);
  $cAdStyle=unMaskExpression($obj2,$cAdStyle);
  $cAdStyle=unMaskExpression($obj3,$cAdStyle);
//  $cAdStyle=unMaskExpression($obj4,$cAdStyle);
  
  $_SESSION["cAdStyle"]=$cAdStyle;
  $_SESSION["cDocument"]=$cDocument;
  $_SESSION["uid"]=$uid;
  
 ?>
<!doctype html>
<html lang="en">
  <head>
    <title>CSS Out! &trade; | Get inline CSS Out of your HTML document
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
     <link rel="stylesheet" href="style.css?c3" type="text/css" media="screen" />
    <!--[if IE 6]><link rel="stylesheet" href="style.ie6.css?c3" type="text/css" media="screen" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" href="style.ie7.css?c3" type="text/css" media="screen" /><![endif]-->
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="script.js"></script>
<script type="text/javascript">
function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}
</script>
  </head>
  <body>
    <div id="cssout-main">
      <div id="cssout-hmenu-bg">
        <div class="cssout-nav-l">
        </div>
        <div class="cssout-nav-r">
        </div>
      </div>
      <div class="cleared">
      </div>
      <div class="cssout-sheet">
        <div class="cssout-sheet-tl">
        </div>
        <div class="cssout-sheet-tr">
        </div>
        <div class="cssout-sheet-bl">
        </div>
        <div class="cssout-sheet-br">
        </div>
        <div class="cssout-sheet-tc">
        </div>
        <div class="cssout-sheet-bc">
        </div>
        <div class="cssout-sheet-cl">
        </div>
        <div class="cssout-sheet-cr">
        </div>
        <div class="cssout-sheet-cc">
        </div>
        <div class="cssout-sheet-body">
          <div class="cssout-nav">
            <div class="cssout-nav-l">
            </div>
            <div class="cssout-nav-r">
            </div>
            <div class="cssout-nav-outer">
              <ul class="cssout-hmenu"> 
                <li> 
                <a href="./" class="active"> 
                  <span class="l">
                  </span> 
                  <span class="r">
                  </span> 
                  <span class="t">CSS Out! &trade; Get inline CSS Out of your HTML document
                  </span></a>
                </li> 
                <li> 
                <a href="./disclaimer.html"> 
                  <span class="l">
                  </span> 
                  <span class="r">
                  </span> 
                  <span class="t">Disclaimer
                  </span></a>
                </li>
                <li>
                <a href="./privacy.html">
                  <span class="l">
                  </span>
                  <span class="r">
                  </span>
                  <span class="t">Privacy
                  </span></a>
                </li>
              </ul>
            </div>
          </div>
          <div class="cleared reset-box">
          </div>
          <div class="cssout-content-layout">
            <div class="cssout-content-layout-row">
              <div class="cssout-layout-cell cssout-content">
                <div class="cssout-post">
                  <div class="cssout-post-body">
                    <div class="cssout-post-inner cssout-article">
                      <h1 id="h1_0">CSS Out! | remove inline CSS</h1>
                      <h2 class="cssout-postheader"> CSS Out! &trade; | Get inline CSS Out of your HTML document</h2>
                      <div class="cleared">
                      </div>
                      <div class="cssout-postcontent">
                        <form method="post" action="./#step3" id="frmOrigHTMLCode" enctype="multipart/form-data">
                          <p>CSS Out! &trade; is efficient conversion &amp; optimization tool for web-masters &amp; web-designers, when it comes to remove inline CSS out of HTML document. Target of the tool is to separate Cascading Style Sheet (CSS) information from the HTML document, providing more clear code structure and certain level of SEO optimization. HTML without inline CSS could result in better readability by major Search Engines &amp; optimize the usage of critical resources (server, network, etc.). CSS Out! &trade; is FREE, online CSS/HTML optimization tool.
                          </p>
                          <p>It is highly recommended to check the validity of your code using 
                            <a href="http://validator.w3.org/" rel="nofollow" target="_blank">HTML</a> &amp; 
                            <a href="http://jigsaw.w3.org/css-validator/" rel="nofollow" target="_blank">CSS</a> validator, before you go to the next step. It is always good idea to choose reliable hosting.
                          </p><h3>How to remove inline CSS?</h3>
                          <h4 id="h4_2c82_0">High quality SEO.</h4><h5>1. Paste your code (HTML, XHTML, PHP, etc.) into textarea (max <?php echo $maxDocLen; ?> chars).</h5>
                          <p>
<textarea rows="18" cols="117" id="txtOrigHTMLCode" name="txtOrigHTMLCode" ><?php echo htmlentities($txtOrigHTMLCode);?></textarea>
                          </p>
                          <p> 
                            <span id="span_1">2. Push the magical button: 
                            </span> 
                            <span class="cssout-button-wrapper"> 
                              <span class="cssout-button-l">
                              </span> 
                              <span class="cssout-button-r">
                              </span> 
                              <a class="cssout-button" onclick="if (document.getElementById('txtOrigHTMLCode').value.length><?php echo $maxDocLen; ?>){alert('Sorry, <?php echo $maxDocLen; ?> char is max.');}else{document.getElementById('frmOrigHTMLCode').submit();}">CSS Out!</a>
                            </span> <br />
                          <h5>3. Your new &amp; optimized code (HTML, XHTML, PHP, etc.) 
                            <a href="./download.php?q=html"> 
                              <img src="./images/down.png" class="down" alt="download HTML" title="Download HTML!" /></a>:</h5> 
<textarea readonly="readonly" rows="18" cols="117" name="txtNewHTML" id="textarea_1" onclick="SelectAll('textarea_1')"><?php echo htmlentities($cDocument);?></textarea><h5 id="step4">4. Your new external CSS, you can place it in the header section or in external file 
                            <a href="./download.php?q=css">
                  <img src="./images/down.png" class="down" alt="download CSS" title="Dwonload CSS!" /></a>:<br /> 
<textarea readonly="readonly" rows="18" cols="117" name="txtExternalCSS" id="textarea_2" onclick="SelectAll('textarea_2')"><?php echo htmlentities($cAdStyle);?></textarea>
<h5>5. Verify changes using <a href="http://winmerge.org/" target="_blank" rel="nofollow">WinMerge</a></h5>
                        </form>
                      </div>
                      <div class="cleared">
                      </div>
                    </div>
                    <div class="cleared">
                    </div>
                  </div>
                </div>
                <div class="cleared">
                </div>
              </div>
            </div>
          </div>
          <div style="text-align:center;">
          </div>
          <div class="cleared">
          </div>
          <div class="cssout-footer">
            <div class="cssout-footer-t">
            </div>
            <div class="cssout-footer-body"> 
              <a href="./rss.xml" class="cssout-rss-tag-icon" title="RSS"></a>
              <div class="cssout-footer-text">
        <p>  
                  <a href="https://www.[example.com]/" target="_top">Home</a> | 
                  <a href="https://www.[example.com]/disclaimer.html" target="_self">Disclaimer</a> | 
                  <a href="https://www.[example.com]/privacy.html" target="_self">Privacy Policy</a>
                </p>
                <p>CSS Out! © 2012. All Rights Reserved.
                </p>
              </div>
              <div class="cleared">
              </div>
            </div>
          </div>
          <div class="cleared">
          </div>
        </div>
      </div>
      <div class="cleared">
      </div>
      <p class="cssout-page-footer">
      </p>
    </div>
  </body>
</html>
