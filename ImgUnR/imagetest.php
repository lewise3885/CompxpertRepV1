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
                        
