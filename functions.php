<?php
// Modified from Paul Robert Lloyd's Barebones
// Config options
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$styleguidePath = '/';
$patternsPath = $rootPath.'/pattern/';
$cssPath = $rootPath.'/style/css/';

// Provide a filter for excluding hidden .git or .svn folders from the inc() function
class ExcludeFilter extends RecursiveFilterIterator {
    public static $FILTERS = array(
        '.svn',
        '.git'
    );
    public function accept() {
        // Check if a file is within one of the folders listed in the exclude list
        foreach(self::$FILTERS as $filter) {
            if(strpos($this->current()->getPath(),$filter)) {
                return false;
            }
        }
        return true;
    }
}

function inc($type,$name) {
    global $patternsPath; 
    global $styleguidePath;
    $filePath = $patternsPath;
    // Determine which directory to look in
    if($type=='article') {
        $filePath = $filePath.'article';
    } else {
        $filePath = $filePath;
    }
    // Iterate over the appropriate path
    $objects = new RecursiveIteratorIterator(new ExcludeFilter(new RecursiveDirectoryIterator($filePath)));
    foreach($objects as $objName => $object) {
        $pos = stripos($objName, $name);
        if ($pos) {
            include($objName); // Include the fragment if the file is found
            break;
        }
    }
}

function displayPatchwork($dir) {
    global $patternsPath;
    global $styleguidePath;
    $ffs = scandir($dir);
    foreach($ffs as $ff) {
        if($ff != '.' && $ff != '..') {
            $fName = basename($ff,'.html');
            $fPlain = ucwords(str_replace('-', '. ', $fName));
            $pathToFile = str_replace($patternsPath, '', $dir);
            if(is_dir($dir.'/'.$ff)) { // If main section
                if ($fName == 'article') {
                    echo "<section class=\"xx-section\" id=\"".$fName."\">\n";
                    echo "    <h2 class=\"xx-section-title\">".$fPlain."</h2>\n";
                } else {
                    echo "<section class=\"xx-section\" id=\"".$fName."\">\n";
                    echo "    <h2 class=\"xx-section-title\">".$fPlain."</h2>\n";
                }
            } else { // If sub section
                if(pathinfo($ff,PATHINFO_EXTENSION) == 'html' && $fName != 'header' && $fName != 'head' && $fName != 'foot') { // Skip non-HTML files
                    echo "<div class=\"xx-pattern\" id=\"".$fName."\">\n";
                    echo "\n";
                    include $dir.'/'.$ff;
                    echo "\n";
                    echo "</div>\n\n";
                }
            }
            if(is_dir($dir.'/'.$ff)) { // If main section
                displayPatchwork($dir.'/'.$ff);
                echo "</section>\n\n";
            }
        }
    }
}

function displayPatterns($dir) {
    global $patternsPath;
    global $styleguidePath;
    $ffs = scandir($dir);
    foreach($ffs as $ff) {
        if($ff != '.' && $ff != '..') {
            $fName = basename($ff,'.html');
            $fPlain = ucwords(str_replace('-', '. ', $fName));
            $pathToFile = str_replace($patternsPath, '', $dir);
            
            if(is_dir($dir.'/'.$ff)) { // If main section
                echo "<section class=\"xx-section\" id=\"".$fName."\">\n";
                echo "    <h3 class=\"xx-section-title\">".$fPlain."</h3>\n\n";
            } else { // If sub section
                if(pathinfo($ff,PATHINFO_EXTENSION) == 'html' && $ff != 'foot.html') { // Skip non-HTML files
                    echo "<div class=\"xx-pattern\" id=\"".$fName."\">\n";
                    echo "    <details class=\"xx-pattern-details\">\n";
                    echo "        <summary class=\"xx-pattern-name\">".$fName." <a class=\"xx-pattern-link\" rel=\"bookmark\" href=\"".$styleguidePath."?url=".$pathToFile."/".$ff."\" title=\"View just this pattern\">#</a></summary>\n";
                    echo "            <pre class=\"xx-pattern-markup language-markup\"><code>".htmlspecialchars(@file_get_contents($dir.'/'.$ff))."</pre></code>\n";
                    echo "        <pre class=\"xx-pattern-usage\"><strong>Usage:</strong> ".htmlspecialchars(@file_get_contents($dir.'/'.str_replace('.html','.txt',$ff)))."</pre>\n";
                    echo "    </details>\n";
                    echo "    <div class=\"xx-pattern-preview\">\n";
                    include $dir.'/'.$ff;
                    echo "    </div>\n";
                    echo "</div>\n\n";
                }
            }
            if(is_dir($dir.'/'.$ff)) { // If main section
                displayPatterns($dir.'/'.$ff);
                echo "        </div>\n";
                echo "    </article>\n";
                echo "</section>\n\n";
            }
        }
    }
}

function displayOptions($dir) {
    global $patternsPath;
    global $styleguidePath;
    $ffs = scandir($dir);
    foreach($ffs as $ff) {
        if($ff != '.' && $ff != '..') {
            $fName = basename($ff,'.html');
            $fPlain = ucwords(str_replace('-', '. ', $fName));
            $pathToFile = str_replace($patternsPath, '', $dir);
            if(is_dir($dir.'/'.$ff)) { // If main section
                echo "    <option value=\"#".$fName."\">".$fPlain."</option>\n";
            } else { // If sub section
                if(pathinfo($ff,PATHINFO_EXTENSION) == 'html' && $ff != 'foot.html') { // Skip non-HTML files
                    echo "    <option value=\"#".$fName."\">&#160;&#160;&#160;&#160;".$fName."</option>\n";
                }
            }
            if(is_dir($dir.'/'.$ff)) {
                displayOptions($dir.'/'.$ff);
            }
        }
    }
}

function sanipath( $path ) {
    global $patternsPath;
    global $styleguidePath;

    $saniPath = str_replace( "../", "", $path );
    $saniPath = str_replace( ";", "", $saniPath );
    $saniPath = htmlspecialchars( $saniPath );

    $saniFile = preg_replace( "/\.[^$]*/", "", $saniPath);
    $ret = $saniFile . ".html";

    return realpath( $ret ) ? $ret : false;
}

function include_pattern( $path, $error ) {
    try {
        $exists = realpath( sanipath( $path ) );

        if ( !$exists ) {
            throw new Exception( $error );
        }
        include( $exists );
    } catch (Exception $e) {
        echo $e -> getMessage();
    }
}

?>