<?php

echo 'test test test';


if($_FILES) {
    var_dump($_FILES);exit;
}
?>


<form action="" enctype="multipart/form-data" method="post">
    <input type="file" name="test" />
    <button>Submit</button>
</form>
