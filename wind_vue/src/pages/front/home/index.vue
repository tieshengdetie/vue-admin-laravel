<template>
    <div class="main-div">

        <div class="header">
            <div class="header-box">
                <div class="l-home">
                    <ul>
                        <li><a href="">logo</a></li>
                        <li><a href="">设为首页</a></li>
                        <li><a href="">手机版</a></li>
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

.main-div {
    border: 1px solid red;

    .header{
        .header-box{
            background-color: #9f0007;
            font-size: 16px;
            color: #fff;
            font-family: microsoft Yahei;
            .l-home{
                height: 50px;
                width:1200px;
                margin: 0 auto;
                ul {
                    width:50%;
                    height: 50px;
                    clear:both;
                    li{
                        float: left;
                        width:135px;
                        padding-right: 2px;
                        //background: url(../../../../assets/image/front/dyw920_xz.png) 14px center no-repeat;
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
