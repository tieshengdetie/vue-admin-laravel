<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ajax图片上传</title>
    <link href="http://libs.baidu.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
​
<form id="photoForm" class="form-horizontal">
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">上传资源：</label>
        <div class="col-sm-10">
            <input type="file" name="resource"/>
        </div>
    </div>
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">上传封面图片：</label>
        <div class="col-sm-10">
            <input type="file" name="face_image"/>
        </div>
    </div>
    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">名称：</label>
        <div class="col-sm-4">
            <input type="text" name="name">
        </div>
    </div>
    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">类型：</label>
        <div class="col-sm-4">
            <input type="text" name="type">
        </div>
    </div>
    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">作者：</label>
        <div class="col-sm-4">
            <input type="text" name="author">
            <input type="hidden" name="category_id" value="2">

        </div>
    </div>
    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">简介：</label>
        <div class="col-sm-4">
            <textarea name="desc" id="" cols="30" rows="10"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">
            <input type="button" value="提交" onclick="doUpload()" />
            <input type="button" value="发送ajax" onclick="doAjax()" />
        </label>
    </div>
    ​

    <div class="form-group">
        <label for="intro" class="col-sm-2 control-label">
            <input type="button" value="上传" id="upload" />
        </label>
    </div>
</form>
​

​
</body>

</html>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="http://libs.baidu.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<script src="//v.polyv.net/file/plug-in-v2/polyv-upload.js"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
    });
    $(function(){
        polyvUpload();
    });
    function doUpload() {
        var formData = new FormData($("#photoForm")[0]);
        formData.append('topic_ids',[1,2]);
        formData.append('label_ids',[3,4]);
        $.ajax({
            url: "http://www.pxapi.com:8086/api/createResource",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (returndata) {

            },
            error: function (returndata) {
                console.log(returndata);
            }
        });
    }
    function doAjax(){
        var formData ={'name':'教育','parent_id':0,'deep_code':''}
        $.ajax({
            url: "http://www.pxapi.com:8086/api/createCategory",
            type: 'POST',
            data: {'name':'教育','parent_id':0,'deep_code':''},
            success: function (returndata) {

            },
            error: function (returndata) {
                console.log(returndata);
            }
        });
    }
    function polyvUpload(){
        let url = 'http://www.pxApi.com:8086/api/resourceApi/getUploadInfo';
        var obj = {
            uploadButtton: 'upload',
            requestUrl:url,
            component: 'all',
            cataid: 1540201369023,
            luping: 1,
            extra: {
                keepsource: 1,
            }
        };
        upload = new PolyvUpload(obj);
    }
</script>
