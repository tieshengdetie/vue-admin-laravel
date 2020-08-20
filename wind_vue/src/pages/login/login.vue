<template>
    <div class="login-container">
        <el-form class="login-form" autoComplete="on" :model="loginForm" :rules="loginRules" ref="loginForm"
                 label-position="left">
            <h3 class="title">后台管理系统</h3>
            <el-form-item prop="username">
                <span class="svg-container svg-container_login">
                    <svg-icon icon-class="user"/>
                </span>
                <el-input name="username" type="text" v-model="loginForm.username" autoComplete="on"
                          placeholder="username"/>
            </el-form-item>
            <el-form-item prop="password">
                <span class="svg-container">
                    <svg-icon icon-class="password"></svg-icon>
                </span>
                <el-input name="password" :type="pwdType" @keyup.enter.native="login" v-model="loginForm.password"
                          autoComplete="on"
                          placeholder="password"></el-input>
                <span class="show-pwd" @click="showPwd"><svg-icon icon-class="eye"/></span>
            </el-form-item>
            <el-row>
                <el-col :span="17">
                    <el-form-item prop="captcha">
                 <span class="svg-container">
                    <svg-icon icon-class="captcha3"></svg-icon>
                </span>
                        <el-input v-model="loginForm.captcha"></el-input>

                    </el-form-item>
                </el-col>

                <el-col :span="7">

                    <span class="captcha_span">
                    <img class="captcha_img" @click="changeCodeImg" :src="captcha.img" alt="图片验证码">
                    </span>


                </el-col>


            </el-row>


            <el-form-item>
                <el-button type="primary" style="width:100%;" :loading="loading"
                           @click.native.prevent="login('loginForm')">
                    登录
                </el-button>
            </el-form-item>
            <div class="tips"></div>
        </el-form>
    </div>
</template>

<script>

    export default {
        data() {
            const validateUsername = (rule, value, callback) => {
                if (value.length < 5) {
                    callback(new Error('请输入正确的用户名'))
                } else {
                    callback()
                }
            }
            const validatePass = (rule, value, callback) => {
                if (value.length < 5) {
                    callback(new Error('密码不能小于5位'))
                } else {
                    callback()
                }
            }
            return {
                loginForm: {
                    username: '',
                    password: '',
                    captcha: '',
                    key:''
                },
                captcha: {
                    img: '',
                    key: ''
                },
                loginRules: {
                    username: [
                        {
                            required: true,
                            trigger: 'blur',
                            validator: validateUsername
                        }
                    ],
                    password: [
                        {required: true, trigger: 'blur', validator: validatePass}
                    ],
                    captcha: [
                        {required: true, message: '请输入验证码', trigger: 'blur'}
                    ]
                },
                loading: false,
                pwdType: 'password'
            }
        },
        mounted() {
            this.changeCodeImg();
        },
        methods: {
            showPwd() {
                if (this.pwdType === 'password') {
                    this.pwdType = ''
                } else {
                    this.pwdType = 'password'
                }
            },
            login(formName) {
                let _that = this

                _that.$refs[formName].validate((valid) => {

                    if (valid) {
                        _that.$http.post('SystermApi/login',_that.loginForm).then((res) => {

                            let token = res.token
                            _that.$store.commit('LOGIN_IN', token)
                            _that.$router.replace('/')
                        }).catch(function () {
                            _that.changeCodeImg()
                        })
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                })


            },
            //后端获取验证码
            changeCodeImg() {
                let _that = this;
                _that.$http.get('SystermApi/getCaptcha',{}).then(function (res) {

                    _that.captcha = res
                    _that.loginForm.key = res.key
                });
            },
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    $bg: #2d3a4b;
    $light_gray: #eee;

    /* reset element-ui css */
    .login-container {
        .el-input {
            display: inline-block;
            height: 47px;
            width: 90%;

            input {
                background: transparent;
                border: 0px;
                -webkit-appearance: none;
                border-radius: 0px;
                padding: 12px 5px 12px 15px;
                color: $light_gray;
                height: 47px;

                &:-webkit-autofill {
                    -webkit-box-shadow: 0 0 0px 1000px $bg inset !important;
                    -webkit-text-fill-color: #fff !important;
                }
            }
        }

        .el-form-item {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            color: #454545;
        }
    }
</style>

<style rel="stylesheet/scss" lang="scss" scoped>
    $bg: #2d3a4b;
    $dark_gray: #889aa4;
    $light_gray: #eee;
    .login-container {
        position: fixed;
        height: 100%;
        width: 100%;
        background-color: $bg;

        .login-form {
            position: absolute;
            left: 0;
            right: 0;
            width: 520px;
            padding: 35px 35px 15px 35px;
            margin: 120px auto;
        }

        .tips {
            font-size: 14px;
            color: #fff;
            margin-bottom: 10px;

            span {
                &:first-of-type {
                    margin-right: 16px;
                }
            }
        }

        .svg-container {
            padding: 6px 5px 6px 15px;
            color: $dark_gray;
            vertical-align: middle;
            width: 30px;
            display: inline-block;

            &_login {
                font-size: 20px;
            }
        }

        .title {
            font-size: 26px;
            font-weight: 400;
            color: $light_gray;
            margin: 0px auto 40px auto;
            text-align: center;
            font-weight: bold;
        }

        .show-pwd {
            position: absolute;
            right: 10px;
            top: 7px;
            font-size: 16px;
            color: $dark_gray;
            cursor: pointer;
            user-select: none;
        }

        .fontcontainer {
            color: #889aa4;
            padding-left: 10px;
        }

        .captcha_span {
            display: block;
            margin-top: 4px;
            margin-left: 6px;
        }

        .captcha_img {
            border-radius: 2px;
            height: 48px;
            width: 125px;
        }

    }
</style>
