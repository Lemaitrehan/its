layui.use(['form', 'layedit', 'laydate', 'upload'], function () {
    var form = layui.form,
        layer = layui.layer,
        layedit = layui.layedit,
        upload = layui.upload,
        laydate = layui.laydate;

    /**
     * 从数组中删除某个内容
     * @param val
     */
    Array.prototype.remove = function (val) {
        var index = this.indexOf(val);
        if (index > -1) {
            this.splice(index, 1);
        }
    };

    //普通图片上传
    var uploadInst = upload.render({
            elem: '#upload_img'
            , url: upload_avatar
            , data: {'number': number},
            before: function (obj) {
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    $('#avatar_path').attr('src', result); //图片链接（base64）
                });
            }
            ,
            done: function (res) {
                //如果上传失败
                if (res.code > 0) {
                    return layer.msg('上传失败');
                }
                //上传成功
            }
            ,
            error: function () {
                //演示失败状态，并实现重传
                var demoText = $('#demoText');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function () {
                    uploadInst.upload();
                });
            }
        })
    ;


    //日期
    laydate.render({
        elem: '#date'
    });


    $('#select').on('click', function () {
        $('#where_type').attr('name', $('#where_field').children('option:selected').val());
    });

    $('#save_button').on('click', function () {
        $.post(list_url, {
                'uid_type': $('#uid_type').val(),
                'study_status': $('#study_status').val(),
                'number': number
            },
            function (data) {
                api_comm_handle(data);
            }, "json");
    });

    //基础身份------课程信息和费用信息
    $('#info_button').on('click', function () {

        $.post(info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }

                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //加报查询------查看加报课程与费用
    $('#append_info_button').on('click', function () {

        $.post(append_entry_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //休学登记------查看休学课程与休学前费用信息
    $('#out_school_info_button').on('click', function () {

        $.post(end_school_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //休学登记------查看休学课程与休学前费用信息
    $('#re_school_info_button').on('click', function () {

        $.post(re_school_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //休学登记------查看休学课程与休学前费用信息
    $('#end_school_info_button').on('click', function () {

        $.post(end_school_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });


    //退课登记------查看退课详情
    $('#cancel_course_info_button').on('click', function () {

        $.post(cancel_course_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //转课登记------查看转课详情
    $('#transfer_course_info_button').on('click', function () {

        $.post(transfer_course_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });

    //学籍档案------查看学籍详情
    $('#student_record_info_button').on('click', function () {

        $.post(student_record_info_url, {
                'number': number
            },
            function (data) {
                if (api_comm_handle(data) == false) {
                    api_comm_handle(data, 1);
                    return false;
                }
                var parent_div = $(parent.document.getElementsByTagName('div')).children('.l-tab-content');
                //页面层
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim', //加上边框
                    area: [
                        parent_div.width() + 'px',

                        parent_div.height() + 'px'
                    ], //宽高
                    content: data.data.html_content
                });
            }, "json");

    });



});

