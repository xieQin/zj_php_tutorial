<html>
    <header>
        <meta http-equiv=Content-Type content="text/html;charset=utf-8">
    </header>
    <body>
        <form action="<?=UA("Index/upload_do") ?>" method="post" enctype="multipart/form-data" >
            <input type="hidden" name="action">
            <table>
                <tr>
                    <td>上传图片</td><td><input type="file" name="file" /></td>
                </tr>
                <tr>
                    <td></td><td><input type="submit" name="submit" value="Submit" /></td>
                </tr>
            </table>
        </form>      
    </body>
</html>