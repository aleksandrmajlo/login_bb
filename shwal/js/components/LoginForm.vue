<template>
    <form action="#" method="post">
        <div v-if="!isCode&&!showSelectUsers">
            <label>{{$t('login_phone')}}</label>
            <div class="input-group">
                <span class="fa fa-phone" aria-hidden="true"></span>
                <imask-input
                        v-model="phone"
                        :mask="'+{38}(000)000-00-00'"
                        :unmask="true"
                        type="text"
                        :lazy="false"
                        @complete="onComplete"
                        @accept="onAccept"
                        :placeholder="$t('login_phone_plac')"
                />
            </div>
        </div>

        <div v-if="isCode">
            <label>{{$t('login_code')}}</label>
            <div class="input-group">
                <span class="fa fa-commenting-o" aria-hidden="true"></span>
                <imask-input
                        v-model="code"
                        :mask="'000000'"
                        :unmask="true"
                        type="text"
                        :lazy="false"
                        :min="100000"
                        :max="999999"
                        @complete="onCompleteCode"
                        @accept="onAcceptCode"
                        :placeholder="$t('login_code')"
                />
            </div>
        </div>

        <div v-if="showSelectUsers">
            <label>{{$t('select_roles')}}</label>
            <div class="input-group input-group-radio ">
                <span v-for="(user,index) in users" :key="index">
                     <label :for="'radio'+index">{{user.role}}</label>
                     <input :id="'radio'+index" type="radio" v-model="role" :value="user.id">
                </span>
            </div>
        </div>
        <button v-if="phoneValid&&!isCode&&!showSelectUsers" @click.prevent="send"
                :disabled="disabled"
                class="btn btn-danger btn-block" type="submit">{{$t('login_enter')}}
        </button>
        <button v-if="isCode&&isCodeValid&&!showSelectUsers" @click.prevent="sendCode"
                :disabled="disabled"
                class="btn btn-danger btn-block" type="submit">{{$t('login_enter')}}
        </button>
        <button v-if="showSelectUsers" @click.prevent="sendUser"
                :disabled="disabled"
                class="btn btn-danger btn-block" type="submit">{{$t('login_enter')}}
        </button>
    </form>
</template>
<script>
    import {IMaskComponent} from 'vue-imask';

    export default {
        name: "LoginForm",
        components: {
            'imask-input': IMaskComponent
        },
        data() {
            return {
                id_sms: "",
                phone: "",
                phoneValid: false,
                code: "",
                isCode: false,
                isCodeValid: false,
                disabled: false,

                users: [],
                showSelectUsers: false,
                role: ""
            }
        },
        methods: {
            onAccept() {
                this.phoneValid = false
            },
            onComplete() {
                this.phoneValid = true
            },
            send() {
                this.disabled = true;
                axios.post('/generateCodeLogin', {
                    phone: this.phone
                })
                    .then(response => {
                        if (response.data.suc) {
                            this.isCode = true;
                            this.id_sms = response.data.id_sms;
                            return true;
                        }
                        if (!response.data.suc && typeof response.data.notUser !== "undefined") {
                            this.showShwal('error', this.$t('notUserLogin'))
                        }
                    })
                    .catch(error => {
                        this.showShwal('error', this.$t('error'))
                    })
                    .then(() => {
                        this.disabled = false;
                    })
            },
            onAcceptCode() {
                this.isCodeValid = false;
            },
            onCompleteCode() {
                this.isCodeValid = true;
            },
            sendCode() {
                this.disabled = true;
                axios.post('/checkCodeLogin', {
                    phone: this.phone,
                    code: this.code,
                    id_sms: this.id_sms
                })
                    .then(response => {
                        if (typeof response.data.users !== "undefined") {
                            this.users = response.data.users;
                            this.isCode = false;
                            this.showSelectUsers = true;
                        } else {
                            if (response.data.suc) {
                                location.href = response.data.url;
                            } else {
                                this.showShwal('info', this.$t('SmsCodeErrorEnter'))
                            }

                        }
                    })
                    .catch(error => {
                        this.showShwal('error', this.$t('error'))
                    })
                    .then(() => {
                        this.disabled = false;
                    })
            },
            // отправка выбраного пользователя  по роли
            sendUser() {
                if (this.role == "") {
                    this.showShwal('info', this.$t('select_roles'))
                } else {
                    this.disabled = true;
                    axios.post('/checkRole', {
                        role: this.role,
                    }).then(response => {
                        if (response.data.suc) {
                            location.href = response.data.url;
                        } else {
                            this.showShwal('info', this.$t('SmsCodeErrorEnter'))
                        }
                    }).catch(error => {
                        this.showShwal('error', this.$t('error'))
                    }).then(() => {
                        this.disabled = false;
                    })
                }
            }

        },
    }
</script>