<?php
        /*
                ## Image Upload and Resize  / File Upload ##
                ################################
                Defaults
                        Upload_and_resize(
                                FileArray,
                                Path=null, <== Directory /Files will be created 
                                MaxSize='false',
                                Scale=100,
                                thumbs=null,
                                Name=null,
                                O_format = 'json'
                        )
                ***********************************
                You can Mix File Types if needed
                ***********************************
                
                FileArray: your uploaded image / file array example
                        $_FILES['images']
                        
                Path:   if path if set to null the folder files will be created to root directory of web_root
                        enter absolute path and or if the folder does not exist it will be created if absolute absolute path of directory above exists. Server DocumentRoot is established so no need to guess.
                        null is default (/Files will be created)
                        Ex: 'Mypath' or 'myfirstPath/myPath'
                        
                MaxSize: can be a single numeric or array(width,height) for example
                        300 :                   single numeric will scale to max w/h of value
                        array(400,700):         will size the image to numerics (possible distortion)
                        array('false',700):     will resize to height numeric (no distortion)
                        array(400,'false'):     will resize to width numeric (no distortion)
                        'false':                default(when used Scale will become active)
                        
                Scale: if(Maxsize == false) you can scale from 0- 100
                        100 is default
                        
                thumbs: thumbnails if set to null will bypass, but if thumbnails are required enter your numeric value
                        null is default
                        
                        
                Name:   if name is null 'Save_' plus a random number will be auto generated Ex. Save_25465.jpg
                        null is default
                        
                O_format: is the return format of the uploaded files to store in the following
                        'json' : Defaulted. will json_encode the php array
                        'phpA' : will encode a php array
                        'phpSA' : will encode a php serialize array
                        
                ***********************************
                code Example S:
                        $imgu = new ImgUnR();
                        
                        $imgu->Upload_and_resize($_FILES['images']); 
                                will scale to 100% no thumbnails or not image to directory /Files
                                
                        $imgu->Upload_and_resize($_FILES['images'],'myfiles'); 
                                will scale to 100% no thumbnails or not image to directory /myfiles
                                
                        $imgu->Upload_and_resize($_FILES['images'],'myfiles','false',null,150); 
                                will scale to 100% with thumbnails 150px max(w/h) to directory /myfiles
                                
                        $imgu->Upload_and_resize($_FILES['images'],'myfiles',array(600,800),null,150,null,'phpA'); 
                                to directory /myfiles
                        
                        <form method="POST" enctype='multipart/form-data'>
                        <input type="file" name="images[]" multiple />
                        <input type="hidden" name="test" value="test" />
                        <input type="submit"/>
                        </form>
                        
                code Example E:
                copy&paste code S:
                        <?php
                                include('ImgUnR.php');
                                if($_POST){
                                        $imgu = new ImgUnR();
                                        echo $imgu->Upload_and_resize($_FILES['images']); 
                                }
                        ?>
                        <form method="POST" enctype='multipart/form-data'>
                                <input type="file" name="images[]" multiple />
                                <input type="hidden" name="test" value="test" />
                                <input type="submit"/>
                        </form>
                copy&paste code E:
                
                
        */
        class ImgUnR{
                public function mime_content_type($filename) {
        
                $mime_types = array(
        
                    'txt' => 'text/plain',
                    'htm' => 'text/html',
                    'html' => 'text/html',
                    'php' => 'text/html',
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'json' => 'application/json',
                    'xml' => 'application/xml',
                    'swf' => 'application/x-shockwave-flash',
                    'flv' => 'video/x-flv',
        
                    // images
                    'png' => 'image/png',
                    'jpe' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'bmp' => 'image/bmp',
                    'ico' => 'image/vnd.microsoft.icon',
                    'tiff' => 'image/tiff',
                    'tif' => 'image/tiff',
                    'svg' => 'image/svg+xml',
                    'svgz' => 'image/svg+xml',
        
                    // archives
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    'exe' => 'application/x-msdownload',
                    'msi' => 'application/x-msdownload',
                    'cab' => 'application/vnd.ms-cab-compressed',
        
                    // audio/video
                    'mp3' => 'audio/mpeg',
                    'qt' => 'video/quicktime',
                    'mov' => 'video/quicktime',
        
                    // adobe
                    'pdf' => 'application/pdf',
                    'psd' => 'image/vnd.adobe.photoshop',
                    'ai' => 'application/postscript',
                    'eps' => 'application/postscript',
                    'ps' => 'application/postscript',
        
                    // ms office
                    'doc' => 'application/msword',
                    'rtf' => 'application/rtf',
                    'xls' => 'application/vnd.ms-excel',
                    'ppt' => 'application/vnd.ms-powerpoint',
        
                    // open office
                    'odt' => 'application/vnd.oasis.opendocument.text',
                    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
                );
        
                $ext = strtolower(array_pop(explode('.',$filename)));
                if (array_key_exists($ext, $mime_types)) {
                    return $mime_types[$ext];
                }
                elseif (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME);
                    $mimetype = finfo_file($finfo, $filename);
                    finfo_close($finfo);
                    return $mimetype;
                }
                else {
                    return 'application/octet-stream';
                }
             }
             public function uploadFiles($folder, $formdata, $itemId = null) {
                        // setup dir names absolute and relative
                        $folder_url = WWW_ROOT.$folder;
                        $rel_url = $folder;
                        
                        // create the folder if it does not exist
                        if(!is_dir($folder_url)) {
                                mkdir($folder_url);
                        }
                                
                        // if itemId is set create an item folder
                        if($itemId) {
                                // set new absolute folder
                                $folder_url = WWW_ROOT.$folder.'/'.$itemId; 
                                // set new relative folder
                                $rel_url = $folder.'/'.$itemId;
                                // create directory
                                if(!is_dir($folder_url)) {
                                        mkdir($folder_url);
                                }
                        }
                        
                        // list of permitted file types, this is only images but documents can be added
                        $permitted = array('image/gif','image/jpeg','image/pjpeg','image/png');
                        
                        // loop through and deal with the files
                        foreach($formdata as $file) {
                                // replace spaces with underscores
                                $filename = str_replace(' ', '_', $file['name']);
                                // assume filetype is false
                                $typeOK = false;
                                // check filetype is ok
                                foreach($permitted as $type) {
                                        if($type == $file['type']) {
                                                $typeOK = true;
                                                break;
                                        }
                                }
                                
                                // if file type ok upload the file
                                if($typeOK) {
                                        // switch based on error code
                                        switch($file['error']) {
                                                case 0:
                                                        // check filename already exists
                                                        if(!file_exists($folder_url.'/'.$filename)) {
                                                                // create full filename
                                                                $full_url = $folder_url.'/'.$filename;
                                                                $url = $rel_url.'/'.$filename;
                                                                // upload the file
                                                                $success = move_uploaded_file($file['tmp_name'], $url);
                                                        } else {
                                                                // create unique filename and upload file
                                                                ini_set('date.timezone', 'Europe/London');
                                                                $now = date('Y-m-d-His');
                                                                $full_url = $folder_url.'/'.$now.$filename;
                                                                $url = $rel_url.'/'.$now.$filename;
                                                                $success = move_uploaded_file($file['tmp_name'], $url);
                                                        }
                                                        // if upload was successful
                                                        if($success) {
                                                                // save the url of the file
                                                                $result['urls'][] = $url;
                                                        } else {
                                                                $result['errors'][] = "Error uploaded $filename. Please try again.";
                                                        }
                                                        break;
                                                case 3:
                                                        // an error occured
                                                        $result['errors'][] = "Error uploading $filename. Please try again.";
                                                        break;
                                                default:
                                                        // an error occured
                                                        $result['errors'][] = "System error uploading $filename. Contact webmaster.";
                                                        break;
                                        }
                                } elseif($file['error'] == 4) {
                                        // no file was selected for upload
                                        $result['nofiles'][] = "No file Selected";
                                } else {
                                        // unacceptable file type
                                        $result['errors'][] = "$filename cannot be uploaded. Acceptable file types: gif, jpg, png.";
                                }
                        }
                return $result;
             }
             public function create_thumbnail($img_name,$filename,$new_w,$new_h){
                                //get image extension.
                                $ext=$this->getExtension($img_name);
                                //creates the new image using the appropriate function from gd library
                                if(!strcmp("jpg",$ext) || !strcmp("jpeg",$ext))
                                                $src_img=imagecreatefromjpeg($img_name);
        
                                if(!strcmp("png",$ext))
                                                $src_img=imagecreatefrompng($img_name);
                                                //gets the dimmensions of the image
                                $old_x=imageSX($src_img);
                                $old_y=imageSY($src_img);
        
                                $ratio1=$old_x/$new_w;
                                $ratio2=$old_y/$new_h;
                                if($ratio1>$ratio2)	{
                                                $thumb_w=$new_w;
                                                $thumb_h=$old_y/$ratio1;
                                }
                                else	{
                                                $thumb_h=$new_h;
                                                $thumb_w=$old_x/$ratio2;
                                }
        
                                // we create a new image with the new dimmensions
                                $dst_img=ImageCreateTrueColor($new_w,$new_h);
        
                                // resize the big image to the new created one
                                imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_w,$new_h,$old_x,$old_y);
        
                                // output the created image to the file. Now we will have the thumbnail into the file named by $filename
                                if(!strcmp("png",$ext))
                                                imagepng($dst_img,$filename);
                                else
                                                imagejpeg($dst_img,$filename);
        
                                //destroys source and destination images.
                                imagedestroy($dst_img);
                                imagedestroy($src_img);
             }
             public function getExtension($str)
             {
                 $i = strrpos($str,".");
                 if (!$i) { return ""; }
                 $l = strlen($str) - $i;
                 $ext = substr($str,$i+1,$l);
                 return $ext;
             }
             
             public function resize($mx,$o,$s=30,$t=null,$p=null,$n=null){
                        $f=$o['tmp_name'];
                        
                        $mime = explode('/',$o['type']);
                        $mime = $mime[1];
                        $img = false;
                        $files = array();
                        $p = $p.'/';
                        /**
                                max,specs,original,scale,thumbnail
                        **/
                        switch($mime){
                                case 'jpeg': $o = imagecreatefromjpeg($f); $img = true; break;
                                case 'gif':  $o = imagecreatefromgif($f);  $img = true; break;
                                case 'png':  $o = imagecreatefrompng($f);  $img = true; break;
                                case 'bmp':  $o = imagecreatefromwbmp($f); $img = true; break;
                                default: $img = false;
                        }
                        if($img === true){
                                $size = $spc = getimagesize($f);
                                if( ($spc[0]/$spc[1]) >= 1){
                                        if($mx == 'false'){
                                                $nW = $spc[0]*($s/100); $nH = $spc[1]*($s/100);
                                        } elseif(is_array($mx)){
                                                $nW = ($mx[0] == 'false')?($spc[0]*($mx[1]/$spc[1])):$mx[0];
                                                $nH = ($mx[1] == 'false')?($spc[1]*($mx[0]/$spc[0])):$mx[1];
                                        } else{
                                                $nW = $mx; $nH = $spc[1]*($mx/$spc[0]);
                                        }
                                        if($t != null){
                                                $tW = $t; $tH = $spc[1]*($t/$spc[0]);
                                        }
                                }else{
                                        if($mx == 'false'){
                                                $nW = $spc[0]*($s/100); $nH = $spc[1]*($s/100);
                                        } elseif(is_array($mx)){
                                                $nW = ($mx[0] == 'false')?($spc[0]*($mx[1]/$spc[1])):$mx[0];
                                                $nH = ($mx[1] == 'false')?($spc[1]*($mx[0]/$spc[0])):$mx[1];
                                                ## W/H Check if false revert to scale
                                                if($mx[0] == 'false' && $mx[1] == 'false'){
                                                        $nW = $spc[0]*($s/100); $nH = $spc[1]*($s/100);
                                                }
                                                ## if W/H new are equivalent or higher revert to original
                                                elseif($mx[0] != 'false' && $mx[1] != 'false'){
                                                        if($mx[0] >= $spc[0] && $mx[1] >= $spc[1]){
                                                                $nW = $spc[0];
                                                                $nH = $spc[1];
                                                        }
                                                }
                                        } else{
                                                $nW = $spc[0]*($mx/$spc[1]); $nH = $mx;
                                        }
                                        if($t != null){
                                                $tW = $spc[0]*($t/$spc[1]); $tH = $t;
                                        }
                                }
                                $tmp = imagecreatetruecolor($nW, $nH);
                                /**
                                        Thumb Temp Canvas Gen.
                                **/
                                if($t != null){
                                        if(!is_dir($p.'thumbs')){
                                                 mkdir($p.'thumbs');  
                                        }
                                        $tm = imagecreatetruecolor($tW,$tH);
                                        if(($mime == 'gif') || ($mime=='png'))
                                        {
                                                imagealphablending($tm, false);
                                                imagesavealpha($tm,true);
                                                $transparent = imagecolorallocatealpha($tm, 255, 255, 255, 127);
                                                imagefilledrectangle($tm, 0, 0, $tW, $tH, $transparent);
                                                
                                        }
                                        imagecopyresampled($tm,$o,0,0,0,0,$tW, $tH,$spc[0],$spc[1]);
                                        call_user_func_array('image'.$mime,array($tm,$p.'thumbs/'.$n.".$mime"));
                                        imagedestroy($tm);
                                        $t_url = $p.'thumbs/'.$n.".$mime";
                                        $t_enabled = true;
                                }else{
                                        $t_enabled = false;
                                }
                                /* Check if this image is PNG or GIF to preserve its transparency */
                                if(($mime == 'gif') || ($mime=='png'))
                                {
                                        imagealphablending($tmp, false);
                                        imagesavealpha($tmp,true);
                                        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                                        imagefilledrectangle($tmp, 0, 0, $nW, $nH, $transparent);
                                        
                                }
                                imagecopyresampled($tmp,$o,0,0,0,0,$nW, $nH,$spc[0],$spc[1]);
                                ## array Build for images
                                if($img === true){
                                       if($t_enabled == true){
                                               $files[] = array('url'=>$p.$n.".$mime",'thumb'=>$t_url,'type'=>'image');
                                       }else{
                                               $files[] = array('url'=>$p.$n.".$mime",'thumb'=>false,'type'=>'image');
                                       }
                                       
                                }
                                if($n != null){
                                        call_user_func_array('image'.$mime,array($tmp,$p.$n.".$mime"));
                                        imagedestroy($tmp);
                                        //echo '<img src="'.$n.".$mime".'" />';
                                }else{
                                        //header('Content-Type: ' . $size['mime']);
                                        call_user_func_array('image'.$mime,array($tmp));
                                        imagedestroy($tmp);
                                }
                                
                        }else{
                               ## array Build for generic files
                               $ext = explode('.',$o['name']);
                               $extc = count($ext) - 1;
                               $file = move_uploaded_file($f,$p.$n.'.'.$ext[$extc]); 
                               if($file){
                                       $files[] = array('url'=>$p.$n.'.'.$ext[$extc],'type'=>'file');
                               }
                        }
                        //var_dump($files);
                        return $files;
                }
             public function Upload_and_resize($Array,$Path=null,$MaxSize='false',$Scale=100,$thumbs=null,$Name=null,$O_format='json'){
                        /*
                         *      Original Build ==
                         *       for($i=0; $i<count($_FILES['Miss']['name']); $i++){
                         *              resize(array('false',400),$_FILES['Miss']['tmp_name'][$i],30,250,'output/','outputname'.$i);
                         *       }
                         *
                        */
                        $ar = array( 'files'=>array(), 'images'=>array() ); 
                        if($Path == null || !is_dir($Path)){ 
                                if($Path == null){ 
                                        $Path = $_SERVER['DOCUMENT_ROOT'].'/Files';
                                        if(!is_dir($Path)){
                                                mkdir($Path);
                                        } 
                                         
                                } 
                                else{ 
                                        $Path = $_SERVER['DOCUMENT_ROOT'].'/'.$Path;
                                        mkdir($Path); 
                                } 
                        }else{
                                $Path = $_SERVER['DOCUMENT_ROOT'].'/'.$Path;
                        }
                        
                        if(!count($Array) || !is_array($Array['name'])){
                                $array = array();
                                $array[] = array(
                                        'tmp_name'=>$Array['tmp_name'],        
                                        'name'=>$Array['name'],        
                                        'type'=>$Array['type'],    
                                );
                                $Array = $array;
                        }else{
                                $array = array();
                                
                                
                               foreach($Array['name'] as $idx=>$v){
                                       $array[] = array(
                                                'tmp_name'=>$Array['tmp_name'][$idx],        
                                                'name'=>$Array['name'][$idx],        
                                                'type'=>$Array['type'][$idx]    
                                       );
                               } 
                               $Array = $array;
                        }
                        for($i=0; $i<count($Array); $i++){
                               if($Name == null){ $N = 'Save_'.$i;  } elseif(is_array($Name)){ $N = $Name[$i]; } else{ $N = $Name.'_'.rand(1574,98536); }
                               $single_file = array(
                                        'tmp_name'=>$Array[$i]['tmp_name'],        
                                        'name'=>$Array[$i]['name'],        
                                        'type'=>$Array[$i]['type'],    
                               );
                               $r= $this->resize($MaxSize,$single_file,$Scale,$thumbs,$Path,$N);
                               //var_dump($r);
                               if($r[0]['type'] == 'file'){ $ar['files'][] = $r; }else{ $ar['images'][] = $r; }
                        }
                        switch($O_format){
                                case 'json': return json_encode($ar);     break;
                                case 'phpA': return $ar;                break;
                                case 'phpSA': return serialize($ar);    break;
                                default : return true;
                        }
                }       
        }  
        
        
?>
                        
