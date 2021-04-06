<template>
    <div class="main-div">

        <div class="header">
            <div class="header-banner">

            </div>
<!--            <div class="header-banner w">-->
<!--                <img src="../../../assets//image/front/rs_banner.gif" alt="">-->
<!--            </div>-->
            <div class="header-box w">
                <div class="l-home">
                    <ul>
                        <li><a href="">首页</a></li>
                        <li><a href="">媒体浏览</a></li>
                        <li><a href="">两个责任</a></li>
                        <li><a href="">企业简介</a></li>
                        <li><a href="">企业文化</a></li>
                        <li><a href="">邮箱查询</a></li>
                        <li><a href="">部室导航</a></li>
                        <li><a href="">文件纪要</a></li>
                    </ul>
                </div>
            </div>

        </div>
        <div class="content-body"></div>
        <div class="footer"></div>
    </div>
</template>

<script>
export default {
    name: "index"
}
</script>

<style lang="scss" rel="stylesheet/scss" scoped>
$libg:"../../../assets/";
.main-div {
    .w{
        width: 1100px;
    }
    .header{
        background: url('#{$libg}/image/front/bg_head.png') repeat-x 0 0 ;

        .header-banner{
            margin: 0 auto;
            height: 162px;
            background: url('#{$libg}/image/front/bg_head2.jpg') no-repeat center top ;
            img{
                width: 1100px;
                height: 100%;
            }

        }
        .header-box{

            font-size: 16px;
            color: #fff;
            font-family: microsoft Yahei;
            margin: 0 auto;
            //background: url('#{$libg}/image/front/bg_menu.png') repeat-x 0 0;
            //background-color: #9F0007;
            .l-home{
                height: 50px;
                ul {
                    /*width:50%;*/
                    height: 50px;
                    clear:both;
                    li{
                        float: left;
                        width:135px;
                        padding-right: 2px;
                        background: url('#{$libg}/image/front/bg_ulli.png') right center no-repeat;
                        a{
                            display: block;
                            text-align: center;
                            color: #FFFFFF;
                            text-decoration: none;
                            height: 50px;
                            line-height: 50px;

                        }
                        a:hover{
                            background-color: #650004;
                        }
                    }

                }
            }

        }


    }

}

</style>
