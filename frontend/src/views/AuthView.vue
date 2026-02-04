<script>
import LogoSVG from "@/svg/LogoSVG.vue";
import axios from "axios";
import {notify} from "@/utils.js";

export default {
    name: "AuthView",
    components: {LogoSVG},
    data () {
        return {
            next: false,
            isReady: false,
            agree: false,
        }
    },
    methods: {
        oninp (ev, iscode = false) {
            let target = iscode ? ev.target.parentNode : ev.target;

            ev.preventDefault();
            if (ev.key >= '0' && ev.key <= '9') {
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
            if (!this.next) {
                let inputs = document.querySelectorAll('.authMain_input input');
                let result = "";
                inputs.forEach(el => {
                    result += el.value;
                });

                this.isReady = result.length === 10 && this.agree;
            } else this.isReady = false;
        },
        async sendCode () {
            this.next = true;

            let phone = "7";
            this.$refs.phone.querySelectorAll('input').forEach(el => {
                phone += el.value;
            });

            notify('your number is ' + phone)

            // const params = new URLSearchParams();
            // params.append("conditions[0][k]", "phone");
            // params.append("conditions[0][v]", "+79823602595");
            //
            // await axios.post("https://kfsamara.ru/api/users/loginSms", params,
            //     {
            //         headers: {
            //             'Authentication': 'a6f29cf6-4d53-4bb9-b188-3f8f1efee4f8',
            //             'Content-Type': 'application/x-www-form-urlencoded',
            //         }
            //     }).then(response => {
            //     console.log(response.data);
            // })
            //     .catch(error => {
            //         console.error(error);
            //     });
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
                    <input @keydown="oninp" v-for="el in 3" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" v-for="el in 3" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" v-for="el in 2" type="number" placeholder="0">
                </div>
                <div>
                    <input @keydown="oninp" v-for="el in 2" type="number" placeholder="0">
                </div>
            </div>
            <div class="authMain_input code" v-else>
                <div v-for="bl in 2">
                    <div v-for="inp in 3">
                        <input @keydown="oninp($event, true)" type="number">
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
        <button @click="sendCode" :class="{active: isReady}">Далее</button>
    </div>
</template>

<style scoped>

</style>