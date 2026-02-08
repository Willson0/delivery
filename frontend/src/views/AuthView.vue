<script>
import LogoSVG from "@/svg/LogoSVG.vue";
import axios from "axios";
import {deepParse, notify, sumCart, whatError} from "@/utils.js";
import config from "@/config.json";

export default {
    name: "AuthView",
    components: {LogoSVG},
    data () {
        return {
            next: false,
            isReady: false,
            agree: false,
            isLoading: false,
        }
    },
    mounted () {
    },
    methods: {
        onfoc (ev) {
            let inputs = ev.target.closest('.authMain_input').querySelectorAll('input');

            let string = "";
            inputs.forEach((inp) => string += inp.value);
            if (string.length === 0) inputs[0].focus();
        },
        oninp (ev, iscode = false) {
            let target = iscode ? ev.target.parentNode : ev.target;

            ev.preventDefault();
            if (ev.key === 'Enter') {
                if (this.agree === false) return this.agree = true;
                return this.nextButton();
            }
            else if (ev.key >= '0' && ev.key <= '9') {
                ev.target.value = ev.key;
                this.nextFocus(target, iscode);
            } else if (ev.key === "ArrowRight") this.nextFocus(target, iscode);
            else if (ev.key === "ArrowLeft") this.previousFocus(target, iscode);
            else if (ev.key === "Enter" && this.isReady) this.next = 1;
            else if (ev.key === "Backspace") {
                if (ev.target.value.length === 0) this.previousFocus(target, iscode);
                ev.target.value = "";
            }
            this.updateReady();
        },
        nextFocus (el, iscode = false) {
            let next = el.nextElementSibling;
            let nextContainer = el.parentNode.nextElementSibling;

            if (next != null) iscode ? next.children[0].focus() : next.focus();
            else if (nextContainer != null)
                iscode ? nextContainer.children[0].children[0].focus()
                    : nextContainer.children[0].focus();
        },
        previousFocus (el, iscode = false) {
            let next = el.previousElementSibling;
            let nextContainer = el.parentNode.previousElementSibling;

            if (next != null) iscode ? next.children[0].focus() : next.focus();
            else if (nextContainer != null) {
                if (iscode) {
                    let childrens = nextContainer.children;
                    childrens[childrens.length - 1].children[0].focus()
                } else nextContainer.children[nextContainer.children.length - 1].focus();
            }
        },
        updateReady () {
            requestAnimationFrame(() => {
                if (!this.next) {
                    let inputs = document.querySelectorAll('.authMain_input input');
                    let result = "";
                    inputs.forEach(el => {
                        result += el.value;
                    });

                    this.isReady = result.length === 10 && this.agree;
                } else {
                    let code = "";
                    this.$refs.code.querySelectorAll('input').forEach(el => code += el.value);

                    this.isReady = code.length === 4;
                }
            })
        },
        async sendCode () {
            if (this.isLoading) return;
            this.next = true;

            let phone = "7";
            this.$refs.phone.querySelectorAll('input').forEach(el => {
                phone += el.value;
            });

            this.isLoading = true;
            await axios.post(config.backend + "auth/register", {
                initData: window.Telegram.WebApp.initData,
                phone: phone,
            }).then((response) => {
            }).catch((error) => {
                // if (error.response && error.response.data && error.response.data.message)
                //     if (error.response.data.message.contains('SMS Error. More SMS for phone')) return;
                // notify(whatError(error), 1)
            }).finally(() => this.isLoading = false);
        },
        async checkCode () {
            if (this.isLoading) return;
            this.next = true;

            let code = "";
            this.$refs.code.querySelectorAll('input').forEach(el => code += el.value);

            this.isLoading = true;
            await axios.post(config.backend + "auth/check", {
                initData: window.Telegram.WebApp.initData,
                code: code,
            }).then((response) => {
                let user = deepParse(JSON.stringify(response.data));
                if (user.cart == null) user.cart = [];
                user.cartSum = sumCart(user.cart, user.products);

                this.$store.dispatch("updateUser", user);

                notify("Успешная авторизация!");
                this.$emit("logged");
            }).catch((error) => {
                notify(whatError(error), 1);
                this.$refs.code.querySelectorAll('input').forEach(el => el.value = "");
                this.$refs.code.querySelectorAll('input')[0].focus();
            }).finally(() => this.isLoading = false);
        },
        nextButton () {
            return this.isReady ? (!this.next ? this.sendCode() : this.checkCode()) : null
        }
    },
    computed: {

    },
    watch: {
        next () {
            this.updateReady();
        },
        agree () {
            this.updateReady();
        }
    }
}
</script>

<template>
    <div class="auth">
        <div class="authMain">
            <logo-s-v-g />
            <div class="authMain_title" v-if="!next">Введите номер телефона</div>
            <div class="authMain_title" v-else>Введите код</div>

            <div class="authMain_input" ref="phone" v-if="next === false">
                <span>+ 7</span>
                <div>
                    <input @keydown="oninp" @focus="onfoc" v-for="el in 3" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" @focus="onfoc" v-for="el in 3" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" @focus="onfoc" v-for="el in 2" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" @focus="onfoc" v-for="el in 2" type="number" placeholder="0">
                </div>
            </div>
            <div class="authMain_input code" ref="code" v-else>
                <div v-for="bl in 1">
                    <div v-for="inp in 4">
                        <input @keydown="oninp($event, true)" @focus="onfoc" type="number">
                        <div></div>
                    </div>
                </div>
            </div>
            <div class="authMain_approval" v-if="next === false" @click="agree = !agree">
                <div class="authMain_checkbox" :class="{active: agree}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 15 15" fill="none">
                        <path d="M3.61108 7.88887L6.72219 11.3889L11.6617 2.83331" stroke="black" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="authMain_approval_text">Соглашаюсь на <a>Политику конфиденциальности</a></div>
            </div>
        </div>
        <button @click="nextButton" :class="{active: isReady}">Далее</button>
    </div>
</template>

<style scoped>

</style>